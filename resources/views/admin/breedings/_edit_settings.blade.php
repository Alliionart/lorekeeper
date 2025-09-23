@extends('admin.layout')

@section('admin-title')
    Breeding Settings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Breeding Settings' => 'admin/breeding/settings']) !!}

    <h1>Breeding Settings</h1>
    <p>Edit the breeding rates and modifiers here.</p>
    <p>RATES HERE: <a href="https://www.deviantart.com/world-of-reos/journal/Genetics-Info-615978178" target="_blank">Genetic Info</a></p>

    {!! Form::open(['url' => 'admin/breeding/settings', 'files' => true]) !!}

    <div class="card mb-3">
        <div class="card-header">
            <h4>Breeding Rates</h4>
            <p class="mb-0">Update the breeding rates for each trait, marking, etc.</p>
        </div>
        <div class="card-body">
            <!-- Start Accordion -->
            <div class="accordion" id="breedingRatesAccordion">
                <div class="card">
                    <div class="card-header" id="headingSpecies">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseSpecies" aria-expanded="true" aria-controls="collapseSpecies">
                        <h5 class="mb-0">Species</h5>
                        <p class="mb-0">Enter the rates for when combining each species. Note that if the species are the same you'll want to enter 100.</p>
                        </button>
                    </h2>
                    </div>

                    <div id="collapseSpecies" class="collapse show" aria-labelledby="headingSpecies" data-parent="#breedingRatesAccordion">
                    <div class="card-body">
                        
                        <div id="SpeciesRepeater" type="species">
                            <div class="row">
                                <div class="col-md-3">Species #1</div>
                                <div class="col-md-2">Species #1 Rate (%)</div>
                                <div class="col-md-3">Species #2</div>
                                <div class="col-md-2">Species #2 Rate (%)</div>
                            </div>
                            <div class="repeaterBody">
                                <div class="row species-row mb-2" type="species" data="row-start">
                                    <div class="col-md-3 form-group mb-0">
                                        {!! Form::select('species_id_0[]', $species, null, ['class' => 'form-control', 'id' => 'species']) !!}
                                    </div>
                                    <div class="col-md-2 form-group mb-0">
                                        {!! Form::number('species_id_0_rate[]', null, ['class' => 'form-control', 'id' => 'species']) !!}
                                    </div>
                                    <div class="col-md-3 form-group mb-0">
                                        {!! Form::select('species_id_1[]', $species, null, ['class' => 'form-control', 'id' => 'species']) !!}
                                    </div>
                                    <div class="col-md-2 form-group mb-0">
                                        {!! Form::number('species_id_1_rate[]', null, ['class' => 'form-control', 'id' => 'species']) !!}
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-end">
                                        <a class="btn btn-danger remove-row">-</a>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <a class="btn btn-primary add-row">Add Row</a>
                            </div>
                        </div>

                    </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingSubtype">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSubtype" aria-expanded="false" aria-controls="collapseSubtype">
                        <h5 class="mb-0">Subtypes</h5>
                        <p class="mb-0">Enter the rates for when combining each subtype. Note that if the subtypes are the same you'll want to enter 100.</p>
                        </button>
                    </h2>
                    </div>
                    <div id="collapseSubtype" class="collapse" aria-labelledby="headingSubtype" data-parent="#breedingRatesAccordion">
                    <div class="card-body">
                        
                        <div id="SubtypeRepeater" type="subtype">
                            <div class="row">
                                <div class="col-md-3">Subtype #1</div>
                                <div class="col-md-2">Subtype #1 Rate (%)</div>
                                <div class="col-md-3">Subtype #2</div>
                                <div class="col-md-2">Subtype #2 Rate (%)</div>
                            </div>
                            <div class="repeaterBody">
                                <div class="row subtype-row mb-2" type="subtype" data="row-start">
                                    <div class="col-md-3 form-group mb-0">
                                        {!! Form::select('subtype_id_0[]', $subtypes, null, ['class' => 'form-control', 'id' => 'subtype']) !!}
                                    </div>
                                    <div class="col-md-2 form-group mb-0">
                                        {!! Form::number('subtype_id_0_rate[]', null, ['class' => 'form-control', 'id' => 'subtype']) !!}
                                    </div>
                                    <div class="col-md-3 form-group mb-0">
                                        {!! Form::select('subtype_id_1[]', $subtypes, null, ['class' => 'form-control', 'id' => 'subtype']) !!}
                                    </div>
                                    <div class="col-md-2 form-group mb-0">
                                        {!! Form::number('subtype_id_1_rate[]', null, ['class' => 'form-control', 'id' => 'subtype']) !!}
                                    </div>
                                    <div class="col-md-2 d-flex align-items-center justify-content-end">
                                        <a class="btn btn-danger remove-row">-</a>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <a class="btn btn-primary add-row">Add Row</a>
                            </div>
                        </div>

                    </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingTraits">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseTraits" aria-expanded="false" aria-controls="collapseTraits">
                        <h5 class="mb-0">Traits</h5>
                        </button>
                    </h2>
                    </div>
                    <div id="collapseTraits" class="collapse" aria-labelledby="headingTraits" data-parent="#breedingRatesAccordion">
                    <div class="card-body px-3">
                        
                        <div id="traitRepeater" type="trait">
                            <div class="repeaterBody">
                                <div class="row trait-row mb-2 p-3 border border-secondary rounded mx-0" type="trait" data="row-start">
                                    <div class="col-md-12">
                                        <strong>Applies to </strong> {!! Form::select('feature_category_id[]', $featureCategories, null, ['class' => 'form-control selectize', 'id' => 'feature_category_id', 'multiple', 'placeholder' => 'Select Trait Categories to Apply Rates to']) !!}
                                    </div>
                                    <div class="col-md-12 d-inline-flex align-items-center form-group mb-0">
                                        {!! Form::select('trait_rarity_0[]', $rarities, null, ['class' => 'form-control', 'id' => 'rarity']) !!}
                                        <span class="mx-4">x</span>
                                        {!! Form::select('trait_rarity_1[]', $rarities, null, ['class' => 'form-control', 'id' => 'rarity']) !!}
                                        <a class="btn btn-danger remove-row ml-3">Remove Group</a>
                                    </div>
                                    <hr class="w-100 my-3" />
                                    <h5>Results</h5>
                                    <div class="col-md-12">
                                        <div class="traitrarityRepeater" sub type="traitrarity">
                                            <div class="repeaterBody subgroup">
                                                <div class="row mb-2" type="traitrarity" data="row-start">
                                                    <div class="col-md-4 form-group mb-0">
                                                        {!! Form::select('trait_rarity_result[]', $rarities, null, ['class' => 'form-control', 'id' => 'trait_rarity_result']) !!}
                                                    </div>
                                                    <div class="col-md-4 form-group mb-0">
                                                        {!! Form::number('trait_rarity_result_rate[]', null, ['class' => 'form-control', 'id' => 'trait_rarity_result_rate', 'placeholder' => 'Rate (%)']) !!}
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
                            </div>
                            <div class="text-right">
                                <a class="btn btn-primary add-row">Add Row</a>
                            </div>
                        </div>
                    
                    </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingMarkings">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseMarkings" aria-expanded="false" aria-controls="collapseMarkings">
                        <h5 class="mb-0">Markings</h5>
                        </button>
                    </h2>
                    </div>
                    <div id="collapseMarkings" class="collapse" aria-labelledby="headingMarkings" data-parent="#breedingRatesAccordion">
                    <div class="card-body">
                        And lastly, the placeholder content for the third and final accordion panel. This panel is hidden by default.
                    </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingSkills">
                    <h2 class="mb-0">
                        <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSkills" aria-expanded="false" aria-controls="collapseSkills">
                        <h5 class="mb-0">Skills</h5>
                        </button>
                    </h2>
                    </div>
                    <div id="collapseSkills" class="collapse" aria-labelledby="headingSkills" data-parent="#breedingRatesAccordion">
                    <div class="card-body">
                        And lastly, the placeholder content for the third and final accordion panel. This panel is hidden by default.
                    </div>
                    </div>
                </div>
            </div>
            <!-- End Accordion -->
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h4>Breeding Modifiers</h4>
            <p class="mb-0">Items and other factors that affect breeding outcomes.</p>
        </div>
        <div class="card-body">
            <div class="form-group">

            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit('Save Settings', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {

            $row_templates = {};

            $('.selectize').selectize({
                multiple: true,
            });

            $('[data="row-start"]').each(function() {
                var key = $(this).attr('type');
                $row_templates[key] = $(this).clone();
            });

            console.log($row_templates);
            
            $('.add-row').click(function(e) {
                e.preventDefault();
                var parent = $(this).parents('[id$="Repeater"]');
                var newRow = $row_templates[parent.attr('type')].clone();
                parent.find('.repeaterBody').append(newRow);
            });

            $('.add-sub-row').click(function(e) {
                e.preventDefault();
                var parent = $(this).parents('[id$="Repeater"]').first();
                var newRow = $row_templates[parent.attr('type')].clone();
                parent.find('.repeaterBody').append(newRow);
            });

            $('body').on('click', '.remove-row', function(e) {
                e.preventDefault();
                $(this).parents('.row').first().remove();
            });

        });
    </script>
@endsection
