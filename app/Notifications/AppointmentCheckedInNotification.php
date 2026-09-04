<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppointmentCheckedInNotification extends Notification
{
    use Queueable;

    public function __construct(public Appointment $appointment)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pet = $this->appointment->pet?->name ?? 'Bệnh nhân';
        $customer = $this->appointment->pet?->customer?->name ?? 'Chủ nuôi';
        $doctorName = $this->appointment->doctor?->name ?? 'Bác sĩ';

        return [
            'type'           => 'appointment_checked_in',
            'title'          => 'Bệnh nhân đã Check-in',
            'message'        => "Bé {$pet} ({$customer}) đã đến phòng khám và check-in, đang đợi khám!",
            'appointment_id' => $this->appointment->id,
            'doctor_name'    => $doctorName,
            'url'            => "/medical-records/create?appointment_id={$this->appointment->id}",
            'created_at'     => now()->toIso8601String(),
        ];
    }
}
