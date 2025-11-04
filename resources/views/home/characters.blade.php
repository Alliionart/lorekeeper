@extends('home.layout')

@section('home-title')
    My {{ ucwords( __('lorekeeper.characters')) }}
@endsection

@section('home-content')
    {!! breadcrumbs(['My ' . ucwords( __('lorekeeper.characters')) => 'characters']) !!}

    <h1>
        My {{ ucwords( __('lorekeeper.characters')) }}
    </h1>

    <p>This is a list of characters you own. Drag and drop to rearrange them.</p>

    <div id="sortable" class="row sortable">
        @foreach ($characters as $character)
            @include('browse._character_box', ['character' => $character])
        @endforeach
    </div>
    {!! Form::open(['url' => 'characters/sort', 'class' => 'text-right']) !!}
    {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
    {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
    {!! Form::close() !!}
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $("#sortable").sortable({
                characters: '.sort-item',
                placeholder: "sortable-placeholder col-md-3 col-6",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
