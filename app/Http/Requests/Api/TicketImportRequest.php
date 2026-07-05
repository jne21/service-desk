<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class TicketImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tickets' => ['required', 'array', 'min:1'],

            'tickets.*.ticket_id' => ['required', 'string', 'max:100'],

            'tickets.*.title' => ['required', 'string', 'max:255'],
            'tickets.*.description' => ['nullable', 'string'],

            'tickets.*.status_id' => ['nullable', 'exists:ticket_statuses,id'],
            'tickets.*.user_id' => ['nullable', 'exists:users,id'],
            'tickets.*.department_id' => ['nullable', 'exists:departments,id'],
        ];
    }
}
