@extends('world.layout')

@section('title')
    World Info
@endsection

@section('content')
    <div class="page-header">
        {!! breadcrumbs(['World' => 'world', $section->name => '/world/info' . $section->key]) !!}
        <h1>{{ $section->name }}</h1>
    </div>

    <div class="row justify-content-center main-section {{ $section->key }}">
        @foreach ($section->categories as $category)
            <div class="col-md-4 mb-4 {{ $category->name }}">
                <div class="card h-100">
                    <div class="card-header text-left pb-0 mb-3">
                        @if ($category->categoryImageUrl)
                            <div class="world-entry-image"><a href="{{ $category->categoryImageUrl }}" data-lightbox="entry" data-title="{{ $category->name }}">
                                    <img class="img-fluid" src="{{ $category->categoryImageUrl }}" class="world-entry-image" /></a></div>
                        @endif
                        <h3 class="card-title mt-3">{!! $category->name !!}</h3>
                        {!! $category->description !!}
                    </div>
                    <ul class="list-group list-group-flush mb-3 px-4">
                        @foreach ($category->pages as $page)
                            <li class="list-group-item'">
                                <p class=card-text>
                                    @if ($page->is_visible)
                                        <a href='{!! $page->url !!}'>{!! $page->title !!}</a>
                                    @else
                                        <span class="text-muted">{!! $page->title !!}</span>
                                    @endif
                                </p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    </div>
@endsection
