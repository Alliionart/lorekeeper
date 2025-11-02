@if($userArea)
    {!! Form::open(['url' => 'cultivation/area/delete/'.$userArea->id]) !!}

    <p>You are about to abandon the <strong>{{ $userArea->area->name }}</strong> area. This will allow you to exploe new areas instead. However, this action is not reversible, and your progress in this area will be lost.</p>
    <p>Are you sure you want to abandon it ?</p>

    <div class="text-right">
        {!! Form::submit('Abandon Area', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid area selected.
@endif