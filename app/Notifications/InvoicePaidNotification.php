<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoicePaidNotification extends Notification
{
    use Queueable;

    public function __construct(public Invoice $invoice)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pet = $this->invoice->medicalRecord?->pet?->name ?? 'Bệnh nhân';
        $amount = number_format($this->invoice->total_amount, 0, ',', '.') . ' ₫';

        return [
            'type'       => 'invoice_paid',
            'title'      => 'Hóa đơn đã được thanh toán',
            'message'    => "Hóa đơn {$this->invoice->invoice_number} cho bé {$pet} số tiền {$amount} đã được thanh toán thành công.",
            'invoice_id' => $this->invoice->id,
            'url'        => "/invoices?search={$this->invoice->invoice_number}",
            'created_at' => now()->toIso8601String(),
        ];
    }
}
