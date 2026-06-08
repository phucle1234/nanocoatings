/**
 * Phân trang AJAX theo tab (news) hoặc một khối (award).
 * LangdingTabFeed.initRoots() sau DOMContentLoaded.
 */
(function (window) {
    'use strict';

    function buildUrl(base, params) {
        var sep = base.indexOf('?') >= 0 ? '&' : '?';
        return base + sep + new URLSearchParams(params).toString();
    }

    function tryOpenAwardGalleryAll(root, clickedHref) {
        try {
            if (typeof Fancybox === 'undefined' || !Fancybox.show) return false;
            var url = root.getAttribute('data-tab-feed-url');
            var categoryId = root.getAttribute('data-tab-feed-category-id');
            if (!url || !categoryId) return false;

            var cacheKey = 'awardAllTiles::' + categoryId;
            var cached = root[cacheKey];

            function openWithTiles(tiles) {
                if (!Array.isArray(tiles) || tiles.length === 0) return false;
                var items = tiles.map(function (t) {
                    return {
                        src: t.image_url,
                        caption: t.title || '',
                    };
                });
                var startIndex = 0;
                for (var i = 0; i < tiles.length; i++) {
                    if (tiles[i] && tiles[i].image_url === clickedHref) {
                        startIndex = i;
                        break;
                    }
                }
                Fancybox.show(items, {
                    startIndex: startIndex,
                    mainClass: 'award-fancybox',
                    autoSize: true,
                    width: 900,
                    height: 600,
                    loop: true,
                    buttons: ['zoom', 'slideshow', 'thumbs', 'close'],
                    placeFocusBack: true,
                    trapFocus: true,
                });
                return true;
            }

            if (cached) {
                return openWithTiles(cached);
            }

            var fullUrl = buildUrl(url, {
                type: 'award',
                category_id: categoryId,
                all: '1',
            });

            fetch(fullUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data || !data.success || !Array.isArray(data.tiles)) return;
                    root[cacheKey] = data.tiles;
                    openWithTiles(data.tiles);
                });

            return true;
        } catch (e) {
            return false;
        }
    }

    function rebindAwardFancybox() {
        if (typeof Fancybox === 'undefined') return;
        Fancybox.bind('[data-fancybox="award-gallery"]', {
            mainClass: 'award-fancybox',
            autoSize: true,
            width: 900,
            height: 600,
            loop: true,
            buttons: ['zoom', 'slideshow', 'thumbs', 'close'],
            placeFocusBack: true,
            trapFocus: true,
        });
    }

    function getPagerState(surface) {
        if (!surface) return { current: 1, last: 1 };
        var label = surface.querySelector('.js-tab-feed-page-label');
        if (!label) return { current: 1, last: 1 };
        var parts = label.textContent.split('/');
        return {
            current: parseInt((parts[0] || '').trim(), 10) || 1,
            last: parseInt((parts[1] || '').trim(), 10) || 1,
        };
    }

    function fetchSurface(root, pane, page) {
        var url = root.getAttribute('data-tab-feed-url');
        var type = root.getAttribute('data-tab-feed-type') || 'news';
        // award per_page phải khớp với initial render (AboutController đang dùng 4)
        var perPage = root.getAttribute('data-tab-feed-per-page') || (type === 'award' ? '4' : '4');
        var categoryId =
            (pane && pane.getAttribute('data-tab-feed-category-id')) ||
            root.getAttribute('data-tab-feed-category-id');
        if (!url || !categoryId) return Promise.resolve();

        var fullUrl = buildUrl(url, {
            type: type,
            category_id: categoryId,
            page: page,
            per_page: perPage,
        });

        return fetch(fullUrl, {
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then(function (r) {
                return r.json();
            })
            .then(function (data) {
                if (!data.success || typeof data.html !== 'string') return;
                var scope = pane || root;
                var surface = scope.querySelector('.js-tab-feed-surface');
                if (!surface) return;
                var parent = surface.parentNode;
                if (!parent) return;
                var wrap = document.createElement('div');
                wrap.innerHTML = data.html.trim();
                var next = wrap.firstElementChild;
                if (next) parent.replaceChild(next, surface);
                if (type === 'award') rebindAwardFancybox();
            });
    }

    function bindPager(root, surface) {
        if (!surface || surface.dataset.tabFeedPagerBound === '1') return;
        surface.dataset.tabFeedPagerBound = '1';

        var pane = surface.closest('.tab-pane') || root;

        function go(delta) {
            var currentSurface = (pane || root).querySelector('.js-tab-feed-surface');
            if (!currentSurface) return;
            var st = getPagerState(currentSurface);
            var target = st.current + delta;
            if (target < 1 || target > st.last) return;
            var pBtn = currentSurface.querySelector('.js-tab-feed-prev');
            var nBtn = currentSurface.querySelector('.js-tab-feed-next');
            if (pBtn) pBtn.disabled = true;
            if (nBtn) nBtn.disabled = true;
            fetchSurface(root, pane, target).finally(function () {
                var fresh = (pane || root).querySelector('.js-tab-feed-surface');
                if (fresh) bindPager(root, fresh);
            });
        }

        var prev = surface.querySelector('.js-tab-feed-prev');
        var next = surface.querySelector('.js-tab-feed-next');
        if (prev) prev.addEventListener('click', function (e) { e.preventDefault(); go(-1); });
        if (next) next.addEventListener('click', function (e) { e.preventDefault(); go(1); });
    }

    function initRoot(root) {
        var type = root.getAttribute('data-tab-feed-type') || 'news';
        var tabList = root.querySelector('[role="tablist"]');

        if (type === 'award') {
            // Intercept click để mở gallery toàn danh mục (không bị giới hạn theo trang).
            root.addEventListener('click', function (e) {
                var a = e.target && e.target.closest ? e.target.closest('a[data-fancybox="award-gallery"]') : null;
                if (!a) return;
                var href = a.getAttribute('href');
                if (!href) return;
                e.preventDefault();
                var opened = tryOpenAwardGalleryAll(root, href);
                if (!opened) {
                    // fallback: bind default fancybox behavior
                    rebindAwardFancybox();
                    a.click();
                }
            });

            // Bind cho trường hợp fallback / môi trường không dùng Fancybox.show
            rebindAwardFancybox();
        }

        if (type === 'news' && tabList) {
            tabList.addEventListener('shown.bs.tab', function () {
                var active = root.querySelector('.tab-pane.active');
                if (!active) return;
                var surface = active.querySelector('.js-tab-feed-surface');
                if (surface) bindPager(root, surface);
            });
        }

        var firstSurface =
            type === 'news'
                ? root.querySelector('.tab-pane.active .js-tab-feed-surface')
                : root.querySelector('.js-tab-feed-surface');
        if (firstSurface) bindPager(root, firstSurface);
    }

    function initRoots() {
        document.querySelectorAll('[data-tab-feed-root]').forEach(initRoot);
    }

    window.LangdingTabFeed = {
        initRoots: initRoots,
        rebindAwardFancybox: rebindAwardFancybox,
    };
})(window);
