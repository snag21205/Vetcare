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

// ===== ROUTE CHỈ ADMIN =====
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('medicines', \App\Http\Controllers\MedicineController::class);
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

// ===== ROUTE CHO LỄ TÂN VÀ ADMIN =====
Route::middleware(['auth', 'role:receptionist|admin'])->group(function () {
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('pets', \App\Http\Controllers\PetController::class);
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
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
    Route::get('pets/{pet}/history', [\App\Http\Controllers\PetController::class, 'history'])
        ->name('pets.history');
});

// ===== ROUTE CHO BÁC SĨ, LỄ TÂN VÀ ADMIN =====
Route::middleware(['auth', 'role:vet|receptionist|admin'])->group(function () {
    Route::resource('medical-records', \App\Http\Controllers\MedicalRecordController::class);
    Route::get('medical-records/{medicalRecord}/pdf', [\App\Http\Controllers\MedicalRecordController::class, 'pdf'])
        ->name('medical-records.pdf');
    Route::post('ai/diagnose', [\App\Http\Controllers\AiDiagnosisController::class, 'diagnose'])
        ->name('ai.diagnose');
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

