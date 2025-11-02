@if($plot)
    {!! Form::open(['url' => 'admin/cultivation/plots/delete/'.$plot->id]) !!}

    <p>You are about to delete the plot <strong>{{ $plot->name }}</strong>. This is not reversible. If users who possess this plot exist, this plot cannot be deleted.</p>
    <p>Are you sure you want to delete <strong>{{ $plot->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Area', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid plot selected.
@endif