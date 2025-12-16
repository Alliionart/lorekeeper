@extends('admin.layout')

@section('admin-title')
    Edit Map Settings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Map' => 'admin/data/map/settings']) !!}

    <h1>Edit Map Settings</h1>

    {!! Form::open(['url' => 'admin/data/map/settings/edit/', 'files' => true]) !!}

    <div class="row">
        
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
