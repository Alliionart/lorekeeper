<div class="marking marking-row d-flex hide mb-2">
    {!! Form::select('marking_id[]', $markings, null, ['class' => 'form-control mr-2 marking-select']) !!}
    <div class="form-group mr-2" style="width:40%">
        <select name="is_dominant[]" id="is_dominant[]" class="form-control markingType" placeholder="Select Type...">
            <option value="" data-code="">Select Type...</option>
            <option value="0" data-code="0">Recessive</option>
            <option value="1" data-code="1">Dominant</option>
        </select>
    </div>
    <div class="form-group mr-2" connect="is_chimera" style="width:35%;display:none;">
        <select name="side_id[]" id="side_id" class="form-control" placeholder="Select Side...">
            <option value="" data-code="">Select Side...</option>
            <option value="0" data-code="0">Side 1</option>
            <option value="1" data-code="1">Side 2</option>
        </select>
    </div>
    <div>
        <a href="#" class="remove-marking btn btn-danger mb-2">×</a>
    </div>
</div>
