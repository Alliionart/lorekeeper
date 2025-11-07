@extends('layouts.app')

@section('title')
    Edit {{ $guild->name }}'s Ranks
@endsection

@section('sidebar')
    @include('guilds._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds'), $guild->name => $guild->name, 'Edit Ranks' => 'edit-ranks']) !!}

    <h1>Edit {{ $guild->name }}'s Ranks</h1>
    <p>Edit your {{ __('guilds.guild') }} below. Only {{ __('guilds.guild') }} owners and mods may edit the guild. Staff may edit your guild as well.</p>

    {!! Form::open(['url' => '/guilds/edit-ranks/' . $guild->id, 'id' => 'guildSettingForm', 'files' => true]) !!}

    <h3>User Ranks</h3>
    <div class="user-ranks">
        <div class="rank-row">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('rank_name', 'Name') !!}
                        {!! Form::text('rank_name', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('rank_threshold[]', 'Reputation Threshold') !!}
                        {!! Form::number('rank_threshold[]', null, ['class' => 'form-control', 'min' => 0]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('description[]', 'Description (Optional)') !!} {!! add_help('Give info about your ' . __('guilds.guild') . '! This can include images, tables, or other bootrap v4 content.') !!}
                        {!! Form::text('description[]', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('Rank Icon (Optional)') !!} {!! add_help('Add an optional icon to distinguish the rank.') !!}
                        <div class="custom-file">
                            {!! Form::label('icon[]', 'Choose file...', ['class' => 'custom-file-label']) !!}
                            {!! Form::file('icon[]', ['class' => 'custom-file-input']) !!}
                        </div>
                        <div class="text-muted">Recommended size: 50px x 50px</div>
                        @if ($guild->has_logo)
                            <div class="form-check">
                                {!! Form::checkbox('remove_icon[]', 1, false, ['class' => 'form-check-input']) !!}
                                {!! Form::label('remove_icon[]', 'Remove current icon', ['class' => 'form-check-label']) !!}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-1">
                    <a href="#" class="btn btn-danger remove-rank">Remove</a>
                </div>
            </div>
        </div>
        <div class="text-right">
            <a href="#" class="btn btn-primary add-rank">Add Rank</a>
        </div>
    </div>

    <h3>Character Ranks</h3>
    <div class="character-ranks">
        <div class="rank-row">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('rank_name', 'Name') !!}
                        {!! Form::text('rank_name', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('rank_threshold[]', 'Reputation Threshold') !!}
                        {!! Form::number('rank_threshold[]', null, ['class' => 'form-control', 'min' => 0]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('description[]', 'Description (Optional)') !!} {!! add_help('Give info about your ' . __('guilds.guild') . '! This can include images, tables, or other bootrap v4 content.') !!}
                        {!! Form::text('description[]', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('Rank Icon (Optional)') !!} {!! add_help('Add an optional icon to distinguish the rank.') !!}
                        <div class="custom-file">
                            {!! Form::label('icon[]', 'Choose file...', ['class' => 'custom-file-label']) !!}
                            {!! Form::file('icon[]', ['class' => 'custom-file-input']) !!}
                        </div>
                        <div class="text-muted">Recommended size: 50px x 50px</div>
                        @if ($guild->has_logo)
                            <div class="form-check">
                                {!! Form::checkbox('remove_icon[]', 1, false, ['class' => 'form-check-input']) !!}
                                {!! Form::label('remove_icon[]', 'Remove current icon', ['class' => 'form-check-label']) !!}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-1">
                    <a href="#" class="btn btn-danger remove-rank">Remove</a>
                </div>
            </div>
        </div>
        <div class="text-right">
            <a href="#" class="btn btn-primary add-rank">Add Rank</a>
        </div>
    </div>

    

    <div class="text-right mt-4">
        {!! Form::submit('Update', ['class' => 'btn btn-primary update-guild']) !!}
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
