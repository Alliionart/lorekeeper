@extends('home.layout')

@section('home-title')
    New {{ ucfirst(__('guilds.guild')) }} Request
@endsection

@section('home-content')
    {!! breadcrumbs([__('guilds.guilds') => 'guilds', 'New ' . __('guilds.guild') . ' Request' => 'guilds/new']) !!}

    <h1>Request to Open a {{ __('guilds.guild') }}</h1>

    {!! Form::open(['url' => 'guilds/new', 'id' => 'guildForm', 'files' => true]) !!}

    <div class="alert alert-info">
        <strong>You are about to request to open a new {{ __('guilds.guild') }}. You will automatically be assigned as the Owner.</strong><br>
        Note that this is a request, and will need to be approved by staff before the {{ __('guilds.guild') }} is created.
        You will be able to add members to your {{ __('guilds.guild') }} once it is approved.
    </div>

    <div class="form-group">
        {!! Form::label('name', 'Guild Name') !!}
        {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Guild Description') !!}
        {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'rows' => 4]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Guild Logo (optional)') !!} {!! add_help('This is the logo that will show on the guild page') !!}
        <div class="custom-file">
            {!! Form::label('logo', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('logo', ['class' => 'custom-file-input', 'accept' => 'image/*']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
    </div>

    <div class="text-right">
        {!! Form::submit('Submit Request', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
@endsection
