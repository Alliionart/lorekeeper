<div class="row subtype-row mb-2 p-3 border border-secondary rounded mx-0{{ $data ? ' delete' : '' }}" type="subtype" data-row="0" data="row-start">
    <div class="col-md-12 d-inline-flex align-items-center form-group mb-0">
        {!! Form::select('subtypes[__INDEX__][parent][0]', $subtypes, null, ['class' => 'form-control', 'id' => 'subtype']) !!}
        <span class="mx-4">x</span>
        {!! Form::select('subtypes[__INDEX__][parent][1]', $subtypes, null, ['class' => 'form-control', 'id' => 'subtype']) !!}
        <a class="btn btn-danger remove-row ml-3">Remove Group</a>
    </div>
    <hr class="w-100 my-3" />
    <h5>Results In...</h5>
    <div class="col-md-12">
        <div class="subtypeRepeater subRepeater" type="subtypeSub">
            <div class="repeaterBody subgroup">
                <div class="row mb-2" type="subtypeSub" data="row-start">
                    <div class="col-md-4 form-group mb-0">
                        {!! Form::select('subtypes[__INDEX__][results][__SUB_INDEX__][subtype]', $subtypes, null, ['class' => 'form-control', 'id' => 'subtype_result']) !!}
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        {!! Form::number('subtypes[__INDEX__][results][__SUB_INDEX__][rate]', null, ['class' => 'form-control', 'id' => 'subtype_result_rate', 'placeholder' => 'Rate (%)', 'min' => 0, 'max' => 100]) !!}
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                        <a class="btn btn-danger remove-row">-</a>
                    </div>
                </div>
            </div>
            <div class="text-right">
                <a class="btn btn-primary add-sub-row">Add Sub Row</a>
            </div>
        </div>
    </div>
    <div class="col-md-2 d-flex align-items-center justify-content-end">

    </div>
</div>

@if( $data )
    got here

    @foreach($data as $index => $subtype)

        <div class="row subtype-row mb-2 p-3 border border-secondary rounded mx-0" type="subtype" data-row="{{ $index }}">
            <div class="col-md-12 d-inline-flex align-items-center form-group mb-0">
                {!! Form::select('subtypes[{{ $index }}][parent][0]', $subtypes, $subtype->parent[0] ?? null, ['class' => 'form-control', 'id' => 'subtype']) !!}
                <span class="mx-4">x</span>
                {!! Form::select('subtypes[{{ $index }}][parent][1]', $subtypes, $subtype->parent[1] ?? null, ['class' => 'form-control', 'id' => 'subtype']) !!}
                <a class="btn btn-danger remove-row ml-3">Remove Group</a>
            </div>
            <hr class="w-100 my-3" />
            <h5>Results In...</h5>
            <div class="col-md-12">
                <div class="subtypeRepeater subRepeater" type="subtypeSub">
                    <div class="repeaterBody subgroup">
                        @foreach($subtype->results ?? [] as $subIndex => $result)
                            <div class="row mb-2" type="subtypeSub" data="row-start">
                                <div class="col-md-4 form-group mb-0">
                                    {!! Form::select('subtypes[{{ $index }}][results][{{ $subIndex }}][subtype]', $subtypes, $result->subtype ?? null, ['class' => 'form-control', 'id' => 'subtype_result']) !!}
                                </div>
                                <div class="col-md-4 form-group mb-0">
                                    {!! Form::number('subtypes[{{ $index }}][results][{{ $subIndex }}][rate]', $result->rate ?? null, ['class' => 'form-control', 'id' => 'subtype_result_rate', 'placeholder' => 'Rate (%)', 'min' => 0, 'max' => 100]) !!}
                                </div>
                                <div class="col-md-4 d-flex align-items-center justify-content-end">
                                    <a class="btn btn-danger remove-row">-</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-right">
                        <a class="btn btn-primary add-sub-row">Add Sub Row</a>
                    </div>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-center justify-content-end">

            </div>
        </div>
    @endforeach
@endif