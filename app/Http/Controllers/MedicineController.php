<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImportStockRequest;
use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\ExpenseVoucher;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->search;
        $filter = $request->filter; // 'all', 'low_stock', 'expiring'

        $medicines = Medicine::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('unit', 'like', "%{$search}%");
            })
            ->when($filter === 'low_stock', function ($query) {
                $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
            })
            ->when($filter === 'expiring', function ($query) {
                $query->whereNotNull('expiry_date')
                      ->whereDate('expiry_date', '<=', now()->addDays(30));
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Thống kê nhanh cho kho
        $stats = [
            'total'     => Medicine::count(),
            'low_stock' => Medicine::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'expiring'  => Medicine::whereNotNull('expiry_date')->whereDate('expiry_date', '<=', now()->addDays(30))->count(),
        ];

        return Inertia::render('Medicines/Index', [
            'medicines' => $medicines,
            'stats'     => $stats,
            'filters'   => [
                'search' => $search,
                'filter' => $filter ?? 'all',
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Medicines/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicineRequest $request)
    {
        Medicine::create($request->validated());

        return redirect()->route('medicines.index')
            ->with('success', 'Đã thêm thuốc mới vào kho thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Medicine $medicine)
    {
        return redirect()->route('medicines.edit', $medicine->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Medicine $medicine): Response
    {
        return Inertia::render('Medicines/Edit', [
            'medicine' => $medicine,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicineRequest $request, Medicine $medicine)
    {
        $medicine->update($request->validated());

        return redirect()->route('medicines.index')
            ->with('success', "Đã cập nhật thông tin thuốc {$medicine->name} thành công!");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()->route('medicines.index')
            ->with('success', "Đã xóa thuốc {$medicine->name} khỏi danh mục kho!");
    }

    /**
     * Nhập thêm hàng vào kho thuốc và tùy chọn tự động sinh phiếu chi.
     */
    public function importStock(ImportStockRequest $request, Medicine $medicine)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $medicine, $request) {
            // 1. Tăng tồn kho và cập nhật giá nhập mới
            $medicine->increment('stock_quantity', (int)$validated['quantity']);

            $updateData = [
                'import_price' => $validated['import_price'],
            ];
            if (!empty($validated['expiry_date'])) {
                $updateData['expiry_date'] = $validated['expiry_date'];
            }
            $medicine->update($updateData);

            // 2. Nếu chọn tạo phiếu chi
            if (!empty($validated['create_voucher'])) {
                $totalAmount = (int)$validated['quantity'] * (float)$validated['import_price'];
                $note = !empty($validated['note']) ? " ({$validated['note']})" : '';

                ExpenseVoucher::create([
                    'category'     => 'medicine_import',
                    'amount'       => $totalAmount,
                    'description'  => "Nhập kho {$validated['quantity']} {$medicine->unit} thuốc {$medicine->name}{$note}",
                    'voucher_date' => today(),
                    'created_by'   => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', "Đã nhập thêm {$validated['quantity']} {$medicine->unit} thuốc {$medicine->name} vào kho thành công!");
    }
}
