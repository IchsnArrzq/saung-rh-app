<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('song_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('table_session_id')->nullable()->constrained('table_sessions')->nullOnDelete();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('table_code')->nullable();
            $table->string('title');
            $table->string('artist')->nullable();
            $table->string('requested_by')->nullable();
            $table->enum('status', ['queued', 'playing', 'done', 'rejected'])->default('queued');
            $table->timestamp('played_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('table_session_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('song_requests');
    }
};
