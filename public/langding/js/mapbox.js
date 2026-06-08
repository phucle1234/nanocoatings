document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  const Core = window.CasuMapCore;

  if (!Core) {
    console.error("CasuMapCore chưa được load. Hãy load langding/js/mapbox-core.js trước mapbox.js.");
    return;
  }

  const config = window.branchPageConfig || {};
  const routes = config.routes || {};
  const locale = config.locale || "vi";
  const translations = config.translations || {};
  const csrfToken = config.csrfToken || "";

  const countrySelect = document.getElementById("countrySelect");
  const provinceSelect = document.getElementById("provinceSelect");
  const productSelect = document.getElementById("productSelect");
  const locationProductSelect = document.getElementById("locationProductSelect");
  const searchButton = document.getElementById("btnSearchDealer");
  const btnGetLocation = document.getElementById("btnGetLocation");
  const contactModalEl = document.getElementById("contactModal");
  const noDealerModalEl = document.getElementById("noDealerModal");
  const tabs = document.querySelectorAll("#dealerSearchTabs .dealer-tab");
  const panels = document.querySelectorAll(".dealer-panel");
  const dealerListShow = document.getElementById("dealerListShow");
  const dealerCount = document.getElementById("dealer-count");
  const noResults = document.getElementById("noResults");
  const layoutRow = document.getElementById("dealerLayoutRow");
  const listColumn = document.getElementById("dealerListColumn");
  const mapColumn = document.getElementById("dealerMapColumn");

  if (!countrySelect || !provinceSelect || !productSelect || !locationProductSelect || !btnGetLocation || !searchButton) {
    console.error("Thiếu element bắt buộc trên trang branch.");
    return;
  }

  const provincesBaseUrl = countrySelect.dataset.provincesUrl || "";
  const categoriesBaseUrl = provinceSelect.dataset.categoriesUrl || "";
  const modals = Core.createModals(contactModalEl, noDealerModalEl);
  const markerStore = Core.createMarkerStore();

  let isVietnam = false;
  let userLocationMarker = null;
  let viewMode = "world";
  let switching = false;

  const countryDataBox = Core.makeCountryData(config, locale, {
    includeVietnamFallback: true,
  });

  const mapbox = Core.createMap(config, locale, {
    container: "mapbox",
    center: Core.DEFAULT_WORLD_CENTER,
    zoom: 2,
  });

  if (!mapbox) return;

  window.mapbox = mapbox;
  window.map = mapbox;

  function setInitialMapLayout() {
    Core.setListMapLayout({
      row: layoutRow,
      listColumn: listColumn,
      mapColumn: mapColumn,
      map: mapbox,
    }, "initial");
  }

  function setSearchMapLayout() {
    Core.setListMapLayout({
      row: layoutRow,
      listColumn: listColumn,
      mapColumn: mapColumn,
      map: mapbox,
    }, "search");
  }

  function clearMarkers() {
    markerStore.clear();
  }

  function clearUserLocationMarker() {
    if (userLocationMarker) {
      userLocationMarker.remove();
      userLocationMarker = null;
    }
  }

  function getVietnamCountry() {
    return countryDataBox.find(function (c) {
      return c.code === "VN";
    }) || null;
  }

  function showInlineLoading() {
    setSearchMapLayout();

    if (noResults) noResults.style.display = "none";
    if (dealerCount) dealerCount.innerHTML = "0";

    if (dealerListShow) {
      dealerListShow.innerHTML = `
        <div class="text-center py-5 w-100">
          <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-3 fs-16 text-muted fw-500 mb-0">${Core.escapeHtml(translations.searching || "Đang tìm kiếm...")}</p>
        </div>
      `;
    }
  }

  function showEmptyResult() {
    if (dealerCount) dealerCount.innerHTML = "0";
    if (dealerListShow) dealerListShow.innerHTML = "";
    if (noResults) noResults.style.display = "";

    clearMarkers();
    clearUserLocationMarker();
    modals.showNoDealerModal();
  }

  function directionIconSvg() {
    return `
      <svg width="26" height="27" viewBox="0 0 26 27" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd"
          d="M15.2666 13.0349L4.57098 11.105L0.169381 2.30095C-0.137565 1.68612 -0.0156524 0.943198 0.472802 0.460047C0.961292 -0.023069 1.70512 -0.138771 2.31636 0.175327L24.6339 11.6202C25.1642 11.8924 25.4988 12.4387 25.4988 13.0349C25.4987 13.631 25.1642 14.1773 24.6339 14.4496L2.31636 25.8944C1.70512 26.2085 0.961289 26.0928 0.4728 25.6097C-0.0156548 25.1266 -0.137566 24.3836 0.16938 23.7688L4.5923 14.922L15.2666 13.0349Z"
          fill="#6D6D6D"></path>
      </svg>
    `;
  }

  function phoneIconSvg() {
    return `
      <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M29.393 23.36C28.519 22.627 23.393 19.381 22.541 19.53C22.141 19.601 21.835 19.942 21.016 20.919C20.6372 21.3999 20.2213 21.8503 19.772 22.266C18.9488 22.0671 18.1519 21.7718 17.398 21.386C14.4413 19.9465 12.0526 17.5571 10.614 14.6C10.2282 13.8461 9.93286 13.0492 9.734 12.226C10.1497 11.7767 10.6001 11.3608 11.081 10.982C12.057 10.163 12.399 9.859 12.47 9.457C12.619 8.603 9.37 3.479 8.64 2.605C8.334 2.243 8.056 2 7.7 2C6.668 2 2 7.772 2 8.52C2 8.581 2.1 14.59 9.689 22.311C17.41 29.9 23.419 30 23.48 30C24.228 30 30 25.332 30 24.3C30 23.944 29.757 23.666 29.393 23.36Z" fill="white" />
        <path d="M23 15H25C24.9976 12.879 24.154 10.8456 22.6542 9.34578C21.1544 7.846 19.121 7.00238 17 7V9C18.5908 9.00159 20.116 9.63424 21.2409 10.7591C22.3658 11.884 22.9984 13.4092 23 15Z" fill="white" />
        <path d="M28 15H30C29.996 11.5534 28.6251 8.24911 26.188 5.812C23.7509 3.37488 20.4466 2.00397 17 2V4C19.9163 4.00344 22.7122 5.16347 24.7744 7.22563C26.8365 9.28778 27.9966 12.0837 28 15Z" fill="white" />
      </svg>
    `;
  }

  function renderDealersInPage(dealers) {
    if (!Array.isArray(dealers) || !dealers.length) {
      showEmptyResult();
      return;
    }

    const vietnamCountry = getVietnamCountry();
    if (vietnamCountry) vietnamCountry.showrooms = [];

    let htmlOnListLocal = "";
    let totalOnListLocal = 0;
    let totalMarkerLocal = 0;

    dealers.forEach(function (dealer) {
      totalOnListLocal++;

      const directionsUrl = Core.makeDirectionsUrl(dealer);
      const phone = Core.normalizePhone(dealer.phonebranch);
      const title = dealer.title || translations.casuminaDealer || "CASUMINA";

      htmlOnListLocal += `
        <div class="nav-link-item dealer-item" role="button">
          <div class="d-flex justify-content-between">
            <div class="nav-link-item-info">
              <h3 class="fs-20 font-hanzel">${Core.escapeHtml(title)}</h3>
              ${dealer.content || ""}
            </div>

            <div class="btn-choose d-flex gap-4">
              ${directionsUrl ? `
                <a class="map-link nav-link" href="${Core.escapeHtml(directionsUrl)}" target="_blank" rel="noopener noreferrer" aria-label="${Core.escapeHtml(translations.directions || "Chỉ đường")}">
                  ${directionIconSvg()}
                </a>
              ` : ""}

              ${phone ? `
                <a class="hot-phone" href="tel:${Core.escapeHtml(phone)}" aria-label="${Core.escapeHtml(translations.contact || "Liên hệ")}">
                  ${phoneIconSvg()}
                </a>
              ` : ""}
            </div>
          </div>
        </div>
      `;

      if (vietnamCountry && Core.isValidCoordinate(dealer)) {
        totalMarkerLocal++;
        vietnamCountry.showrooms.push({
          name: dealer.title || "",
          title: dealer.title || "",
          address: dealer.address || "",
          link_map: dealer.link_map || "",
          directions_url: dealer.directions_url || "",
          directionsUrl: directionsUrl,
          coords: [parseFloat(dealer.longitude), parseFloat(dealer.latitude)],
          content: dealer.content_map || dealer.content || "",
        });
      }
    });

    if (dealerCount) dealerCount.innerHTML = String(totalOnListLocal);
    if (dealerListShow) dealerListShow.innerHTML = htmlOnListLocal;
    if (noResults) noResults.style.display = "none";

    if (totalMarkerLocal > 0 && vietnamCountry) {
      showCountryDetail(vietnamCountry);
    } else if (vietnamCountry) {
      clearMarkers();
      mapbox.flyTo({
        center: vietnamCountry.center,
        zoom: vietnamCountry.zoom || 5,
        speed: 1.2,
        curve: 1.4,
        essential: true,
      });
    }
  }

  function doSearchDealers(provinceCode, categoryCode) {
    clearUserLocationMarker();
    showInlineLoading();

    Core.postJson(routes.searchDealers, {
      province_code: provinceCode,
      category_code: categoryCode || "",
    }, csrfToken)
      .then(function (data) {
        if (data.success && Array.isArray(data.dealers) && data.dealers.length) {
          // switching=true chặn zoomend handler gọi showWorldView() khi map.resize()
          // (từ resizeMapLater) cancel animation fitBounds đang chạy ở zoom thấp
          switching = true;
          renderDealersInPage(data.dealers);
          setTimeout(function () { switching = false; }, 2500);
        } else {
          showEmptyResult();
        }
      })
      .catch(function (error) {
        console.error("Search dealers error:", error);
        showEmptyResult();
      });
  }

  function doSearchNearestDealers(latitude, longitude, categoryCode) {
    showInlineLoading();

    Core.postJson(routes.searchNearestDealers, {
      latitude: latitude,
      longitude: longitude,
      category_code: categoryCode || "",
    }, csrfToken)
      .then(function (data) {
        if (data.success && Array.isArray(data.dealers) && data.dealers.length) {
  Core.afterMapLayoutReady(mapbox, function () {
    // switching=true ngăn zoomend handler gọi showWorldView() khi fitBounds
    // bên trong renderDealersInPage bị cancel ngay bởi flyTo bên dưới
    switching = true;
    renderDealersInPage(data.dealers);
    mapbox.flyTo({ center: [longitude, latitude], zoom: 10, essential: true });
    setTimeout(function () { switching = false; }, 3000);
  });
} else {
  showEmptyResult();
}
      })
      .catch(function (error) {
        console.error("Nearest dealers error:", error);
        showEmptyResult();
      });
  }

  function showUserLocation(lat, lng) {
    if (userLocationMarker) {
      userLocationMarker.setLngLat([lng, lat]);
      return;
    }

    const el = document.createElement("div");
    el.className = "marker-user-location";

    userLocationMarker = new mapboxgl.Marker(el)
      .setLngLat([lng, lat])
      .setPopup(
        new mapboxgl.Popup({ offset: 25, closeButton: false }).setHTML(
          `<div class="fw-500 fs-14">${Core.escapeHtml(translations.yourLocation || "Vị trí của bạn")}</div>`
        )
      )
      .addTo(mapbox);

    userLocationMarker.getPopup().addTo(mapbox);
  }

  function showWorldView() {
    viewMode = "world";
    setInitialMapLayout();

    Core.renderCountryMarkers(mapbox, markerStore, countryDataBox, {
      onClick: function (country) {
        if (country.code !== "VN") {
          modals.showContactModal();
          return;
        }

        if (country._loaded) {
          if (country.showrooms.length === 0) {
            modals.showContactModal();
          } else {
            showCountryDetail(country);
          }
          return;
        }

        loadAllDealersForCountry(country);
      },
    });
  }

  function loadAllDealersForCountry(country) {
    Core.getJson(routes.searchAllDealers)
      .then(function (data) {
        country._loaded = true;

        if (data.success && Array.isArray(data.dealers)) {
          country.showrooms = data.dealers
            .filter(Core.isValidCoordinate)
            .map(function (dealer) {
              return {
                name: dealer.title || "",
                title: dealer.title || "",
                address: dealer.address || "",
                link_map: dealer.link_map || "",
                directions_url: dealer.directions_url || "",
                directionsUrl: Core.makeDirectionsUrl(dealer),
                coords: [parseFloat(dealer.longitude), parseFloat(dealer.latitude)],
                content: dealer.content_map || dealer.content || "",
              };
            });
        }

        if (!country.showrooms.length) {
          modals.showContactModal();
          return;
        }

        showCountryDetail(country);
      })
      .catch(function (error) {
        console.error("Load all dealers error:", error);
        country._loaded = true;
        modals.showContactModal();
      });
  }

  function showCountryDetail(country) {
    viewMode = "country";

    const markerShowrooms = Array.isArray(country.showrooms)
      ? country.showrooms.filter(function (shop) {
          return Core.isValidCoordsArray(shop.coords);
        })
      : [];

    if (!markerShowrooms.length) {
      clearMarkers();
      mapbox.flyTo({
        center: country.center,
        zoom: country.zoom || 5,
        speed: 1.2,
        curve: 1.4,
        essential: true,
      });
      return;
    }

    Core.renderMarkers(mapbox, markerStore, markerShowrooms, {
      className: "marker-showroom",
      popupHtml: function (shop) {
        const directionsUrl = Core.makeDirectionsUrl(shop);
        const directionsHtml = directionsUrl
          ? `
            <div class="mt-2">
              <a class="text-red fw-500" href="${Core.escapeHtml(directionsUrl)}" target="_blank" rel="noopener noreferrer">
                ${Core.escapeHtml(translations.directions || "Chỉ đường")}
              </a>
            </div>
          `
          : "";

        return `
          <div class="map-showroom">
            <p class="fs-14 fw-500 text-uppercase text-red">
              <strong>${Core.escapeHtml(shop.name || shop.title || "")}</strong>
            </p>
            <div>${shop.content || ""}</div>
            ${directionsHtml}
          </div>
        `;
      },
      fitOptions: {
        fallbackCenter: country.center,
        fallbackZoom: country.zoom || 5,
      },
    });
  }

  function findNearestCountry(center) {
    let nearest = null;
    let minDist = Infinity;

    countryDataBox.forEach(function (country) {
      const dx = country.center[0] - center.lng;
      const dy = country.center[1] - center.lat;
      const dist = dx * dx + dy * dy;

      if (dist < minDist) {
        minDist = dist;
        nearest = country;
      }
    });

    return nearest;
  }

  function onCountrySelectChanged() {
    isVietnam = Core.checkIfVietnam(countrySelect);

    provinceSelect.innerHTML = `<option value="">${Core.escapeHtml(translations.provinceCity || "Tỉnh/Thành")}</option>`;
    provinceSelect.disabled = true;
    Core.resetSelect(productSelect, translations.products || "Sản phẩm");

    const selectedOption = countrySelect.options[countrySelect.selectedIndex];
    const countryId = selectedOption ? selectedOption.dataset.id : "";

    if (!isVietnam && countrySelect.value) {
      modals.showContactModal();
      return;
    }

    if (isVietnam && countryId) {
      Core.loadProvinces({
        countryId: countryId,
        provinceSelect: provinceSelect,
        productSelect: productSelect,
        provincesBaseUrl: provincesBaseUrl,
        translations: translations,
        locale: locale,
      });
    }
  }

  Core.bindTabs(tabs, panels);

  countrySelect.addEventListener("change", onCountrySelectChanged);

  provinceSelect.addEventListener("change", function () {
    Core.loadCategories({
      provinceCode: this.value,
      productSelect: productSelect,
      categoriesBaseUrl: categoriesBaseUrl,
      translations: translations,
    });
  });

  searchButton.addEventListener("click", function () {
    if (!isVietnam) {
      modals.showContactModal();
      return;
    }

    const provinceCode = provinceSelect.value;

    if (!provinceCode) {
      alert(translations.pleaseSelectProvince || "Vui lòng chọn Tỉnh/Thành.");
      return;
    }

    const category = Core.getSelectedCategory(productSelect);

    if (category.code === "quocte") {
      modals.showContactModal();
      return;
    }

    doSearchDealers(provinceCode, category.code);
  });

  btnGetLocation.addEventListener("click", function () {
    const category = Core.getSelectedCategory(locationProductSelect);

    if (!category.code) {
      alert(translations.pleaseSelectLocationCategory || "Vui lòng chọn sản phẩm.");
      return;
    }

    if (category.code === "quocte") {
      modals.showContactModal();
      return;
    }

    Core.getLocation(translations, function (position) {
      showUserLocation(position.lat, position.lng);
      doSearchNearestDealers(position.lat, position.lng, category.code);
    });
  });

  mapbox.on("zoomend", function () {
    if (switching) return;

    const DETAIL_ZOOM = 4;
    const zoom = mapbox.getZoom();

    if (zoom >= DETAIL_ZOOM && viewMode === "world") {
      const nearest = findNearestCountry(mapbox.getCenter());
      if (!nearest) return;

      switching = true;
      setTimeout(function () {
        switching = false;
      }, 1500);

      if (nearest.code !== "VN") {
        modals.showContactModal();
        return;
      }

      if (nearest._loaded) {
        if (!nearest.showrooms.length) {
          modals.showContactModal();
        } else {
          showCountryDetail(nearest);
        }
        return;
      }

      loadAllDealersForCountry(nearest);
    } else if (zoom < DETAIL_ZOOM && viewMode === "country") {
      switching = true;
      setTimeout(function () {
        switching = false;
      }, 1500);

      showWorldView();
    }
  });

  isVietnam = Core.checkIfVietnam(countrySelect);

  const initialCountryOption = countrySelect.options[countrySelect.selectedIndex];
  const initialCountryId = initialCountryOption ? initialCountryOption.dataset.id : "";

  if (isVietnam && initialCountryId && provinceSelect.options.length <= 1) {
    Core.loadProvinces({
      countryId: initialCountryId,
      provinceSelect: provinceSelect,
      productSelect: productSelect,
      provincesBaseUrl: provincesBaseUrl,
      translations: translations,
      locale: locale,
    });
  }

  mapbox.on("load", function () {
    showWorldView();
    Core.addVietnamSovereigntyLabels(mapbox);
  });
});
