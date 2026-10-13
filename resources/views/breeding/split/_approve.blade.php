{!! Form::open(['url' => 'breeding/' . $breeding->id . '/split/approve']) !!}
<p>This will approve the breeding with the breeding creator and add it into the admin queue.</p>
<div class="text-right">
    {!! Form::submit('Approve', ['class' => 'btn btn-success']) !!}
</div>
{!! Form::close() !!}
