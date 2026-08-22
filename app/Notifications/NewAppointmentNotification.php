<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewAppointmentNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        // Database notification cho chuông trên web + Mail nếu có cấu hình
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $time = $this->appointment->scheduled_at->format('H:i d/m/Y');
        $pet = $this->appointment->pet?->name ?? 'Vật nuôi';
        $customer = $this->appointment->pet?->customer?->name ?? 'Khách hàng';
        $greetingName = $notifiable->name ?? 'Quý khách';

        return (new MailMessage)
            ->subject("[VetCare] Lịch hẹn khám mới: {$pet} ({$customer})")
            ->greeting("Xin chào {$greetingName},")
            ->line("Có một lịch hẹn khám mới vừa được đặt trên hệ thống:")
            ->line("- **Thú cưng:** {$pet}")
            ->line("- **Chủ nuôi:** {$customer}")
            ->line("- **Thời gian:** {$time}")
            ->line("- **Lý do khám:** {$this->appointment->reason}")
            ->action('Xem chi tiết lịch hẹn', url('/appointments'))
            ->line('Vui lòng kiểm tra và tiếp nhận bệnh nhân.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'new_appointment',
            'title'          => 'Lịch hẹn khám mới',
            'message'        => "Lịch hẹn cho bé {$this->appointment->pet?->name} ({$this->appointment->pet?->customer?->name}) lúc {$this->appointment->scheduled_at->format('H:i d/m')}",
            'appointment_id' => $this->appointment->id,
            'url'            => '/appointments',
            'created_at'     => now()->toIso8601String(),
        ];
    }
}
