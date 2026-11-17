<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\HomepageCta;

use App\Data\HomepageCta\HomepageCtaButtonData;
use App\Data\HomepageCta\HomepageCtaData;
use App\Http\Requests\Admin\HomepageCta\Concerns\HasHomepageCtaRules;
use Illuminate\Foundation\Http\FormRequest;

final class StoreHomepageCtaRequest extends FormRequest
{
    use HasHomepageCtaRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->homepageCtaRules();
    }

    public function toData(): HomepageCtaData
    {
        $validated = $this->validated();

        $buttons = array_map(
            fn (array $button): HomepageCtaButtonData => new HomepageCtaButtonData(
                label: $button['label'],
                type: $button['type'],
                url: $button['url'] ?? null,
                routeName: $button['route_name'] ?? null,
                target: $button['target'] ?? '_self',
                sortOrder: (int) ($button['sort_order'] ?? 0),
                isActive: (bool) ($button['is_active'] ?? true),
            ),
            $validated['buttons'],
        );

        return new HomepageCtaData(
            title: $validated['title'],
            description: $validated['description'] ?? null,
            sortOrder: (int) ($validated['sort_order'] ?? 0),
            isActive: (bool) ($validated['is_active'] ?? true),
            buttons: $buttons,
        );
    }
}
