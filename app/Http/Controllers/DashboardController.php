<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        // Lấy user hiện tại từ session
        $user = $request->user();

        // Khởi tạo biến dữ liệu mặc định là null
        $stats              = null;
        $todayAppointments  = null;
        $pendingInvoices    = null;
        $lowStockMedicines  = null;

        // Tùy role mà query khác nhau
        $expiringMedicines  = null;

        if ($user->hasRole('admin')) {
            $stats = [
                'totalCustomers'    => Customer::count(),
                'todayAppointments' => Appointment::whereDate('scheduled_at', today())->count(),
                'monthRevenue'      => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->sum('total_amount'),
                'lowStockCount'     => Medicine::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            ];

            // Lấy danh sách thuốc sắp hết kho
            $lowStockMedicines = Medicine::whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->get(['id', 'name', 'unit', 'stock_quantity', 'low_stock_threshold']);

            // Lấy danh sách thuốc sắp hết hạn
            $expiringMedicines = Medicine::whereNotNull('expiry_date')
                ->whereDate('expiry_date', '<=', now()->addDays(30))
                ->get(['id', 'name', 'unit', 'expiry_date']);
        } elseif ($user->hasRole('receptionist')) {
            $todayAppointments = Appointment::with(['pet.customer', 'pet'])
                ->whereDate('scheduled_at', today())
                ->orderBy('scheduled_at')
                ->get();

            $stats = [
                'todayAppointments' => $todayAppointments->count(),
                'checkedIn'         => $todayAppointments->where('status', 'checked_in')->count(),
                'pendingPayments'   => Invoice::where('status', 'unpaid')->count(),
            ];
        } elseif ($user->hasRole('vet')) {
            // Bác sĩ chỉ thấy lịch hẹn được assign cho MÌNH
            $todayAppointments = Appointment::with(['pet.customer', 'pet'])
                ->where('doctor_id', $user->id)
                ->whereDate('scheduled_at', today())
                ->whereIn('status', ['confirmed', 'checked_in']) // Chỉ hiện lịch chờ khám
                ->orderBy('scheduled_at')
                ->get();

            $stats = [
                'myTodayPatients' => Appointment::where('doctor_id', $user->id)
                    ->whereDate('scheduled_at', today())
                    ->count(),
                'waitingPatients' => $todayAppointments->where('status', 'checked_in')->count(),
                'completedToday'  => Appointment::where('doctor_id', $user->id)
                    ->whereDate('scheduled_at', today())
                    ->where('status', 'completed')
                    ->count(),
            ];
        } elseif ($user->hasRole('accountant')) {
            $pendingInvoices = Invoice::with('customer')
                ->where('status', 'unpaid')
                ->latest()
                ->take(10) // Lấy 10 cái mới nhất
                ->get();

            $stats = [
                'todayRevenue'  => Invoice::where('status', 'paid')
                    ->whereDate('paid_at', today())
                    ->sum('total_amount'),
                'pendingCount'  => Invoice::where('status', 'unpaid')->count(),
                'monthRevenue'  => Invoice::where('status', 'paid')
                    ->whereMonth('paid_at', now()->month)
                    ->sum('total_amount'),
            ];
        }

        return Inertia::render('Dashboard', [
            'stats'             => $stats,
            'todayAppointments' => $todayAppointments,
            'pendingInvoices'   => $pendingInvoices,
            'lowStockMedicines' => $lowStockMedicines,
            'expiringMedicines' => $expiringMedicines,
        ]);
    }
}
