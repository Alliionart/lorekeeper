@if ($breedingPermission)
    {!! Form::open(['url' => 'admin/character/' . $character->slug . '/breeding-permissions/' . $breedingPermission->id . '/use']) !!}

    @if ($breedingPermission->quantity > 1)
        <p>This will mark x1 breeding permission as being used. This is not reversible. After this action is performed you will have {{ $breedingPermission->quantity - 1 }} breeding permissions remaining.</p>
    @else
        <p>This will marked this breeding permission as being used. This is not reversible. Are you sure you want to mark this breeding permission as used?</p>
    @endif

    <div class="form-group text-right">
        {!! Form::submit('Mark Used', ['class' => 'btn btn-success']) !!}
    </div>

    {!! Form::close() !!}
@else
    <p>Invalid breeding permission selected.</p>
@endif
