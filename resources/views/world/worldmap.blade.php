@extends('world.layout_map')

@section('world-title')
    World Map
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', 'World Map' => 'world/world-map']) !!}
    <h1>World Map</h1>

    <div id="map"></div>
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
            height: 50vh;
        }
    </style>
    <script>
        $(document).ready(function() {
            var map = L.map('map').setView([51.505, -0.09], 13);

            //https://commenthol.github.io/leaflet-rastercoords/
            //https://gdal.org/en/stable/programs/gdal2tiles.html

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="/credits">{{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}</a>'
            }).addTo(map);
        })
    </script>
@endpush
