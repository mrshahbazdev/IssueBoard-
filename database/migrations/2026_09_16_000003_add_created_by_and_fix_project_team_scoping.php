<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('projects') && ! Schema::hasColumn('projects', 'created_by')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->index();
            });
        }

        // Backfill any projects that had no team_owner_id or created_by
        $firstAdminId = DB::table('users')->whereNull('invited_by')->orderBy('id')->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if ($firstAdminId) {
            DB::table('projects')
                ->whereNull('team_owner_id')
                ->update([
                    'team_owner_id' => $firstAdminId,
                    'created_by' => $firstAdminId,
                ]);

            DB::table('projects')
                ->whereNull('created_by')
                ->whereNotNull('team_owner_id')
                ->update([
                    'created_by' => DB::raw('team_owner_id'),
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('projects') && Schema::hasColumn('projects', 'created_by')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
