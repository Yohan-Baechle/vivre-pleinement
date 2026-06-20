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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('meeting_url')->nullable()->after('notes');
            $table->string('token', 64)->nullable()->unique()->after('reference');
            $table->dateTime('reminded_24h_at')->nullable()->after('cancelled_at');
            $table->dateTime('reminded_1h_at')->nullable()->after('reminded_24h_at');
            $table->dateTime('followed_up_at')->nullable()->after('reminded_1h_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['meeting_url', 'token', 'reminded_24h_at', 'reminded_1h_at', 'followed_up_at']);
        });
    }
};
