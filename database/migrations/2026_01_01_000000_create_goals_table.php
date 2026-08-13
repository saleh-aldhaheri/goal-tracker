<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type'); // App\Enums\GoalType
            $table->string('status')->default('active'); // App\Enums\GoalStatus
            $table->string('priority')->default('medium'); // App\Enums\GoalPriority
            $table->string('tracking_mode'); // App\Enums\TrackingMode
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('target_value', 10, 2)->nullable();
            $table->string('target_unit')->nullable();
            // Flexible, goal-type-specific configuration (e.g. habit frequency,
            // recurring cadence, manual percentage) — see spec section 8 & 11.
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
