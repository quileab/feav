<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('invoices/create', \App\Livewire\Invoices\Create::class)->name('invoices.create');
    Route::get('invoices/{voucher}/download', [\App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');
});
