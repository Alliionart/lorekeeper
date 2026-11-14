<div class="form-group">
    {!! Form::checkbox('is_chimera', 1, $is_chimera, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'is_chimera']) !!}
    {!! Form::label('is_chimera', 'Is Chimera', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this character has the Chimera modifier, then this will give you the ability to customize both genomes.') !!}
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('Base Color') !!}
            {!! Form::select('base', $bases, $base, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group" connect="is_chimera" {!! $is_chimera ? '' : 'style="display:none"' !!}>
            {!! Form::label('Secondary Base Color') !!}
            {!! Form::select('secondary_base', $bases, $secondary_base, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Markings') !!}
    {!! add_help('Select markings applicable to character') !!}
    <div><a href="#" class="btn btn-primary mb-2" id="add-marking">Add Marking</a></div>
    <div id="markingList">
        @if (isset($characterMarkings))
            @foreach ($characterMarkings as $marking)
                <div class="marking d-flex mb-2">
                    {!! Form::select('marking_id[]', $markings, $marking->marking_id, ['class' => 'form-control mr-2 marking-select']) !!}
                    <div class="form-group mr-2" style="width:40%">
                        <select name="is_dominant[]" id="is_dominant[]" class="form-control markingType" placeholder="Select Type...">
                            <option value="" data-code="">Select Type...</option>
                            <option value="0" data-code="0" {{ $marking->is_dominant == 0 ? 'selected' : '' }}>Recessive</option>
                            <option value="1" data-code="1" {{ $marking->is_dominant == 1 ? 'selected' : '' }}>Dominant</option>
                        </select>
                    </div>
                    <div class="form-group mr-2" connect="is_chimera" style="width:35%;{{ $is_chimera ? '' : 'display:none;' }}">
                        <select name="side_id[]" id="side_id" class="form-control" placeholder="Select Side...">
                            <option value="" data-code="">Select Side...</option>
                            <option value="0" data-code="0" {{ $marking->data == 0 ? 'selected' : '' }}>Side 1</option>
                            <option value="1" data-code="1" {{ $marking->data == 1 ? 'selected' : '' }}>Side 2</option>
                        </select>
                    </div>
                    <div>
                        <a href="#" class="remove-marking btn btn-danger mb-2">×</a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
