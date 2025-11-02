@extends('layouts.app')

@section('title') 
    Cultivation :: 
    @yield('cultivation-title')
@endsection

@section('sidebar')
    @include('cultivation._sidebar')
@endsection

@section('content')
    @yield('cultivation-content')
@endsection

@section('scripts')
@parent
@endsection