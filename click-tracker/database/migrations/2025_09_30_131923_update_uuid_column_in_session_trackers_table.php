<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_trackers', function (Blueprint $table) {
            $table->string('uuid', 250)->change(); 
        });
    }

    public function down(): void
    {
        Schema::table('session_trackers', function (Blueprint $table) {
            $table->string('uuid', 36)->change(); // Volta para o tamanho original
        });
    }
};