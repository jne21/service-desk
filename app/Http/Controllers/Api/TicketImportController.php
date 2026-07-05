<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Api\TicketImportRequest;

class TicketImportController extends Controller
{
    public function store(TicketImportRequest $request): JsonResponse
    {
        $source = $request->attributes->get('ticket_source');
        $validated = $request->validated();

        return response()->json(
            [
                'success' => true,
                'source' => [
                    'id' => $source->id,
                    'code' => $source->code,
                    'name' => $source->name,
                ],
                'validated' => $validated,
            ],
            200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
    }
}
