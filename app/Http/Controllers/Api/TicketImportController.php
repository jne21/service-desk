<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

use App\Models\TicketSource;
use App\Services\TicketImportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TicketImportRequest;
use App\Http\Controllers\Concerns\ApiResponses;


class TicketImportController extends Controller
{
    use ApiResponses;

    public function store(
        TicketImportRequest $request,
        TicketImportService $ticketImportService
    ): JsonResponse
    {
        /** @var TicketSource $source */
        $source = $request->attributes->get('ticket_source');

        if (! $source) {
            return $this->errorResponse('Ticket source is not resolved.', 401);

            //return response()->json([
            //    'success' => false,
            //    'message' => 'Ticket source was not resolved.',
            //], 401, [], JSON_UNESCAPED_UNICODE);
            //abort(401, 'Ticket source was not resolved.');
        }

        try {
            $result = $ticketImportService->import(
                $source,
                $request->validated('tickets')
            );
        } catch (\Throwable $e) {
            return $this->errorResponse(
                'Помилка під час імпорту заявок.',
                500
            );
        }

        return $this->successResponse([
            'source' => [
                'id' => $source->id,
                'code' => $source->code,
                'name' => $source->name,
            ],
            'created' => $result['created'],
            'updated' => $result['updated'],
        ]);
    }
}