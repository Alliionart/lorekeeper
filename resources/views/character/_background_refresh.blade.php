{!! Form::label('background', 'Background') !!}
{!! Form::select('background', $character->applicableBackgrounds($location), null, ['class' => 'form-control selectize', 'required']) !!}
