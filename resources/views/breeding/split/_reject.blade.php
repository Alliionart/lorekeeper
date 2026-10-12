{!! Form::open(['url' => 'breeding/' . $breeding->id . '/split/reject']) !!}
<p>This will reject the breeding split request and send a notification to the breeding creator.</p>
<div class="text-right">
    {!! Form::submit('Reject', ['class' => 'btn btn-danger']) !!}
</div>
{!! Form::close() !!}