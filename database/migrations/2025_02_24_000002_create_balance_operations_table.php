<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('balance_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 20);
            $table->string('type', 10);
            $table->unsignedBigInteger('amount');
            $table->string('status', 20)->default('confirmed');
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->string('reference', 255)->nullable();
            $table->json('risk_flags')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'currency', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_operations');
    }
};
