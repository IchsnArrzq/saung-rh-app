<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Polymorphic owner (Menu, and later Ingredient / Contact, dll)
            $table->uuidMorphs('mediable');
            $table->enum('type', ['image', 'video'])->default('image');
            $table->string('disk', 30)->default('public');
            $table->string('path'); // relative path on the disk
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->nullable(); // bytes
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
