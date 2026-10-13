<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();

            // Партнёр определяется по проверенной аутентификации webhook.
            $table->foreignId('partner_id')
                ->constrained('partners')
                ->restrictOnDelete();

            // Может быть NULL, если request_id неизвестен.
            $table->foreignId('delivery_id')
                ->nullable()
                ->constrained('lead_deliveries')
                ->restrictOnDelete();

            $table->string('request_id');

            // Если партнёр присылает уникальный ID события.
            $table->string('event_id')->nullable();

            $table->string('status', 32)->nullable();
            $table->unsignedBigInteger('amount')->nullable();

            $table->json('payload');

            // Для диагностики и поиска повторяющихся тел, не как единственный
            // механизм идемпотентности.
            $table->char('payload_hash', 64);

            // received, processed, ignored_duplicate, not_found, failed.
            $table->string('processing_result', 32)->default('received');

            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Если event_id передан, он уникален в рамках партнёра.
            $table->unique(['partner_id', 'event_id']);

            $table->index(['partner_id', 'request_id']);
            $table->index(['delivery_id', 'received_at']);
            $table->unique(
                ['partner_id', 'payload_hash'],
                'webhook_events_partner_payload_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
