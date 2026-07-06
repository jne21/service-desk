<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserTicketController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $startedAt = microtime(true);

        $user = $request->user();

        $query = Ticket::query()
            ->with([
                'status',
                'department',
                'user',
                'source',
            ])
            ->visibleFor($user)
            ->latest();

        if ($request->filled('ticket_id')) {
            $query->where('id', $request->integer('ticket_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $tickets = $query->get();

        return response()->json([
            'success' => true,
            'totalTime' => round(microtime(true) - $startedAt, 3),
            'tickets' => $tickets,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
