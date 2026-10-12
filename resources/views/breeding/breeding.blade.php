@extends('breeding.layout')


@section('title')
    Breeding
@endsection


@section('breeding-content')
    {!! breadcrumbs(['Breeding' => 'breeding']) !!}
    <h1>Breeding</h1>

    <div class="site-page-content parsed-text">
        {!! $page ? $page->text : 'There is currently no content for this page. This page is editable in the admin panel.' !!}
    </div>
@endsection

@section('scripts')
    @parent
    <script></script>
@endsection
