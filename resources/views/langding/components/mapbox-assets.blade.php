{{--
    Component: mapbox-assets
    Load Mapbox GL JS v3.1.0, mapbox-core.js và CSS dùng chung cho marker/popup.
    Dùng @once để đảm bảo chỉ load 1 lần dù component được include nhiều chỗ.
--}}
@once
@push('styles')
<link href="https://api.mapbox.com/mapbox-gl-js/v3.1.0/mapbox-gl.css" rel="stylesheet" />
<style>
    .marker-country {
        background-image: url("/langding/imgs/icon-11.png");
        background-size: cover;
        width: 26px;
        height: 26px;
        cursor: pointer;
    }

    .marker-showroom {
        background-image: url("/langding/imgs/icon-11.png");
        background-size: cover;
        width: 24px;
        height: 24px;
        cursor: pointer;
    }

    .marker-distributor {
        width: 26px;
        height: 26px;
    }

    .marker-user-location {
        width: 20px;
        height: 20px;
        background-color: #4285F4;
        border: 3px solid #fff;
        border-radius: 50%;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
        cursor: default;
    }

    .mapboxgl-popup {
        max-width: 280px !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://api.mapbox.com/mapbox-gl-js/v3.1.0/mapbox-gl.js"></script>
<script src="{{ asset('langding/js/mapbox-core.js') }}"></script>
@endpush
@endonce
