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
        Schema::create('device_version', function (Blueprint $table) {
            $table->id();
            $table->boolean('update_installed')->default(0);
            $table->timestamp('update_date');
            $table->string('uuid', 100)->index();
            $table->foreign('uuid')->references('uuid')->on('devices')->onDelete('cascade');
            $table->string('update_version', 100);
            $table->string('update_file_name', 100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_version');
    }
};
