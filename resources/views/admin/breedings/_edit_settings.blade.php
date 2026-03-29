@extends('admin.layout')

@section('admin-title')
    Breeding Settings
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Breeding Settings' => 'admin/breeding/settings']) !!}

    <h1>Breeding Settings</h1>
    <p>Edit the breeding rates and modifiers here.</p>
    <p>RATES HERE: <a href="https://www.deviantart.com/world-of-reos/journal/Genetics-Info-615978178" target="_blank">Genetic Info</a></p>

    {!! Form::open(['url' => 'admin/breedings/settings/save']) !!}

    <pre style="background-color:#eee;" class="">
        {{ print_r($currentSettings, true) }}
    </pre>

    <div class="card mb-3">
        <div class="card-header">
            <h4>General Settings</h4>
            <p class="mb-0">Basic settings for breeding.</p>
        </div>
        <div class="card-body">
            <div class="form-group">
                <h5>Litter Sizes</h5>
                @foreach ($species as $id => $name)
                    @if ($id == 0)
                        @continue
                    @endif
                    <?php
                    $currentConfig = isset($currentSettings['litter_config']) && property_exists($currentSettings['litter_config'], $id) ? $currentSettings['litter_config']->$id : null;
                    ?>
                    <div class="row mb-2">
                        <div class="col-md-4">
                            {{ $name }}
                        </div>
                        <div class="col-md-4">
                            {!! Form::number('litter_size_min_' . $id, $currentConfig->min ?? null, ['class' => 'form-control', 'placeholder' => 'Enter minimum litter size for this species', 'min' => 0, 'max' => 10]) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::number('litter_size_max_' . $id, $currentConfig->max ?? null, ['class' => 'form-control', 'placeholder' => 'Enter maximum litter size for this species', 'min' => 0, 'max' => 10]) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

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
                                <h5 class="mb-0 text-secondary text-decoration-non">Species</h5>
                                <p class="mb-0 text-secondary text-decoration-non">Enter the rates for when combining each species. Note that if the species are the same you'll want to enter 100.</p>
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
                                    @if ($currentSettings['species_rates'])
                                        @foreach ($currentSettings['species_rates'] as $row)
                                            <div class="row species-row mb-2" type="species" data="row-start">
                                                <div class="col-md-3 form-group mb-0">
                                                    {!! Form::select('species_id_0[]', $species, $row->species_id_0, ['class' => 'form-control', 'id' => 'species']) !!}
                                                </div>
                                                <div class="col-md-2 form-group mb-0">
                                                    {!! Form::number('species_id_0_rate[]', $row->species_id_0_rate, ['class' => 'form-control', 'id' => 'species', 'min' => 0, 'max' => 100]) !!}
                                                </div>
                                                <div class="col-md-3 form-group mb-0">
                                                    {!! Form::select('species_id_1[]', $species, $row->species_id_1, ['class' => 'form-control', 'id' => 'species']) !!}
                                                </div>
                                                <div class="col-md-2 form-group mb-0">
                                                    {!! Form::number('species_id_1_rate[]', $row->species_id_1_rate, ['class' => 'form-control', 'id' => 'species', 'min' => 0, 'max' => 100]) !!}
                                                </div>
                                                <div class="col-md-2 d-flex align-items-center justify-content-end">
                                                    <a class="btn btn-danger remove-row">-</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="row species-row mb-2" type="species" data="row-start">
                                            <div class="col-md-3 form-group mb-0">
                                                {!! Form::select('species_id_0[]', $species, null, ['class' => 'form-control', 'id' => 'species']) !!}
                                            </div>
                                            <div class="col-md-2 form-group mb-0">
                                                {!! Form::number('species_id_0_rate[]', null, ['class' => 'form-control', 'id' => 'species', 'min' => 0, 'max' => 100]) !!}
                                            </div>
                                            <div class="col-md-3 form-group mb-0">
                                                {!! Form::select('species_id_1[]', $species, null, ['class' => 'form-control', 'id' => 'species']) !!}
                                            </div>
                                            <div class="col-md-2 form-group mb-0">
                                                {!! Form::number('species_id_1_rate[]', null, ['class' => 'form-control', 'id' => 'species', 'min' => 0, 'max' => 100]) !!}
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center justify-content-end">
                                                <a class="btn btn-danger remove-row">-</a>
                                            </div>
                                        </div>
                                    @endif
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
                            <button class="btn btn-block btn-link text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSubtype" aria-expanded="false" aria-controls="collapseSubtype">
                                <h5 class="mb-0 text-secondary text-decoration-none">Subtypes</h5>
                                <p class="mb-0 text-secondary text-decoration-none">Enter the rates for when combining each subtype. Note that if the subtypes are the same you'll want to enter 100.</p>
                            </button>
                        </h2>
                    </div>
                    <div id="collapseSubtype" class="collapse" aria-labelledby="headingSubtype" data-parent="#breedingRatesAccordion">
                        <div class="card-body">

                            <div id="subtypeRepeater" type="subtype">
                                <div class="repeaterBody">

                                    @include('admin.breedings.__subtype_settings_rows', ['data' => $currentSettings['subtype_rates'] ])

                                    
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
                                <h5 class="mb-0 text-secondary text-decoration-none">Traits</h5>
                                <p class="mb-0 text-secondary text-decoration-none">Select trait categories to apply the rates to, and what rarities to match for the percentages. These should be for normal/passable traits only.</p>
                            </button>
                        </h2>
                    </div>
                    <div id="collapseTraits" class="collapse" aria-labelledby="headingTraits" data-parent="#breedingRatesAccordion">
                        <div class="card-body px-3">

                            <div id="traitRepeater" type="trait">
                                <div class="repeaterBody">
                                    @include('admin.breedings.__trait_settings_rows', ['data' => $currentSettings['trait_rates'], 'rarities' => $rarities])
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
                                <h5 class="mb-0 text-secondary text-decoration-none">Markings</h5>
                                <p class="mb-0 text-secondary text-decoration-none">Fill out the rates for each marking rarity. Note that these are automatically found. If you would like to include multiplication use an 'x' in your equation. E.g. 50x2
                                    to roll twice.</p>
                            </button>
                        </h2>
                    </div>
                    <div id="collapseMarkings" class="collapse" aria-labelledby="headingMarkings" data-parent="#breedingRatesAccordion">
                        <div class="card-body">
                            @foreach ($markingRarities as $rarity_id => $rarity_name)
                                <h5>{{ $rarity_name }}</h5>
                                @foreach ($markingConfig as $row)
                                    <?php
                                        $id = strtolower($rarity_name) . '__' . substr(array_key_first($row), 0, 3) . 'X' . (array_values($row)[0] ? substr(array_values($row)[0], 0, 3) : 'non');
                                        $type = explode('__', $id)[1];
                                        $currentConfig = isset($currentSettings['marking_rates']) && $currentSettings['marking_rates'] && property_exists($currentSettings['marking_rates'], $rarity_name) ? $currentSettings['marking_rates']->$rarity_name : null;
                                        $current = $currentConfig->$type ?? null;
                                    ?>
                                    <div class="row mb-2">
                                        <div class="col-md-4">
                                            {{ ucwords(array_key_first($row)) }} <i style="font-size:10px;" class="fas fa-times"></i> {{ ucwords(array_values($row)[0] ?? 'Non-Marked') }}
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::text('marking_rate_' . $id, $current->rate ?? null, ['class' => 'form-control', 'placeholder' => 'Enter rate to roll (%)']) !!}
                                        </div>
                                        <div class="col-md-4">
                                            {!! Form::text('marking_rate_dom_' . $id, $current->roll_dom ?? null, ['class' => 'form-control', 'placeholder' => 'Enter rate to roll DOMINANT (%)']) !!}
                                        </div>
                                    </div>
                                @endforeach
                                <hr />
                            @endforeach

                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingMutations">
                        <h2 class="mb-0">
                            <button class="btn btn-block btn-link text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseMutations" aria-expanded="false" aria-controls="collapseMutations">
                                <h5 class="mb-0 text-secondary text-decoration-none">Mutations</h5>
                                <p class="mb-0 text-secondary text-decoration-none">Traits are are <em>mutated</em> instead of rolled when combining rarities.</p>
                            </button>
                        </h2>
                    </div>
                    <div id="collapseMutations" class="collapse" aria-labelledby="headingMutations" data-parent="#breedingRatesAccordion">
                        <div class="card-body">

                            <div id="MutationRepeater" type="mutation">
                                <div class="row">
                                    <div class="col-md-4">Category</div>
                                    <div class="col-md-4">Rarity</div>
                                    <div class="col-md-2">Drop Rate (%)</div>
                                </div>
                                <div class="repeaterBody">
                                    @if ($currentSettings['mutation_rates'])
                                        @foreach ($currentSettings['mutation_rates'] as $row)
                                            <div class="row mutation-row mb-2" type="mutation" data="row-start">
                                                <div class="col-md-4 form-group mb-0">
                                                    {!! Form::select('mutation_category[]', $featureCategories, $row->category, ['class' => 'form-control', 'id' => 'mutation']) !!}
                                                </div>
                                                <div class="col-md-4 form-group mb-0">
                                                    {!! Form::select('mutation_rarity[]', $rarities, $row->rarity, ['class' => 'form-control', 'id' => 'mutation']) !!}
                                                </div>
                                                <div class="col-md-2 form-group mb-0">
                                                    {!! Form::number('mutation_rate[]', $row->rate, ['class' => 'form-control', 'id' => 'mutation', 'min' => 0, 'step' => 'any', 'max' => 100]) !!}
                                                </div>
                                                <div class="col-md-2 d-flex align-items-center justify-content-end">
                                                    <a class="btn btn-danger remove-row">-</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="row mutation-row mb-2" type="mutation" data="row-start">
                                            <div class="col-md-4 form-group mb-0">
                                                {!! Form::select('mutation_category[]', $featureCategories, null, ['class' => 'form-control', 'id' => 'mutation']) !!}
                                            </div>
                                            <div class="col-md-4 form-group mb-0">
                                                {!! Form::select('mutation_rarity[]', $rarities, null, ['class' => 'form-control', 'id' => 'mutation']) !!}
                                            </div>
                                            <div class="col-md-2 form-group mb-0">
                                                {!! Form::number('mutation_rate[]', null, ['class' => 'form-control', 'id' => 'mutation', 'min' => 0, 'step' => 'any', 'max' => 100]) !!}
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center justify-content-end">
                                                <a class="btn btn-danger remove-row">-</a>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <a class="btn btn-primary add-row">Add Row</a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header" id="headingSkills">
                        <h2 class="mb-0">
                            <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-target="#collapseSkills" aria-expanded="false" aria-controls="collapseSkills">
                                <h5 class="mb-0 text-secondary text-decoration-none">Skills</h5>
                                <p class="mb-0 text-secondary text-decoration-none">Fil out the drop rate percentage for each skill rarity.</p>
                            </button>
                        </h2>
                    </div>
                    <div id="collapseSkills" class="collapse" aria-labelledby="headingSkills" data-parent="#breedingRatesAccordion">
                        <div class="card-body">
                            @if ($skillRarities)
                                @foreach ($skillRarities as $id => $name)
                                    <div class="d-flex form-group mb-2">
                                        <div class="mr-2" style="min-width:200px;">{{ $name }} Skills (Drop rate (%))</div>
                                        {!! Form::number('skill_rate__' . $id, null, ['class' => 'form-control', 'id' => 'skill', 'min' => 0, 'step' => 'any', 'max' => 100]) !!}
                                    </div>
                                @endforeach
                            @endif
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

                <div id="modifierRepeater" type="modifier">
                    <div class="row">
                        <div class="col-md-4">Type of Modifier</div>
                        <div class="col-md-4">Item</div>
                        <div class="col-md-2">Number</div>
                    </div>
                    <div class="repeaterBody">
                        <div class="row modifier-row mb-2" type="modifier" data="row-start">
                            <div class="col-md-4 form-group mb-0">
                                {!! Form::select('mod_type[]', $modifier_types, null, ['class' => 'form-control', 'id' => 'mod']) !!}
                            </div>
                            <div class="col-md-4 form-group mb-0">
                                {!! Form::select('mod_item[]', $items, null, ['class' => 'form-control', 'id' => 'mod']) !!}
                            </div>
                            <div class="col-md-2 form-group mb-0">
                                {!! Form::number('mod_rate[]', null, ['class' => 'form-control', 'id' => 'mod', 'min' => 0, 'max' => 100]) !!}
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

    <div class="card mb-3">
        <div class="card-header">
            <h4>Inbreeding</h4>
            <p class="mb-0">What traits can pass due to inbreeding.</p>
        </div>
        <div class="card-body">
            <div class="form-group">
                <p>Note that these are automatically pulled from the set Inbreeding trait category. If you would like to change this, you can do so in the site settings. Stillborn does NOT need to be added, as it is automatic.</p>

                @foreach ($inbreeding_traits as $id => $name)
                    <div class="d-flex form-group mb-2">
                        <div class="mr-2" style="min-width:200px;">{{ $name }} (Drop rate (%))</div>
                        {!! Form::number('inbreeding_trait__' . $id, null, ['class' => 'form-control', 'id' => 'skill', 'min' => 0, 'step' => 'any', 'max' => 100]) !!}
                    </div>
                @endforeach
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
            $counts = {};

            $('[data="row-start"]').each(function() {
                var $original = $(this);
                var key = $(this).attr('type');
                var $row = $(this).clone();
                $row.find('input, select').val('');
                $row.removeAttr('data');
                $row_templates[key] = $row;

                $original.removeAttr('data');

                //After cloning, update the original field name attributes to have 0 instead of __INDEX__
                $original.find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        if (name.includes('__INDEX__')) {
                            var new_name = name.replace('__INDEX__', '0');
                            $(this).attr('name', new_name);
                            $(this).attr('id', new_name);
                        } else if (name.includes('__SUB_INDEX__')) {
                            console.log('got here');
                            var new_name = name.replace('__SUB_INDEX__', '0');
                            $(this).attr('name', new_name);
                            $(this).attr('id', new_name);
                        }
                    }
                });
            });

            $('.delete').remove();

            $('.selectize').selectize({
                multiple: true,
            });

            $('[id$="Repeater"]:has(.subRepeater)').each(function() {
                var key = $(this).attr('type');
                $counts[key] = $(this).find('.subRepeater .row').length;
            });

            $('.add-row').click(function(e) {
                e.preventDefault();
                var parent = $(this).parents('[id$="Repeater"]');
                var newRow = $row_templates[parent.attr('type')].clone();
                var count = parent.find('> .repeaterBody > .row').length;
                newRow.attr('data-row', count);

                newRow.find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        var new_name = name.replace('__INDEX__', count).replace().replace('__SUB_INDEX__', 0);
                        $(this).attr('name', new_name);
                        $(this).attr('id', new_name);
                    }
                });

                parent.find('.repeaterBody:not(.subgroup)').first().append(newRow);
                newRow.find('.selectize:not(.selectized)').selectize({
                    multiple: true,
                });
                $counts[parent.attr('type')] = parent.find('.subRepeater .row').length;
            });

            $('body').on('click', '.add-sub-row', function(e) {
                e.preventDefault();
                var parent = $(this).parents('.subRepeater').first();
                var newRow = $row_templates[parent.attr('type')].clone();
                var count = parent.find('> .repeaterBody > .row').length;
                var parentIndex = parent.closest('[data-row]').attr('data-row');

                // Update the name attributes in the new sub-row
                newRow.find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        // Replace the first 0 with the parent index for both subtypes and traits
                        var new_name = name.replace(/(subtypes|traits)\[0\]/, '$1[' + parentIndex + ']').replace('__SUB_INDEX__', count);
                        $(this).attr('name', new_name);
                        $(this).attr('id', new_name);
                    }
                });

                // Append the new sub-row to the parent
                parent.find('.repeaterBody').first().append(newRow);

                // Optional: Rebalance percentages or perform additional logic
                rebalanceSubRowPercents(parent);
            });

            $('body').on('click', '.remove-row', function(e) {
                e.preventDefault();
                $(this).parents('.row').first().remove();
            });

            function rebalanceSubRowPercents($parent) {

                $fields = $parent.find('.row input[name*="result_rate"]');
                $percentages = {};
                $count = 0;

                $fields.each(function(i, element) {
                    var val = parseFloat($(this).val());
                    if (!isNaN(val)) {
                        $percentages[i] = val;
                    } else {
                        $percentages[i] = 0;
                    }
                    $count++;
                });
                

                console.log($percentages);

                if ($percentages[0] == 100) {
                    console.log('Already 100%, skipping rebalance');
                }

                for (const [key, value] of Object.entries($percentages)) {
                    if ($count > 0) {
                        var new_val = (value / Object.values($percentages).reduce((a, b) => a + b, 0)) * 100;
                        $fields.eq(key).val(new_val.toFixed(2));
                    } else {
                        $fields.eq(key).val(0);
                    }
                }

            }

        });
    </script>
@endsection
