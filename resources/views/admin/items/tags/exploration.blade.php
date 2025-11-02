<h3>Area</h3>
<div class="row">
    <div class="col">
        {!! Form::label('Unlock Area') !!} {!! add_help('Choose the area that should be unlocked when using this item from the inventory.') !!}
    </div>
    <div class="col">
        {{ Form::select('area_id', $areas, $tag->getData()['area_id'] ?? 0, ['class' => 'form-control mr-2', 'placeholder' => 'Select Area']) }}
    </div>
</div>