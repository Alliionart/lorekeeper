@extends('layouts.app')

@section('title')
    MYO Slot Masterlist
@endsection

@section('sidebar')
    @include('browse._sidebar')
@endsection

@section('content')
    <section class="masterlist-header">
    <div class="header-s">
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos']) !!}
        <h1>Genotype Masterlist</h1> 
    </div>
    @include('browse._masterlist_content', ['characters' => $slots])
@endsection

@section('scripts')
    @include('browse._masterlist_js')
@endsection
