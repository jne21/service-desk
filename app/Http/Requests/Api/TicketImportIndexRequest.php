<?php

namespace App\Http\Requests\Api;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class TicketImportIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxPerPage = (int) config('ticket_imports.list_per_page_max', 100);

        return [
            'status' => ['nullable', 'string', 'exists:ticket_import_statuses,code'],

            'date_from' => ['required', 'date_format:Y-m-d H:i:s'],
            'date_to' => ['required', 'date_format:Y-m-d H:i:s', 'after_or_equal:date_from'],

            'per_page' => ['nullable', 'integer', 'min:1', "max:{$maxPerPage}"],
        ];
    }

    public function messages(): array
    {
        $maxMinutes = (int) config('ticket_imports.list_max_interval_minutes', 360);
        $maxPerPage = (int) config('ticket_imports.list_per_page_max', 100);

        return [
            'status.exists' => 'Невідомий статус імпорту.',

            'date_from.required' => 'Параметр date_from є обов’язковим.',
            'date_from.date_format' => 'Параметр date_from має бути у форматі YYYY-MM-DD HH:MM:SS.',

            'date_to.required' => 'Параметр date_to є обов’язковим.',
            'date_to.date_format' => 'Параметр date_to має бути у форматі YYYY-MM-DD HH:MM:SS.',
            'date_to.after_or_equal' => 'Параметр date_to має бути не раніше date_from.',

            'per_page.integer' => 'Параметр per_page має бути числом.',
            'per_page.min' => 'Параметр per_page має бути більше 0.',
            'per_page.max' => "Параметр per_page не може бути більше {$maxPerPage}.",

            'date_interval.max' => "Інтервал між date_from і date_to не може перевищувати {$maxMinutes} хвилин.",
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $dateFrom = CarbonImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $this->input('date_from')
                );

                $dateTo = CarbonImmutable::createFromFormat(
                    'Y-m-d H:i:s',
                    $this->input('date_to')
                );

                $maxMinutes = (int) config('ticket_imports.list_max_interval_minutes', 360);

                if ($dateFrom->diffInMinutes($dateTo) > $maxMinutes) {
                    $validator->errors()->add(
                        'date_interval',
                        $this->messages()['date_interval.max']
                    );
                }
            },
        ];
    }

    protected function failedValidation(ValidatorContract $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422, [], JSON_UNESCAPED_UNICODE)
        );
    }
}
