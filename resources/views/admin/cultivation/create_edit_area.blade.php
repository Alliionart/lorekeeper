@extends('admin.layout')

@section('admin-title') Areas @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Areas' => 'admin/cultivation/areas', ($area->id ? 'Edit' : 'Create').' Area' => $area->id ? 'admin/cultivation/areas/edit/'.$area->id : 'admin/cultivation/areas/edit']) !!}

<h1>{{ $area->id ? 'Edit' : 'Create' }} Area
    @if($area->id)
        ({!! $area->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-type-button">Delete Area</a>
    @endif
</h1>

{!! Form::open(['url' => $area->id ? 'admin/cultivation/areas/edit/'.$area->id : 'admin/cultivation/areas/edit', 'files' => true]) !!}


<div class="card mb-3">
    <div class="card-header h3">Basic Information</div>
    <div class="card-body">

        <div class="row mx-0 px-0">
            <div class="form-group col-md px-0 pr-md-1">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $area->name, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group col-md px-0 pr-md-1">
            {!! Form::label('Max Plots') !!} {!! add_help('The maximum amount of plots a user may unlock here.') !!}
            {!! Form::number('max_plots', $area->max_plots ?? 1,  ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h3">Images</div>
    <div class="card-body row">
        <div class="form-group col-md-6">
            @if($area->background_extension)
                <a href="{{$area->backgroundImageUrl}}"  data-lightbox="entry" data-title="{{ $area->name }}"><img src="{{$area->backgroundImageUrl}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Background Image (Optional)') !!} {!! add_help('This image will serve as the background to the area which plots will be rendered on to.') !!}
            <div>{!! Form::file('background_image') !!}</div>
            <div class="text-muted">Recommended size: any, but might need some fiddling to look good.</div>
            @if(isset($area->background_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_background', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Background Image As-Is', 'data-on' => 'Remove Background Image']) !!}
                </div>
            @endif
        </div>

        <div class="form-group col-md-6">
            @if($area->plot_extension)
                <a href="{{$area->plotImageUrl}}"  data-lightbox="entry" data-title="{{ $area->name }}"><img src="{{$area->plotImageUrl}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Default Plot Image (Optional)') !!} {!! add_help('This image is for plots that are not yet ready for planting.') !!}
            <div>{!! Form::file('plot_image') !!}</div>
            <div class="text-muted">Recommended size: 250px wide, as that is what images will be sized to.</div>
            @if(isset($area->plot_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_plot', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Plot Image As-Is', 'data-on' => 'Remove Plot Image']) !!}
                </div>
            @endif
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h3">
        {!! Form::label('Description (Optional)') !!}
    </div>
    <div class="card-body">
        <div class="form-group" style="clear:both">
            {!! Form::textarea('description', $area->description, ['class' => 'form-control wysiwyg']) !!}
        </div>
    </div>
</div>

@if($area->id)
<div class="card mb-3">
    <div class="card-header h3">
        {!! Form::label('Plots') !!} {!! add_help('Add all plots that will be available in this area.') !!}
    </div>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="addPlot">Add Plot</a>
    </div>

    <table class="table table-sm" id="plotTable">
        <tbody id="plotTableBody">

            @if($area->allowedPlots->count() > 0)
            @foreach($area->allowedPlots as $plot)
            <tr class="loot-row">
                <td class="loot-row-select">
                    {!! Form::select('plot_id[]', $plots, $plot->id, ['class' => 'form-control item-select',
                    'placeholder' => 'Select Plot']) !!}
                </td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-plot-button">Remove</a></td>
            </tr>
            @endforeach
            @endif

        </tbody>
    </table>
</div>
@endif

<div class="form-group">
    {!! Form::checkbox('is_active', 1, $area->id ? $area->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the type will not be visible to regular users.') !!}
</div>

<div class="text-right">
    {!! Form::submit($area->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}


<div id="plotRowData" class="hide">
    <table class="table table-sm">
        <tbody id="plotRow">
        <tr class="loot-row">
        <td class="loot-row-select">
            {!! Form::select('plot_id[]', $plots, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Plot']) !!}
        </td>
        <td class="text-right"><a href="#" class="btn btn-danger remove-plot-button">Remove</a></td>
    </tr>
        </tbody>
    </table>
</div>

@endsection

@section('scripts')
@parent
<script>
$( document ).ready(function() {
    $('.delete-type-button').on('click', function(e) {
        e.preventDefault();
        loadModal("{{ url('admin/cultivation/areas/delete') }}/{{ $area->id }}", 'Delete Area');
    });
    $('.selectize').selectize();

    
    var $plotTable = $('#plotTableBody');
    var $plotRow = $('#plotRow').find('.loot-row');

    console.log($plotRow);

    $('#plotTableBody .selectize').selectize();
    attachRemoveListener($('#plotTableBody .remove-plot-button'));


    $('#addPlot').on('click', function(e) {
        e.preventDefault();
        var $clone = $plotRow.clone();
        $clone.removeClass('hide');

        $plotTable.append($clone);
        attachRemoveListener($clone.find('.remove-plot-button'));
    });

   
    function attachRemoveListener(node) {
        node.on('click', function(e) {
            e.preventDefault();
            $(this).parent().parent().remove();
        });
    }
});

</script>
@endsection
