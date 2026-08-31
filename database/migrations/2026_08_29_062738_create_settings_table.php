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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Public-safe identifier
            $table->uuid('uuid')->unique();

            // Setting grouping
            $table->string('group', 100)->index();

            // Unique key inside each group
            $table->string('key', 150);

            // Value and its intended data type
            $table->longText('value')->nullable();

            $table->string('type', 50)
                ->default('string');

            // Determines whether the setting may be exposed publicly
            $table->boolean('is_public')
                ->default(false)
                ->index();

            $table->timestamps();

            // Prevent duplicate settings inside the same group
            $table->unique(['group', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
