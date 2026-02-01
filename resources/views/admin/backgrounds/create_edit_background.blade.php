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

    <?php
    $conditions = $background->groupedConditions();
    ?>

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
                {!! Form::select('location', $locations, $background->location()->id ?? null, ['class' => 'form-control selectize', 'required']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $background->id ? $background->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the background will not be visible in the background list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>

    <hr/>
    <h3>Background Image(s)</h3>
    <p>Add dynamic backgrounds based on the species. Only add applicable species.</p>
    <div class="form-group repeater">
        @if($image_data)
            <div class="repeater-item d-inline-flex align-items-end mb-2 w-100 template" data-id="0">
                <div class="form-group flex-grow-1 mb-0 mr-2">
                    {!! Form::label('Species') !!}
                    {!! Form::select('image[#SPECIES_ID][species_id]', $specieses, null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group flex-grow-1 mb-0 mr-2">
                    {!! Form::label('Image') !!}
                    <div class="custom-file">
                        {!! Form::label('image[#SPECIES_ID][file]', 'Choose file...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('image[#SPECIES_ID][file]', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
                    </div>
                </div>
                <a class="btn btn-danger remove-repeater-item">Remove</a>
            </div>
            @foreach($image_data as $species_id => $image_name)
                <div class="repeater-item d-inline-flex align-items-end mb-2 w-100" data-id="0">
                    <div class="form-group flex-shrink-1 mb-0 mr-2">
                        <img src="{{ url('/') . '/' . $background->imageDirectory . '/' . $image_name }}" alt="Background Image" class="rounded" style="max-width:65px;"/>
                    </div>
                    <div class="form-group flex-grow-1 mb-0 mr-2">
                        {!! Form::label('Species') !!}
                        {!! Form::select('image['.$species_id.'][species_id]', $specieses, $species_id, ['class' => 'form-control']) !!}
                    </div>
                    <div class="form-group flex-grow-1 mb-0 mr-2">
                        {!! Form::label('Image') !!}
                        <div class="custom-file">
                            {!! Form::label('image['.$species_id.'][file]', $image_name, ['class' => 'custom-file-label']) !!}
                            {!! Form::file('image['.$species_id.'][file]', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
                        </div>
                    </div>
                    <a class="btn btn-danger remove-repeater-item">Remove</a>
                </div>
            @endforeach
        @else
            <div class="repeater-item d-inline-flex align-items-end mb-2 w-100 template" data-id="0">
                <div class="form-group flex-grow-1 mb-0 mr-2">
                    {!! Form::label('Species') !!}
                    {!! Form::select('image[#SPECIES_ID][species_id]', $specieses, null, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group flex-grow-1 mb-0 mr-2">
                    {!! Form::label('Image') !!}
                    <div class="custom-file">
                        {!! Form::label('image[#SPECIES_ID][file]', 'Choose file...', ['class' => 'custom-file-label']) !!}
                        {!! Form::file('image[#SPECIES_ID][file]', ['class' => 'custom-file-input', 'id' => 'mainImage']) !!}
                    </div>
                </div>
                <a class="btn btn-danger remove-repeater-item">Remove</a>
            </div>
        @endif
    </div>
    <div class="text-right">
        <a class="btn btn-primary add-repeater-item">Add Row</a>
    </div>
    <hr/>



    <h3>Conditional Options</h3>
    <p>How this background is accessible to a character. Leave these conditions blank if this background is free to use within the location. Note that all of these conditions are "OR" conditions. If the background starts as a personal background and then
        also moves to a award-based background you'll want to enter the player so they can always use it, AND the applicable award so that any character with that award can also use the background.</p>

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
        {!! Form::select('award_id[]', $awards, array_key_exists('Award', $conditions) ? $conditions['Award'] : null, ['class' => 'form-control selectize', 'multiple']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Applicable Status') !!}{!! add_help('What status the character needs to be in to use this background.') !!}
        {!! Form::select('status', $statuses, array_key_exists('Status', $conditions) ? $conditions['Status'] : null, ['class' => 'form-control selectize']) !!}
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

            $image_item = $('.repeater-item.template').first().clone();
            $('.repeater-item.template').first().remove();

            //Add a new repeater item
            $('.add-repeater-item').on('click', function(e) {
                e.preventDefault();
                var newId = $('.repeater-item').length;
                var newItem = $image_item.clone();

                console.log($image_item);

                newItem.attr('data-id', newId);
                newItem.find('select, input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        name = name.replace('#SPECIES_ID', newId);
                        $(this).attr('name', name);
                    }
                    if ($(this).is('select')) {
                        $(this).val('');
                    } else {
                        $(this).val(null);
                    }
                });
                newItem.find('label').each(function() {
                    var labelFor = $(this).attr('for');
                    if (labelFor) {
                        labelFor = labelFor.replace('#SPECIES_ID', newId);
                        $(this).attr('for', labelFor);
                    }
                });
                //newItem.find('.custom-file-label').text('Choose file...');
                $('.repeater').append(newItem);
            });

            //Remove a repeater item
            $(document).on('click', '.remove-repeater-item', function(e) {
                e.preventDefault();
                $(this).closest('.repeater-item').remove();
            });

        });
    </script>
@endsection
