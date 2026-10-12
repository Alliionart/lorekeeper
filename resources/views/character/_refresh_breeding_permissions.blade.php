{!! Form::open(['url' => 'character/' . $character->slug . '/breeding-permissions/refresh']) !!}

<div class="form-group">
    {!! Form::label('quantity', 'Quantity') !!}
    {!! Form::number('quantity', 50, ['class' => 'form-control', 'value' => 1, 'min' => 1, 'max' => 50]) !!}
</div>

<div class="form-group text-right">
    {!! Form::submit('Refresh Breeding Permissions', ['class' => 'btn btn-success']) !!}
</div>

{!! Form::close() !!}