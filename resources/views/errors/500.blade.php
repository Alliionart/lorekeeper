@extends('layouts.app')

@section('title')
    Error 500 - Internal Server Error
@endsection

@section('content')
    
    <div class="my-4 text-center">
        <h1 class="display-1 font-weight-bold text-danger">500</h1>
        <h3>Internal Server Error</h3>

        @if ( Auth::check() && Auth::user()->is_admin && Auth::user()->hasPower('edit_site_settings') ) 
            <div class="card border border-warning my-4">
                <h5 class="card-header">Message Only Available to Admins</h5>
                <pre class="text-left bg-dark text-light p-3 rounded mb-0" style="max-height: 300px; overflow-y: auto; -ms-overflow-style: none; scrollbar-width: none;">
                    <code>
                        {{ $exception ?? null }}
                    </code>
                </pre>
            </div>
            @php
                // Get all image files from the public/images directory with specified extensions
                $images = glob(public_path('files/errors/*.{png,gif}'), GLOB_BRACE);

                // Select a random image path from the array
                if (!empty($images)) {
                    $randomImage = $images[array_rand($images)];
                    $relativePath = 'files/errors/' . basename($randomImage);
                }
                if ( file_exists(public_path($relativePath)) ) {
                    @endphp 
                    <img src="{{ asset($relativePath) }}" alt="Error 404 Image" class="img-fluid my-4" style="max-height: 200px;">
                    @php
                }
            @endphp
        @else
            @php
                // Get all image files from the public/images directory with specified extensions
                $images = glob(public_path('files/errors/*.{png,gif}'), GLOB_BRACE);

                // Select a random image path from the array
                if (!empty($images)) {
                    $randomImage = $images[array_rand($images)];
                    $relativePath = 'files/errors/' . basename($randomImage);
                }
                if ( file_exists(public_path($relativePath)) ) {
                    @endphp 
                    <img src="{{ asset($relativePath) }}" alt="Error 404 Image" class="img-fluid my-4" style="max-height: 400px;">
                    @php
                }
            @endphp
            <p>There seems to have been an error in the code! Our apologies, check back later.</p>
        @endif

        <br>
        <div class="d-flex justify-content-center mt-4">
            <a href="{{ url('/') }}" class="btn btn-primary mr-2">Go to Homepage</a>
            <a href="{{ url('/reports/new') }}" class="btn btn-warning">Submit a Bug Report</a>
        </div>
    </div>

@endsection