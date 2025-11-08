@extends('admin.layout')

@section('admin-title')
    Character Classes
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Character Classes' => 'admin/character-classes', ($class->id ? 'Edit' : 'Create') . ' Class' => $class->id ? 'admin/character-classes/edit/' . $class->id : 'admin/character-classes/create']) !!}

    <h1>{{ $class->id ? 'Edit' : 'Create' }} Class
        @if ($class->id)
            <a href="#" class="btn btn-danger float-right delete-class-button">Delete Class</a>
        @endif
    </h1>

    {!! Form::open(['url' => $class->id ? 'admin/character-classes/edit/' . $class->id : 'admin/character-classes/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $class->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($class->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description') !!}
        {!! Form::textarea('description', $class->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $class->id ? $class->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, this class will not be visible on world pages.') !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Prerequisite') !!} {!! add_help('If linked to another class this will be a class further in the class tree.') !!}
                {!! Form::select('parent_class_id', $classes, $class->parent_class_id, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('Class Type') !!}
                {!! Form::select('class_type', $class_types, $class->class_type, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('Class Subtype') !!}
                {!! Form::select('class_subtype', $class_types, $class->class_subtype, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('abilities[]', 'Linked Abilities') !!} {!! add_help('Select applicable abilities for this class. If there are multiple options you may select up to 5. Note that if there ARE multiple options, characters will be required to choose one.') !!}
                {!! Form::select('abilities[]', $abilities, $c_ability, ['class' => 'form-control', 'id' => 'abilities', 'multiple']) !!}
            </div>
        </div>
    </div>


    <div class="text-right">
        {!! Form::submit($class->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($class->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._entry', ['imageUrl' => $class->classImageUrl, 'name' => $class->displayName, 'description' => $class->parsed_description, 'visible' => $class->is_visible])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-class-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/character-classes/delete') }}/{{ $class->id }}", 'Delete Class');
            });
            $('#abilities').selectize({
                multiple: true,
                maxItems: 5,
            });
        });
    </script>
@endsection
