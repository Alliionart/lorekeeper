@extends('world.layout_map')

@section('world-title')
    World Map
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-3 p-4">
            <h1>World Map</h1>
        </div>
        <div class="col-lg-9">
            <div id="map"></div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            console.log('map ready');
        });
    </script>
@endsection
@push('head_scripts_end')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        #map {
            min-height: 75vh;
        }

        .col-lg-8 {
            flex: unset !important;
            max-width: unset !important;
        }

        .site-header-image,
        #sidebar {
            display: none !important;
        }
    </style>
    <script>
        $(document).ready(function() {
            var map = L.map('map').setView([51.505, -0.09], 13);

            //https://commenthol.github.io/leaflet-rastercoords/
            //https://gdal.org/en/stable/programs/gdal2tiles.html

            var base = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="/credits">{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}</a>'
            }).addTo(map);

            var baseLayers = {
                "Base": base
            };

            var overlays = {
                "init": null
            };

            var layerControl = L.control.layers(baseLayers, overlays).addTo(map);

            //Add markers
            function addMarker(lat, lng, content) {
                var marker = L.marker([lat, lng]).addTo(map);
                if (content) {
                    marker.bindPopup(content);
                }
            }

            //Add layer groups


        })
    </script>
@endpush
