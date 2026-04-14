<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rule_runs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('automation_rule_id')->constrained('automation_rules')->onDelete('cascade');
            $table->string('status');
            $table->json('result_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('automation_rule_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rule_runs');
    }
};