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
        Schema::create('ticket_imports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_source_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('status_id')
                ->constrained('ticket_import_statuses')
                ->restrictOnDelete();

            $table->unsignedInteger('tickets_count')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('updated_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();

            $table->timestamps();

            $table->index(['ticket_source_id', 'created_at']);
            $table->index('status_id');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_imports');
    }
};
