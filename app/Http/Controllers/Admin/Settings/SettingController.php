<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Settings\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\Settings\SettingService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
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
            'settings' => $this->settingService->all(),
        ]);
    }

    /**
     * Create or update a setting.
     */
    public function update(
        UpdateSettingRequest $request,
    ): Response {
        abort_unless(
            Auth::user()?->can('settings.update'),
            403,
        );

        $this->settingService->setData(
            $request->toData(),
        );

        return response('', 200);
    }
}
