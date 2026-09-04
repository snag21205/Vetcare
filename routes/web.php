<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\PetController;
use Illuminate\Support\Facades\Route;

// Trang chủ → redirect về login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== ROUTE CHỈ ADMIN (QUẢN TRỊ TOÀN QUYỀN) =====
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Quản lý tạo/sửa thuốc và nhập kho (Chỉ Admin)
    Route::resource('medicines', \App\Http\Controllers\MedicineController::class)->except(['index', 'show']);
    Route::post('medicines/{medicine}/import', [\App\Http\Controllers\MedicineController::class, 'importStock'])
        ->name('medicines.import');
    Route::put('medicine-batches/{batch}', [\App\Http\Controllers\MedicineController::class, 'updateBatch'])
        ->name('medicine-batches.update');
    Route::delete('medicine-batches/{batch}', [\App\Http\Controllers\MedicineController::class, 'destroyBatch'])
        ->name('medicine-batches.destroy');
    Route::resource('services', \App\Http\Controllers\ServiceController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::patch('users/{user}/toggle-status', [\App\Http\Controllers\UserController::class, 'toggleStatus'])
        ->name('users.toggle-status');
});

// ===== ROUTE BÁC SĨ & ADMIN TRA CỨU DANH MỤC THUỐC =====
Route::middleware(['auth', 'role:vet|admin'])->group(function () {
    Route::get('medicines', [\App\Http\Controllers\MedicineController::class, 'index'])->name('medicines.index');
    Route::get('medicines/{medicine}', [\App\Http\Controllers\MedicineController::class, 'show'])->name('medicines.show');
});

// ===== ROUTE CHO LỄ TÂN & ADMIN QUẢN LÝ TIẾP ĐÓN & ĐIỀU PHỐI LỊCH HẸN =====
Route::middleware(['auth', 'role:receptionist|admin'])->group(function () {
    // Thao tác tạo/sửa/xóa khách hàng & thú cưng
    Route::resource('customers', \App\Http\Controllers\CustomerController::class)->except(['index', 'show']);
    Route::resource('pets', \App\Http\Controllers\PetController::class)->except(['index', 'show']);

    // Thao tác tạo/sửa/hủy/check-in/xác nhận lịch hẹn
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class)->except(['index']);
    Route::match(
        ['post', 'patch'],
        'appointments/{appointment}/check-in',
        [\App\Http\Controllers\AppointmentController::class, 'checkIn']
    )->name('appointments.check-in');
    Route::match(
        ['post', 'patch'],
        'appointments/{appointment}/confirm',
        [\App\Http\Controllers\AppointmentController::class, 'confirm']
    )->name('appointments.confirm');
});

// ===== ROUTE XEM TIẾP ĐÓN & LỊCH HẸN (LỄ TÂN, BÁC SĨ VÀ ADMIN) =====
Route::middleware(['auth', 'role:receptionist|vet|admin'])->group(function () {
    Route::get('customers', [\App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [\App\Http\Controllers\CustomerController::class, 'show'])->name('customers.show');
    Route::get('pets', [\App\Http\Controllers\PetController::class, 'index'])->name('pets.index');
    Route::get('pets/{pet}', [\App\Http\Controllers\PetController::class, 'show'])->name('pets.show');
    Route::get('pets/{pet}/history', [\App\Http\Controllers\PetController::class, 'history'])
        ->name('pets.history');
    Route::get('appointments', [\App\Http\Controllers\AppointmentController::class, 'index'])
        ->name('appointments.index');
});

// ===== ROUTE CHUYÊN MÔN KHÁM BỆNH & KÊ ĐƠN (CHỈ BÁC SĨ VÀ ADMIN) =====
Route::middleware(['auth', 'role:vet|admin'])->group(function () {
    Route::get('medical-records/create', [\App\Http\Controllers\MedicalRecordController::class, 'create'])
        ->name('medical-records.create');
    Route::post('medical-records', [\App\Http\Controllers\MedicalRecordController::class, 'store'])
        ->name('medical-records.store');
    Route::get('medical-records/{medical_record}/edit', [\App\Http\Controllers\MedicalRecordController::class, 'edit'])
        ->name('medical-records.edit');
    Route::match(['put', 'patch'], 'medical-records/{medical_record}', [\App\Http\Controllers\MedicalRecordController::class, 'update'])
        ->name('medical-records.update');
    Route::delete('medical-records/{medical_record}', [\App\Http\Controllers\MedicalRecordController::class, 'destroy'])
        ->name('medical-records.destroy');
    Route::post('ai/diagnose', [\App\Http\Controllers\AiDiagnosisController::class, 'diagnose'])
        ->name('ai.diagnose');
});

// ===== ROUTE XEM BỆNH ÁN & IN PDF (BÁC SĨ, ADMIN VÀ LỄ TÂN) =====
Route::middleware(['auth', 'role:vet|admin|receptionist'])->group(function () {
    Route::get('medical-records', [\App\Http\Controllers\MedicalRecordController::class, 'index'])
        ->name('medical-records.index');
    Route::get('medical-records/{medical_record}', [\App\Http\Controllers\MedicalRecordController::class, 'show'])
        ->name('medical-records.show');
    Route::get('medical-records/{medicalRecord}/pdf', [\App\Http\Controllers\MedicalRecordController::class, 'pdf'])
        ->name('medical-records.pdf');
});

// ===== ROUTE HÓA ĐƠN (LỄ TÂN, KẾ TOÁN VÀ ADMIN) =====
Route::middleware(['auth', 'role:receptionist|accountant|admin'])->group(function () {
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::get('invoices/{invoice}/pdf', [\App\Http\Controllers\InvoiceController::class, 'pdf'])
        ->name('invoices.pdf');
    Route::patch('invoices/{invoice}/pay', [\App\Http\Controllers\InvoiceController::class, 'pay'])
        ->name('invoices.pay');
});

// ===== ROUTE KẾ TOÁN VÀ ADMIN =====
Route::middleware(['auth', 'role:accountant|admin'])->group(function () {
    Route::resource('expense-vouchers', \App\Http\Controllers\ExpenseVoucherController::class);
    Route::get('/reports/revenue', [\App\Http\Controllers\ReportController::class, 'revenue'])
        ->name('reports.revenue');
    Route::get('/reports/revenue/export-excel', [\App\Http\Controllers\ReportController::class, 'exportRevenueExcel'])
        ->name('reports.revenue.excel');
    Route::get('/reports/diseases', [\App\Http\Controllers\ReportController::class, 'diseases'])
        ->name('reports.diseases');
    Route::get('/reports/loyal-customers', [\App\Http\Controllers\ReportController::class, 'loyalCustomers'])
        ->name('reports.loyal-customers');
});

// ===== ROUTE THÔNG BÁO (TẤT CẢ USER ĐÃ ĐĂNG NHẬP) =====
Route::middleware('auth')->group(function () {
    Route::get('api/notifications/recent', [\App\Http\Controllers\NotificationController::class, 'recent'])
        ->name('notifications.recent');
    Route::post('api/notifications/test', [\App\Http\Controllers\NotificationController::class, 'sendTestNotification'])
        ->name('notifications.test');
    Route::get('notifications', [\App\Http\Controllers\NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::match(['post', 'patch'], 'notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::match(['post', 'patch'], 'notifications/{id}/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::match(['post', 'patch'], 'notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])
        ->name('notifications.mark-all-read');
    Route::match(['post', 'patch'], 'notifications/mark-all-as-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
    Route::delete('notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])
        ->name('notifications.destroy');
});

// ===== ROUTE XEM TRƯỚC / TEST TRANG LỖI =====
Route::get('/errors/{status?}', function ($status = 404) {
    $validStatuses = [400, 401, 403, 404, 419, 500, 503];
    $status = in_array((int)$status, $validStatuses) ? (int)$status : 404;
    return \Inertia\Inertia::render('Error', ['status' => $status]);
})->name('errors.preview');

require __DIR__ . '/auth.php';

