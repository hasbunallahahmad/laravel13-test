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
        Schema::create('media', function (Blueprint $table): void {
            $table->id();

            /*
             * Public-safe identifier.
             */
            $table->uuid('uuid')
                ->unique();

            /*
             * Original filename provided by the user.
             */
            $table->string('original_name');

            /*
             * Generated filename used internally in storage.
             */
            $table->string('file_name');

            /*
             * Storage disk.
             *
             * Example:
             * public
             * local
             * s3
             */
            $table->string('disk')
                ->default('public');

            /*
             * Relative storage path.
             */
            $table->string('path');

            /*
             * MIME information.
             */
            $table->string('mime_type', 150);

            $table->string('extension', 20);

            /*
             * File size in bytes.
             */
            $table->unsignedBigInteger('size');

            /*
             * Optional metadata.
             *
             * Example for images:
             * width
             * height
             */
            $table->json('metadata')
                ->nullable();

            /*
             * Accessibility / CMS information.
             */
            $table->string('alt_text')
                ->nullable();

            $table->text('caption')
                ->nullable();

            /*
             * User who uploaded the media.
             *
             * Nullable to support imported/system media.
             */
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

            /*
             * Useful indexes.
             */
            $table->index([
                'mime_type',
            ]);

            $table->index([
                'uploaded_by',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
