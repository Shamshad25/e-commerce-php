<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name')->default('');
            $table->string('last_name');
            $table->string('email');
            $table->string('mobile');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->text('address');
            $table->string('appartment')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->timestamps();
        });
    }

    // "SQLSTATE[HY000]: General error: 1364 Field 'first_name' doesn't have a default value (Connection: mysql, SQL: insert into `customer_addresses` (`user_id`, `last_name`, `email`, `mobile`, `country_id`, `address`, `appartment`, `city`, `state`, `zip`, `updated_at`, `created_at`) values (3, Hingmang, joseph@gmail.com, 08327251923, 100, Guabari, Jaigaon, Jaygaon(CT), Jalpaiguri, 23424, Jaigaon, West Bengal, 736182, 2024-02-02 15:48:00, 2024-02-02 15:48:00))

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
