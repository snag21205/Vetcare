<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ExpenseVoucher;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Báo cáo Doanh thu, Chi phí & Lợi nhuận (Theo ngày/tháng/bác sĩ).
     */
    public function revenue(Request $request): Response
    {
        $month    = $request->month ?? now()->format('Y-m');
        $doctorId = $request->doctor_id;

        [$year, $m] = explode('-', $month);

        // 1. Query Hóa đơn đã thanh toán trong tháng
        $invoicesQuery = Invoice::with(['medicalRecord.doctor', 'customer'])
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $m)
            ->when($doctorId, function ($query, $doctorId) {
                $query->whereHas('medicalRecord', fn($q) => $q->where('doctor_id', $doctorId));
            });

        $invoices = (clone $invoicesQuery)->latest('paid_at')->get();

        $totalRevenue  = (float)$invoices->sum('total_amount');
        $totalExam     = (float)$invoices->sum('subtotal_exam');
        $totalService  = (float)$invoices->sum('subtotal_service');
        $totalMedicine = (float)$invoices->sum('subtotal_medicine');
        $invoiceCount  = $invoices->count();

        // 2. Query Phiếu chi trong tháng
        $expensesQuery = ExpenseVoucher::whereYear('voucher_date', $year)
            ->whereMonth('voucher_date', $m);

        $expenses = (clone $expensesQuery)->get();
        $totalExpense = (float)$expenses->sum('amount');
        $expenseMedicineImport = (float)$expenses->where('category', 'medicine_import')->sum('amount');
        $expenseOperational    = (float)$expenses->where('category', 'operational')->sum('amount');
        $expenseOther          = (float)$expenses->where('category', 'other')->sum('amount');

        $netProfit = $totalRevenue - $totalExpense;

        // 3. Phân bổ doanh thu & chi phí theo từng ngày trong tháng (cho biểu đồ Line/Bar Chart)
        $daysInMonth = (int)date('t', strtotime("{$year}-{$m}-01"));
        $dailyData = [];

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dateStr = sprintf('%04d-%02d-%02d', $year, $m, $d);

            $dayRevenue = (float)$invoices->filter(function ($inv) use ($dateStr) {
                return $inv->paid_at && $inv->paid_at->format('Y-m-d') === $dateStr;
            })->sum('total_amount');

            $dayExpense = (float)$expenses->filter(function ($exp) use ($dateStr) {
                return $exp->voucher_date && $exp->voucher_date->format('Y-m-d') === $dateStr;
            })->sum('amount');

            $dailyData[] = [
                'day'     => sprintf('%02d', $d),
                'date'    => $dateStr,
                'revenue' => $dayRevenue,
                'expense' => $dayExpense,
                'profit'  => $dayRevenue - $dayExpense,
            ];
        }

        // 4. Phân bổ doanh thu theo Bác sĩ
        $doctorStats = [];
        $vets = User::role('vet')->get(['id', 'name']);

        foreach ($vets as $vet) {
            $vetInvoices = $invoices->filter(fn($i) => $i->medicalRecord?->doctor_id === $vet->id);
            $doctorStats[] = [
                'doctor_id'   => $vet->id,
                'doctor_name' => $vet->name,
                'count'       => $vetInvoices->count(),
                'revenue'     => (float)$vetInvoices->sum('total_amount'),
            ];
        }

        return Inertia::render('Reports/Revenue', [
            'month'             => $month,
            'selectedDoctorId'  => $doctorId,
            'doctors'           => $vets,
            'summary'           => [
                'total_revenue'   => $totalRevenue,
                'total_expense'   => $totalExpense,
                'net_profit'      => $netProfit,
                'invoice_count'   => $invoiceCount,
                'total_exam'      => $totalExam,
                'total_service'   => $totalService,
                'total_medicine'  => $totalMedicine,
                'expense_import'  => $expenseMedicineImport,
                'expense_ops'     => $expenseOperational,
                'expense_other'   => $expenseOther,
            ],
            'dailyData'         => $dailyData,
            'doctorStats'       => $doctorStats,
        ]);
    }

    /**
     * Xuất Excel / CSV báo cáo doanh thu với UTF-8 BOM chuẩn tiếng Việt.
     */
    public function exportRevenueExcel(Request $request): StreamedResponse
    {
        $month = $request->month ?? now()->format('Y-m');
        [$year, $m] = explode('-', $month);

        $invoices = Invoice::with(['medicalRecord.doctor', 'customer'])
            ->where('status', 'paid')
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $m)
            ->latest('paid_at')
            ->get();

        $filename = "Bao-cao-doanh-thu-thang-{$month}.csv";

        return response()->streamDownload(function () use ($invoices, $month) {
            $handle = fopen('php://output', 'w');
            
            // Thêm UTF-8 BOM để Excel hiển thị đúng dấu tiếng Việt
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Dòng tiêu đề báo cáo
            fputcsv($handle, ["BÁO CÁO DOANH THU PHÒNG KHÁM VETCARE - THÁNG {$month}"]);
            fputcsv($handle, []);

            // Header bảng
            fputcsv($handle, [
                'STT',
                'Mã hóa đơn',
                'Ngày thanh toán',
                'Tên khách hàng',
                'Số điện thoại',
                'Bác sĩ phụ trách',
                'Tiền khám (VNĐ)',
                'Tiền dịch vụ (VNĐ)',
                'Tiền thuốc (VNĐ)',
                'Tổng thanh toán (VNĐ)'
            ]);

            $stt = 1;
            foreach ($invoices as $inv) {
                fputcsv($handle, [
                    $stt++,
                    $inv->invoice_number,
                    $inv->paid_at ? $inv->paid_at->format('d/m/Y H:i') : '',
                    $inv->customer->name ?? '',
                    $inv->customer->phone ?? '',
                    $inv->medicalRecord?->doctor?->name ?? '—',
                    $inv->subtotal_exam,
                    $inv->subtotal_service,
                    $inv->subtotal_medicine,
                    $inv->total_amount,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['', '', '', '', '', 'TỔNG CỘNG DOANH THU:', $invoices->sum('subtotal_exam'), $invoices->sum('subtotal_service'), $invoices->sum('subtotal_medicine'), $invoices->sum('total_amount')]);

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Báo cáo Thống kê bệnh phổ biến theo loài.
     */
    public function diseases(Request $request): Response
    {
        $species = $request->species ?? '';

        $diseases = DB::table('medical_records')
            ->join('pets', 'medical_records.pet_id', '=', 'pets.id')
            ->select('pets.species', 'medical_records.diagnosis', DB::raw('count(*) as count'))
            ->when($species, fn($q) => $q->where('pets.species', $species))
            ->whereNotNull('medical_records.diagnosis')
            ->groupBy('pets.species', 'medical_records.diagnosis')
            ->orderByDesc('count')
            ->get();

        // Tổng số ca khám
        $totalCases = $diseases->sum('count');

        // Nhóm theo loài
        $speciesBreakdown = DB::table('medical_records')
            ->join('pets', 'medical_records.pet_id', '=', 'pets.id')
            ->select('pets.species', DB::raw('count(*) as count'))
            ->groupBy('pets.species')
            ->get();

        return Inertia::render('Reports/Diseases', [
            'diseases'         => $diseases,
            'totalCases'       => $totalCases,
            'speciesBreakdown' => $speciesBreakdown,
            'selectedSpecies'  => $species,
        ]);
    }

    /**
     * Danh sách Khách hàng thân thiết (Dựa trên số lần khám và tổng chi tiêu).
     */
    public function loyalCustomers(Request $request): Response
    {
        $customers = Customer::withCount(['pets', 'medicalRecords'])
            ->withSum(['invoices as total_spent' => function ($query) {
                $query->where('status', 'paid');
            }], 'total_amount')
            ->orderByDesc('total_spent')
            ->paginate(15);

        return Inertia::render('Reports/LoyalCustomers', [
            'customers' => $customers,
        ]);
    }
}
