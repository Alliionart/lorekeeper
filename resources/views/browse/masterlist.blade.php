@extends('layouts.app')

@section('title')
    Character Masterlist
@endsection

@section('sidebar')
    @include('browse._sidebar')
@endsection

@section('content')
    <section class="masterlist-header">
    <div class="header-s">
        {!! breadcrumbs(['Character Masterlist' => 'masterlist']) !!}
        <h1>Character Masterlist</h1>
    </div>

    @include('browse._masterlist_content', ['characters' => $characters])
@endsection

@section('scripts')
    @include('browse._masterlist_js')
@endsection
