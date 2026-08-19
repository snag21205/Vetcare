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
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/pets', [PetController::class, 'index'])->name('pets');
});

// ===== ROUTE CHỈ ADMIN =====
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('medicines', \App\Http\Controllers\MedicineController::class);
    Route::resource('services', \App\Http\Controllers\ServiceController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
});

// ===== ROUTE CHO LỄ TÂN VÀ ADMIN =====
Route::middleware(['auth', 'role:receptionist|admin'])->group(function () {
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('pets', \App\Http\Controllers\PetController::class);
    Route::resource('appointments', \App\Http\Controllers\AppointmentController::class);
    Route::patch(
        'appointments/{appointment}/check-in',
        [\App\Http\Controllers\AppointmentController::class, 'checkIn']
    )
        ->name('appointments.check-in');
    Route::patch(
        'appointments/{appointment}/confirm',
        [\App\Http\Controllers\AppointmentController::class, 'confirm']
    )
        ->name('appointments.confirm');
});

// ===== ROUTE CHO BÁC SĨ VÀ ADMIN =====
Route::middleware(['auth', 'role:vet|admin'])->group(function () {
    Route::resource('medical-records', \App\Http\Controllers\MedicalRecordController::class);
});

// ===== ROUTE CHO KẾ TOÁN VÀ ADMIN =====
Route::middleware(['auth', 'role:accountant|admin'])->group(function () {
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::resource('expense-vouchers', \App\Http\Controllers\ExpenseVoucherController::class);
    Route::get('/reports/revenue', [\App\Http\Controllers\ReportController::class, 'revenue'])
        ->name('reports.revenue');
});

require __DIR__ . '/auth.php';
