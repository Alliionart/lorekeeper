@extends('layouts.app')


@section('title')
    Submit a Breeding
@endsection


@section('content')
    {!! breadcrumbs(['Breeding' => 'breeding']) !!}
    <h1>
        @if (isset($breeding) && $breeding)
            Edit Breeding #{{ $breeding->id }}
        @else
            <?php $breeding = null; ?>
            Submit a Breeding
        @endif
    </h1>

    @switch($queue_status)
        @case(1)
            <div class="alert alert-warning">
                Breeding is currently only open to staff. This may be for testing or other reasons.
            </div>
        @break

        @case(0)
            <div class="alert alert-danger">
                Breeding is currently <strong>Closed</strong>, this form is only available for preview. Check back later.
            </div>
        @break
    @endswitch

    <?php
    $fullRemaining = $maxSlots['full'] - $counts['full'] < 0 ? 0 : $maxSlots['full'] - $counts['full'];
    $splitRemaining = $maxSlots['split'] - $counts['split'] < 0 ? 0 : $maxSlots['split'] - $counts['split'];
    ?>

    <div class="site-page-content parsed-text">
        <p>To submit your breeding select both characters, and any items you'd like to use. Make sure you review before you submit!</p>
        {!! Form::open(['url' => $breeding ? 'breeding/' . $breeding->id . '/edit' : '/breeding/submit']) !!}

        <div class="alert warning alert-warning p-2 border border-warning hide">
            <strong>Warning:</strong> You cannot select the same slot for both parents. Please choose different slots.
        </div>
        <div class="alert inbreeding alert-danger p-2 border border-danger hide">
            <strong>Inbreeding:</strong> There is inbreeding through <span></span>, ensure you are ok with breeding this pair.
        </div>

        <?php
        if ($breeding) {
            $characterData = $breeding->characterData;
            $breedingData = $breeding->breedingData;
        } else {
            $characterData = null;
            $breedingData = null;
        }
        ?>

        <div class="row">
            @for ($i = 1; $i <= 2; $i++)
                <?php $key = 'parents[' . $i . ']'; ?>
                <div class="col-md-6">
                    <div class="card mb-3" id="parent_{{ $i }}">
                        <div class="card-header">
                            <h4>Parent #{{ $i }}</h4>
                        </div>
                        <div class="card-body">
                            <?php
                            $pData = $characterData ? $characterData->{$i} : null;
                            $parent = $breeding && $breeding->{'parent_' . $i} ? $breeding->get_parent($breeding->{'parent_' . $i}, $i) : null;
                            ?>
                            {!! Form::label('I would like to use:') !!}
                            {!! Form::select(
                                $key . '[type]',
                                [
                                    '' => 'Select an option',
                                    'own' => 'One of my own characters',
                                    'permission' => 'A character I have a breeding permission for',
                                    'starter' => 'A group starter from a token',
                                ],
                                $breeding && $pData ? $pData->type : null,
                                ['class' => 'form-control perm_select'],
                            ) !!}

                            <div class="my-2 {{ $pData && $pData->type == 'permission' ? '' : 'hide' }}" slot_type="permission">
                                {!! Form::label('Select Permission') !!} {!! add_help('Select a slot from your breeding permissions.') !!}
                                {!! Form::select($key . '[permission]', $permissions, $parent ? $parent->permission : null, ['class' => 'form-control characterSelect selectize', 'id' => 'permission_' . $i, 'slot_id' => $i]) !!}
                            </div>
                            <div class="my-2 {{ $pData && $pData->type == 'own' ? '' : 'hide' }}" slot_type="own">
                                {!! Form::label('Select Character') !!} {!! add_help('Select a slot from your breeding permissions.') !!}
                                {!! Form::select($key . '[own]', $own_characters, $parent ? $parent->id : null, ['class' => 'form-control ownSelect selectize', 'id' => 'own_' . $i, 'slot_id' => $i]) !!}
                            </div>
                            <div class="my-2 {{ $pData && $pData->type == 'starter' ? '' : 'hide' }}" slot_type="starter">
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::label('Select Token') !!} {!! add_help('Select a token that contains group starters.') !!}
                                        {!! Form::select($key . '[starter][token]', $tokens, null, ['class' => 'form-control tokenSelect', 'id' => 'starter_token_' . $i, 'slot_id' => $i]) !!}
                                    </div>
                                    <div class="col-md-6">
                                        {!! Form::label('Select Starter') !!} {!! add_help('Select a starter from the selected token.') !!}
                                        {!! Form::select($key . '[starter][character]', [], null, ['class' => 'form-control starterSelect selectize', 'id' => 'starter_' . $i, 'slot_id' => $i]) !!}
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="parent_{{ $i }}_id" name="{{ $key }}[character_id]" value="{{ $breeding ? $parent->id : null }}" />


                            <div id="character_{{ $i }}_display" class="mt-3 rounded p-3 border border-secondary {{ $breeding ? '' : 'hide' }}">
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="{{ $breeding ? $parent->image : null }}" alt="" class="img-fluid" />
                                    </div>
                                    <div class="col-md-9 d-flex flex-column justify-content-center">
                                        <h4>{!! $breeding ? $parent->character->displayName : 'Character Name' !!}</h4>
                                        <span class="position-absolute badge badge-secondary hide permission-quantity" style="font-size: 14px;top: 0;right: 10px;"></span>
                                        <ul class="p-0 list-unstyled">
                                            <li><strong>Sex: </strong><span class="sex">{{ $breeding ? $parent->character->sex : 'Unknown' }}</span></li>
                                            <li><strong>Species: </strong><span class="species">{!! $breeding ? $parent->species : 'Unknown' !!}</span></li>
                                            <li><strong>Subtype: </strong><span class="subtype">{!! $breeding ? ($parent->subtype ?: 'Unknown') : 'Unknown' !!}</span></li>
                                            <li><strong>Markings: </strong><span class="markings">{!! $breeding ? $parent->genotype : 'Unknown' !!}</span></li>
                                            <li><strong>Traits: </strong>
                                                @if ($breeding && $parent && $parent->traits)
                                                    <ul class="traits">
                                                        @foreach ($parent->traits as $traitKey => $traitValue)
                                                            <li><strong>{!! $traitKey !!}: </strong>{!! $traitValue !!}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <ul class="traits"></ul>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        <hr />
        <div class="alert alert-info">
            You currently have {{ $fullRemaining }} full slots and {{ $splitRemaining }} split slots remaining for this month.
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('Breeding Type') !!} {!! add_help('Select a starter from the selected token.') !!}
                    {!! Form::select(
                        'type',
                        [
                            '' => 'Select type of breeding...',
                            'Full' => 'Full',
                            'Split' => 'Split',
                        ],
                        $breeding ? $breeding->type : null,
                        ['class' => 'form-control', 'id' => 'type', 'required' => 'required'],
                    ) !!}
                </div>
            </div>
            <div class="col-md-6" id="splitUserField" style="{{ $breeding && $breeding->type == 'Split' ? '' : 'display:none;' }}">
                <div class="form-group">
                    {!! Form::label('Split User') !!} {!! add_help('Select a user to split this breeding with.') !!}
                    {!! Form::select('split_user', $users, $breeding ? $breeding->split_user_id : null, ['class' => 'form-control selectize', 'id' => 'splitUser']) !!}
                </div>
            </div>
        </div>
        <div class="alert alert-danger" id="slotAlert" style="display:none;"></div>

        <hr />

        @if (isset($breeding_items) && isset($item_ids) && $item_ids)
            <div class="card">
                <div class="card-header">
                    <h4>Breeding Modifiers</h4>
                </div>
                <?php
                $item_ids = isset($mod_items->items) ? array_keys((array) $mod_items->items) : null;
                $current_items = $breeding && $breedingData && isset($breedingData->user->user_items) ? $breedingData->user->user_items : null;
                ?>
                <div class="card-body modifiers">
                    <p>Select any modifiers for your breeding. Some may require item usage.</p>
                    @if ($breeding_items->count() > 0)
                        <div class="row">
                            @foreach ($breeding_items as $item)
                                @if (in_array($item->id, $item_ids))
                                    <?php
                                    $item_count = isset($user_items[$item->id]) ? $user_items[$item->id] : 0;
                                    $type = isset($mod_items->items->{$item->id}) ? $mod_items->items->{$item->id}->type : null;
                                    $existing_stack_id = null;
                                    $existing_value = null;
                                    if ($current_items) {
                                        foreach ($current_items as $stack_id => $current_item) {
                                            $current_item_id = is_array($current_item) ? $current_item['item_id'] ?? null : $current_item->item_id ?? null;
                                            $current_item_value = is_array($current_item) ? $current_item['value'] ?? null : $current_item->value ?? null;
                                    
                                            if ((int) ($current_item_id ?? 0) === (int) $item->id) {
                                                $existing_stack_id = $stack_id;
                                                $existing_value = $current_item_value;
                                                break;
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="col-md-4">
                                        <div class="card item-card mb-2{{ $item_count == 0 ? ' text-muted border border-danger' : '' }}" data-item="{{ $item->id }}" id="{{ $type }}">
                                            <label class="card-header d-flex align-items-center justify-content-between h5" for="{{ 'breeding_items[' . $item->id . ']' }}">
                                                <span>{{ Form::checkbox('breeding_items[' . $item->id . '][item_id]', $item->id, (bool) $existing_stack_id, ['class' => 'mr-1', 'disabled' => $item_count == 0 && !$existing_stack_id, 'id' => 'breeding_items[' . $item->id . ']']) }}
                                                    {{ $item->name }}</span>
                                                <span class="badge badge-{{ $item_count > 0 ? 'primary' : 'secondary' }} ml-2">x{{ $item_count }} Owned</span>
                                            </label>
                                            <div class="card-body">
                                                {!! substr(strip_tags($item->description), 0, 200) !!}
                                                @if ($existing_stack_id)
                                                    {!! Form::hidden('breeding_items[' . $item->id . '][stack_id]', $existing_stack_id, ['class' => 'stack-id']) !!}
                                                @endif
                                                @if ($existing_stack_id && in_array($type, ['subtype_override', 'base_override'], true))
                                                    <div class="form-group mt-2">
                                                        @switch($type)
                                                            @case('subtype_override')
                                                                {!! Form::label('Subtype') !!} {!! add_help('Select your desired subtype based off the parents.') !!}
                                                                {!! Form::select('breeding_items[' . $item->id . '][value]', $subtypes, $existing_value, ['class' => 'form-control']) !!}
                                                            @break

                                                            @case('base_override')
                                                                {!! Form::label('Base Color') !!} {!! add_help('Select your desired base from the parents.') !!}
                                                                {!! Form::select('breeding_items[' . $item->id . '][value]', $bases, $existing_value, ['class' => 'form-control']) !!}
                                                            @break
                                                        @endswitch
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if ($fullRemaining > 0 || $splitRemaining > 0)
            @if ($queue_status == 2 || ($queue_status == 1 && Auth::check() && Auth::user()->isStaff))
                <div class="text-right">
                    {!! Form::submit('Submit Breeding', ['class' => 'btn btn-primary mt-4']) !!}
                </div>
            @else
                <div class="alert alert-danger mt-3">
                    Breeding is currently closed.
                </div>
            @endif
        @else
            @if ($breeding && $breeding->status == 'Draft')
                <div class="text-right">
                    {!! Form::submit('Submit Breeding', ['class' => 'btn btn-primary mt-4']) !!}
                </div>
            @else
                <div class="alert alert-warning">You may no longer submit breedings this month.</div>
            @endif
        @endif

        {!! Form::close() !!}

        <div class="modifier_templates hide">
            <div class="form-group mt-2" id="subtype_override">
                {!! Form::label('Subtype') !!} {!! add_help('Select your desired subtype based off the parents.') !!}
                {!! Form::select('breeding_items[__ID__][value]', [], null, ['class' => 'form-control', 'id' => 'subtype_override_field']) !!}
            </div>
            <div class="form-group mt-2" id="base_override">
                {!! Form::label('Base Color') !!} {!! add_help('Select your desired base from the parents.') !!}
                {!! Form::select('breeding_items[__ID__][value]', $bases, null, ['class' => 'form-control', 'id' => 'base_override_field']) !!}
            </div>
        </div>

    </div>
@endsection
<style>
    .modifiers .card:has(input:checked) {
        border-color: var(--blue);
    }

    .modifiers .card:has(input:disabled) {
        filter: grayscale(100%);
        opacity: 0.5;
    }

    .modifiers .card label {
        transition: 250ms all;
    }

    .modifiers .card label:hover {
        cursor: pointer;
        color: var(--blue);
    }
</style>

@section('scripts')
    @parent
    <script>
        var characters = {
            parent_1: null,
            parent_2: null,
        };
        const slotsRemaining = {
            Full: {{ $fullRemaining }},
            Split: {{ $splitRemaining }}
        };
        const modTemplate = {};
        const subtypes = @js($subtypes);
        const bases = @js($bases);
        var currentSubtypes = {
            parent_1: {
                text: null,
                value: null,
            },
            parent_2: {
                text: null,
                value: null,
            },
        };
        var parentBaseRange = {};
        const subtype_choices = @js($subtype_choices);
        var hasStarter = false;

        $(document).ready(function() {
            $('.selectize').selectize({
                multiple: false,
            });
            $('.modifier_templates .form-group').each(function() {
                modTemplate[$(this).attr('id')] = $(this).clone();
            });
            $('.modifier_templates').remove();
        });

        $('#type').change(function() {
            var remaining = slotsRemaining[$(this).val()];
            if (remaining < 1) {
                $('#slotAlert').text('You are out of slots!').show();
            } else {
                $('#slotAlert').hide();
            }

            if ($(this).val() === 'Split') {
                $('#splitUserField').show();
            } else {
                $('#splitUserField').hide().val('');
            }
        });

        $('.characterSelect').change(function() {
            getPermission(this);
        });

        $('.ownSelect').change(function() {
            getCharacter(this);
        });

        $('.tokenSelect').change(function() {
            getStarters(this);
        });

        $('.starterSelect').change(function() {
            getCharacter(this);
        });

        $('.perm_select').change(function() {
            var val = $(this).val();
            var parentDiv = $(this).closest('[id^="parent_"]');
            $(parentDiv).find('[slot_type]').each(function() {
                if ($(this).attr('slot_type') == val) {
                    $(this).removeClass('hide');
                } else {
                    $(this).addClass('hide');
                }
            });
        });

        $('.modifiers input[name^="breeding_items"]').on('change', function() {
            var type = $(this).parents('.item-card').attr('id');
            var item_id = $(this).parents('.item-card').data('item');

            if ($(this).is(':checked')) {
                var row = modTemplate[type].clone();
                if (row) {
                    row.html(function(index, oldHtml) {
                        return oldHtml.replaceAll('__ID__', item_id);
                    });
                    $(this).parents('.item-card').find('.card-body').append(row);
                    checkModifierForDynamicOptions($(this).parents('.item-card'));
                }
            } else {
                $(this).parents('.item-card').find('.form-group').remove();
            }

        });

        var lineage = {
            parent_1: [],
            parent_2: [],
        };
        var inbreeding = false;

        function getCharacter($selector) {
            var charId = $($selector).val();
            var slotId = $($selector).attr('slot_id');
            var displayDiv = $('#parent_' + slotId + ' #character_' + slotId + '_display');

            if (charId == 0) {
                displayDiv.addClass('hide');
                return;
            }

            $.ajax({
                type: "GET",
                url: "/breeding/get-character/?character_id=" + charId,
                dataType: "json",
            }).done(function(data) {
                fillCharacter(data, slotId);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        }

        function getPermission($selector) {
            var permId = $($selector).val();
            var slotId = $($selector).attr('slot_id');
            var displayDiv = $('#parent_' + slotId + ' #character_' + slotId + '_display');

            if (permId == 0) {
                displayDiv.addClass('hide');
                return;
            }

            $.ajax({
                type: "GET",
                url: "/breeding/permission/?permission_id=" + permId + "&slot_id=" + slotId,
                dataType: "json",
            }).done(function(data) {
                fillCharacter(data, slotId);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        }

        function getStarters($selector) {
            var tokenId = $($selector).val();
            var slotId = $($selector).attr('slot_id');
            var displayDiv = $('#parent_' + slotId + ' #character_' + slotId + '_display');

            $.ajax({
                type: "GET",
                url: "/breeding/starter/?token=" + tokenId,
                dataType: "json",
            }).done(function(data) {
                var starters = data.starters;

                if (Object.keys(starters).length > 0) {
                    var starterSelect = $('#starter_' + slotId)[0].selectize;
                    starterSelect.clearOptions();
                    $.each(starters, function(i, val) {
                        starterSelect.addOption({
                            value: i,
                            text: val
                        });
                    });
                    starterSelect.refreshOptions(false);
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.log("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        }

        function fillCharacter(data, slotId) {
            var parentKey = 'parent_' + slotId;

            if (slotId == 1) {
                characters.parent_1 = data;
                currentSubtypes.parent_1 = {
                    value: data.character.rawsubtype,
                    text: data.character.subtype.replace(/<\/?[^>]+(>|$)/g, "")
                };
                $('#parent_1_id').val(data.character.id);
            } else if (slotId == 2) {
                characters.parent_2 = data;
                currentSubtypes.parent_2 = {
                    value: data.character.rawsubtype,
                    text: data.character.subtype.replace(/<\/?[^>]+(>|$)/g, "")
                };
                $('#parent_2_id').val(data.character.id);
            }

            var displayDiv = $('#parent_' + slotId + ' #character_' + slotId + '_display');
            displayDiv.find('.traits li').remove();
            displayDiv.find('img').attr('src', data.character.image);
            displayDiv.find('h4').text(data.character.name).wrap('<a href="' + data.character.url + '"></a>');
            displayDiv.find('.species').html(data.character.species);
            displayDiv.find('.subtype').html(data.character.subtype);
            displayDiv.find('.markings').text(data.character.markings);
            displayDiv.find('.sex').text(data.character.sex);
            lineage[parentKey] = [(data.character.id).toString()];
            handleCharacterLineage(data.character.lineage, parentKey);
            $.each(data.character.traits, function(i, val) {
                displayDiv.find('.traits').append('<li><strong>' + i + ': </strong>' + val + '</li>');
            });
            if (data.permission) {
                displayDiv.find('.permission-quantity').text(data.permission.quantity + '/' + data.permission.full_quantity).removeClass('hide');
            } else {
                displayDiv.find('.permission-quantity').addClass('hide');
            }
            displayDiv.removeClass('hide');
            checkSlots();
            getParentBaseRange();
            checkOther();
        }

        function handleCharacterLineage(obj, parentKey) {
            lineage[parentKey].push(...Object.keys(obj));

            var otherParentKey = parentKey === 'parent_1' ? 'parent_2' : 'parent_1';
            var sharedLineage = lineage[parentKey].filter(key => lineage[otherParentKey].includes(key));

            if (sharedLineage.length) {
                inbreeding = true;
                $('.alert.inbreeding span').html(sharedLineage.map(key => obj[key]).join(', '));
                $('.alert.inbreeding').removeClass('hide');
            } else {
                inbreeding = false;
                $('.alert.inbreeding span').empty();
                $('.alert.inbreeding').addClass('hide');
            }
        }

        function checkSlots() {
            var slot1 = characters.parent_1 ? characters.parent_1.character.id : null;
            var slot2 = characters.parent_2 ? characters.parent_2.character.id : null;

            if (slot1 !== null && slot2 !== null && slot1 !== slot2) {
                $(':input[type="submit"]').prop('disabled', false);
                $('.alert.warning').addClass('hide');
            } else {
                $(':input[type="submit"]').prop('disabled', true);
                $('.alert.warning').removeClass('hide');
            }
        }

        function checkOther() {
            if (characters.parent_1 && characters.parent_2) {
                if (characters.parent_1.character.species !== characters.parent_2.character.species) {
                    $('#subtype_override').find('input[type="checkbox"]').prop('checked', false);
                    $('#subtype_override').find('input[type="checkbox"]').prop('disabled', true);
                    $('#subtype_override').find('select').prop('disabled', true);
                    $('#subtype_override').find('select').val('');
                } else {
                    $('#subtype_override').find('input[type="checkbox"]').prop('disabled', false);
                    $('#subtype_override').find('select').prop('disabled', false);
                    checkModifierForDynamicOptions($('#subtype_override'));
                }

                //Check if there is a starter involved
                hasStarter = characters.parent_1.character.is_starter || characters.parent_2.character.is_starter;
                $('#litter_size input').prop('disabled', hasStarter);
            }

            checkModifierForDynamicOptions($('#base_override'));
        }

        function checkModifierForDynamicOptions($element) {
            switch ($element.attr('id')) {
                case 'subtype_override':
                    var matched = subtype_choices.find(item => {
                        return item.parent[0] == currentSubtypes.parent_1.value && item.parent[1] == currentSubtypes.parent_2.value;
                    });

                    if (matched) {
                        var choices = matched.results.map(result => {
                            return result.subtype;
                        });
                        var $select = $element.find('select');
                        var $checkbox = $element.find('input[type="checkbox"]');
                        $select.empty();
                        choices.forEach(function(id) {
                            if (Object.prototype.hasOwnProperty.call(subtypes, id)) {
                                $select.append($('<option>', {
                                    value: id,
                                    text: subtypes[id]
                                }));
                            }
                        });
                        $checkbox.prop('disabled', choices.length === 0);

                    } else {
                        var $select = $element.find('select');
                        var $checkbox = $element.find('input[type="checkbox"]');
                        $select.empty();
                        $select.append($('<option>', currentSubtypes.parent_1));
                        if (currentSubtypes.parent_1.value !== currentSubtypes.parent_2.value) {
                            $select.append($('<option>', currentSubtypes.parent_2));
                        }
                    }

                    break;
                case 'base_override':
                    $('#base_override_field').empty();
                    $.each(parentBaseRange, function(key, value) {
                        $('#base_override_field').append($('<option>', {
                            value: key,
                            text: value
                        }));
                    });
                    break;
            }
        }

        function getParentBaseRange() {
            $('#base_override_field').empty();
            if (!characters.parent_1 || !characters.parent_2) return {};

            const base1 = Number(characters.parent_1.character.base);
            const base2 = Number(characters.parent_2.character.base);
            const baseCount = Object.keys(bases).length;

            if (base1 === base2) {
                return {
                    [base1]: bases[base1]
                };
            }

            const forwardDist = (base2 - base1 + baseCount) % baseCount;
            const backwardDist = (base1 - base2 + baseCount) % baseCount;

            const step = forwardDist <= backwardDist ? 1 : -1;
            const pathIds = [base1];

            let current = base1;
            while (current !== base2) {
                current = ((current - 1 + step + baseCount) % baseCount) + 1;
                pathIds.push(current);
            }

            const rangeObject = {};
            pathIds.forEach((id) => {
                if (bases[id]) {
                    rangeObject[id] = bases[id];
                }
            });

            parentBaseRange = rangeObject;

            $('#base_override_field').empty();
            $.each(parentBaseRange, function(key, value) {
                $('#base_override_field').append($('<option>', {
                    value: key,
                    text: value
                }));
            });
        }
    </script>
@endsection
