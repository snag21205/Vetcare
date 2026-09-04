<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /**
     * Danh sách tất cả thông báo của tài khoản hiện tại.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $unreadCount = $user->unreadNotifications()->count();

        return Inertia::render('Notifications/Index', [
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount,
        ]);
    }

    /**
     * Lấy 5 thông báo gần nhất và số lượng chưa đọc (cho dropdown header).
     */
    public function recent(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['notifications' => [], 'unread_count' => 0]);
        }

        return response()->json([
            'notifications' => $user->notifications()->latest()->limit(5)->get(),
            'unread_count'  => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Đánh dấu 1 thông báo cụ thể là đã đọc.
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Đánh dấu tất cả thông báo là đã đọc.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Xóa 1 thông báo.
     */
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->delete();

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Đã xóa thông báo.');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Bắn thử 1 thông báo mẫu để kiểm tra hệ thống.
     */
    public function sendTestNotification(Request $request)
    {
        $user = $request->user();
        
        $types = ['new_appointment', 'appointment_checked_in', 'invoice_created', 'low_stock'];
        $type = $request->input('type', $types[array_rand($types)]);

        $samples = [
            'new_appointment' => [
                'type'       => 'new_appointment',
                'title'      => 'Lịch hẹn khám mới (Thử nghiệm)',
                'message'    => 'Bé Corgi Lucky (Nguyễn Văn An) vừa đặt lịch khám tổng quát lúc 14:30 chiều nay.',
                'url'        => '/appointments',
                'created_at' => now()->toIso8601String(),
            ],
            'appointment_checked_in' => [
                'type'       => 'appointment_checked_in',
                'title'      => 'Bệnh nhân đã Check-in (Thử nghiệm)',
                'message'    => 'Bé Miu Miu (Lê Thị Hoa) đã đến phòng khám và check-in, đang đợi bác sĩ!',
                'url'        => '/medical-records/create',
                'created_at' => now()->toIso8601String(),
            ],
            'invoice_created' => [
                'type'       => 'invoice_created',
                'title'      => 'Hóa đơn mới chờ thanh toán (Thử nghiệm)',
                'message'    => 'Hóa đơn #HD-TEST-999 cho bé Lu Lu số tiền 450.000 ₫ đang chờ thu ngân.',
                'url'        => '/invoices',
                'created_at' => now()->toIso8601String(),
            ],
            'low_stock' => [
                'type'       => 'low_stock',
                'title'      => 'Cảnh báo tồn kho thấp (Thử nghiệm)',
                'message'    => 'Thuốc Amoxicillin 250mg chỉ còn 15 viên trong kho (Ngưỡng cảnh báo: 50 viên).',
                'url'        => '/medicines?filter=low_stock',
                'created_at' => now()->toIso8601String(),
            ],
        ];

        $data = $samples[$type] ?? $samples['new_appointment'];

        $user->notifications()->create([
            'id'      => (string)\Illuminate\Support\Str::uuid(),
            'type'    => 'App\Notifications\CustomTestNotification',
            'data'    => $data,
            'read_at' => null,
        ]);

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Đã gửi thông báo thử nghiệm thành công!');
        }

        return response()->json([
            'success'      => true,
            'message'      => 'Đã gửi thông báo thử nghiệm thành công!',
            'notification' => $data,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
