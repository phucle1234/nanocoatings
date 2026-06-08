<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\HomepageLayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;

class HomepageLayoutController extends Controller
{
    public function __construct(
        protected HomepageLayoutService $homepageLayoutService
    ) {
    }

    public function index(): View
    {
        $blocks = $this->homepageLayoutService->getLayoutBlocksForAdmin();
        $locale = app()->getLocale();

        return view('admin.homepage-layout.index', compact('blocks', 'locale'));
    }

    public function updateOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'blocks' => ['required', 'array', 'size:9'],
            'blocks.*.id' => ['required', 'integer'],
            'blocks.*.is_active' => ['nullable', 'boolean'],
        ]);

        $this->homepageLayoutService->saveOrderAndStatus($validated['blocks']);

        Alert::success('Đã lưu thứ tự trang chủ.')->flash();

        return redirect()->route('admin.homepage-layout.index');
    }
}
