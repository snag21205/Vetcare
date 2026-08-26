<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\ExpenseVoucher;
use App\Models\Invoice;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Pet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Khởi tạo các biến trả về
        $stats              = [];
        $todayAppointments  = [];
        $pendingInvoices    = [];
        $recentRecords      = [];
        $recentExpenses     = [];
        $inProgressRecords  = [];
        $lowStockMedicines  = [];
        $expiringMedicines  = [];
        $revenue7Days       = [];

        // 1. ADMIN DASHBOARD
        if ($user->hasRole('admin')) {
            $monthRevenue = (float)Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total_amount');

            $monthExpense = (float)ExpenseVoucher::whereMonth('voucher_date', now()->month)
                ->whereYear('voucher_date', now()->year)
                ->sum('amount');

            $stats = [
                'totalCustomers'    => Customer::count(),
                'totalPets'         => Pet::count(),
                'todayAppointments' => Appointment::whereDate('scheduled_at', today())->count(),
                'monthRevenue'      => $monthRevenue,
                'monthExpense'      => $monthExpense,
                'monthProfit'       => $monthRevenue - $monthExpense,
                'lowStockCount'     => Medicine::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
                'pendingInvoices'   => Invoice::where('status', 'unpaid')->count(),
            ];

            // Cảnh báo kho
            $lowStockMedicines = Medicine::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->take(6)
                ->get(['id', 'name', 'unit', 'stock_quantity', 'low_stock_threshold']);

            $expiringMedicines = Medicine::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->take(6)
                ->get(['id', 'name', 'unit', 'expiry_date']);

            // 5 hồ sơ khám bệnh gần nhất
            $recentRecords = MedicalRecord::with(['pet.customer', 'doctor'])
                ->latest()
                ->take(6)
                ->get();

            // 5 hóa đơn gần nhất
            $pendingInvoices = Invoice::with(['customer', 'medicalRecord.doctor'])
                ->latest()
                ->take(6)
                ->get();

            // Doanh thu 7 ngày gần nhất cho mini chart
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $rev = (float)Invoice::where('status', 'paid')
                    ->whereDate('paid_at', $date)
                    ->sum('total_amount');
                $revenue7Days[] = [
                    'date'  => date('d/m', strtotime($date)),
                    'value' => $rev,
                ];
            }
        }

        // 2. RECEPTIONIST DASHBOARD
        elseif ($user->hasRole('receptionist')) {
            $todayAppointments = Appointment::with(['pet.customer', 'pet', 'doctor', 'medicalRecord'])
                ->whereDate('scheduled_at', today())
                ->orderBy('scheduled_at')
                ->get();

            $stats = [
                'todayAppointments' => $todayAppointments->count(),
                'checkedIn'         => $todayAppointments->where('status', 'checked_in')->count(),
                'pendingPayments'   => Invoice::where('status', 'unpaid')->count(),
                'newCustomersMonth' => Customer::whereMonth('created_at', now()->month)->count(),
            ];

            // Hóa đơn chờ thanh toán
            $pendingInvoices = Invoice::with(['customer', 'medicalRecord.doctor'])
                ->where('status', 'unpaid')
                ->latest()
                ->take(8)
                ->get();
        }

        // 3. VET (BÁC SĨ) DASHBOARD
        elseif ($user->hasRole('vet')) {
            // Lịch hẹn hôm nay của bác sĩ
            $todayAppointments = Appointment::with(['pet.customer', 'pet', 'medicalRecord'])
                ->where('doctor_id', $user->id)
                ->whereDate('scheduled_at', today())
                ->orderBy('scheduled_at')
                ->get();

            // Ca bệnh đang điều trị nhiều ngày (in_progress) của bác sĩ
            $inProgressRecords = MedicalRecord::with(['pet.customer'])
                ->where('doctor_id', $user->id)
                ->where('status', 'in_progress')
                ->latest()
                ->take(6)
                ->get();

            // Các ca khám vừa hoàn thành gần đây
            $recentRecords = MedicalRecord::with(['pet.customer'])
                ->where('doctor_id', $user->id)
                ->latest()
                ->take(6)
                ->get();

            $stats = [
                'myTodayPatients'  => $todayAppointments->count(),
                'waitingPatients'  => $todayAppointments->where('status', 'checked_in')->whereNull('medicalRecord')->count(),
                'completedToday'   => $todayAppointments->where('status', 'completed')->count(),
                'inProgressCount'  => MedicalRecord::where('doctor_id', $user->id)->where('status', 'in_progress')->count(),
            ];
        }

        // 4. ACCOUNTANT (KẾ TOÁN) DASHBOARD
        elseif ($user->hasRole('accountant')) {
            $pendingInvoices = Invoice::with(['customer', 'medicalRecord.doctor'])
                ->where('status', 'unpaid')
                ->latest()
                ->take(10)
                ->get();

            $recentExpenses = ExpenseVoucher::with('creator')
                ->latest('voucher_date')
                ->take(6)
                ->get();

            $todayRev = (float)Invoice::where('status', 'paid')
                ->whereDate('paid_at', today())
                ->sum('total_amount');

            $monthRev = (float)Invoice::where('status', 'paid')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('total_amount');

            $monthExp = (float)ExpenseVoucher::whereMonth('voucher_date', now()->month)
                ->whereYear('voucher_date', now()->year)
                ->sum('amount');

            $stats = [
                'todayRevenue'  => $todayRev,
                'pendingCount'  => Invoice::where('status', 'unpaid')->count(),
                'pendingAmount' => (float)Invoice::where('status', 'unpaid')->sum('total_amount'),
                'monthRevenue'  => $monthRev,
                'monthExpense'  => $monthExp,
                'monthProfit'   => $monthRev - $monthExp,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats'              => $stats,
            'todayAppointments'  => $todayAppointments,
            'pendingInvoices'    => $pendingInvoices,
            'recentRecords'      => $recentRecords,
            'recentExpenses'     => $recentExpenses,
            'inProgressRecords'  => $inProgressRecords,
            'lowStockMedicines'  => $lowStockMedicines,
            'expiringMedicines'  => $expiringMedicines,
            'revenue7Days'       => $revenue7Days,
        ]);
    }
}
