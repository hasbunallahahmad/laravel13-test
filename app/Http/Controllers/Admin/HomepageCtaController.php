<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HomepageCta\StoreHomepageCtaRequest;
use App\Http\Requests\Admin\HomepageCta\UpdateHomepageCtaRequest;
use App\Models\HomepageCta;
use App\Services\HomepageCta\HomepageCtaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class HomepageCtaController extends Controller
{
    public function index(): View
    {
        $homepageCtas = HomepageCta::query()
            ->with('buttons')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(15);

        return view('admin.homepage-ctas.index', compact('homepageCtas'));
    }

    public function create(): View
    {
        return view('admin.homepage-ctas.create');
    }

    public function edit(HomepageCta $homepageCta): View
    {
        $homepageCta->load('buttons');

        return view('admin.homepage-ctas.edit', compact('homepageCta'));
    }

    public function update(
        UpdateHomepageCtaRequest $request,
        HomepageCta $homepageCta,
        HomepageCtaService $homepageCtaService,
    ): RedirectResponse {
        $homepageCtaService->update(
            $homepageCta,
            $request->toData(),
        );

        return redirect()
            ->route('admin.homepage-ctas.index')
            ->with('success', 'Homepage CTA berhasil diperbarui.');
    }

    public function store(
        StoreHomepageCtaRequest $request,
        HomepageCtaService $homepageCtaService,
    ): RedirectResponse {
        $homepageCtaService->create($request->toData());

        return redirect()
            ->route('admin.homepage-ctas.index')
            ->with('success', 'Homepage CTA berhasil dibuat.');
    }

    public function destroy(
        HomepageCta $homepageCta,
        HomepageCtaService $homepageCtaService,
    ): RedirectResponse {
        $homepageCtaService->delete($homepageCta);

        return redirect()
            ->route('admin.homepage-ctas.index')
            ->with('success', 'Homepage CTA berhasil dihapus.');
    }

    public function trash(): View
    {
        $homepageCtas = HomepageCta::onlyTrashed()
            ->with([
                'buttons' => fn ($query) => $query->withTrashed(),
            ])
            ->orderByDesc('deleted_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('admin.homepage-ctas.trash', compact('homepageCtas'));
    }

    public function restore(
        HomepageCta $homepageCta,
        HomepageCtaService $homepageCtaService,
    ): RedirectResponse {
        $homepageCtaService->restore($homepageCta);

        return redirect()
            ->route('admin.homepage-ctas.trash')
            ->with('success', 'Homepage CTA berhasil dipulihkan.');
    }

    public function forceDelete(
        HomepageCta $homepageCta,
        HomepageCtaService $homepageCtaService,
    ): RedirectResponse {
        $homepageCtaService->forceDelete($homepageCta);

        return redirect()
            ->route('admin.homepage-ctas.trash')
            ->with('success', 'Homepage CTA berhasil dihapus secara permanen.');
    }
}
