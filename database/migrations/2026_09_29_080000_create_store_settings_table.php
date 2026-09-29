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
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('EcoStore');
            $table->string('tagline')->nullable()->default('Produk Kriya Berkualitas Ramah Lingkungan');
            $table->string('logo')->nullable();
            $table->string('phone')->default('08985454555');
            $table->string('email')->nullable()->default('kontak@ecostore.com');
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
