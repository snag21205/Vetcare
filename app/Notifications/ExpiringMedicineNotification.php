<?php

namespace App\Notifications;

use App\Models\Medicine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExpiringMedicineNotification extends Notification
{
    use Queueable;

    public function __construct(public Medicine $medicine, public ?string $batchNumber = null, public ?string $expiryDate = null)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $batchText = $this->batchNumber ? " [Lô {$this->batchNumber}]" : '';
        $dateText = $this->expiryDate ? date('d/m/Y', strtotime($this->expiryDate)) : ($this->medicine->expiry_date ? $this->medicine->expiry_date->format('d/m/Y') : '');

        return [
            'type'        => 'expiring_medicine',
            'title'       => 'Cảnh báo thuốc sắp hết hạn (FEFO)',
            'message'     => "Thuốc {$this->medicine->name}{$batchText} sẽ hết hạn vào ngày {$dateText} (còn dưới 30 ngày).",
            'medicine_id' => $this->medicine->id,
            'url'         => '/medicines?filter=expiring',
            'created_at'  => now()->toIso8601String(),
        ];
    }
}
