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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('merchant_id')->default(null);
            $table->string('payment_id')->default(null);
            $table->string('status');
            $table->string('amount');
            $table->string('amount_paid');
            $table->string('sign');
            $table->integer('limit');
            $table->string('provider');
            $table->integer('project')->default(null);
            $table->integer('invoice')->default(null);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment');
    }
};
