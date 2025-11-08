@if ($reward)
    {!! Form::open(['url' => 'admin/discord/rewards/delete/' . $reward->id]) !!}

    <p>You are about to delete the reward for level <strong>{{ $reward->level }}</strong>. This is not reversible.</p>

    <div class="text-right">
        {!! Form::submit('Delete Reward', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid reward selected.
@endif
