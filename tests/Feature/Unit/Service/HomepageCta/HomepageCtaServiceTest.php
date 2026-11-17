<?php

declare(strict_types=1);

use App\Data\HomepageCta\HomepageCtaButtonData;
use App\Data\HomepageCta\HomepageCtaData;
use App\Models\HomepageCta;
use App\Models\HomepageCtaButton;
use App\Services\HomepageCta\HomepageCtaService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;

uses(RefreshDatabase::class);

it('creates a homepage CTA with its buttons', function (): void {
    $service = app(HomepageCtaService::class);

    $data = new HomepageCtaData(
        title: 'Jelajahi Layanan',
        description: 'Temukan layanan kami.',
        sortOrder: 1,
        isActive: true,
        buttons: [
            new HomepageCtaButtonData(
                label: 'Lihat Layanan',
                type: 'url',
                url: '/layanan',
                target: '_self',
                sortOrder: 1,
                isActive: true,
            ),
            new HomepageCtaButtonData(
                label: 'Beranda',
                type: 'route',
                routeName: 'home',
                target: '_self',
                sortOrder: 2,
                isActive: true,
            ),
        ],
    );

    $homepageCta = $service->create($data);

    expect($homepageCta)
        ->toBeInstanceOf(HomepageCta::class)
        ->and($homepageCta->title)->toBe('Jelajahi Layanan')
        ->and($homepageCta->description)->toBe('Temukan layanan kami.')
        ->and($homepageCta->sort_order)->toBe(1)
        ->and($homepageCta->is_active)->toBeTrue();

    expect($homepageCta->buttons)->toHaveCount(2);

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $homepageCta->id,
        'title' => 'Jelajahi Layanan',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'homepage_cta_id' => $homepageCta->id,
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'homepage_cta_id' => $homepageCta->id,
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
    ]);
});

it('updates a homepage CTA and synchronizes its buttons', function (): void {
    $service = app(HomepageCtaService::class);

    $homepageCta = HomepageCta::query()->create([
        'title' => 'Judul Lama',
        'description' => 'Deskripsi lama.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $existingButton = $homepageCta->buttons()->create([
        'label' => 'Tombol Lama',
        'type' => 'url',
        'url' => '/lama',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $data = new HomepageCtaData(
        title: 'Jelajahi Layanan',
        description: 'Temukan layanan terbaru kami.',
        sortOrder: 2,
        isActive: true,
        buttons: [
            new HomepageCtaButtonData(
                uuid: $existingButton->uuid,
                label: 'Lihat Layanan',
                type: 'url',
                url: '/layanan',
                target: '_blank',
                sortOrder: 1,
                isActive: true,
            ),
            new HomepageCtaButtonData(
                label: 'Beranda',
                type: 'route',
                routeName: 'home',
                target: '_self',
                sortOrder: 2,
                isActive: true,
            ),
        ],
    );

    $updatedCta = $service->update($homepageCta, $data);

    expect($updatedCta->title)->toBe('Jelajahi Layanan')
        ->and($updatedCta->description)->toBe('Temukan layanan terbaru kami.')
        ->and($updatedCta->sort_order)->toBe(2)
        ->and($updatedCta->buttons)->toHaveCount(2);

    $updatedExistingButton = $updatedCta->buttons
        ->firstWhere('uuid', $existingButton->uuid);

    expect($updatedExistingButton)->not->toBeNull()
        ->and($updatedExistingButton->label)->toBe('Lihat Layanan')
        ->and($updatedExistingButton->url)->toBe('/layanan')
        ->and($updatedExistingButton->target)->toBe('_blank');

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $existingButton->id,
        'homepage_cta_id' => $homepageCta->id,
        'label' => 'Lihat Layanan',
        'url' => '/layanan',
    ]);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'homepage_cta_id' => $homepageCta->id,
        'label' => 'Beranda',
        'route_name' => 'home',
    ]);
});

it('soft deletes existing buttons omitted from update data', function (): void {
    $service = app(HomepageCtaService::class);

    $homepageCta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
        'description' => 'Deskripsi lama.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $retainedButton = $homepageCta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $removedButton = $homepageCta->buttons()->create([
        'label' => 'Tombol Lama',
        'type' => 'url',
        'url' => '/lama',
        'target' => '_self',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $data = new HomepageCtaData(
        title: 'Jelajahi Layanan',
        description: 'Deskripsi terbaru.',
        sortOrder: 1,
        isActive: true,
        buttons: [
            new HomepageCtaButtonData(
                uuid: $retainedButton->uuid,
                label: 'Lihat Layanan',
                type: 'url',
                url: '/layanan',
                target: '_self',
                sortOrder: 1,
                isActive: true,
            ),
        ],
    );

    $service->update($homepageCta, $data);

    $this->assertDatabaseHas('homepage_cta_buttons', [
        'id' => $retainedButton->id,
        'deleted_at' => null,
    ]);

    $this->assertSoftDeleted('homepage_cta_buttons', [
        'id' => $removedButton->id,
    ]);

    expect($homepageCta->buttons()->count())->toBe(1);
});

it('rejects a button uuid belonging to another homepage CTA', function (): void {
    $service = app(HomepageCtaService::class);

    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Pertama',
        'description' => 'Deskripsi pertama.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $anotherCta = HomepageCta::query()->create([
        'title' => 'CTA Kedua',
        'description' => 'Deskripsi kedua.',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $foreignButton = $anotherCta->buttons()->create([
        'label' => 'Tombol CTA Kedua',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $data = new HomepageCtaData(
        title: 'CTA Pertama Diperbarui',
        description: 'Deskripsi diperbarui.',
        sortOrder: 1,
        isActive: true,
        buttons: [
            new HomepageCtaButtonData(
                uuid: $foreignButton->uuid,
                label: 'Tombol Manipulasi',
                type: 'url',
                url: '/manipulasi',
                target: '_self',
                sortOrder: 1,
                isActive: true,
            ),
        ],
    );

    expect(fn () => $service->update($homepageCta, $data))
        ->toThrow(
            InvalidArgumentException::class,
            'The selected button does not belong to this homepage CTA.',
        );

    expect($homepageCta->fresh()->title)->toBe('CTA Pertama')
        ->and($foreignButton->fresh()->label)->toBe('Tombol CTA Kedua');
});

it('soft deletes a homepage CTA and its active buttons', function (): void {
    $service = app(HomepageCtaService::class);

    $homepageCta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
        'description' => 'Temukan layanan kami.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $activeButton = $homepageCta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $inactiveButton = $homepageCta->buttons()->create([
        'label' => 'Beranda',
        'type' => 'route',
        'route_name' => 'home',
        'target' => '_self',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    $service->delete($homepageCta);

    $this->assertSoftDeleted('homepage_ctas', [
        'id' => $homepageCta->id,
    ]);

    $this->assertSoftDeleted('homepage_cta_buttons', [
        'id' => $activeButton->id,
    ]);

    $this->assertSoftDeleted('homepage_cta_buttons', [
        'id' => $inactiveButton->id,
    ]);

    expect(HomepageCta::withTrashed()->find($homepageCta->id))
        ->not->toBeNull()
        ->and(HomepageCta::withTrashed()->find($homepageCta->id)->buttons()->withTrashed()->count())
        ->toBe(2);
});

it('restores a soft deleted homepage CTA without restoring its buttons', function (): void {
    $service = app(HomepageCtaService::class);

    $homepageCta = HomepageCta::query()->create([
        'title' => 'Jelajahi Layanan',
        'description' => 'Temukan layanan kami.',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $button = $homepageCta->buttons()->create([
        'label' => 'Lihat Layanan',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $service->delete($homepageCta);

    $deletedCta = HomepageCta::withTrashed()
        ->findOrFail($homepageCta->id);

    $restoredCta = $service->restore($deletedCta);

    expect($restoredCta->trashed())->toBeFalse()
        ->and($restoredCta->title)->toBe('Jelajahi Layanan')
        ->and($restoredCta->buttons)->toHaveCount(0);

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $homepageCta->id,
        'deleted_at' => null,
    ]);

    $this->assertSoftDeleted('homepage_cta_buttons', [
        'id' => $button->id,
    ]);
});

it('rejects restoring a homepage CTA that is not deleted', function () {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Aktif',
        'description' => 'CTA yang belum dihapus',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    expect(fn () => app(HomepageCtaService::class)->restore($homepageCta))
        ->toThrow(DomainException::class);

    $this->assertDatabaseHas('homepage_ctas', [
        'id' => $homepageCta->id,
        'deleted_at' => null,
    ]);
});

it('force deletes a homepage CTA and its buttons', function () {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA untuk force delete',
        'description' => 'Deskripsi CTA',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $activeButton = $homepageCta->buttons()->create([
        'label' => 'Tombol aktif',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $deletedButton = $homepageCta->buttons()->create([
        'label' => 'Tombol terhapus',
        'type' => 'url',
        'url' => '/berita',
        'target' => '_self',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $deletedButton->delete();

    $homepageCtaId = $homepageCta->id;
    $activeButtonId = $activeButton->id;
    $deletedButtonId = $deletedButton->id;

    app(HomepageCtaService::class)->forceDelete($homepageCta);

    expect(HomepageCta::withTrashed()->find($homepageCtaId))
        ->toBeNull()
        ->and(HomepageCtaButton::withTrashed()->find($activeButtonId))
        ->toBeNull()
        ->and(HomepageCtaButton::withTrashed()->find($deletedButtonId))
        ->toBeNull();
});

it('rejects duplicate button uuids during update and rolls back changes', function () {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Sebelum Update',
        'description' => 'Deskripsi awal',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $button = $homepageCta->buttons()->create([
        'label' => 'Tombol Awal',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $data = new HomepageCtaData(
        title: 'CTA Setelah Update',
        description: 'Deskripsi baru',
        sortOrder: 2,
        isActive: true,
        buttons: [
            new HomepageCtaButtonData(
                label: 'Tombol Pertama',
                type: 'url',
                url: '/berita',
                uuid: $button->uuid,
            ),
            new HomepageCtaButtonData(
                label: 'Tombol Kedua',
                type: 'url',
                url: '/publikasi',
                uuid: $button->uuid,
            ),
        ],
    );

    expect(fn () => app(HomepageCtaService::class)->update(
        $homepageCta,
        $data,
    ))->toThrow(InvalidArgumentException::class);

    $homepageCta->refresh();
    $button->refresh();

    expect($homepageCta->title)->toBe('CTA Sebelum Update')
        ->and($homepageCta->description)->toBe('Deskripsi awal')
        ->and($button->label)->toBe('Tombol Awal')
        ->and($button->url)->toBe('/layanan')
        ->and($homepageCta->buttons()->count())->toBe(1);
});

it('rejects creating a homepage CTA without buttons', function () {
    $data = new HomepageCtaData(
        title: 'CTA Tanpa Tombol',
        description: null,
        sortOrder: 1,
        isActive: true,
        buttons: [],
    );

    expect(fn () => app(HomepageCtaService::class)->create($data))
        ->toThrow(InvalidArgumentException::class);

    expect(HomepageCta::query()->count())->toBe(0);
});

it('rejects updating a homepage CTA without buttons and preserves existing data', function () {
    $homepageCta = HomepageCta::query()->create([
        'title' => 'CTA Sebelum Update',
        'description' => 'Deskripsi awal',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $homepageCta->buttons()->create([
        'label' => 'Tombol Awal',
        'type' => 'url',
        'url' => '/layanan',
        'target' => '_self',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $data = new HomepageCtaData(
        title: 'CTA Setelah Update',
        description: 'Deskripsi baru',
        sortOrder: 2,
        isActive: false,
        buttons: [],
    );

    expect(fn () => app(HomepageCtaService::class)->update(
        $homepageCta,
        $data,
    ))->toThrow(InvalidArgumentException::class);

    $homepageCta->refresh();

    expect($homepageCta->title)->toBe('CTA Sebelum Update')
        ->and($homepageCta->description)->toBe('Deskripsi awal')
        ->and($homepageCta->sort_order)->toBe(1)
        ->and($homepageCta->is_active)->toBeTrue()
        ->and($homepageCta->buttons()->count())->toBe(1);
});
