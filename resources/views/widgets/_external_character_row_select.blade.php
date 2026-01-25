<div class="row no-gutters external-character-row mb-1">
    <div class="col-5 p-1">
        {!! Form::text('external_name[]', isset($extCharacter) ? $extCharacter['name'] : '', ['class' => 'form-control', 'placeholder' => 'Character Name']) !!}
    </div>
    <div class="col-6 p-1">
        {!! Form::text('external_link[]', isset($extCharacter) ? $extCharacter['link'] : '', ['class' => 'form-control', 'placeholder' => 'Link to Character']) !!}
    </div>
    <div class="col-1 p-1">
        <a href="#" class="btn btn-danger remove-ext-character-button">x</a>
    </div>
</div>
