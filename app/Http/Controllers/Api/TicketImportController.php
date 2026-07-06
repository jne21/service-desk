<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketImportRequest;
use App\Models\TicketSource;
use App\Services\TicketImportService;

class TicketImportController extends Controller
{
    public function store(
        TicketImportRequest $request,
        TicketImportService $ticketImportService
    ): JsonResponse
    {
        /** @var TicketSource $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            abort(401, 'Ticket source was not resolved.');
        }

        $result = $ticketImportService->import(
            $source,
            $request->validated('tickets')
        );

        return response()->json([
            'success' => true,
            'source' => [
                'id' => $source->id,
                'code' => $source->code,
                'name' => $source->name,
            ],
            'created' => $result['created'],
            'updated' => $result['updated'],
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}