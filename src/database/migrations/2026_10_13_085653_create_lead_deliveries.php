<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_deliveries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lead_id')
                ->constrained('leads')
                ->restrictOnDelete();

            $table->foreignId('partner_id')
                ->constrained('partners')
                ->restrictOnDelete();

            // Идентификатор, полученный от партнёра.
            $table->string('request_id')->nullable();

            // pending, sent, failed, approved, rejected.
            $table->string('status', 32)->default('pending');

            $table->text('last_error')->nullable();
            $table->unsignedSmallInteger('attempts_count')->default(0);

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            // Идентификатор уникален в рамках одного партнёра.
            $table->unique(['partner_id', 'request_id']);
            $table->unique(
                ['lead_id', 'partner_id'],
                'lead_deliveries_lead_partner_unique',
            );

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_deliveries');
    }
};
