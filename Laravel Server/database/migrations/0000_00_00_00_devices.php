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
        Schema::create('devices', function (Blueprint $table) {
            $table->string('uuid', 100)->unique()->primary();
            $table->timestamp('last_check')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->string('customer_name',100)->nullable();
            $table->string('wifi_username',100)->nullable();
            $table->string('wifi_password',100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
