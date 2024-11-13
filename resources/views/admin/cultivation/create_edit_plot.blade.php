@extends('admin.layout')

@section('admin-title') Plots @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Plots' => 'admin/cultivation/plots', ($plot->id ? 'Edit' : 'Create').' Plot' => $plot->id ? 'admin/cultivation/plots/edit/'.$plot->id : 'admin/cultivation/plots/edit']) !!}

<h1>{{ $plot->id ? 'Edit' : 'Create' }} Plot
    @if($plot->id)
        ({!! $plot->displayName !!})
        <a href="#" class="btn btn-danger float-right delete-type-button">Delete Plot</a>
    @endif
</h1>

{!! Form::open(['url' => $plot->id ? 'admin/cultivation/plots/edit/'.$plot->id : 'admin/cultivation/plots/edit', 'files' => true]) !!}


<div class="card mb-3">
    <div class="card-header h3">Basic Information</div>
    <div class="card-body">

        <div class="row mx-0 px-0">
            <div class="form-group col-md px-0 pr-md-1">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $plot->name, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header h3">Images</div>
    <div class="m-4">A plot has 5 different stages of growth that it goes through. If you wish to use less images, you can use the same one for multiple stages.
        <div class="text-muted">Recommended size: 250px wide, as that is what images will be sized to. </div>
    </div>
    <div class="card-body row">

        <div class="form-group col-md p-4 m-2 border">
            @if($plot->stage_1_extension)
                <a href="{{$plot->getStageImage(1)}}"  data-lightbox="entry" data-title="{{ $plot->name }}"><img src="{{$plot->getStageImage(1)}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Stage 1 Image (Optional)') !!} {!! add_help('Image of the empty plot.') !!}
            <div>{!! Form::file('stage_1_image') !!}</div>
            @if(isset($plot->stage_1_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_stage_1', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Image As-Is', 'data-on' => 'Remove Image']) !!}
                </div>
            @endif
        </div>

        <div class="form-group col-md p-4 m-2 border">
            @if($plot->stage_2_extension)
                <a href="{{$plot->getStageImage(2)}}"  data-lightbox="entry" data-title="{{ $plot->name }}"><img src="{{$plot->getStageImage(2)}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Stage 2 Image (Optional)') !!} {!! add_help('Image of a tiny first growth, after planting something.') !!}
            <div>{!! Form::file('stage_2_image') !!}</div>
            @if(isset($plot->stage_2_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_stage_2', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Image As-Is', 'data-on' => 'Remove Image']) !!}
                </div>
            @endif
        </div>

        <div class="form-group col-md p-4 m-2 border">
            @if($plot->stage_3_extension)
                <a href="{{$plot->getStageImage(3)}}"  data-lightbox="entry" data-title="{{ $plot->name }}"><img src="{{$plot->getStageImage(3)}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Stage 3 Image (Optional)') !!} {!! add_help('Mid sized growth stage.') !!}
            <div>{!! Form::file('stage_3_image') !!}</div>
            @if(isset($plot->stage_3_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_stage_3', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Image As-Is', 'data-on' => 'Remove Image']) !!}
                </div>
            @endif
        </div>

        <div class="form-group col-md p-4 m-2 border">
            @if($plot->stage_4_extension)
                <a href="{{$plot->getStageImage(4)}}"  data-lightbox="entry" data-title="{{ $plot->name }}"><img src="{{$plot->getStageImage(4)}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Stage 4 Image (Optional)') !!} {!! add_help('Fully grown stage.') !!}
            <div>{!! Form::file('stage_4_image') !!}</div>
            @if(isset($plot->stage_4_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_stage_4', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Image As-Is', 'data-on' => 'Remove Image']) !!}
                </div>
            @endif
        </div>
 
        <div class="form-group col-md p-4 m-2 border">
            @if($plot->stage_5_extension)
                <a href="{{$plot->getStageImage(5)}}"  data-lightbox="entry" data-title="{{ $plot->name }}"><img src="{{$plot->getStageImage(5)}}" class="mw-100 float-left mr-3" style="max-height:125px"></a>
            @endif
            {!! Form::label('Stage 5 Image (Optional)') !!} {!! add_help('Ready for harvest with fruit/whatever!') !!}
            <div>{!! Form::file('stage_5_image') !!}</div>
            @if(isset($plot->stage_5_extension))
                <div class="form-check">
                    {!! Form::checkbox('remove_stage_5', 1, false, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-off' => 'Leave Image As-Is', 'data-on' => 'Remove Image']) !!}
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
            {!! Form::textarea('description', $plot->description, ['class' => 'form-control wysiwyg']) !!}
        </div>
    </div>
</div>

@if($plot->id)
<div class="card mb-3">
    <div class="card-header h3">
        {!! Form::label('Seed Items') !!} {!! add_help('Add all Seed items that may be planted on this plot.') !!}
    </div>

    <div class="text-right mb-3">
        <a href="#" class="btn btn-outline-info" id="addArea">Add Item</a>
    </div>

    <table class="table table-sm" id="itemTable">
        <tbody id="itemTableBody">
            @if($plot->allowedItems->count() > 0)
            @foreach($plot->allowedItems as $item)
            <tr class="loot-row">
                <td class="loot-row-select">
                    {!! Form::select('item_id[]', $items, $item->id, ['class' => 'form-control item-select',
                    'placeholder' => 'Select Item']) !!}
                </td>
                <td class="text-right"><a href="#" class="btn btn-danger remove-area-button">Remove</a></td>
            </tr>
            @endforeach
            @endif

        </tbody>
    </table>
</div>
@endif

<div class="form-group">
    {!! Form::checkbox('is_active', 1, $plot->id ? $plot->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the type will not be visible to regular users.') !!}
</div>

<div class="text-right">
    {!! Form::submit($plot->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
</div>

{!! Form::close() !!}

<div id="itemRowData" class="hide">
    <table class="table table-sm">
        <tbody id="itemRow">
        <tr class="loot-row">
        <td class="loot-row-select">
            {!! Form::select('item_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
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
        loadModal("{{ url('admin/cultivation/plots/delete') }}/{{ $plot->id }}", 'Delete Plot');
    });
    $('.selectize').selectize();

    var $itemTable = $('#itemTableBody');
    var $itemRow = $('#itemRow').find('.loot-row');

    $('#itemTableBody .selectize').selectize();
    attachRemoveListener($('#itemTableBody .remove-area-button'));


    $('#addArea').on('click', function(e) {
        e.preventDefault();
        var $clone = $itemRow.clone();
        $clone.removeClass('hide');

        $itemTable.append($clone);
        attachRemoveListener($clone.find('.remove-area-button'));
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
