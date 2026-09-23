<?php

declare(strict_types=1);

namespace App\ContentBlocks\Contracts;

interface BlockDataContract
{
    /**
     * @param array<string, mixed> $data
     */
    public static function validate(array $data): void;

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static;

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;

    /**
     * Return UUIDs of media referenced by this block.
     *
     * @return array<int, string>
     */
    public function mediaReferences(): array;
}
