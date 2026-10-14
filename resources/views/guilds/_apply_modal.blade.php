{!! Form::open(['url' => '/' . __('guilds.guilds') . '/' . $guild->id . '/apply']) !!}

    <div class="form-group">
        {!! Form::label('application_message', 'Application Message') !!}
        {!! Form::textarea('application_message', $guild->application_message, ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Enter your application message here...']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Apply to Guild', ['class' => 'btn btn-primary']) !!}
    </div>

{!! Form::close() !!}