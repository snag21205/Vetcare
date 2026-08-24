<?php

namespace App\Notifications;

use App\Models\MedicalRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PrescriptionSummaryNotification extends Notification
{
    use Queueable;

    public function __construct(public MedicalRecord $record)
    {
        //
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pet = $this->record->pet?->name ?? 'Bé thú cưng';
        $doctor = $this->record->doctor?->name ?? 'Bác sĩ VetCare';
        $date = $this->record->created_at->format('d/m/Y');
        $customerName = $notifiable->name ?? ($this->record->pet?->customer?->name ?? 'Quý khách');

        $mail = (new MailMessage)
            ->subject("[VetCare] Tóm tắt đơn thuốc & Kết quả khám bệnh cho {$pet}")
            ->greeting("Kính gửi Quý khách {$customerName},")
            ->line("Cảm ơn Quý khách đã tin tưởng đưa bé **{$pet}** đến khám tại phòng khám thú y VetCare ngày {$date}.")
            ->line("---")
            ->line("**Thông tin kết quả khám bệnh:**")
            ->line("- **Chẩn đoán:** {$this->record->diagnosis}")
            ->line("- **Bác sĩ phụ trách:** BS. {$doctor}");

        if ($this->record->weight_at_visit) {
            $mail->line("- **Cân nặng:** {$this->record->weight_at_visit} kg");
        }

        if ($this->record->prescriptions && $this->record->prescriptions->count() > 0) {
            $mail->line("---");
            $mail->line("**Đơn thuốc & Hướng dẫn sử dụng:**");
            foreach ($this->record->prescriptions as $p) {
                $medName = $p->medicine?->name ?? 'Thuốc';
                $unit = $p->medicine?->unit ?? '';
                $mail->line("• **{$medName}**: {$p->quantity} {$unit} — Liều: {$p->dosage} ({$p->days} ngày)" . ($p->note ? " [Lưu ý: {$p->note}]" : ''));
            }
        }

        if ($this->record->note) {
            $mail->line("---");
            $mail->line("**Lời dặn của Bác sĩ:** {$this->record->note}");
        }

        return $mail
            ->line("---")
            ->line("Nếu có bất kỳ thắc mắc hay dấu hiệu bất thường, xin vui lòng gọi ngay hotline phòng khám: **0901.234.567**.")
            ->salutation("Trân trọng,\nĐội ngũ Bác sĩ Thú y VetCare");
    }
}
