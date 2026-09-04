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
        $medicine->load(['batches' => function ($query) {
            $query->orderBy('expiry_date', 'asc')->orderBy('id', 'asc');
        }]);

        return Inertia::render('Medicines/Edit', [
            'medicine' => $medicine,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicineRequest $request, Medicine $medicine)
    {
        DB::transaction(function () use ($request, $medicine) {
            $validated = $request->validated();

            $medicine->update([
                'name'                => $validated['name'],
                'unit'                => $validated['unit'],
                'sell_price'          => $validated['sell_price'],
                'import_price'        => $validated['import_price'],
                'low_stock_threshold' => $validated['low_stock_threshold'],
            ]);

            // Cập nhật danh sách các lô thuốc
            if (isset($validated['batches']) && is_array($validated['batches'])) {
                foreach ($validated['batches'] as $bData) {
                    // Nếu đánh dấu xóa
                    if (!empty($bData['_delete']) && !empty($bData['id'])) {
                        $medicine->batches()->where('id', $bData['id'])->delete();
                        continue;
                    }

                    $batchAttrs = [
                        'batch_number'       => !empty($bData['batch_number']) ? trim($bData['batch_number']) : ('LO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4))),
                        'remaining_quantity' => (int)($bData['remaining_quantity'] ?? 0),
                        'quantity'           => max((int)($bData['quantity'] ?? $bData['remaining_quantity'] ?? 0), (int)($bData['remaining_quantity'] ?? 0)),
                        'import_price'       => isset($bData['import_price']) && $bData['import_price'] !== '' ? (float)$bData['import_price'] : $medicine->import_price,
                        'expiry_date'        => !empty($bData['expiry_date']) ? $bData['expiry_date'] : null,
                    ];

                    if (!empty($bData['id'])) {
                        $batch = $medicine->batches()->where('id', $bData['id'])->first();
                        if ($batch) {
                            $batch->update($batchAttrs);
                        }
                    } else {
                        // Thêm lô mới
                        $medicine->batches()->create($batchAttrs);
                    }
                }
            }

            // Nếu thuốc chưa có lô nào trong DB nhưng người dùng nhập tồn kho > 0
            if ($medicine->batches()->count() === 0 && !empty($validated['stock_quantity'])) {
                $medicine->batches()->create([
                    'batch_number'       => 'LO-' . date('Ymd') . '-001',
                    'quantity'           => (int)$validated['stock_quantity'],
                    'remaining_quantity' => (int)$validated['stock_quantity'],
                    'import_price'       => $medicine->import_price,
                    'expiry_date'        => $validated['expiry_date'] ?? null,
                ]);
            }

            // Luôn đồng bộ lại tổng tồn kho và hạn dùng gần nhất (FEFO)
            $medicine->recalculateStockAndEarliestExpiry();
        });

        return redirect()->route('medicines.index')
            ->with('success', "Đã cập nhật thông tin thuốc {$medicine->name} và các lô thành công!");
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
     * Cập nhật nhanh thông tin 1 lô thuốc từ modal.
     */
    public function updateBatch(Request $request, \App\Models\MedicineBatch $batch)
    {
        $validated = $request->validate([
            'batch_number'       => ['nullable', 'string', 'max:100'],
            'remaining_quantity' => ['required', 'integer', 'min:0'],
            'import_price'       => ['nullable', 'numeric', 'min:0'],
            'expiry_date'        => ['nullable', 'date'],
        ], [
            'remaining_quantity.required' => 'Số lượng tồn không được để trống.',
            'remaining_quantity.min'      => 'Số lượng tồn phải lớn hơn hoặc bằng 0.',
        ]);

        DB::transaction(function () use ($batch, $validated) {
            $batch->update([
                'batch_number'       => !empty($validated['batch_number']) ? trim($validated['batch_number']) : $batch->batch_number,
                'remaining_quantity' => (int)$validated['remaining_quantity'],
                'quantity'           => max($batch->quantity, (int)$validated['remaining_quantity']),
                'import_price'       => isset($validated['import_price']) && $validated['import_price'] !== '' ? (float)$validated['import_price'] : $batch->import_price,
                'expiry_date'        => !empty($validated['expiry_date']) ? $validated['expiry_date'] : null,
            ]);

            $batch->medicine->recalculateStockAndEarliestExpiry();
        });

        return back()->with('success', "Đã cập nhật thông tin lô " . ($batch->batch_number ?: '#' . $batch->id) . " thành công!");
    }

    /**
     * Xóa 1 lô thuốc.
     */
    public function destroyBatch(\App\Models\MedicineBatch $batch)
    {
        $medicine = $batch->medicine;
        $batchNumber = $batch->batch_number ?: ('Lô #' . $batch->id);

        DB::transaction(function () use ($batch, $medicine) {
            $batch->delete();
            $medicine->recalculateStockAndEarliestExpiry();
        });

        return back()->with('success', "Đã xóa lô thuốc {$batchNumber} thành công!");
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
