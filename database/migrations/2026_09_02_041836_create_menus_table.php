<?php

declare(strict_types=1);

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
        Schema::create('menus', function (Blueprint $table): void {
            $table->id();

            /*
             * Public-safe identifier.
             */
            $table->uuid('uuid')
                ->unique();

            /*
             * Self-referencing hierarchy.
             */
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('menus')
                ->nullOnDelete();

            /*
             * Internal identifier and visible label.
             */
            $table->string('name')
                ->unique();

            $table->string('label');

            /*
             * Destination type.
             *
             * Supported values:
             * - route
             * - url
             */
            $table->string('type', 20);

            /*
             * Destination fields.
             */
            $table->string('url')
                ->nullable();

            $table->string('route_name')
                ->nullable();

            /*
             * Browser target.
             */
            $table->string('target', 20)
                ->default('_self');

            /*
             * Optional presentation.
             */
            $table->string('icon')
                ->nullable();

            /*
             * Ordering and visibility.
             */
            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->boolean('is_active')
                ->default(true);

            $table->timestamps();
            $table->softDeletes();

            /*
             * Query optimization.
             */
            $table->index(['parent_id', 'sort_order']);
            $table->index(['is_active']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
