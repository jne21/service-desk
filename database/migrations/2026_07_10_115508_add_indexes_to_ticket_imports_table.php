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
            $table->index(['ticket_source_id', 'id'], 'ticket_imports_source_id_index');
            $table->index(['ticket_source_id', 'status_id', 'id'], 'ticket_imports_source_status_id_index');
            $table->index(['ticket_source_id', 'created_at'], 'ticket_imports_source_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_imports', function (Blueprint $table) {
            $table->dropIndex('ticket_imports_source_id_index');
            $table->dropIndex('ticket_imports_source_status_id_index');
            $table->dropIndex('ticket_imports_source_created_at_index');
        });
    }
};
