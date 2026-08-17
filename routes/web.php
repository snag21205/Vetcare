<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
    return redirect()->router('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== ROUTE CHỈ ADMIN =====
// middleware(['auth', 'role:admin']): phải login VÀ phải có role admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('medicines', MedicineController::class);
    Route::resource('services', ServiceController::class);
    Route::resource('users', UserController::class);
});

// ===== ROUTE CHO LỄ TÂN VÀ ADMIN =====
// 'role:receptionist|admin': cho phép receptionist HOẶC admin
Route::middleware(['auth', 'role:receptionist|admin'])->group(function () {
    Route::resource('customers', CustomerController::class);
    Route::resource('pets', PetController::class);
    Route::resource('appointments', AppointmentController::class);
});

// ===== ROUTE CHO BÁC SĨ VÀ ADMIN =====
Route::middleware(['auth', 'role:vet|admin'])->group(function () {
    Route::resource('medical-records', MedicalRecordController::class);
});

// ===== ROUTE CHO KẾ TOÁN VÀ ADMIN =====
Route::middleware(['auth', 'role:accountant|admin'])->group(function () {
    Route::resource('invoices', InvoiceController::class);
    Route::resource('expense-vouchers', ExpenseVoucherController::class);
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])
        ->name('reports.revenue');
});

require __DIR__ . '/auth.php';
