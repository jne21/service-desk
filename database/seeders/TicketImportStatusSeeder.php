<?php

namespace Database\Seeders;

use App\Models\TicketImportStatus;
use Illuminate\Database\Seeder;

class TicketImportStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => TicketImportStatus::CODE_QUEUED,
                'name' => 'Очікує обробки',
                'sort_order' => 10,
                'is_final' => false,
            ],
            [
                'code' => TicketImportStatus::CODE_PROCESSING,
                'name' => 'Обробляється',
                'sort_order' => 20,
                'is_final' => false,
            ],
            [
                'code' => TicketImportStatus::CODE_FINISHED,
                'name' => 'Завершено',
                'sort_order' => 30,
                'is_final' => true,
            ],
            [
                'code' => TicketImportStatus::CODE_FINISHED_WITH_ERRORS,
                'name' => 'Завершено з помилками',
                'sort_order' => 40,
                'is_final' => true,
            ],
            [
                'code' => TicketImportStatus::CODE_FAILED,
                'name' => 'Помилка',
                'sort_order' => 50,
                'is_final' => true,
            ],
        ];

        foreach ($statuses as $status) {
            TicketImportStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}