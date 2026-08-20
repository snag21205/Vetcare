<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Medicine;
use App\Models\Pet;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class MedicalRecordController extends Controller
{

    public function __construct(protected InventoryService $inventoryService) {}
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = $request->search;

        $records = MedicalRecord::with(['pet.customer', 'doctor'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('pet', function ($petQuery) use ($search) {
                        $petQuery->where('name', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
                    })
                    ->orWhere('diagnosis', 'like', "%{$search}%")
                    ->orWhere('symptoms', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('MedicalRecords/Index', [
            'records' => $records,
            'filters' => ['search' => $search],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        // Nếu chuyển từ Appointment đã check-in sang khám
        $appointment = null;
        if ($request->has('appointment_id')) {
            $appointment = Appointment::with(['pet.customer', 'doctor'])
                ->findOrFail($request->appointment_id);
        }

        $pets = Pet::with('customer')->orderBy('name')->get();
        // Lấy danh sách thuốc còn tồn kho
        $medicines = Medicine::where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'stock_quantity', 'sell_price']);

        return Inertia::render('MedicalRecords/Create', [
            'appointment' => $appointment,
            'pets'        => $pets,
            'medicines'   => $medicines,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalRecordRequest $request)
    {
        $validated = $request->validated();

        // Sử dụng DB::transaction để bảo đảm an toàn dữ liệu
        DB::transaction(function () use ($validated) {
            // 1. Tạo phiếu khám
            $record = MedicalRecord::create([
                'appointment_id'       => $validated['appointment_id'] ?? null,
                'pet_id'               => $validated['pet_id'],
                'doctor_id'            => $validated['doctor_id'],
                'symptoms'             => $validated['symptoms'],
                'diagnosis'            => $validated['diagnosis'],
                'weight_at_visit'      => $validated['weight_at_visit'] ?? null,
                'temperature_at_visit' => $validated['temperature_at_visit'] ?? null,
                'note'                 => $validated['note'] ?? null,
                'status'               => $validated['status'],
            ]);

            // 2. Kê đơn & trừ kho
            if (!empty($validated['prescriptions'])) {
                foreach ($validated['prescriptions'] as $item) {
                    // Trừ kho qua Service
                    $this->inventoryService->deductStock($item['medicine_id'], (int)$item['quantity']);

                    // Tạo đơn thuốc
                    $record->prescriptions()->create([
                        'medicine_id' => $item['medicine_id'],
                        'dosage'      => $item['dosage'],
                        'quantity'    => $item['quantity'],
                        'days'        => $item['days'] ?? null,
                        'note'        => $item['note'] ?? null,
                    ]);
                }
            }

            // 3. Nếu có appointment đi kèm, cập nhật status appointment
            if (!empty($validated['appointment_id'])) {
                Appointment::where('id', $validated['appointment_id'])->update([
                    'status' => $validated['status'] === 'completed' ? 'completed' : 'in_progress'
                ]);
            }
        });
        return redirect()->route('medical-records.index')->with('success', 'Đã lưu phiếu khám và cập nhập kho thuốc thành công!');
    }
    /**
     * Display the specified resource.
     */
    public function show(MedicalRecord $medicalRecord): Response
    {
        $medicalRecord->load(['pet.customer', 'doctor', 'prescriptions.medicine', 'services']);

        return Inertia::render('MedicalRecords/Show', [
            'record' => $medicalRecord,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
