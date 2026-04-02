<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connectors', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->enum('type', ['cpanel', 'wordpress']);
            $table->string('base_url');
            $table->string('username')->nullable();
            $table->text('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('ssl_verify')->default(true);
            $table->integer('timeout_seconds')->default(30);
            $table->json('meta_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connectors');
    }
};
