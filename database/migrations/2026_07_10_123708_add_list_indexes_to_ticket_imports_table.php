<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ticket_imports', function (Blueprint $table) {
            $table->index(
                ['ticket_source_id', 'status_id', 'created_at', 'id'],
                'ticket_imports_source_status_created_id_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_imports', function (Blueprint $table) {
            $table->dropIndex('ticket_imports_source_status_created_id_idx');
        });
    }
};