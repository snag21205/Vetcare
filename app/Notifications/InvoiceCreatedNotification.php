<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoiceCreatedNotification extends Notification
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
        $customer = $this->invoice->customer?->name ?? 'Khách hàng';
        $amount = number_format($this->invoice->total_amount, 0, ',', '.') . ' ₫';

        return [
            'type'       => 'invoice_created',
            'title'      => 'Hóa đơn mới chờ thanh toán',
            'message'    => "Hóa đơn {$this->invoice->invoice_number} cho bé {$pet} ({$customer}) số tiền {$amount} đang chờ thu ngân thanh toán.",
            'invoice_id' => $this->invoice->id,
            'url'        => "/invoices?search={$this->invoice->invoice_number}",
            'created_at' => now()->toIso8601String(),
        ];
    }
}
