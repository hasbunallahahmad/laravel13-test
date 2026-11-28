<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_sections', function (Blueprint $table): void {
            $table->id();

            $table->string('key', 50)->unique();

            $table->boolean('is_enabled')->default(true);

            $table->unsignedInteger('sort_order')->default(0);

            $table->unsignedInteger('config_version')->default(1);

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_sections');
    }
};
