@extends('layouts.app')


@section('title')
    Submit a Breeding
@endsection


@section('content')
    {!! breadcrumbs(['Breeding' => 'breeding']) !!}
    <h1>Submit a Breeding</h1>

    <div class="site-page-content parsed-text">
        <p>To submit your breeding select both characters, and any items you'd like to use. Make sure you review before you submit!</p>
        {!! Form::open(['url' => 'submit-breeding']) !!}
        <div class="card mb-3">
            <div class="card-header">
                <h4>Character #1</h4>
            </div>
            <div class="card-body">

            </div>
        </div>

        {!! Form::close() !!}
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {

        });
    </script>
@endsection
