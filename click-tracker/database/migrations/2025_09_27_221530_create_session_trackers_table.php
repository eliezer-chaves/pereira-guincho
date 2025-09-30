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
        Schema::create('session_trackers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->date('initialTime')->nullable();
            $table->date('lastTime')->nullable();
            $table->string('time')->nullable(); // formato 00:00:00
            $table->boolean('clicou')->default(false); // se clicou em algum botão
            $table->json('info')->nullable(); // dados do usuário (userAgent, language etc.)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_trackers');
    }
};
