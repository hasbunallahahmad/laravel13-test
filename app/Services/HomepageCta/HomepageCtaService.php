<?php

declare(strict_types=1);

namespace App\Services\HomepageCta;

use App\Data\HomepageCta\HomepageCtaButtonData;
use App\Data\HomepageCta\HomepageCtaData;
use App\Models\HomepageCta;
use App\Models\HomepageCtaButton;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class HomepageCtaService
{
    /**
     * Create a homepage CTA and all its buttons atomically.
     */
    public function create(HomepageCtaData $data): HomepageCta
    {
        if ($data->buttons === []) {
            throw new InvalidArgumentException(
                'A homepage CTA must have at least one button.',
            );
        }

        return DB::transaction(function () use ($data): HomepageCta {
            $homepageCta = HomepageCta::query()->create([
                'title' => $data->title,
                'description' => $data->description,
                'sort_order' => $data->sortOrder,
                'is_active' => $data->isActive,
            ]);

            foreach ($data->buttons as $button) {
                if (! $button instanceof HomepageCtaButtonData) {
                    throw new InvalidArgumentException(
                        'Every homepage CTA button must use HomepageCtaButtonData.',
                    );
                }

                $homepageCta->buttons()->create(
                    $this->buttonAttributes($button),
                );
            }

            return $homepageCta->load('buttons');
        });
    }

    /**
     * Update a homepage CTA and synchronize its buttons atomically.
     */
    public function update(
        HomepageCta $homepageCta,
        HomepageCtaData $data,
    ): HomepageCta {
        if ($data->buttons === []) {
            throw new InvalidArgumentException(
                'A homepage CTA must have at least one button.',
            );
        }

        return DB::transaction(function () use (
            $homepageCta,
            $data,
        ): HomepageCta {
            $homepageCta->update([
                'title' => $data->title,
                'description' => $data->description,
                'sort_order' => $data->sortOrder,
                'is_active' => $data->isActive,
            ]);

            $retainedButtonIds = [];
            $submittedUuids = [];

            foreach ($data->buttons as $button) {
                if (! $button instanceof HomepageCtaButtonData) {
                    throw new InvalidArgumentException(
                        'Every homepage CTA button must use HomepageCtaButtonData.',
                    );
                }

                if ($button->uuid !== null) {
                    if (in_array($button->uuid, $submittedUuids, true)) {
                        throw new InvalidArgumentException(
                            'A homepage CTA button UUID may only be submitted once.',
                        );
                    }

                    $existingButton = $homepageCta->buttons()
                        ->where('uuid', $button->uuid)
                        ->first();

                    if (! $existingButton instanceof HomepageCtaButton) {
                        throw new InvalidArgumentException(
                            'The selected button does not belong to this homepage CTA.',
                        );
                    }

                    $existingButton->update(
                        $this->buttonAttributes($button),
                    );

                    $retainedButtonIds[] = $existingButton->id;
                    $submittedUuids[] = $button->uuid;

                    continue;
                }

                $newButton = $homepageCta->buttons()->create(
                    $this->buttonAttributes($button),
                );

                $retainedButtonIds[] = $newButton->id;
            }

            $homepageCta->buttons()
                ->whereNotIn('id', $retainedButtonIds)
                ->delete();

            return $homepageCta->refresh()->load('buttons');
        });
    }

    /**
     * Restore a soft deleted homepage CTA and buttons deleted with it.
     */
    public function restore(HomepageCta $homepageCta): HomepageCta
    {
        return DB::transaction(function () use ($homepageCta): HomepageCta {
            if (! $homepageCta->trashed()) {
                throw new DomainException(
                    'The homepage CTA has not been deleted.',
                );
            }

            $homepageCta->restore();

            $homepageCta->buttons()
                ->withTrashed()
                ->where('deleted_with_homepage_cta', true)
                ->restore();

            $homepageCta->buttons()
                ->where('deleted_with_homepage_cta', true)
                ->update([
                    'deleted_with_homepage_cta' => false,
                ]);

            return $homepageCta->refresh()->load('buttons');
        });
    }

    /**
     * Permanently delete a homepage CTA.
     *
     * Related buttons are permanently deleted through the database
     * foreign key cascade constraint.
     */
    public function forceDelete(HomepageCta $homepageCta): void
    {
        DB::transaction(function () use ($homepageCta): void {
            $homepageCta->forceDelete();
        });
    }

    /**
     * Convert button DTO into model attributes.
     *
     * UUID is intentionally excluded because new buttons
     * must receive their UUID from the model.
     *
     * @return array{
     *     label: string,
     *     type: string,
     *     url: ?string,
     *     route_name: ?string,
     *     target: string,
     *     sort_order: int,
     *     is_active: bool
     * }
     */
    private function buttonAttributes(
        HomepageCtaButtonData $button,
    ): array {
        return [
            'label' => $button->label,
            'type' => $button->type,
            'url' => $button->url,
            'route_name' => $button->routeName,
            'target' => $button->target,
            'sort_order' => $button->sortOrder,
            'is_active' => $button->isActive,
        ];
    }

    /**
     * Soft delete a homepage CTA and all its active buttons atomically.
     */
    public function delete(HomepageCta $homepageCta): void
    {
        DB::transaction(function () use ($homepageCta): void {
            $homepageCta->buttons()->update([
                'deleted_with_homepage_cta' => true,
            ]);

            $homepageCta->buttons()->delete();

            $homepageCta->delete();
        });
    }
}
