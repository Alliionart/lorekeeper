@extends('admin.layout')

@section('admin-title') Plots @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Plots' => 'admin/cultivation/plots']) !!}

<h1>Plots</h1>

<p>This is a list of plots that users can cultivate and grow stuff in.</p> 
<p>The sorting order reflects the order in which the plots will be listed on plot index and info pages.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/cultivation/plots/edit') }}"><i class="fas fa-plus"></i> Create New Plot</a></div>
@if(!count($plots))
    <p>No plots found.</p>
@else 
    <table class="table table-sm plot-table">
        <tbody id="sortable" class="sortable">
            @foreach($plots as $plot)
                <tr class="sort-item" data-id="{{ $plot->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $plot->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/cultivation/plots/edit/'.$plot->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/cultivation/plots/sort']) !!}
        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    </div>
@endif

@endsection

@section('scripts')
@parent
<script>

$( document ).ready(function() {
    $('.handle').on('click', function(e) {
        e.preventDefault();
    });
    $( "#sortable" ).sortable({
        items: '.sort-item',
        handle: ".handle",
        placeholder: "sortable-placeholder",
        stop: function( event, ui ) {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        },
        create: function() {
            $('#sortableOrder').val($(this).sortable("toArray", {attribute:"data-id"}));
        }
    });
    $( "#sortable" ).disableSelection();
});
</script>
@endsection