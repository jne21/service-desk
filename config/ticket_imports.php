<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import list API max time window
    |--------------------------------------------------------------------------
    |
    | Максимальний дозволений інтервал для endpoint-а списку імпортів.
    | Значення задається у хвилинах.
    |
    | За замовчуванням: 360 хвилин = 6 годин.
    |
    */

    'list_max_interval_minutes' => (int) env('TICKET_IMPORT_LIST_MAX_INTERVAL_MINUTES', 360),

    /*
    |--------------------------------------------------------------------------
    | Import list pagination
    |--------------------------------------------------------------------------
    */

    'list_per_page_default' => (int) env('TICKET_IMPORT_LIST_PER_PAGE_DEFAULT', 20),
    'list_per_page_max' => (int) env('TICKET_IMPORT_LIST_PER_PAGE_MAX', 100),
];
