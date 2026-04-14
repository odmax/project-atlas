<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('connector_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('linked_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_assignment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('job_type');
            $table->string('status');
            $table->string('direction')->nullable();
            $table->string('correlation_id')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('last_error')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('job_type');
            $table->index('status');
            $table->index('connector_id');
            $table->index('user_id');
            $table->index('correlation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_jobs');
    }
};