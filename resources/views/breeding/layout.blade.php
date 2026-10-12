@extends('layouts.app')

@section('title')
    Breeding
@endsection

@section('sidebar')
    @include('breeding._sidebar')
@endsection

@section('content')
    @yield('breeding-content')
@endsection
