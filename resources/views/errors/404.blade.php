@extends('layouts.app')

@section('title')
    Error 404 - Page Not Found
@endsection

@section('content')

    <div class="my-4 text-center">
        <h1 class="display-1 font-weight-bold text-danger">404</h1>
        <h3>Page Not Found</h3>

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

        <p>We couldn't find the page you were looking for.</p>

        <br>
        <a href="{{ url('/') }}" class="btn btn-primary mt-4">Go to Homepage</a>
    </div>
    
@endsection