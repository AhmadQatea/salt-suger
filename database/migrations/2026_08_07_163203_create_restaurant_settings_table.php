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
        Schema::create('restaurant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('restaurant_name')->default('Salt&Suger');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->text('description')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('currency')->default('ل.س');
            $table->string('primary_color')->default('#c8102e');
            $table->string('secondary_color')->default('#111111');
            $table->string('accent_color')->default('#f5c518');
            $table->boolean('whatsapp_enabled')->default(true);
            $table->text('whatsapp_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_settings');
    }
};
