<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('homepage_cta_buttons', function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();

            $table->foreignId('homepage_cta_id')
                ->constrained('homepage_ctas')
                ->cascadeOnDelete();

            $table->string('label', 100);
            $table->string('type', 10);
            $table->string('url', 2048)->nullable();
            $table->string('route_name', 100)->nullable();
            $table->string('target', 10)->default('_self');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['homepage_cta_id', 'is_active', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_cta_buttons');
    }
};
