$(document).ready(function () {
    let searchTimer;

    function getBaseParams() {
        const status = $("#warranty-list-container").data("status");
        const params = {};
        if (status) params.status = status;
        return params;
    }

    function bindWarrantySearch() {
        const searchInput = $("#customer-warranty-search-keyword");
        const clearBtn = $("#customer-warranty-clear-search");

        searchInput.off("input").on("input", function () {
            clearTimeout(searchTimer);
            const keyword = $(this).val().trim();

            if (keyword !== "") {
                clearBtn.removeClass("d-none");
            } else {
                clearBtn.addClass("d-none");
            }

            searchTimer = setTimeout(function () {
                loadWarrantyList({ ...getBaseParams(), keyword: keyword });
            }, 500);
        });

        clearBtn.off("click").on("click", function () {
            $("#customer-warranty-search-keyword").val("");
            $(this).addClass("d-none");
            loadWarrantyList({ ...getBaseParams() });
        });
    }

    function bindWarrantyPagination() {
        $("#warranty-list-container")
            .find(".pagination a")
            .off("click")
            .on("click", function (e) {
                e.preventDefault();
                const url = new URL($(this).attr("href"));
                const params = Object.fromEntries(url.searchParams.entries());
                loadWarrantyList(params);
            });
    }

    function loadWarrantyList(params = {}) {
        const container = $("#warranty-list-container");
        const urlParams = new URLSearchParams();

        for (const key in params) {
            if (params[key] != null && params[key] !== "") {
                urlParams.set(key, params[key]);
            }
        }

        const queryString = urlParams.toString();
        history.pushState({}, "", queryString ? "?" + queryString : location.pathname);

        const shimmer = `<div class="warranty-item bg-light rounded-3 p-3 mt-3"><div style="height:70px" class="loading-shimmer"></div></div>`;
        container.html(shimmer.repeat(3));

        $.ajax({
            url: container.data("url"),
            data: params,
            headers: { "X-Requested-With": "XMLHttpRequest" },
            success: function (html) {
                container.html(html);
                bindWarrantyPagination();
            },
            error: function () {
                container.html('<div class="alert alert-danger">Có lỗi xảy ra. Vui lòng thử lại.</div>');
            },
        });
    }

    bindWarrantySearch();
    bindWarrantyPagination();
});
