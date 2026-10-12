<div class="row trait-row mb-2 p-3 border border-secondary rounded mx-0{{ $data ? ' delete' : '' }}" type="trait" data-row="0" data="row-start">
    <div class="col-md-12">
        <strong>Applies to </strong> {!! Form::select('traits[__INDEX__][feature_categories][]', $featureCategories, null, ['class' => 'form-control selectize', 'id' => 'feature_category_id', 'multiple', 'placeholder' => 'Select Trait Categories to Apply Rates to']) !!}
    </div>
    <div class="col-md-12 d-inline-flex align-items-center form-group mb-0">
        {!! Form::select('traits[__INDEX__][rarity][0]', $rarities, null, ['class' => 'form-control', 'id' => 'rarity']) !!}
        <span class="mx-4">x</span>
        {!! Form::select('traits[__INDEX__][rarity][1]', $rarities, null, ['class' => 'form-control', 'id' => 'rarity']) !!}
        <a class="btn btn-danger remove-row ml-3">Remove Group</a>
    </div>
    <hr class="w-100 my-3" />
    <h5>Results In...</h5>
    <div class="col-md-12">
        <div class="traitrarityRepeater subRepeater" type="traitraritySub">
            <div class="repeaterBody subgroup">
                <div class="row mb-2" type="traitraritySub" data="row-start">
                    <div class="col-md-4 form-group mb-0">
                        {!! Form::select('traits[__INDEX__][rarity_results][__SUB_INDEX__][rarity]', $rarities, null, ['class' => 'form-control', 'id' => 'trait_rarity_result']) !!}
                    </div>
                    <div class="col-md-4 form-group mb-0">
                        {!! Form::number('traits[__INDEX__][rarity_results][__SUB_INDEX__][rate]', null, ['class' => 'form-control', 'id' => 'trait_rarity_result_rate', 'placeholder' => 'Rate (%)', 'min' => 0, 'max' => 100]) !!}
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

    @foreach($data as $index => $trait)
        <div class="row trait-row mb-2 p-3 border border-secondary rounded mx-0" type="trait" data-row="0">
            <div class="col-md-12">
                <strong>Applies to </strong> {!! Form::select('traits['.$index.'][feature_categories][]', $featureCategories, $trait->feature_categories ?? null, ['class' => 'form-control selectize', 'id' => 'feature_category_id', 'multiple', 'placeholder' => 'Select Trait Categories to Apply Rates to']) !!}
            </div>
            <div class="col-md-12 d-inline-flex align-items-center form-group mb-0">
                {!! Form::select('traits['.$index.'][rarity][0]', $rarities, $trait->rarity[0] ?? null, ['class' => 'form-control', 'id' => 'rarity']) !!}
                <span class="mx-4">x</span>
                {!! Form::select('traits['.$index.'][rarity][1]', $rarities, $trait->rarity[1] ?? null, ['class' => 'form-control', 'id' => 'rarity']) !!}
                <a class="btn btn-danger remove-row ml-3">Remove Group</a>
            </div>
            <hr class="w-100 my-3" />
            <h5>Results In...</h5>
            <div class="col-md-12">
                <div class="traitrarityRepeater subRepeater" type="traitraritySub">
                    <?php $results = $trait->rarity_results ?? null; ?>
                    <div class="repeaterBody subgroup">
                        @if($results)
                            @foreach($results as $subIndex => $result)
                                <div class="row mb-2" type="traitraritySub">
                                    <div class="col-md-4 form-group mb-0">
                                        {!! Form::select('traits['.$index.'][rarity_results]['.$subIndex.'][rarity]', $rarities, $result->rarity ?? null, ['class' => 'form-control', 'id' => 'trait_rarity_result']) !!}
                                    </div>
                                    <div class="col-md-4 form-group mb-0">
                                        {!! Form::number('traits['.$index.'][rarity_results]['.$subIndex.'][rate]', $result->rate ?? null, ['class' => 'form-control', 'id' => 'trait_rarity_result_rate', 'placeholder' => 'Rate (%)', 'min' => 0, 'max' => 100]) !!}
                                    </div>
                                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                                        <a class="btn btn-danger remove-row">-</a>
                                    </div>
                                </div>
                            @endforeach
                        @endif
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