<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('linked_accounts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('connector_id')->constrained()->onDelete('cascade');
            $table->enum('account_type', ['cpanel_email', 'cpanel_ftp', 'wordpress_user']);
            $table->string('external_id')->nullable();
            $table->string('external_username')->nullable();
            $table->string('external_email')->nullable();
            $table->enum('desired_state', ['active', 'suspended', 'deleted'])->default('active');
            $table->enum('actual_state', ['active', 'suspended', 'deleted', 'unknown'])->nullable();
            $table->boolean('is_suspended')->default(false);
            $table->enum('provisioning_status', ['pending', 'provisioning', 'active', 'failed', 'deprovisioned'])->default('pending');
            $table->string('external_role')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('last_sync_status')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('connector_id');
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('linked_accounts');
    }
};
