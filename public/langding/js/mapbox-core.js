(function (window) {
  "use strict";

  const DEFAULT_MAPBOX_TOKEN =
    "pk.eyJ1IjoidmlldHFtYXgiLCJhIjoiY21uZnJpbWJpMDFrcDJxb29udDd1MWRhayJ9.uJCcqjC9rK9YvyTB7C9iZw";

  const DEFAULT_WORLD_CENTER = [30, 20];
  const DEFAULT_VN_CENTER = [106.660172, 10.762622];

  function escapeHtml(value) {
    return String(value == null ? "" : value)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function normalizePhone(phone) {
    return String(phone == null ? "" : phone).replace(/[^0-9+]/g, "");
  }

  function createModals(contactModalEl, noDealerModalEl) {
    const contactModal =
      typeof bootstrap !== "undefined" && contactModalEl
        ? new bootstrap.Modal(contactModalEl)
        : null;

    const noDealerModal =
      typeof bootstrap !== "undefined" && noDealerModalEl
        ? new bootstrap.Modal(noDealerModalEl)
        : null;

    return {
      contactModal,
      noDealerModal,
      showContactModal() {
        if (noDealerModal) noDealerModal.hide();
        if (contactModal) contactModal.show();
      },
      showNoDealerModal() {
        if (contactModal) contactModal.hide();
        if (noDealerModal) noDealerModal.show();
      },
    };
  }

  function createMap(config, locale, options) {
    if (typeof mapboxgl === "undefined") {
      console.error("Mapbox GL JS chưa được load.");
      return null;
    }

    options = options || {};
    mapboxgl.accessToken = (config && config.mapboxToken) || DEFAULT_MAPBOX_TOKEN;

    const map = new mapboxgl.Map({
      container: options.container || "mapbox",
      style: options.style || "mapbox://styles/mapbox/streets-v12",
      center: options.center || DEFAULT_WORLD_CENTER,
      zoom: options.zoom == null ? 2 : options.zoom,
      projection: "mercator",
      language: locale === "vi" ? "vi" : "en",
    });

    if (options.navigation !== false) {
      map.addControl(new mapboxgl.NavigationControl(), "top-right");
    }

    return map;
  }

  function createMarkerStore() {
    let markers = [];

    return {
      add(marker) {
        markers.push(marker);
      },
      clear() {
        markers.forEach(function (marker) {
          marker.remove();
        });
        markers = [];
      },
      all() {
        return markers;
      },
    };
  }

  function isValidCoordinate(item) {
    if (!item) return false;

    const lat = parseFloat(item.latitude);
    const lng = parseFloat(item.longitude);

    return Number.isFinite(lat) && Number.isFinite(lng);
  }

  function isValidCoordsArray(coords) {
    if (!Array.isArray(coords) || coords.length < 2) return false;

    const lng = parseFloat(coords[0]);
    const lat = parseFloat(coords[1]);

    return Number.isFinite(lng) && Number.isFinite(lat);
  }

  function getLngLat(item) {
    if (!item) return null;

    if (isValidCoordinate(item)) {
      return [parseFloat(item.longitude), parseFloat(item.latitude)];
    }

    if (isValidCoordsArray(item.coords)) {
      return [parseFloat(item.coords[0]), parseFloat(item.coords[1])];
    }

    return null;
  }

  function makeGoogleMapsUrl(lat, lng) {
    return `https://www.google.com/maps?q=${encodeURIComponent(lat)},${encodeURIComponent(lng)}`;
  }

  function makeGoogleMapsSearchUrl(keyword) {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(keyword)}`;
  }

  function makeDirectionsUrl(item) {
    if (!item) return "";

    const directUrl = item.directions_url || item.directionsUrl || item.link_map || "";

    if (directUrl && /^https?:\/\//i.test(String(directUrl))) {
      return String(directUrl);
    }

    if (isValidCoordinate(item)) {
      return makeGoogleMapsUrl(item.latitude, item.longitude);
    }

    if (isValidCoordsArray(item.coords)) {
      return makeGoogleMapsUrl(item.coords[1], item.coords[0]);
    }

    const keyword = item.address || item.title || item.name || "";
    return keyword ? makeGoogleMapsSearchUrl(keyword) : "";
  }

  function fitItems(map, items, options) {
    if (!map) return;

    options = options || {};

    const coords = (Array.isArray(items) ? items : [])
      .map(getLngLat)
      .filter(Boolean);

    const fallbackCenter = options.fallbackCenter || DEFAULT_VN_CENTER;
    const fallbackZoom = options.fallbackZoom || 5;

    if (!coords.length) {
      map.flyTo({
        center: fallbackCenter,
        zoom: fallbackZoom,
        essential: true,
      });
      return;
    }

    if (coords.length === 1) {
      map.flyTo({
        center: coords[0],
        zoom: options.singleZoom || 13,
        speed: 1.2,
        curve: 1.4,
        essential: true,
      });
      return;
    }

    const bounds = coords.reduce(
      function (b, coord) {
        return b.extend(coord);
      },
      new mapboxgl.LngLatBounds(coords[0], coords[0])
    );

    map.fitBounds(bounds, {
      padding: options.padding || 80,
      maxZoom: options.maxZoom || 13,
      speed: 1.2,
      essential: true,
    });
  }

  function renderMarkers(map, markerStore, items, options) {
    options = options || {};

    if (!map || !markerStore) return 0;

    if (options.clear !== false) {
      markerStore.clear();
    }

    const list = Array.isArray(items) ? items : [];
    let count = 0;

    list.forEach(function (item) {
      const lngLat = getLngLat(item);
      if (!lngLat) return;

      const el = document.createElement("div");
      el.className = options.className || "marker-showroom";

      const marker = new mapboxgl.Marker(el).setLngLat(lngLat);

      if (typeof options.popupHtml === "function") {
        marker.setPopup(
          new mapboxgl.Popup({ offset: options.popupOffset || 25 }).setHTML(options.popupHtml(item))
        );
      }

      marker.addTo(map);
      markerStore.add(marker);
      count++;
    });

    if (options.fit !== false) {
      fitItems(map, list, options.fitOptions || {});
    }

    return count;
  }

  function getCountryCode(country) {
    return String(country && country.code ? country.code : "").trim().toUpperCase();
  }

  function getCountryName(country, locale) {
    if (!country) return "";

    return locale === "vi"
      ? country.name_vi || country.name_en || country.code || ""
      : country.name_en || country.name_vi || country.code || "";
  }

  function makeCountryData(config, locale, options) {
    options = options || {};

    const rawCountries = Array.isArray(config && config.countries) ? config.countries : [];

    const countries = rawCountries
      .map(function (country) {
        const code = getCountryCode(country);
        const lat = parseFloat(country.latitude);
        const lng = parseFloat(country.longitude);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
          return null;
        }

        return {
          code,
          name: getCountryName(country, locale),
          center: [lng, lat],
          zoom: Number.isFinite(parseFloat(country.default_zoom))
            ? parseFloat(country.default_zoom)
            : 5,
          _loaded: false,
          showrooms: [],
        };
      })
      .filter(Boolean);

    if (options.includeVietnamFallback && !countries.some(function (c) { return c.code === "VN"; })) {
      countries.unshift({
        code: "VN",
        name: locale === "vi" ? "Việt Nam" : "Vietnam",
        center: DEFAULT_VN_CENTER,
        zoom: 5,
        _loaded: false,
        showrooms: [],
      });
    }

    return countries;
  }

  function renderCountryMarkers(map, markerStore, countries, options) {
    options = options || {};

    if (!map || !markerStore) return;

    markerStore.clear();

    if (options.fly !== false) {
      map.flyTo({
        center: options.worldCenter || DEFAULT_WORLD_CENTER,
        zoom: options.worldZoom || 2,
        essential: true,
      });
    }

    (Array.isArray(countries) ? countries : []).forEach(function (country) {
      if (!Array.isArray(country.center)) return;

      const el = document.createElement("div");
      el.className = "marker-country";

      const hoverPopup = new mapboxgl.Popup({
        offset: 25,
        closeButton: false,
        closeOnClick: false,
      }).setHTML(`<div class="fw-500 fs-14">${escapeHtml(country.name)}</div>`);

      const marker = new mapboxgl.Marker(el).setLngLat(country.center).addTo(map);

      el.addEventListener("mouseenter", function () {
        hoverPopup.setLngLat(country.center).addTo(map);
      });

      el.addEventListener("mouseleave", function () {
        hoverPopup.remove();
      });

      el.addEventListener("click", function () {
        if (typeof options.onClick === "function") {
          options.onClick(country);
        }
      });

      markerStore.add(marker);
    });
  }

  function resetSelect(selectEl, placeholderText) {
    if (!selectEl) return;

    selectEl.innerHTML = `<option value="" data-id="" data-code="">${escapeHtml(placeholderText || "")}</option>`;
    selectEl.disabled = true;
  }

  function fillCategorySelect(selectEl, categories, placeholderText) {
    if (!selectEl) return;

    resetSelect(selectEl, placeholderText || "Sản phẩm");

    if (!Array.isArray(categories) || !categories.length) return;

    categories.forEach(function (cat) {
      const opt = document.createElement("option");
      opt.value = cat.code || "";
      opt.dataset.id = cat.id || "";
      opt.dataset.code = cat.code || "";
      opt.textContent = cat.name || "";
      selectEl.appendChild(opt);
    });

    selectEl.disabled = false;
  }

  function getSelectedCategory(selectEl) {
    if (!selectEl) return { id: "", code: "" };

    const option = selectEl.options[selectEl.selectedIndex];

    return {
      id: option ? option.dataset.id || "" : "",
      code: option ? option.dataset.code || option.value || "" : "",
    };
  }

  function checkIfVietnam(countrySelect) {
    if (!countrySelect) return false;

    const opt = countrySelect.options[countrySelect.selectedIndex];

    return !!(opt && String(opt.dataset.code || "").toUpperCase() === "VN");
  }

  function postJson(url, payload, csrfToken) {
    return fetch(url, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken || "",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify(payload || {}),
    }).then(async function (response) {
      const data = await response.json().catch(function () {
        return {};
      });

      if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}`);
      }

      return data;
    });
  }

  function getJson(url) {
    return fetch(url, {
      headers: {
        "X-Requested-With": "XMLHttpRequest",
      },
    }).then(async function (response) {
      const data = await response.json().catch(function () {
        return {};
      });

      if (!response.ok) {
        throw new Error(data.message || `HTTP ${response.status}`);
      }

      return data;
    });
  }

  function loadProvinces(options) {
    const countryId = options.countryId;
    const provinceSelect = options.provinceSelect;
    const productSelect = options.productSelect;
    const provincesBaseUrl = options.provincesBaseUrl;
    const translations = options.translations || {};
    const locale = options.locale || "vi";

    if (!countryId || !provinceSelect || !provincesBaseUrl) return Promise.resolve();

    provinceSelect.innerHTML = `<option value="">${escapeHtml(
      translations.provinceCity || "Tỉnh/Thành phố"
    )}</option>`;
    provinceSelect.classList.add("loading");
    provinceSelect.disabled = true;

    resetSelect(productSelect, translations.products || "Sản phẩm");

    return getJson(`${provincesBaseUrl}/${encodeURIComponent(countryId)}`)
      .then(function (data) {
        if (data.success && Array.isArray(data.provinces)) {
          data.provinces.forEach(function (province) {
            const opt = document.createElement("option");
            opt.value = province.code || "";
            opt.dataset.id = province.id || "";
            opt.textContent = locale === "vi" ? province.name_vi || "" : province.name_en || "";
            provinceSelect.appendChild(opt);
          });
        }
      })
      .catch(function (error) {
        console.error("Load provinces error:", error);
      })
      .finally(function () {
        provinceSelect.classList.remove("loading");
        provinceSelect.disabled = false;
      });
  }

  function loadCategories(options) {
    const provinceCode = options.provinceCode;
    const productSelect = options.productSelect;
    const categoriesBaseUrl = options.categoriesBaseUrl;
    const translations = options.translations || {};

    resetSelect(productSelect, translations.products || "Sản phẩm");

    if (!provinceCode || !productSelect || !categoriesBaseUrl) return Promise.resolve();

    productSelect.classList.add("loading");

    return getJson(`${categoriesBaseUrl}/${encodeURIComponent(provinceCode)}`)
      .then(function (data) {
        if (data.success && Array.isArray(data.categories)) {
          fillCategorySelect(productSelect, data.categories, translations.products || "Sản phẩm");
        }
      })
      .catch(function (error) {
        console.error("Load categories error:", error);
      })
      .finally(function () {
        productSelect.classList.remove("loading");

        if (productSelect.options.length > 1) {
          productSelect.disabled = false;
        }
      });
  }

  function getLocation(translations, callback) {
    translations = translations || {};

    if (!navigator.geolocation) {
      alert(translations.locationNotSupported || "Trình duyệt của bạn không hỗ trợ Geolocation.");
      return;
    }

    navigator.geolocation.getCurrentPosition(
      function (position) {
        if (typeof callback === "function") {
          callback({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          });
        }
      },
      function (error) {
        console.error("Geolocation error:", error.message);
        alert(translations.locationError || "Không lấy được vị trí hiện tại của bạn.");
      },
      {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0,
      }
    );
  }

  function bindTabs(tabs, panels) {
    (tabs || []).forEach(function (tab) {
      tab.addEventListener("click", function () {
        const target = tab.dataset.target;

        tabs.forEach(function (item) {
          item.classList.remove("is-active");
        });

        panels.forEach(function (panel) {
          panel.classList.remove("is-active");
        });

        tab.classList.add("is-active");

        const targetPanel = document.getElementById(target);
        if (targetPanel) targetPanel.classList.add("is-active");
      });
    });
  }

  function addVietnamSovereigntyLabels(map) {
    if (!map || map.getSource("vietnam-islands")) return;

    const sovereigntyData = {
      type: "FeatureCollection",
      features: [
        {
          type: "Feature",
          properties: {
            name: "quần đảo Hoàng Sa\n(Việt Nam)",
          },
          geometry: {
            type: "Point",
            coordinates: [111.6, 16.5],
          },
        },
        {
          type: "Feature",
          properties: {
            name: "quần đảo Trường Sa\n(Việt Nam)",
          },
          geometry: {
            type: "Point",
            coordinates: [114.5, 10.5],
          },
        },
      ],
    };

    map.addSource("vietnam-islands", {
      type: "geojson",
      data: sovereigntyData,
    });

    map.addLayer({
      id: "islands-labels",
      type: "symbol",
      source: "vietnam-islands",
      layout: {
        "text-field": ["get", "name"],
        "text-size": ["interpolate", ["linear"], ["zoom"], 3, 8, 8, 12],
        "text-variable-anchor": ["top", "bottom", "left", "right"],
        "text-radial-offset": 0.5,
        "text-justify": "auto",
      },
      paint: {
        "text-color": "#ff0000",
        "text-halo-color": "#ffffff",
        "text-halo-width": 1,
      },
    });
  }

  function resizeMapLater(map, delay) {
    if (!map || typeof map.resize !== "function") return;

    setTimeout(function () {
      map.resize();
    }, delay || 300);
  }

  function setListMapLayout(options, mode) {
    options = options || {};

    const row = typeof options.row === "string" ? document.getElementById(options.row) : options.row;
    const listColumn =
      typeof options.listColumn === "string" ? document.getElementById(options.listColumn) : options.listColumn;
    const mapColumn =
      typeof options.mapColumn === "string" ? document.getElementById(options.mapColumn) : options.mapColumn;

    if (!row || !listColumn || !mapColumn) return;

    if (mode === "search") {
      listColumn.classList.remove("d-none");
      mapColumn.classList.remove("col-12");
      mapColumn.classList.add(options.splitClass || "col-xl-6");
      row.classList.remove("map-fullscreen");
    } else {
      listColumn.classList.add("d-none");
      mapColumn.classList.remove(options.splitClass || "col-xl-6");
      mapColumn.classList.remove("col-lg-6", "col-md-6", "col-sm-12");
      mapColumn.classList.add("col-12");
      row.classList.add("map-fullscreen");
    }

    resizeMapLater(options.map || window.mapbox || window.map || window.distributionMapbox, options.delay || 300);
  }
function afterMapLayoutReady(map, callback) {
  if (!map || typeof callback !== "function") return;

  requestAnimationFrame(function () {
    if (typeof map.resize === "function") map.resize();

    requestAnimationFrame(function () {
      if (typeof map.resize === "function") map.resize();
      callback();
    });
  });
}
  window.CasuMapCore = {
    DEFAULT_WORLD_CENTER,
    DEFAULT_VN_CENTER,
    escapeHtml,
    normalizePhone,
    createModals,
    createMap,
    createMarkerStore,
    isValidCoordinate,
    isValidCoordsArray,
    getLngLat,
    makeGoogleMapsUrl,
    makeGoogleMapsSearchUrl,
    makeDirectionsUrl,
    fitItems,
    renderMarkers,
    makeCountryData,
    renderCountryMarkers,
    resetSelect,
    fillCategorySelect,
    getSelectedCategory,
    checkIfVietnam,
    postJson,
    getJson,
    loadProvinces,
    loadCategories,
    getLocation,
    bindTabs,
    addVietnamSovereigntyLabels,
    resizeMapLater,
    setListMapLayout,
    afterMapLayoutReady,
  };
})(window);
