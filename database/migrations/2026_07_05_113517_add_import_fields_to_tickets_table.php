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
        Schema::table('tickets', function (Blueprint $table) {
            $table->foreignId('source_id')
                ->nullable()
                ->after('id')
                ->constrained('ticket_sources')
                ->nullOnDelete();

            $table->string('external_id')
                ->nullable()
                ->after('source_id');

            $table->unique(['source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropUnique(['source_id', 'external_id']);
            $table->dropConstrainedForeignId('source_id');
            $table->dropColumn('external_id');
        });
    }
};
