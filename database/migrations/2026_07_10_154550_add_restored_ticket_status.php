<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('ticket_statuses')->updateOrInsert(
            ['code' => 'restored'],
            [
                'name' => 'Відновлена',
                'sort_order' => 15,
                'is_final' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('ticket_statuses')
            ->where('code', 'restored')
            ->delete();
    }
};