<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('shift_date');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('position')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'absent'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['shift_date', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
