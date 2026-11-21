<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\Settings\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class SettingController extends Controller
{
    public function __construct(
        private readonly SettingsManager $settings,
    ) {}

    /**
     * Display the settings index.
     */
    public function index(): View
    {
        abort_unless(
            Auth::user()?->can('settings.view'),
            403,
        );

        return view('admin.settings.index', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function edit(
        string $group,
        string $key,
    ): View {
        $setting = Setting::query()
            ->where('group', $group)
            ->where('key', $key)
            ->firstOrFail();

        return view('admin.settings.edit', [
            'setting' => $setting,
        ]);
    }

    /**
     * Create or update a setting.
     */
    public function update(
        UpdateSettingRequest $request,
    ): RedirectResponse {
        abort_unless(
            Auth::user()?->can('settings.update'),
            403,
        );

        $this->settings->setData(
            $request->toData(),
        );

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Setting updated successfully.');
    }
}
