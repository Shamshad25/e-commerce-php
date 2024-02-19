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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->double('subtotal',10,2);
            $table->double('shipping',10,2);
           $table->string('coupon_code')->nullable();
            $table->integer('coupon_code_id')->nullable();
            $table->double('discount',10,2)->nullable();
            $table->double('grand_total',10,2);

            // migration update

            // $table->string('coupon_code')->nullable();
            // $table->integer('coupon_code_id')->nullable();
            // $table->double('discount',10,2)->nullable();

            // User Address related columns

            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('mobile');
            $table->foreignId('country_id');
            $table->text('address');
            $table->string('appartment')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
