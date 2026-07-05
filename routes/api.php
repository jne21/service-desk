<?php

use App\Http\Controllers\Api\TicketImportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['ticket-source'])->group(function () {
    Route::post('/tickets/import', [TicketImportController::class, 'store']);
});