<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_template_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('service_template_id')->constrained('service_templates')->onDelete('cascade');
            $table->foreignId('connector_id')->constrained()->onDelete('cascade');
            $table->string('account_type');
            $table->string('default_role')->nullable();
            $table->json('metadata_json')->nullable();
            $table->timestamps();

            $table->index('uuid');
            $table->index('service_template_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_template_items');
    }
};