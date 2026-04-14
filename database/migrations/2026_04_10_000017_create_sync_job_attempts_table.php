<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_job_attempts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('sync_job_id')->constrained('sync_jobs')->onDelete('cascade');
            $table->unsignedInteger('attempt_number');
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->json('response_json')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('sync_job_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_job_attempts');
    }
};