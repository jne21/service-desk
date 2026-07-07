<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TicketImportController;
use App\Http\Controllers\Api\UserTicketController;

Route::middleware(['ticket-source'])->group(function () {
    Route::get('/tickets/imports/{ticketImport}', [TicketImportController::class, 'show']);
    Route::post('/tickets/import', [TicketImportController::class, 'store']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user/tickets', [UserTicketController::class, 'index']);
});