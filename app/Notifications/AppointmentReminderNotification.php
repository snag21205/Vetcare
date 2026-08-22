<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $time = $this->appointment->scheduled_at->format('H:i');
        $date = $this->appointment->scheduled_at->format('d/m/Y');
        $pet = $this->appointment->pet?->name ?? 'Bé thú cưng';
        $customer = $this->appointment->pet?->customer?->name ?? 'Quý khách';
        $greetingName = $notifiable->name ?? $customer;

        return (new MailMessage)
            ->subject("[VetCare] Nhắc hẹn khám ngày mai: {$pet} lúc {$time}")
            ->greeting("Xin chào {$greetingName},")
            ->line("Nhắc nhở lịch hẹn khám bệnh vào ngày mai (**{$date}**):")
            ->line("- **Thú cưng:** {$pet} ({$customer})")
            ->line("- **Giờ hẹn:** {$time}")
            ->line("- **Lý do khám:** {$this->appointment->reason}")
            ->action('Xem lịch hẹn ngày mai', url('/appointments?date=' . $this->appointment->scheduled_at->format('Y-m-d')))
            ->line('Vui lòng chuẩn bị sẵn sàng tiếp đón bệnh nhân.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'           => 'appointment_reminder',
            'title'          => 'Nhắc lịch hẹn ngày mai',
            'message'        => "Lịch hẹn ngày mai: Bé {$this->appointment->pet?->name} ({$this->appointment->pet?->customer?->name}) lúc {$this->appointment->scheduled_at->format('H:i')}",
            'appointment_id' => $this->appointment->id,
            'url'            => '/appointments?date=' . $this->appointment->scheduled_at->format('Y-m-d'),
            'created_at'     => now()->toIso8601String(),
        ];
    }
}
