<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public MedicalRecord $medicalRecord,
        public ?int $userId = null
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): Invoice
    {
        $record = $this->medicalRecord->load(['pet.customer', 'prescriptions.medicine', 'services']);

        // 1. Tính tiền thuốc
        $subtotalMedicine = $record->prescriptions->reduce(function ($total, $p) {
            $sellPrice = $p->medicine ? (float) $p->medicine->sell_price : 0;
            return $total + ($sellPrice * (int) $p->quantity);
        }, 0);

        // 2. Tính tiền khám cơ bản và các dịch vụ khác
        $subtotalExam = 0;
        $subtotalService = 0;

        foreach ($record->services as $svc) {
            $price = (float) $svc->pivot->price;
            // Phân loại: Dịch vụ khám -> subtotal_exam, các dịch vụ khác -> subtotal_service
            if (mb_stripos($svc->name, 'khám') !== false || mb_stripos($svc->name, 'tổng quát') !== false) {
                $subtotalExam += $price;
            } else {
                $subtotalService += $price;
            }
        }

        // Nếu chưa có phí khám nào nhưng có dịch vụ thì lấy dịch vụ đầu tiên làm phí khám
        if ($subtotalExam == 0 && $record->services->isNotEmpty()) {
            $firstSvc = $record->services->first();
            $subtotalExam = (float) $firstSvc->pivot->price;
            $subtotalService = max(0, $subtotalService - $subtotalExam);
        }

        $totalAmount = $subtotalExam + $subtotalMedicine + $subtotalService;

        // 3. Sinh mã hóa đơn duy nhất: HD-YYYYMMDD-0001
        $datePrefix = now()->format('Ymd');
        $invoiceNumber = 'HD-' . $datePrefix . '-' . str_pad($record->id, 4, '0', STR_PAD_LEFT);

        // 4. Lưu / Cập nhật Hóa đơn
        $invoice = Invoice::updateOrCreate(
            ['medical_record_id' => $record->id],
            [
                'customer_id'       => $record->pet->customer_id,
                'invoice_number'    => $invoiceNumber,
                'subtotal_exam'     => $subtotalExam,
                'subtotal_medicine' => $subtotalMedicine,
                'subtotal_service'  => $subtotalService,
                'total_amount'      => $totalAmount,
                'status'            => 'unpaid',
                'created_by'        => $this->userId ?? $record->doctor_id ?? 1,
            ]
        );

        return $invoice;
    }
}
