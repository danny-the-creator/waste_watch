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
        Schema::create('trashcans', function (Blueprint $table) {
            $table->id();

            $table->string('tag');
            $table->text('description')->nullable();
            $table->string('location');
            $table->integer('fill_level')->default(0);
            $table->boolean('lid_blocked')->default(false);
            $table->boolean('service_lid_blocked')->default(true);
            $table->boolean('lid_jammed')->default(false);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trashcans');
    }
};
