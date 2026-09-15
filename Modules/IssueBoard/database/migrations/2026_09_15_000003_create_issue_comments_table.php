<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('issue_comments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('body');
            $table->boolean('is_question')->default(false);
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['issue_id', 'created_at']);
            $table->index(['is_question', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_comments');
    }
};
