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
            ->with(['batches' => function ($query) {
                $query->where('remaining_quantity', '>', 0)->orderBy('expiry_date', 'asc');
            }])
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
        $medicine = Medicine::create($request->validated());

        // Nếu khai báo có tồn kho ban đầu, tự động tạo lô thuốc đầu tiên
        if ($medicine->stock_quantity > 0) {
            $medicine->batches()->create([
                'batch_number'       => 'LO-' . date('Ymd') . '-001',
                'quantity'           => $medicine->stock_quantity,
                'remaining_quantity' => $medicine->stock_quantity,
                'import_price'       => $medicine->import_price,
                'expiry_date'        => $medicine->expiry_date,
            ]);
        }

        return redirect()->route('medicines.index')
            ->with('success', "Đã thêm thuốc {$medicine->name} vào danh mục thành công!");
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
        $medicine->recalculateStockAndEarliestExpiry();

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
     * Nhập thêm hàng vào kho thuốc theo từng lô riêng biệt và tùy chọn tự động sinh phiếu chi.
     */
    public function importStock(ImportStockRequest $request, Medicine $medicine)
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $medicine, $request) {
            // 1. Tạo lô nhập thuốc mới (MedicineBatch) với HSD và số lượng riêng biệt
            $batchNumber = !empty($validated['batch_number']) 
                ? $validated['batch_number'] 
                : 'LO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $medicine->batches()->create([
                'batch_number'       => $batchNumber,
                'quantity'           => (int)$validated['quantity'],
                'remaining_quantity' => (int)$validated['quantity'],
                'import_price'       => $validated['import_price'],
                'expiry_date'        => $validated['expiry_date'] ?? null,
            ]);

            // Cập nhật giá nhập mới nhất cho thuốc
            $medicine->import_price = $validated['import_price'];
            $medicine->save();

            // 2. Tự động tính toán lại tổng tồn kho và hạn sử dụng gần nhất (FEFO)
            $medicine->recalculateStockAndEarliestExpiry();

            // 3. Nếu chọn tạo phiếu chi
            if (!empty($validated['create_voucher'])) {
                $totalAmount = (int)$validated['quantity'] * (float)$validated['import_price'];
                $note = !empty($validated['note']) ? " ({$validated['note']})" : '';

                ExpenseVoucher::create([
                    'category'     => 'medicine_import',
                    'amount'       => $totalAmount,
                    'description'  => "Nhập kho {$validated['quantity']} {$medicine->unit} thuốc {$medicine->name} [Lô: {$batchNumber}]{$note}",
                    'voucher_date' => today(),
                    'created_by'   => $request->user()->id,
                ]);
            }
        });

        return back()->with('success', "Đã nhập thêm {$validated['quantity']} {$medicine->unit} thuốc {$medicine->name} vào lô mới thành công!");
    }
}
