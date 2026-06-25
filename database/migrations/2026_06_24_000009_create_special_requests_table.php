<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('table_session_id')->nullable()->constrained('table_sessions')->nullOnDelete();
            $table->foreignUuid('table_id')->nullable()->constrained('tables')->nullOnDelete();
            $table->string('table_code')->nullable();
            $table->string('requested_by')->nullable();
            $table->enum('category', ['service', 'kitchen', 'ambience', 'celebration', 'other'])->default('other');
            $table->text('description');
            $table->boolean('is_paid')->default(false);
            $table->decimal('price', 12, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'assigned', 'done'])->default('pending');
            $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_requests');
    }
};
