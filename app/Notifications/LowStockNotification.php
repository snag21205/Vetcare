<?php

namespace App\Notifications;

use App\Models\Medicine;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Medicine $medicine)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $adminName = $notifiable->name ?? 'Quản trị viên';

        return (new MailMessage)
            ->subject("[VetCare Cảnh Báo] Thuốc sắp hết kho: {$this->medicine->name}")
            ->greeting("Xin chào {$adminName},")
            ->line("Cảnh báo tồn kho thuốc xuống dưới ngưỡng an toàn:")
            ->line("- **Tên thuốc:** {$this->medicine->name}")
            ->line("- **Tồn kho hiện tại:** {$this->medicine->stock_quantity} {$this->medicine->unit}")
            ->line("- **Ngưỡng cảnh báo:** {$this->medicine->low_stock_threshold} {$this->medicine->unit}")
            ->action('Nhập thêm kho thuốc', url('/medicines?filter=low_stock'))
            ->line('Vui lòng sớm lập phiếu nhập hàng để không làm gián đoạn việc điều trị.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'low_stock',
            'title'       => 'Cảnh báo tồn kho thấp',
            'message'     => "Thuốc {$this->medicine->name} chỉ còn {$this->medicine->stock_quantity} {$this->medicine->unit} (Ngưỡng: {$this->medicine->low_stock_threshold})",
            'medicine_id' => $this->medicine->id,
            'url'         => '/medicines?filter=low_stock',
            'created_at'  => now()->toIso8601String(),
        ];
    }
}
