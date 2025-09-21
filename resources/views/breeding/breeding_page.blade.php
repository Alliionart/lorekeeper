@extends('layouts.app')


@section('title')
    Breeding (#{{ $id }})
@endsection


@section('content')
    {!! breadcrumbs(['Breeding' => 'breeding'], ['Breeding #' . $id => 'breeding/' . $id]) !!}
    <h1>Breeding (#{{ $id }})</h1>

    <div class="site-page-content parsed-text">
        {{ $breeding }}
    </div>
@endsection

@section('scripts')
    @parent
    <script></script>
@endsection
