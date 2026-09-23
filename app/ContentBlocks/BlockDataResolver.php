<?php

declare(strict_types=1);

namespace App\ContentBlocks;

use App\ContentBlocks\Contracts\BlockDataContract;
use App\ContentBlocks\Data\CtaBlockData;
use App\ContentBlocks\Data\GalleryBlockData;
use App\ContentBlocks\Data\HeroBlockData;
use App\ContentBlocks\Data\ImageBlockData;
use App\ContentBlocks\Data\TextBlockData;
use App\Enums\ContentBlockType;
use App\Models\ContentBlock;
use InvalidArgumentException;

final class BlockDataResolver
{
    /**
     * @var array<string, class-string<BlockDataContract>>
     */
    private const DATA_CLASSES = [
        ContentBlockType::HERO->value => HeroBlockData::class,
        ContentBlockType::TEXT->value => TextBlockData::class,
        ContentBlockType::IMAGE->value => ImageBlockData::class,
        ContentBlockType::GALLERY->value => GalleryBlockData::class,
        ContentBlockType::CTA->value => CtaBlockData::class,
    ];

    public function resolve(ContentBlock $block): BlockDataContract
    {
        $type = $block->type;

        if (! $type instanceof ContentBlockType) {
            throw new InvalidArgumentException(
                'Content block type is invalid.',
            );
        }

        return $this->resolveData(
            $type,
            $block->data,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function resolveData(
        ContentBlockType $type,
        array $data,
    ): BlockDataContract {
        $dataClass = self::DATA_CLASSES[$type->value] ?? null;

        if ($dataClass === null) {
            throw new InvalidArgumentException(
                "Unsupported content block type [{$type->value}].",
            );
        }

        return $dataClass::fromArray($data);
    }

    /**
     * @return class-string<BlockDataContract>
     */
    public function dataClass(ContentBlockType $type): string
    {
        $dataClass = self::DATA_CLASSES[$type->value] ?? null;

        if ($dataClass === null) {
            throw new InvalidArgumentException(
                "Unsupported content block type [{$type->value}].",
            );
        }

        return $dataClass;
    }
}
