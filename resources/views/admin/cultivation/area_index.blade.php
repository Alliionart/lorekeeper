@extends('admin.layout')

@section('admin-title') Areas @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Areas' => 'admin/cultivation/areas']) !!}

<h1>Areas</h1>

<p>This is a list of areas that users can cultivate and grow stuff in.</p> 
<p>The sorting order reflects the order in which the areas will be listed on area index and info pages.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/cultivation/areas/edit') }}"><i class="fas fa-plus"></i> Create New Area</a></div>
@if(!count($areas))
    <p>No areas found.</p>
@else 
    <table class="table table-sm area-table">
        <tbody id="sortable" class="sortable">
            @foreach($areas as $area)
                <tr class="sort-item" data-id="{{ $area->id }}">
                    <td>
                        <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                        {!! $area->displayName !!}
                    </td>
                    <td class="text-right">
                        <a href="{{ url('admin/cultivation/areas/edit/'.$area->id) }}" class="btn btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
    <div class="mb-4">
        {!! Form::open(['url' => 'admin/cultivation/areas/sort']) !!}
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