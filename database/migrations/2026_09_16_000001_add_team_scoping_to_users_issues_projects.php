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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('invited_by')->nullable()->index();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('team_owner_id')->nullable()->index();
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->foreignId('team_owner_id')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('team_owner_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('team_owner_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('invited_by');
        });
    }
};
