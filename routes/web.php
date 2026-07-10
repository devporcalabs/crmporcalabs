<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

use App\Http\Controllers\PDFController;

Route::get('/', function () {
    return redirect('/admin'); // Redirect root to admin panel
});

Route::middleware(['auth'])->group(function () {
    Route::get('/invoice/{invoice}/download-pdf', [PDFController::class, 'downloadInvoice'])->name('invoice.download-pdf');
    Route::get('/payment/{payment}/download-kuitansi', [PDFController::class, 'downloadKuitansi'])->name('payment.download-kuitansi');
    Route::get('/quotation/{quotation}/download-pdf', [PDFController::class, 'downloadQuotation'])->name('quotation.download-pdf');
});

Route::get('/invoice/{invoice}/preview', [PDFController::class, 'previewInvoice'])
    ->name('invoice.public-preview')
    ->middleware('signed');

Route::get('/invoice/{invoice}/pdf', [PDFController::class, 'streamPDF'])
    ->name('invoice.public-pdf')
    ->middleware('signed');

Route::post('/invoice/{invoice}/pay-token', [\App\Http\Controllers\MidtransController::class, 'getSnapToken'])
    ->name('invoice.pay-token')
    ->middleware('signed');

Route::get('/quotation/{quotation}/preview', [PDFController::class, 'previewQuotation'])
    ->name('quotation.public-preview')
    ->middleware('signed');

Route::get('/invoice/{invoice}/verify', [PDFController::class, 'verifyInvoice'])
    ->name('invoice.verify')
    ->middleware('signed');

Route::get('/quotation/{quotation}/verify', [PDFController::class, 'verifyQuotation'])
    ->name('quotation.verify')
    ->middleware('signed');
