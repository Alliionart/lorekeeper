@if ($teams)
    {!! Form::open(['url' => 'admin/teams/delete/' . $teams->id]) !!}

    <p>You are about to delete the team <strong>{{ $teams->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $teams->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Team', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid team selected.
@endif
