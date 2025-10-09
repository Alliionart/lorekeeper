@extends('admin.layout')

@section('admin-title')
    {{ $background->id ? 'Edit' : 'Create' }} Background
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Backgrounds' => 'admin/data/backgrounds',
        ($background->id ? 'Edit' : 'Create') . ' Background' => $background->id ? 'admin/data/background/edit/' . $background->id : 'admin/data/background/create',
    ]) !!}

    <h1>{{ $background->id ? 'Edit' : 'Create' }} Background
        @if ($background->id)
            <a href="#" class="btn btn-danger float-right delete-button">Delete Background</a>
        @endif
    </h1>

    {!! Form::open(['url' => $background->id ? 'admin/data/background/edit/' . $background->id : 'admin/data/background/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $background->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Applicable Location') !!}{!! add_help('What location the character needs to be in to use this background.') !!}
                {!! Form::select('location', $locations, $background->location() ?? null, ['class' => 'form-control selectize', 'required']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $background->id ? $background->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the background will not be visible in the background list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>

    <h3>Image Upload</h3>
    <div class="form-group">
        {!! Form::label('Image') !!}
        <div class="custom-file">
            {!! Form::label('image', file_exists($background->imageDirectory . '/' . $background->imageFileName) ? $background->imageFileName : 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
        </div>
        <h5 class="mt-3">Select a Preview Image using the cropper</h5>
        <div class="form-group mt-2 hide">
            {!! Form::checkbox('use_cropper', 1, 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'useCropper']) !!}
            {!! Form::label('use_cropper', 'Use Image Cropper', ['class' => 'form-check-label ml-3']) !!} {!! add_help('A thumbnail is required for the upload (used for the masterlist). You can use the image cropper (crop dimensions can be adjusted in the site code), or upload a custom thumbnail.') !!}
        </div>
        <div class="card mb-3" id="thumbnailCrop">
            <div class="card-body">
                <div id="cropSelect">Select an image to use the thumbnail cropper.</div>
                <img src="#" id="cropper" class="hide" alt="" />
                {!! Form::hidden('x0', null, ['id' => 'cropX0']) !!}
                {!! Form::hidden('x1', null, ['id' => 'cropX1']) !!}
                {!! Form::hidden('y0', null, ['id' => 'cropY0']) !!}
                {!! Form::hidden('y1', null, ['id' => 'cropY1']) !!}
            </div>
        </div>
    </div>

    <h3>Conditional Options</h3>
    <p>How this background is accessible to a character. Leave these conditions blank if this background is free to use within the location. Note that all of these conditions are "OR" conditions. If the background starts as a personal background and then
        also moves to a award-based background you'll want to enter the player so they can always use it, AND the applicable award so that any character with that award can also use the background.</p>

    <?php
    $conditions = $background->groupedConditions();
    ?>

    <div class="form-group">
        {!! Form::label('Users that may use this Background') !!}{!! add_help('Characters MUST be owned by these users to use the background.') !!}
        {!! Form::select('user_id[]', $users, array_key_exists('User', $conditions) ? $conditions['User'] : null, ['class' => 'form-control selectize', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Applicable Guilds') !!}{!! add_help('Guilds that use this background.') !!}
        {!! Form::select('guild_id[]', [], array_key_exists('Guild', $conditions) ? $conditions['Guild'] : null, ['class' => 'form-control selectize', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Applicable Awards') !!}{!! add_help('What awards the character needs to use this background.') !!}
        {!! Form::select('award_id[]', [], array_key_exists('Award', $conditions) ? $conditions['Award'] : null, ['class' => 'form-control selectize', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Applicable Status') !!}{!! add_help('What status the character needs to be in to use this background.') !!}
        {!! Form::select('status', [], null, ['class' => 'form-control selectize']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Applicable Item') !!}{!! add_help('What item the character needs in their inventory to use this background.') !!}
        {!! Form::select('item_id', $items, array_key_exists('Item', $conditions) ? $conditions['Item'] : null, ['class' => 'form-control selectize']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($background->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    @include('widgets._image_upload_js')
    <script>
        $(document).ready(function() {
            $('.delete-feature-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/background/delete') }}/{{ $background->id }}", 'Delete Background');
            });
        });
    </script>
@endsection
