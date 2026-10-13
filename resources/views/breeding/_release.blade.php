{!! Form::open(['url' => 'breeding/' . $breeding->id . '/' . $pup . '/release']) !!}
<p>This will release pup #{{ $pup + 1 }} of this litter and provide you 1,000 Krones. You cannot undo this action, are you sure?</p>

<div class="text-right">
    {!! Form::submit('Release', ['class' => 'btn btn-danger']) !!}
</div>
{!! Form::close() !!}
