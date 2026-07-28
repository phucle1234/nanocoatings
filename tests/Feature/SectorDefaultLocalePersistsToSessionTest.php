<?php

namespace Tests\Feature;

use App\Http\Controllers\langding\SectorController;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Session\NullSessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\App;
use ReflectionMethod;
use Tests\TestCase;

class SectorDefaultLocalePersistsToSessionTest extends TestCase
{
    private function requestWithSession(): Request
    {
        $request = Request::create('/applications/test-sector');
        $request->setLaravelSession(new Store('test', new NullSessionHandler));

        return $request;
    }

    public function test_sector_default_locale_is_persisted_to_session_for_subsequent_requests(): void
    {
        App::setLocale('en');

        $sector = new PostCategory(['default_locale' => 'vi']);
        $request = $this->requestWithSession();

        $controller = app(SectorController::class);
        $method = new ReflectionMethod($controller, 'applySectorDefaultLocale');
        $method->setAccessible(true);

        $locale = $method->invoke($controller, $request, $sector);

        $this->assertSame('vi', $locale);
        $this->assertSame('vi', app()->getLocale());

        // Pagination / post-detail navigation issues a brand new request that only
        // has the session to go on (no route-level locale prefix exists on this
        // site). If the sector's default locale never reaches the session, the
        // next request's SetLocale middleware falls back to whatever locale was
        // there before (usually 'en'), flipping listings back to English and
        // breaking slug lookups scoped to the wrong locale.
        $this->assertSame('vi', $request->session()->get('locale'));
    }

    public function test_manual_language_selection_still_wins_over_sector_default_locale(): void
    {
        App::setLocale('en');

        $sector = new PostCategory(['default_locale' => 'vi']);
        $request = $this->requestWithSession();
        $request->session()->put('locale_manually_selected', true);
        $request->session()->put('locale', 'en');

        $controller = app(SectorController::class);
        $method = new ReflectionMethod($controller, 'applySectorDefaultLocale');
        $method->setAccessible(true);

        $locale = $method->invoke($controller, $request, $sector);

        $this->assertSame('en', $locale);
        $this->assertSame('en', $request->session()->get('locale'));
    }
}
