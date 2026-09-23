<?php

declare(strict_types=1);

namespace App\ContentBlocks;

use App\ContentBlocks\Contracts\BlockDataContract;
use App\ContentBlocks\Validation\MediaReferenceValidator;
use App\Enums\ContentBlockType;
use App\Models\Content;
use App\Models\ContentBlock;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class PageBuilderService
{
    public function __construct(
        private readonly BlockDataResolver $resolver,
        private readonly MediaReferenceValidator $mediaValidator,
    ) {}

    /**
     * Create a new block for a Page.
     *
     * @param array<string, mixed> $data
     */
    public function createBlock(
        Content $content,
        ContentBlockType $type,
        array $data,
        int $sortOrder = 0,
        bool $isActive = true,
    ): ContentBlock {
        $this->ensurePage($content);

        $blockData = $this->resolveAndValidate($type, $data);

        return DB::transaction(function () use (
            $content,
            $type,
            $blockData,
            $sortOrder,
            $isActive,
        ): ContentBlock {
            return $content->blocks()->create([
                'type' => $type,
                'data' => $blockData->toArray(),
                'sort_order' => $sortOrder,
                'is_active' => $isActive,
            ]);
        });
    }

    /**
     * Update an existing block belonging to the Page.
     *
     * @param array<string, mixed> $data
     */
    public function updateBlock(
        Content $content,
        ContentBlock $block,
        ContentBlockType $type,
        array $data,
        ?int $sortOrder = null,
        ?bool $isActive = null,
    ): ContentBlock {
        $this->ensurePage($content);
        $this->ensureOwnership($content, $block);

        $blockData = $this->resolveAndValidate($type, $data);

        return DB::transaction(function () use (
            $block,
            $type,
            $blockData,
            $sortOrder,
            $isActive,
        ): ContentBlock {
            $attributes = [
                'type' => $type,
                'data' => $blockData->toArray(),
            ];

            if ($sortOrder !== null) {
                $attributes['sort_order'] = $sortOrder;
            }

            if ($isActive !== null) {
                $attributes['is_active'] = $isActive;
            }

            $block->update($attributes);

            return $block->refresh();
        });
    }

    /**
     * Soft-delete an existing block belonging to the Page.
     */
    public function deleteBlock(
        Content $content,
        ContentBlock $block,
    ): void {
        $this->ensurePage($content);
        $this->ensureOwnership($content, $block);

        DB::transaction(function () use ($block): void {
            $block->delete();
        });
    }

    /**
     * Restore a soft-deleted block belonging to the Page.
     */
    public function restoreBlock(
        Content $content,
        ContentBlock $block,
    ): ContentBlock {
        $this->ensurePage($content);
        $this->ensureOwnership($content, $block);

        return DB::transaction(function () use ($block): ContentBlock {
            $block->restore();

            return $block->refresh();
        });
    }

    /**
     * Reorder blocks belonging to the Page.
     *
     * @param array<int, string> $blockUuids
     */
    public function reorderBlocks(
        Content $content,
        array $blockUuids,
    ): void {
        $this->ensurePage($content);

        DB::transaction(function () use ($content, $blockUuids): void {
            $blocks = $content->blocks()
                ->whereIn('uuid', $blockUuids)
                ->get()
                ->keyBy('uuid');

            if ($blocks->count() !== count(array_unique($blockUuids))) {
                throw new InvalidArgumentException(
                    'One or more blocks do not belong to this page.',
                );
            }

            foreach ($blockUuids as $sortOrder => $uuid) {
                $blocks->get($uuid)->update([
                    'sort_order' => $sortOrder,
                ]);
            }
        });
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveAndValidate(
        ContentBlockType $type,
        array $data,
    ): BlockDataContract {
        $blockData = $this->resolver->resolveData($type, $data);

        $this->mediaValidator->validateMany(
            $blockData->mediaReferences(),
        );

        return $blockData;
    }

    private function ensurePage(Content $content): void
    {
        if (! $content->isPage()) {
            throw new InvalidArgumentException(
                'Page Builder can only be used with Page content.',
            );
        }
    }

    private function ensureOwnership(
        Content $content,
        ContentBlock $block,
    ): void {
        if ((int) $block->content_id !== (int) $content->id) {
            throw new InvalidArgumentException(
                'Content block does not belong to this page.',
            );
        }
    }
}
