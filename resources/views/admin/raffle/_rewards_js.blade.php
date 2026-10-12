<script>
    $(document).ready(function() {
        
        const $rowTemplate = $('.loot-row.template').clone().removeClass('template');
        $('.loot-row.template').remove();
        let $activeDataInput = null;

        function getDataFieldKey($field) {
            return $field.attr('name') || $field.data('field') || $field.attr('id');
        }

        function clearDataFields() {
            $('#dataConfigContainer').find(':input').each(function() {
                const $field = $(this);
                if ($field.is(':checkbox, :radio')) {
                    $field.prop('checked', false);
                } else {
                    $field.val('');
                }
            });
        }

        function fillDataFields(data) {
            $('#dataConfigContainer').find(':input').each(function() {
                const $field = $(this);
                const key = getDataFieldKey($field);
                if (!key || !Object.prototype.hasOwnProperty.call(data, key)) {
                    return;
                }

                const value = data[key];
                if ($field.is(':checkbox')) {
                    $field.prop('checked', Array.isArray(value) ? value.includes($field.val()) : Boolean(value));
                } else if ($field.is(':radio')) {
                    $field.prop('checked', $field.val() == value);
                } else {
                    $field.val(value);
                }
            });
        }

        function readDataFields() {
            const data = {};
            $('#dataConfigContainer').find(':input').each(function() {
                const $field = $(this);
                const key = getDataFieldKey($field);
                if (!key || $field.is(':button, :submit, :reset')) {
                    return;
                }

                if ($field.is(':radio') && !$field.is(':checked')) {
                    return;
                }

                let value = $field.val();
                if ($field.is(':checkbox')) {
                    value = $field.is(':checked') ? value : false;
                }
                if (Object.prototype.hasOwnProperty.call(data, key) && Array.isArray(data[key])) {
                    data[key].push(value);
                } else if (Object.prototype.hasOwnProperty.call(data, key)) {
                    data[key] = [data[key], value];
                } else {
                    data[key] = value;
                }
            });
            return data;
        }

        function reindexTierGroups() {
            $('#tierGroups .tier-group').each(function(tierIndex) {
                const $tier = $(this);
                $tier.find('[data-field="tier"]').attr('name', `tiers[${tierIndex}][tier]`);
                $tier.find('[data-field="opt_out"]').attr('name', `tiers[${tierIndex}][opt_out]`);
                $tier.find('[data-field="data"]').attr('name', `tiers[${tierIndex}][data]`);

                $tier.find('.tier-reward-row').each(function(rewardIndex) {
                    $(this).find('[data-field]').each(function() {
                        const field = $(this).data('field');
                        $(this).attr('name', `tiers[${tierIndex}][rewards][${rewardIndex}][${field}]`);
                    });
                });
            });
        }

        reindexTierGroups();

        $('#addTier').on('click', function() {
            $('#tierGroups').append($('#tierGroupTemplate').html());
            reindexTierGroups();
        });

        $(document).on('click', '.add-tier-reward', function() {
            $(this).closest('.tier-group').find('.tier-rewards').append($('#tierRewardTemplate').html());
            reindexTierGroups();
        });

        $(document).on('click', '.remove-tier-reward', function() {
            $(this).closest('.tier-reward-row').remove();
            reindexTierGroups();
        });

        $(document).on('click', '.remove-tier-button', function() {
            $(this).closest('.tier-group').remove();
            reindexTierGroups();
        });

        $('#addLoot').on('click', function(e) {
            e.preventDefault();
            $('#lootTableBody').append($rowTemplate.clone());
        });
        
        $(document).on('click', '.remove-loot-button', function(e) {
            e.preventDefault();
            $(this).closest('.loot-row').remove();
        });

        $(document).on('click', '.data-config', function(e) {
            e.preventDefault();
            $activeDataInput = $(this).siblings('input[type="hidden"]').first();

            clearDataFields();
            if ($activeDataInput.length && $activeDataInput.val()) {
                try {
                    fillDataFields(JSON.parse($activeDataInput.val()));
                } catch (error) {
                    fillDataFields({});
                }
            }

            $('#dataModal').modal('show');
        });

        $('#dataSubmit').on('click', function(e) {
            e.preventDefault();
            if ($activeDataInput && $activeDataInput.length) {
                $activeDataInput.val(JSON.stringify(readDataFields()));
            }
            clearDataFields();
            $('#dataModal').modal('hide');
            $activeDataInput = null;
        });

        $(document).on('change', '.reward-type', function() {
            const $row = $(this).closest('.loot-row, .tier-reward-row');
            const rewardType = $(this).val();
            const $selectCell = $row.find('.loot-row-select');
            const isTierReward = $row.hasClass('tier-reward-row');

            let options = '';
            switch(rewardType) {
                case 'Item':
                    options = `{!! Form::select('rewardable_id[]', $items, null, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item', 'data-field' => 'rewardable_id']) !!}`;
                    break;
                case 'Currency':
                    options = `{!! Form::select('rewardable_id[]', $currencies, null, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency', 'data-field' => 'rewardable_id']) !!}`;
                    break;
                @if (config('lorekeeper.raffle_options.pets'))
                case 'Pet':
                    options = `{!! Form::select('rewardable_id[]', $pets, null, ['class' => 'form-control pet-select selectize', 'placeholder' => 'Select Pet', 'data-field' => 'rewardable_id']) !!}`;
                    break;
                @endif
                @if (config('lorekeeper.raffle_options.weapons'))
                case 'Weapon':
                    options = `{!! Form::select('rewardable_id[]', $weapons, null, ['class' => 'form-control weapon-select selectize', 'placeholder' => 'Select Weapon', 'data-field' => 'rewardable_id']) !!}`;
                    break;
                @endif
                @if (config('lorekeeper.raffle_options.gears'))
                case 'Gear':
                    options = `{!! Form::select('rewardable_id[]', $gears, null, ['class' => 'form-control gear-select selectize', 'placeholder' => 'Select Gear', 'data-field' => 'rewardable_id']) !!}`;
                    break;
                @endif
                @if (config('lorekeeper.raffle_options.breedingPermissions'))
                case 'BreedingPermission':
                    options = `{!! Form::select('rewardable_id[]', $characters, null, ['class' => 'form-control character-select selectize', 'placeholder' => 'Select Character', 'data-field' => 'rewardable_id']) !!}`;
                    break;
                @endif
                default:
                    options = '';
            }
            $selectCell.html(options);
            $selectCell.find('.selectize').selectize();
            if (isTierReward) {
                reindexTierGroups();
            }
        });

    });
</script>