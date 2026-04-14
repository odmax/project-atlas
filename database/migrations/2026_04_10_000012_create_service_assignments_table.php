<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_assignments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('connector_id')->nullable()->constrained()->nullOnDelete();
            $table->string('account_type');
            $table->string('desired_state')->default('active');
            $table->string('default_role')->nullable();
            $table->enum('status', ['pending', 'provisioning', 'active', 'suspended', 'failed'])->default('pending');
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_assignments');
    }
};