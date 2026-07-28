<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostCategory;
use App\Services\BlockAdminLinkResolver;
use App\Services\SectorLayoutService;
use App\Services\SectorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\Facades\Alert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SectorLayoutController extends Controller
{
    public function __construct(
        protected SectorLayoutService $sectorLayoutService,
        protected BlockAdminLinkResolver $blockAdminLinkResolver,
        protected SectorService $sectorService
    ) {
    }

    public function index(int $sectorId): View
    {
        $sector = $this->findSector($sectorId);
        $this->sectorService->syncSectorResources($sector);
        $blocks = $this->sectorLayoutService->getLayoutBlocksForAdmin($sector);
        $locale = app()->getLocale();
        $sectorName = $sector->translations->firstWhere('language', $locale)->name
            ?? $sector->translations->first()->name
            ?? 'Ngành';
        $blockContentLinks = $this->blockAdminLinkResolver->sectorBlockLinks($sector);
        $blockBannerLinks = $this->blockAdminLinkResolver->sectorBannerMetaLinks($sector);
        $mediaCategoryButtons = $this->blockAdminLinkResolver->sectorMediaCategoryButtons($sector);

        return view('admin.sector-layout.index', compact(
            'blocks',
            'locale',
            'sector',
            'sectorName',
            'blockContentLinks',
            'blockBannerLinks',
            'mediaCategoryButtons'
        ));
    }

    public function updateOrder(Request $request, int $sectorId): RedirectResponse
    {
        $sector = $this->findSector($sectorId);
        $blockCount = count(config('sector_layout.blocks', []));

        $validated = $request->validate([
            'blocks' => ['required', 'array', 'size:' . $blockCount],
            'blocks.*.id' => ['required', 'integer'],
            'blocks.*.is_active' => ['nullable', 'boolean'],
        ]);

        $this->sectorLayoutService->saveOrderAndStatus($sector, $validated['blocks']);

        Alert::success('Đã lưu thứ tự block cho ngành.')->flash();

        return redirect()->route('admin.sector-layout.index', $sectorId);
    }

    protected function findSector(int $sectorId): PostCategory
    {
        $sector = PostCategory::withoutGlobalScopes()
            ->where('id', $sectorId)
            ->where('is_sector', true)
            ->with('translations')
            ->first();

        if (!$sector) {
            throw new NotFoundHttpException();
        }

        return $sector;
    }
}
