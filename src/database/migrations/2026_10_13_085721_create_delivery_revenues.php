<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_revenues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('delivery_id')
                ->constrained('lead_deliveries')
                ->restrictOnDelete();

            $table->foreignId('webhook_event_id')
                ->constrained('webhook_events')
                ->restrictOnDelete();

            $table->unsignedBigInteger('amount');
            $table->char('currency', 3)->default('RUB');

            $table->timestamps();

            // В рамках текущей модели у доставки одно начисление.
            $table->unique('delivery_id');

            // Одно событие не может создать два начисления.
            $table->unique('webhook_event_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_revenues');
    }
};
