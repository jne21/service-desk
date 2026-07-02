<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'new', 'name' => 'Нова', 'sort_order' => 10, 'is_final' => false],
            ['code' => 'in_progress', 'name' => 'В роботі', 'sort_order' => 20, 'is_final' => false],
            ['code' => 'done', 'name' => 'Виконана', 'sort_order' => 30, 'is_final' => true],
            ['code' => 'cancelled', 'name' => 'Скасована', 'sort_order' => 40, 'is_final' => true],
        ];

        foreach ($statuses as $status) {
            TicketStatus::updateOrCreate(
                ['code' => $status['code']],
                $status
            );
        }
    }
}
