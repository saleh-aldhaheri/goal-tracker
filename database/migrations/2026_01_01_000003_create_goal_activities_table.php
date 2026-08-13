<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goal_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained('goal_topics')->nullOnDelete();
            $table->foreignId('milestone_id')->nullable()->constrained('goal_milestones')->nullOnDelete();
            $table->string('type'); // App\Enums\ActivityType (free-form, see enum docblock)
            $table->decimal('value', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('occurred_at');
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['goal_id', 'occurred_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goal_activities');
    }
};
