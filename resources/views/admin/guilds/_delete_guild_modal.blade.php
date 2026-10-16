@if ($guild)
    {!! Form::open(['url' => 'admin/guilds/delete/' . $guild->id]) !!}

    <p>You are about to delete the {{ ucfirst(__('guilds.guild')) }} <strong>{{ $guild->name }}</strong>.</p>
    <p>This will remove the {{ ucfirst(__('guilds.guild')) }} entirely and it will not be recoverable
    <p>Are you sure you want to delete <strong>{{ $guild->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete guild', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ ucfirst(__('guilds.guild')) }} selected.
@endif
