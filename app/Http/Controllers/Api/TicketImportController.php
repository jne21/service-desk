<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TicketSource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketImportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        /** @var TicketSource $source */
        $source = $request->attributes->get('ticket_source');

        return response()->json([
            'success' => true,
            'source' => [
                'id' => $source->id,
                'code' => $source->code,
                'name' => $source->name,
            ],
            200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        ]);
    }
}