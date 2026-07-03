<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    public function index(): Response
    {
        $tickets = Ticket::query()
            ->with('status')
            ->latest()
            ->get();

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
        ]);
    }

    public function create(): Response
    {
        $statuses = TicketStatus::query()
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('Tickets/Create', [
            'statuses' => $statuses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status_id' => ['required', 'exists:ticket_statuses,id'],
        ]);

        Ticket::create($validated);

        return redirect()
            ->route('tickets.index', $ticket)
            ->with('success', 'Заявку оновлено');
    }

    public function show(Ticket $ticket): Response
    {
        $ticket->load('status');

        $statuses = TicketStatus::query()
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'statuses' => $statuses,
        ]);
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status_id' => ['required', 'exists:ticket_statuses,id'],
        ]);

        $ticket->update($validated);

        return redirect()
            ->route('tickets.index', $ticket)
            ->with('success', 'Заявку оновлено');
    }
}
