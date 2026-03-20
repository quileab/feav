<?php

use App\Http\Controllers\InvoiceController;
use App\Livewire\Invoices\Create;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
})->name('home');

Route::get('/login', fn () => redirect('/admin/login'))->name('login');
Route::get('/dashboard', fn () => redirect('/admin'))->name('dashboard');
Route::get('/profile/edit', fn () => redirect('/admin'))->name('profile.edit');
Route::get('/security/edit', fn () => redirect('/admin'))->name('security.edit');
Route::get('/user/confirm-password', fn () => redirect('/admin'))->name('password.confirm');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('invoices/create', Create::class)->name('invoices.create');
    Route::get('invoices/{voucher}/download', [InvoiceController::class, 'download'])->name('invoices.download');
});
