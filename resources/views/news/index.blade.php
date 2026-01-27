@extends('news.layout')

@section('title')
    Site News
@endsection

@section('news-content')
    {!! breadcrumbs(['Site News' => 'news']) !!}
    <h1>Site News</h1>
    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end mb-3">
            <div class="form-group mr-2">
                {!! Form::select('category_id', $categories ?? null, Request::get('category_id'), ['class' => 'form-control', 'placeholder' => 'Any Category']) !!}
            </div>
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    
    @if (count($newses))
        {!! $newses->render() !!}
        @foreach ($newses as $news)
            @include('news._news', ['news' => $news, 'page' => false])
        @endforeach
        {!! $newses->render() !!}
    @else
        <div>No news posts yet.</div>
    @endif
@endsection
