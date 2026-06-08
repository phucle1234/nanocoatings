document.addEventListener("DOMContentLoaded", function () {
  "use strict";

  const Core = window.CasuMapCore;

  if (!Core) {
    console.error("CasuMapCore chưa được load. Hãy load langding/js/mapbox-core.js trước distribution-mapbox.js.");
    return;
  }

  const config = window.distributionPageConfig || {};
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
  const tabs = document.querySelectorAll("#dealerSearchTabs .dealer-tab");
  const panels = document.querySelectorAll(".dealer-panel");

  const contactModalEl = document.getElementById("contactModal");
  const noDealerModalEl = document.getElementById("noDealerModal");
  const distributorList = document.getElementById("distributorList");
  const distributorCount = document.getElementById("distributor-count");
  const noResults = document.getElementById("noResults");
  const layoutRow = document.getElementById("distributionLayoutRow");
  const listColumn = document.getElementById("distributionListColumn");
  const mapColumn = document.getElementById("distributionMapColumn");

  if (!countrySelect || !provinceSelect || !productSelect || !searchButton || !distributorList) {
    console.error("Thiếu element bắt buộc trên trang distribution-system.");
    return;
  }

  const provincesBaseUrl = countrySelect.dataset.provincesUrl || "";
  const categoriesBaseUrl = provinceSelect.dataset.categoriesUrl || "";
  const modals = Core.createModals(contactModalEl, noDealerModalEl);
  const markerStore = Core.createMarkerStore();

  let isVietnam = false;
  let activeProvinceCode = "";
  let activeCategoryCode = "";

  const countryData = Core.makeCountryData(config, locale, {
    includeVietnamFallback: true,
  });

  const map = Core.createMap(config, locale, {
    container: "mapbox",
    center: Core.DEFAULT_WORLD_CENTER,
    zoom: 2,
  });

  if (!map) return;

  window.distributionMapbox = map;

  function setSearchLayout() {
    Core.setListMapLayout({
      row: layoutRow,
      listColumn: listColumn,
      mapColumn: mapColumn,
      map: map,
    }, "search");
  }

  function setInitialLayout() {
    Core.setListMapLayout({
      row: layoutRow,
      listColumn: listColumn,
      mapColumn: mapColumn,
      map: map,
    }, "initial");
  }

  function clearMarkers() {
    markerStore.clear();
  }

  function resetMapToVietnam() {
    clearMarkers();
    map.flyTo({ center: Core.DEFAULT_VN_CENTER, zoom: 5, essential: true });
  }

  function showListLoading(message) {
    setSearchLayout();

    if (noResults) noResults.style.display = "none";

    distributorList.innerHTML = `
      <div class="text-center py-5 w-100">
        <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-3 fs-16 text-muted fw-500 mb-0">${Core.escapeHtml(message || translations.searching || "Đang tìm kiếm...")}</p>
      </div>
    `;
  }

  function showEmptyList() {
    distributorList.innerHTML = "";
    if (distributorCount) distributorCount.textContent = "0";
    if (noResults) noResults.style.display = "";
    resetMapToVietnam();
    modals.showNoDealerModal();
  }

  function directionIconSvg() {
    return `
      <svg width="26" height="27" viewBox="0 0 26 27" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path fill-rule="evenodd" clip-rule="evenodd" d="M15.2666 13.0349L4.57098 11.105L0.169381 2.30095C-0.137565 1.68612 -0.0156524 0.943198 0.472802 0.460047C0.961292 -0.023069 1.70512 -0.138771 2.31636 0.175327L24.6339 11.6202C25.1642 11.8924 25.4988 12.4387 25.4988 13.0349C25.4987 13.631 25.1642 14.1773 24.6339 14.4496L2.31636 25.8944C1.70512 26.2085 0.961289 26.0928 0.4728 25.6097C-0.0156548 25.1266 -0.137566 24.3836 0.16938 23.7688L4.5923 14.922L15.2666 13.0349Z" fill="#6D6D6D" />
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

  function renderDistributorCard(distributor) {
    const code = distributor.code || "";
    const phone = Core.normalizePhone(distributor.phonebranch);
    const distanceText = distributor.distance_km
      ? `<div class="text-muted fs-13 mt-1">${Core.escapeHtml(distributor.distance_km)} km</div>`
      : "";

    return `
      <div class="nav-link-item distributor-item" data-code="${Core.escapeHtml(code)}" role="button">
        <div class="d-flex justify-content-between gap-3">
          <div class="nav-link-item-info flex-grow-1">
            <h3 class="fs-18 font-hanzel text-uppercase mb-2">${Core.escapeHtml(distributor.title || translations.casuminaDealer || "Nhà phân phối")}</h3>
            ${distributor.content || ""}
            ${distanceText}
          </div>
          <div class="btn-choose d-flex gap-3 align-items-start">
            <button class="nav-link distributor-toggle" type="button" aria-label="Xem showroom">
              ${directionIconSvg()}
            </button>
            ${phone ? `
              <a class="hot-phone" href="tel:${Core.escapeHtml(phone)}" aria-label="Gọi điện">
                ${phoneIconSvg()}
              </a>
            ` : ""}
          </div>
        </div>
        <div class="showroom-list mt-3" data-showroom-list="${Core.escapeHtml(code)}" style="display:none;"></div>
      </div>
    `;
  }

  function renderDistributors(distributors) {
    const list = Array.isArray(distributors) ? distributors : [];

    if (distributorCount) distributorCount.textContent = String(list.length);

    if (!list.length) {
      showEmptyList();
      return;
    }

    if (noResults) noResults.style.display = "none";
    distributorList.innerHTML = list.map(renderDistributorCard).join("");

    const markerCount = renderDistributorMarkers(list);

    if (markerCount === 0) {
      resetMapToVietnam();
      autoLoadFirstDistributorShowroomsIfNeeded(list);
    }
  }

  function distributorPopupHtml(distributor) {
    const directionsUrl = Core.makeDirectionsUrl(distributor);
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
      <div class="map-showroom map-distributor">
        <p class="fs-14 fw-500 text-uppercase text-red mb-2">
          <strong>${Core.escapeHtml(distributor.title || translations.casuminaDealer || "Nhà phân phối")}</strong>
        </p>
        <div>${distributor.content_map || distributor.content || ""}</div>
        ${directionsHtml}
      </div>
    `;
  }

  function showroomPopupHtml(showroom) {
    const directionsUrl = Core.makeDirectionsUrl(showroom);
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
        <p class="fs-14 fw-500 text-uppercase text-red mb-2"><strong>${Core.escapeHtml(showroom.title)}</strong></p>
        <div>${showroom.content_map || showroom.content || ""}</div>
        ${directionsHtml}
      </div>
    `;
  }

  function renderDistributorMarkers(distributors) {
    return Core.renderMarkers(map, markerStore, distributors, {
      className: "marker-showroom marker-distributor",
      popupHtml: distributorPopupHtml,
      fitOptions: {
        fallbackCenter: Core.DEFAULT_VN_CENTER,
        fallbackZoom: 5,
      },
    });
  }

  function renderShowroomMarkers(showrooms) {
    Core.renderMarkers(map, markerStore, showrooms, {
      className: "marker-showroom",
      popupHtml: showroomPopupHtml,
      fitOptions: {
        fallbackCenter: Core.DEFAULT_VN_CENTER,
        fallbackZoom: 5,
      },
    });
  }

  function autoLoadFirstDistributorShowroomsIfNeeded(distributors) {
    const list = Array.isArray(distributors) ? distributors : [];
    const hasDistributorCoords = list.some(Core.isValidCoordinate);

    if (hasDistributorCoords) return;

    const firstItem = distributorList.querySelector(".distributor-item");
    if (firstItem) {
      loadDistributorShowrooms(firstItem);
    }
  }

  function renderShowroomList(container, showrooms) {
    const list = Array.isArray(showrooms) ? showrooms : [];

    if (!list.length) {
      container.style.display = "block";
      container.innerHTML = `
        <div class="showroom-empty text-muted fs-14 py-3">
          ${Core.escapeHtml(translations.noShowroomsFound || "Nhà phân phối này chưa có showroom phù hợp trong khu vực đã chọn.")}
        </div>
      `;
      return;
    }

    container.style.display = "block";
    container.innerHTML = `
      <div class="showroom-title fs-15 fw-700 text-uppercase mb-2">
        ${Core.escapeHtml(translations.showroomList || "Danh sách showroom")}
        <span class="text-muted">(${list.length})</span>
      </div>
      ${list.map(function (showroom) {
        const phone = Core.normalizePhone(showroom.phonebranch);
        const mapsUrl = Core.makeDirectionsUrl(showroom);

        return `
          <div class="showroom-child-item border-top py-3">
            <h4 class="fs-15 fw-700 text-uppercase mb-2">${Core.escapeHtml(showroom.title)}</h4>
            ${showroom.content || ""}
            <div class="d-flex gap-2 flex-wrap mt-2">
              ${mapsUrl ? `
                <a class="btn fw-600 text-white bg-red fs-16 btn-sm" href="${Core.escapeHtml(mapsUrl)}" target="_blank" rel="noopener noreferrer">
                  ${Core.escapeHtml(translations.directions || "Chỉ đường")}
                </a>
              ` : ""}
              ${phone ? `<a class="btn fw-600 text-white bg-red fs-16" href="tel:${Core.escapeHtml(phone)}">${Core.escapeHtml(translations.contact || "Liên hệ")}</a>` : ""}
            </div>
          </div>
        `;
      }).join("")}
    `;
  }

  function buildShowroomsUrl(code) {
    const template = routes.distributorShowrooms || "";
    const baseUrl = template.replace("__CODE__", encodeURIComponent(code));
    const params = new URLSearchParams();

    if (activeProvinceCode) params.set("province_code", activeProvinceCode);
    if (activeCategoryCode) params.set("category_code", activeCategoryCode);

    const query = params.toString();
    return query ? `${baseUrl}?${query}` : baseUrl;
  }

  function setActiveDistributor(item) {
    distributorList.querySelectorAll(".distributor-item").forEach(function (el) {
      el.classList.remove("is-active");
    });
    item.classList.add("is-active");
  }

  function closeOtherShowroomLists(activeCode) {
    distributorList.querySelectorAll(".showroom-list").forEach(function (el) {
      if (el.dataset.showroomList !== activeCode) {
        el.style.display = "none";
        el.innerHTML = "";
      }
    });
  }

  function loadDistributorShowrooms(item) {
    const code = item.dataset.code || "";
    const container = item.querySelector(".showroom-list");

    if (!code || !container) {
      modals.showNoDealerModal();
      return;
    }

    setActiveDistributor(item);
    closeOtherShowroomLists(code);

    container.style.display = "block";
    container.innerHTML = `
      <div class="text-muted fs-14 py-3">
        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
        ${Core.escapeHtml(translations.searching || "Đang tải showroom...")}
      </div>
    `;

    Core.getJson(buildShowroomsUrl(code))
      .then(function (data) {
        const showrooms = data.success && Array.isArray(data.dealers) ? data.dealers : [];
        renderShowroomList(container, showrooms);
        renderShowroomMarkers(showrooms);

        if (!showrooms.length) {
          modals.showNoDealerModal();
        }
      })
      .catch(function (error) {
        console.error("Load distributor showrooms error:", error);
        container.innerHTML = `<div class="text-danger fs-14 py-3">Không tải được danh sách showroom.</div>`;
        modals.showNoDealerModal();
      });
  }

  function doSearchDistributors(provinceCode, categoryCode) {
    activeProvinceCode = provinceCode || "";
    activeCategoryCode = categoryCode || "";

    showListLoading(translations.searching || "Đang tìm kiếm...");

    Core.postJson(routes.searchDistributors, {
      province_code: activeProvinceCode,
      category_code: activeCategoryCode,
    }, csrfToken)
      .then(function (data) {
        if (data.success && Array.isArray(data.dealers) && data.dealers.length > 0) {
          renderDistributors(data.dealers);
        } else {
          showEmptyList();
        }
      })
      .catch(function (error) {
        console.error("Search distributors error:", error);
        showEmptyList();
      });
  }

  function doSearchNearestDistributors(latitude, longitude, categoryCode) {
    activeProvinceCode = "";
    activeCategoryCode = categoryCode || "";

    showListLoading(translations.searching || "Đang tìm kiếm...");

    Core.postJson(routes.searchNearestDistributors, {
      latitude: latitude,
      longitude: longitude,
      category_code: activeCategoryCode,
    }, csrfToken)
      .then(function (data) {
        if (data.success && Array.isArray(data.dealers) && data.dealers.length > 0) {
  Core.afterMapLayoutReady(map, function () {
    renderDistributors(data.dealers);
    map.flyTo({ center: [longitude, latitude], zoom: 10, essential: true });
  });
} else {
  showEmptyList();
}
      })
      .catch(function (error) {
        console.error("Search nearest distributors error:", error);
        showEmptyList();
      });
  }

  function showWorldView() {
    setInitialLayout();
    clearMarkers();

    Core.renderCountryMarkers(map, markerStore, countryData, {
      onClick: function (country) {
        if (country.code !== "VN") {
          modals.showContactModal();
          return;
        }

        map.flyTo({
          center: country.center,
          zoom: country.zoom,
          speed: 1.2,
          curve: 1.4,
          essential: true,
        });
      },
    });
  }

  Core.bindTabs(tabs, panels);

  countrySelect.addEventListener("change", function () {
    isVietnam = Core.checkIfVietnam(countrySelect);

    provinceSelect.innerHTML = `<option value="">${Core.escapeHtml(translations.provinceCity || "Tỉnh/Thành phố")}</option>`;
    provinceSelect.disabled = true;
    Core.resetSelect(productSelect, translations.products || "Sản phẩm");

    const selectedOption = this.options[this.selectedIndex];
    const countryId = selectedOption ? selectedOption.dataset.id : "";

    if (!isVietnam && this.value) {
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
  });

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
      alert(translations.pleaseSelectProvince || "Vui lòng chọn tỉnh/thành phố.");
      return;
    }

    const category = Core.getSelectedCategory(productSelect);

    if (category.code === "quocte") {
      modals.showNoDealerModal();
      return;
    }

    doSearchDistributors(provinceCode, category.code);
  });

  if (btnGetLocation && locationProductSelect) {
    btnGetLocation.addEventListener("click", function () {
      const category = Core.getSelectedCategory(locationProductSelect);

      if (!category.code) {
        alert(translations.pleaseSelectLocationCategory || "Vui lòng chọn danh mục sản phẩm.");
        return;
      }

      if (category.code === "quocte") {
        modals.showContactModal();
        return;
      }

      Core.getLocation(translations, function (position) {
        doSearchNearestDistributors(position.lat, position.lng, category.code);
      });
    });
  }

  distributorList.addEventListener("click", function (event) {
    const link = event.target.closest("a");
    if (link) return;

    const item = event.target.closest(".distributor-item");
    if (!item || !distributorList.contains(item)) return;

    loadDistributorShowrooms(item);
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

  map.on("load", function () {
    showWorldView();
    Core.addVietnamSovereigntyLabels(map);
  });
});
