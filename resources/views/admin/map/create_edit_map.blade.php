@extends('admin.layout')

@section('admin-title')
    Edit Map Settings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Map' => 'admin/data/map/settings']) !!}

    <h1>Edit Map Settings</h1>

    {!! Form::open(['url' => 'admin/data/map/settings/edit/', 'files' => true]) !!}


    <a class="btn btn-sm btn-secondary" data-toggle="collapse" href="#collapseTutorial" role="button" aria-expanded="false" aria-controls="collapseTutorial">
        Tutorial: How to Create a Custom Map
    </a>

    <div class="collapse mt-3 mb-3" id="collapseTutorial">
        <div class="card mb-3">
            <h3 class="card-header">How to Create a Custom Map</h3>
            <div class="card-body">
                <p>Maps require "<a href="https://gdal.org/en/stable/programs/gdal2tiles.html">GDAL2Tiles</a>" to be generated. This guide will walk you through the process of creating a custom map for your ARPG.</p>
                <em>This extension includes a built-in map generator that uses GDAL2Tiles to create a custom map for your ARPG. You do NOT need to install it on your computer.</em>
                <ol>
                    <li>Create a map of your world in an art program (e.g. Clip Studio Paint, GIMP, Photoshop). If you want to use an online RPG map maker of some kind - that's okay too! Just ensure it follows these requirements:
                        <ul>
                            <li>Your map should be in a single image file (PNG, JPG, etc.). OR it should be a set of tiles (typically in a zip folder).</li>
                            <li>Your map should be at least 2048 pixels in width and height. While it may be smaller, your map should be large enough to allow for zooming in and out without losing quality.</li>
                            <li>Your map should be bare with little to no text or UI elements. This extension will add them for you! Though you are free to label different biomes, continents, etc. on your map ensure they are subtle for the best quality.</li>
                        </ul>
                    </li>
                    <li>If you need to generate the map tiles:
                        <ul>
                            <li>Upload your Map to the Map Image field.</li>
                            <li>The system will take care of generating your tiles and placing them into the map's image directory.</li>
                        </ul>
                        If you <span class="text-danger">do not</span> need to generate your map tiles (e.g. you already have a set of tiles):
                        <ul>
                            <li>Upload your map tiles (in a zip folder) to the "Map Tiles" field.</li>
                            <li>The system will take care of placing them into the map's image directory.</li>
                        </ul>
                    </li>
                    <li>Save the map settings and you should be done for the map creation!</li>
                </ol>
            </div>
        </div>
    </div>

    <h3 class="mt-3">Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $map->name ?? null, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Map Image (Required)') !!} {!! add_help('This image is used only on the world information pages.') !!}
                <div class="custom-file">
                    {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
                    {!! Form::file('image', ['class' => 'custom-file-input', 'required' => true]) !!}
                </div>
                <div class="text-muted">Recommended size: 2048px x 2048px</div>
            </div>
        </div>
    </div>

    {!! Form::hidden('type', 'map') !!}

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!} {!! add_help('Add a description for your map. Helpful if you have multiple maps.') !!}
        {!! Form::textarea('description', $map->description ?? null, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Update Settings', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            
        });
    </script>
@endsection
