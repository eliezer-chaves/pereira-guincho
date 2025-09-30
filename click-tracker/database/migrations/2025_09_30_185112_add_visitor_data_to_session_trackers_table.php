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
        Schema::table('session_trackers', function (Blueprint $table) {
            $table->json('visitor_data')->nullable()->after('info');
            $table->string('ip_address')->nullable()->after('visitor_data');
            $table->string('country')->nullable()->after('ip_address');
            $table->string('city')->nullable()->after('country');
            $table->string('device_type')->nullable()->after('city');
            
            // Adicionar índice para melhor performance nas consultas
            $table->index(['country', 'device_type']);
            $table->index('clicou');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_trackers', function (Blueprint $table) {
            $table->dropColumn(['visitor_data', 'ip_address', 'country', 'city', 'device_type']);
            $table->dropIndex(['country', 'device_type']);
            $table->dropIndex(['clicou']);
        });
    }
};