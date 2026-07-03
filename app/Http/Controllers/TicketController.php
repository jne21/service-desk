<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\TicketRequest;
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

    public function store(TicketRequest $request): RedirectResponse
    {
        Ticket::create($request->validated());

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку створено');
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

    public function update(TicketRequest $request, Ticket $ticket): RedirectResponse
    {
        $ticket->update($request->validated());

        return redirect()
            ->route('tickets.index')
            ->with('success', 'Заявку оновлено');
    }
}
