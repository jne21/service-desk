<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserTicketIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ticket_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_id.integer' => 'Параметр ticket_id має бути числом.',
            'ticket_id.min' => 'Параметр ticket_id має бути більше 0.',

            'date_from.date' => 'Параметр date_from має бути коректною датою.',
            'date_to.date' => 'Параметр date_to має бути коректною датою.',
            'date_to.after_or_equal' => 'Параметр date_to має бути не раніше date_from.',
            'page.integer' => 'Параметр page має бути числом.',
            'page.min' => 'Параметр page має бути більше 0.',
            'per_page.integer' => 'Параметр per_page має бути числом.',
            'per_page.min' => 'Параметр per_page має бути більше 0.',
            'per_page.max' => 'Параметр per_page не може бути більше 100.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422, [], JSON_UNESCAPED_UNICODE)
        );
    }
}
