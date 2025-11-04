@extends('layouts.app')

@section('title')
    {{ ucwords(__('lorekeeper.character')) }} Masterlist
@endsection

@section('sidebar')
    @include('browse._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ __('lorekeeper.character') . ' Masterlist' => 'masterlist']) !!}
    <h1>{{ __('lorekeeper.character') }} Masterlist</h1>

    @include('browse._masterlist_content', [ __('lorekeeper.characters') => $characters])
@endsection

@section('scripts')
    @include('browse._masterlist_js')
@endsection
