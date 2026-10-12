{!! Form::open(['url' => 'breeding/' . $breeding->id . '/split/confirm']) !!}
<p>By confirming the rolled breeding, both you and the breeding creator will be notified and able to convert the genotypes.</p>
<div class="text-right">
    {!! Form::submit('I confirm', ['class' => 'btn btn-success']) !!}
</div>
{!! Form::close() !!}