<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('label');
            $table->enum('type', ['bank', 'ewallet', 'qris'])->default('bank');
            $table->string('provider')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};
