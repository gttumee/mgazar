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
        Schema::create('lands', function (Blueprint $table) {
        $table->id();
        $table->string('title');
         $table->text('user_id');
        $table->text('description')->nullable();
        $table->string('cadastral_code')->nullable();
        $table->string('phone')->nullable();
        $table->decimal('price', 15, 2);
        $table->decimal('size_ha', 8, 2)->nullable();
        $table->string('district');
        $table->string('rights_type');
        $table->boolean('has_electricity')->default(false);
        $table->boolean('has_water')->default(false);
        $table->boolean('has_fence')->default(false);
        $table->boolean('near_road')->default(false);
        $table->boolean('is_swamp')->default(false);
        $table->boolean('is_sloped')->default(false);
        $table->text('images')->nullable(); // JSON хэлбэрээр хадгалах
        $table->string('latitude')->nullable();
        $table->string('longitude')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lands');
    }
};