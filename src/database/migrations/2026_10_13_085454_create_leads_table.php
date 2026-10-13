<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('patronymic', 100)->nullable();

            $table->date('birth_date');

            $table->string('phone', 32);
            $table->string('email')->nullable();

            $table->unsignedBigInteger('amount');
            $table->unsignedSmallInteger('term');

            $table->string('issuing_region')->nullable();
            $table->string('issuing_region_kladr_id', 32)->nullable();
            $table->string('issuing_city')->nullable();

            $table->string('employment_type')->nullable();
            $table->string('income')->nullable();
            $table->string('education_type')->nullable();
            $table->string('marital_status')->nullable();

            // passport, address_registration, address_residence, sub_ids
            $table->json('payload');

            $table->timestamps();

            $table->index('phone');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
