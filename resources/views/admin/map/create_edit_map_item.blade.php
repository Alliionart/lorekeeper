@extends('admin.layout')

@section('admin-title')
    {{ $map_item->id ? 'Edit' : 'Create' }} Map Item
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Map' => 'admin/data/map', ($map_item->id ? 'Edit' : 'Create') . ' Map Item' => $map_item->id ? 'admin/data/map/edit/' . $map_item->id : 'admin/data/map/create']) !!}

    <h1>{{ $map_item->id ? 'Edit' : 'Create' }} Map Item</h1>

    {!! Form::open(['url' => $map_item->id ? 'admin/data/map/edit/' . $map_item->id : 'admin/data/map/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Map Item Name') !!}
                {!! Form::text('name', $map_item->name, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Type') !!} {!! add_help('This is a unique name used to form the URL of the page. Only alphanumeric characters, dash and underscore (no spaces) can be used.') !!}
                {!! Form::select('type', [], $map_item->type, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Map InfoWindow Content') !!}
        {!! Form::textarea('description', $map_item->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Latitude') !!}
                {!! Form::number('latitude', $map_item->latitude ?? null, ['class' => 'form-control', 'step' => 'any']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Longitude') !!}
                {!! Form::number('longitude', $map_item->longitude ?? null, ['class' => 'form-control', 'step' => 'any']) !!}
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($map_item->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-page-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/dta/map/delete') }}/{{ $map_item->id }}", 'Delete Map Item');
            });
        });
    </script>
@endsection
