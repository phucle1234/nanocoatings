<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BlockAdminLinkResolver;
use App\Services\HomepageLayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;

class HomepageLayoutController extends Controller
{
    public function __construct(
        protected HomepageLayoutService $homepageLayoutService,
        protected BlockAdminLinkResolver $blockAdminLinkResolver
    ) {}

    public function index(): View
    {
        $blocks = $this->homepageLayoutService->getLayoutBlocksForAdmin();
        $locale = app()->getLocale();
        $blockContentLinks = $this->blockAdminLinkResolver->homepageBlockLinks();

        return view('admin.homepage-layout.index', compact('blocks', 'locale', 'blockContentLinks'));
    }

    public function updateOrder(Request $request): RedirectResponse
    {
        $blockCount = count(config('homepage_layout.blocks', []));

        $validated = $request->validate([
            'blocks' => ['required', 'array', 'size:'.$blockCount],
            'blocks.*.id' => ['required', 'integer'],
            'blocks.*.is_active' => ['nullable', 'boolean'],
        ]);

        $this->homepageLayoutService->saveOrderAndStatus($validated['blocks']);

        Alert::success('Đã lưu thứ tự trang chủ.')->flash();

        return redirect()->route('admin.homepage-layout.index');
    }
}
