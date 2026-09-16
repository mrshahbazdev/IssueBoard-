<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Labels table
        if (! Schema::hasTable('labels')) {
            Schema::create('labels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_owner_id')->nullable()->index();
                $table->string('name');
                $table->string('color', 20)->default('#64748b');
                $table->timestamps();
            });
        }

        // 2. Issue Label pivot table
        if (! Schema::hasTable('issue_label')) {
            Schema::create('issue_label', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
                $table->foreignId('label_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 3. Checklist Items table
        if (! Schema::hasTable('issue_checklist_items')) {
            Schema::create('issue_checklist_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->boolean('is_completed')->default(false);
                $table->integer('position')->default(0);
                $table->timestamps();
            });
        }

        // 4. Issue Activities table
        if (! Schema::hasTable('issue_activities')) {
            Schema::create('issue_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('action');
                $table->string('description')->nullable();
                $table->string('from_value')->nullable();
                $table->string('to_value')->nullable();
                $table->timestamps();

                $table->index(['issue_id', 'created_at']);
            });
        }

        // 5. Workspace Settings table
        if (! Schema::hasTable('workspace_settings')) {
            Schema::create('workspace_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('team_owner_id')->unique();
                $table->string('company_name')->nullable();
                $table->string('logo_path')->nullable();
                $table->string('accent_color', 20)->nullable();
                $table->timestamps();
            });
        }

        // 6. Issues columns (time tracking)
        Schema::table('issues', function (Blueprint $table) {
            if (! Schema::hasColumn('issues', 'estimated_hours')) {
                $table->decimal('estimated_hours', 8, 2)->nullable();
            }
            if (! Schema::hasColumn('issues', 'spent_hours')) {
                $table->decimal('spent_hours', 8, 2)->default(0);
            }
        });

        // 7. Users columns (2FA)
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false);
            }
            if (! Schema::hasColumn('users', 'two_factor_code')) {
                $table->string('two_factor_code', 10)->nullable();
            }
            if (! Schema::hasColumn('users', 'two_factor_expires_at')) {
                $table->timestamp('two_factor_expires_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_settings');
        Schema::dropIfExists('issue_activities');
        Schema::dropIfExists('issue_checklist_items');
        Schema::dropIfExists('issue_label');
        Schema::dropIfExists('labels');

        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn(['estimated_hours', 'spent_hours']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_code', 'two_factor_expires_at', 'two_factor_recovery_codes']);
        });
    }
};
