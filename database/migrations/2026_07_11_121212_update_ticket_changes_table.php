<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ticket_changes', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_changes', 'ticket_id')) {
                $table->foreignId('ticket_id')
                    ->constrained('tickets')
                    ->cascadeOnDelete();
            }

            if (! Schema::hasColumn('ticket_changes', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('ticket_changes', 'ticket_source_id')) {
                $table->foreignId('ticket_source_id')
                    ->nullable()
                    ->constrained('ticket_sources')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('ticket_changes', 'event')) {
                $table->string('event', 50);
            }

            if (! Schema::hasColumn('ticket_changes', 'changes')) {
                $table->json('changes')->nullable();
            }
        });

        Schema::table('ticket_changes', function (Blueprint $table) {
            if (! Schema::hasIndex(
                'ticket_changes',
                ['ticket_id', 'created_at']
            )) {
                $table->index(['ticket_id', 'created_at']);
            }

            if (! Schema::hasIndex('ticket_changes', 'event')) {
                $table->index('event');
            }
        });
    }

    public function down(): void
    {
  	//
    }
};