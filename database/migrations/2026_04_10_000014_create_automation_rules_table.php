<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('trigger_type');
            $table->json('condition_json')->nullable();
            $table->json('action_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('trigger_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};