@extends('admin.layout')

@section('admin-title')
    Class Types
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Character Classes' => 'admin/character-classes', 'Class Types' => 'admin/character-classes/types', ($type->id ? 'Edit' : 'Create') . ' Types' => $type->id ? 'admin/character-classes/type/edit/' . $type->id : 'admin/character-classes/type/create']) !!}

    <h1>{{ $type->id ? 'Edit' : 'Create' }} Type
        @if ($type->id)
            <a href="#" class="btn btn-danger float-right delete-class-button">Delete Type</a>
        @endif
    </h1>

    {!! Form::open(['url' => $type->id ? 'admin/character-classes/types/edit/' . $type->id : 'admin/character-classes/types/create']) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $type->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Parent Type') !!}
                {!! Form::select('parent_type_id', $types, $type->parent_type_id, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description') !!}
        {!! Form::textarea('description', $type->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($type->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-class-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/character-classes/types/delete') }}/{{ $type->id }}", 'Delete Type');
            });
        });
    </script>
@endsection
