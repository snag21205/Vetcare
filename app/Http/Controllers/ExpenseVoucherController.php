<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExpenseVoucherRequest;
use App\Http\Requests\UpdateExpenseVoucherRequest;
use App\Models\ExpenseVoucher;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseVoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search   = $request->search;
        $category = $request->category;
        $month    = $request->month ?? now()->format('Y-m');

        $vouchers = ExpenseVoucher::with('creator')
            ->when($search, function ($query, $search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($month, function ($query, $month) {
                [$year, $m] = explode('-', $month);
                $query->whereYear('voucher_date', $year)->whereMonth('voucher_date', $m);
            })
            ->latest('voucher_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Tổng chi trong tháng đã chọn
        [$year, $m] = explode('-', $month);
        $totalMonthExpense = ExpenseVoucher::whereYear('voucher_date', $year)
            ->whereMonth('voucher_date', $m)
            ->sum('amount');

        return Inertia::render('ExpenseVouchers/Index', [
            'vouchers'          => $vouchers,
            'totalMonthExpense' => $totalMonthExpense,
            'filters'           => [
                'search'   => $search,
                'category' => $category,
                'month'    => $month,
            ],
            'categories'        => [
                'medicine_import' => 'Nhập kho thuốc',
                'operational'     => 'Chi phí vận hành',
                'other'           => 'Chi phí khác',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('ExpenseVouchers/Create', [
            'categories' => [
                'medicine_import' => 'Nhập kho thuốc',
                'operational'     => 'Chi phí vận hành (Điện, nước, internet, thuê mặt bằng...)',
                'other'           => 'Chi phí khác',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreExpenseVoucherRequest $request)
    {
        ExpenseVoucher::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('expense-vouchers.index')
            ->with('success', 'Đã tạo phiếu chi mới thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ExpenseVoucher $expenseVoucher)
    {
        return redirect()->route('expense-vouchers.edit', $expenseVoucher->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExpenseVoucher $expenseVoucher): Response
    {
        return Inertia::render('ExpenseVouchers/Edit', [
            'voucher'    => $expenseVoucher,
            'categories' => [
                'medicine_import' => 'Nhập kho thuốc',
                'operational'     => 'Chi phí vận hành (Điện, nước, internet, thuê mặt bằng...)',
                'other'           => 'Chi phí khác',
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateExpenseVoucherRequest $request, ExpenseVoucher $expenseVoucher)
    {
        $expenseVoucher->update($request->validated());

        return redirect()->route('expense-vouchers.index')
            ->with('success', 'Đã cập nhật thông tin phiếu chi thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExpenseVoucher $expenseVoucher)
    {
        $expenseVoucher->delete();

        return redirect()->route('expense-vouchers.index')
            ->with('success', 'Đã xóa phiếu chi thành công!');
    }
}
