<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendDailyAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vetcare:remind-appointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gửi thông báo nhắc lịch hẹn khám ngày mai cho bác sĩ, lễ tân và chủ nuôi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tomorrow = now()->addDay()->format('Y-m-d');
        $this->info("Đang kiểm tra lịch hẹn ngày mai: {$tomorrow}...");

        $appointments = Appointment::with(['pet.customer', 'doctor'])
            ->whereDate('scheduled_at', $tomorrow)
            ->whereIn('status', ['confirmed', 'pending'])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("Không có lịch hẹn nào cần nhắc cho ngày mai.");
            return Command::SUCCESS;
        }

        $this->info("Tìm thấy {$appointments->count()} lịch hẹn.");

        // Danh sách Lễ tân nhận thông báo
        $receptionists = User::role('receptionist')->get();

        foreach ($appointments as $apt) {
            $pet = $apt->pet?->name ?? 'Vật nuôi';
            $customer = $apt->pet?->customer?->name ?? 'Khách hàng';
            $time = $apt->scheduled_at->format('H:i');

            $this->line("-> Gửi nhắc hẹn cho: [{$time}] {$pet} - Chủ: {$customer}");

            // 1. Gửi cho Lễ tân
            if ($receptionists->isNotEmpty()) {
                Notification::send($receptionists, new AppointmentReminderNotification($apt));
            }

            // 2. Gửi cho Bác sĩ phụ trách (nếu có)
            if ($apt->doctor) {
                $apt->doctor->notify(new AppointmentReminderNotification($apt));
            }

            // 3. Gửi Email cho Chủ nuôi (nếu khách có email)
            if ($apt->pet?->customer?->email) {
                Notification::route('mail', $apt->pet->customer->email)
                    ->notify(new AppointmentReminderNotification($apt));
            }
        }

        $this->info("Hoàn tất gửi thông báo nhắc lịch hẹn ngày mai!");
        return Command::SUCCESS;
    }
}
