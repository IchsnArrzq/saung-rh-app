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
        Schema::create('visitor_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->foreignUuid('table_session_id')->nullable()->constrained('table_sessions')->nullOnDelete();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['qr', 'walk_in', 'reservation'])->default('walk_in');
            $table->unsignedInteger('pax')->default(1);
            $table->timestamp('visited_at');
            $table->timestamps();

            $table->index('visited_at');
            $table->index(['source', 'visited_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitor_logs');
    }
};
