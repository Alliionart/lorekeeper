<!--
    Needs:
    - Double repeater first by Tier then by rewards for that tier.
    - Ability for player to opt out of the specific reward tier. Need a checkbox per row that allows the player to decline that tier's reward.
    - Allow adding data that will attach upon reward creation.
    - Might need modals for each award type?
-->

<h4>Tiered Rewards</h4>
<p>Configure the rewards for each tier below. Players will have the option to opt out of specific tiers if desired. When entering a tier, you may enter a static number like <strong>1</strong> for first place, or a range like <strong>3-5</strong> and
    reward multiple positions at once.</p>
<hr>

<div class="text-right mb-3">
    <button type="button" class="btn btn-outline-info" id="addTier">Add Tier</button>
</div>

<div id="tierGroups">
    @if ($raffle->rewards()->count())
        @foreach ($raffle->rewards->groupBy('position') as $position => $tierRewards)
            <section class="tier-group border rounded p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <label class="mb-0">Tier
                        {!! Form::text('tier[]', $position, ['class' => 'form-control d-inline-block tier-number', 'style' => 'width: 180px;', 'placeholder' => '1,2,3-5', 'aria-label' => 'Tier numbers or range', 'data-field' => 'tier']) !!}
                    </label>
                    <div>
                        <label class="mb-0 mr-3">
                            <input type="checkbox" data-field="opt_out" value="1" {{ $tierRewards->contains('opt_out', true) ? 'checked' : '' }}>
                            Allow players to opt out of this tier
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-tier-button">Remove Tier</button>
                    </div>
                </div>

                <div class="text-right mb-2">
                    <button type="button" class="btn btn-sm btn-outline-info add-tier-reward">Add Reward</button>
                </div>

                <div>
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th width="30%">Reward Type</th>
                                <th width="30%">Reward</th>
                                <th width="10%">Quantity</th>
                                <th width="10%">Weight</th>
                                <th width="10%">Data</th>
                                <th width="10%"></th>
                            </tr>
                        </thead>
                        <tbody class="tier-rewards">
                            @foreach ($tierRewards as $loot)
                                <tr class="tier-reward-row">
                                    <td>{!! Form::select('rewardable_type[]', $rewardTypes, $loot->rewardable_type, ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type', 'data-field' => 'rewardable_type']) !!}</td>
                                    <td class="loot-row-select">
                                        @if ($loot->rewardable_type == 'Item')
                                            {!! Form::select('rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item', 'data-field' => 'rewardable_id']) !!}
                                        @elseif($loot->rewardable_type == 'Currency')
                                            {!! Form::select('rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency', 'data-field' => 'rewardable_id']) !!}
                                        @elseif($loot->rewardable_type == 'Pet')
                                            {!! Form::select('rewardable_id[]', $pets, $loot->rewardable_id, ['class' => 'form-control pet-select selectize', 'placeholder' => 'Select Pet', 'data-field' => 'rewardable_id']) !!}
                                        @elseif($loot->rewardable_type == 'Weapon')
                                            {!! Form::select('rewardable_id[]', $weapons, $loot->rewardable_id, ['class' => 'form-control weapon-select selectize', 'placeholder' => 'Select Weapon', 'data-field' => 'rewardable_id']) !!}
                                        @elseif($loot->rewardable_type == 'Gear')
                                            {!! Form::select('rewardable_id[]', $gears, $loot->rewardable_id, ['class' => 'form-control gear-select selectize', 'placeholder' => 'Select Gear', 'data-field' => 'rewardable_id']) !!}
                                        @elseif($loot->rewardable_type == 'BreedingPermission')
                                            {!! Form::select('rewardable_id[]', $characters, $loot->rewardable_id, ['class' => 'form-control character-select selectize', 'placeholder' => 'Select Character', 'data-field' => 'rewardable_id']) !!}
                                        @endif
                                    </td>
                                    <td>{!! Form::number('quantity[]', $loot->quantity, ['class' => 'form-control', 'min' => 1, 'data-field' => 'quantity']) !!}</td>
                                    <td>{!! Form::number('weight[]', $loot->weight, ['class' => 'form-control', 'min' => 1, 'data-field' => 'weight']) !!}</td>
                                    <td>
                                        <a href="#" class="btn data-config btn-info"><i class="fas fa-cog"></i></a>
                                        {!! Form::hidden('data[]', $loot->data ?? '', ['class' => 'form-control', 'data-field' => 'data']) !!}
                                    </td>
                                    <td class="text-right"><button type="button" class="btn btn-sm btn-danger remove-tier-reward">Remove</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endforeach
    @endif
</div>

<template id="tierGroupTemplate">
    <section class="tier-group border rounded p-3 mb-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <label class="mb-0">Tier
                {!! Form::text('tier[]', null, ['class' => 'form-control d-inline-block tier-number', 'style' => 'width: 180px;', 'placeholder' => '1,2,3-5', 'aria-label' => 'Tier numbers or range', 'data-field' => 'tier']) !!}
            </label>
            <div>
                <label class="mb-0 mr-3">
                    <input type="checkbox" data-field="opt_out" value="1">
                    Allow players to opt out of this tier
                </label>
                <button type="button" class="btn btn-sm btn-outline-danger remove-tier-button">Remove Tier</button>
            </div>
        </div>

        <div class="text-right mb-2">
            <button type="button" class="btn btn-sm btn-outline-info add-tier-reward">Add Reward</button>
        </div>

        <div>
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th width="30%">Reward Type</th>
                        <th width="30%">Reward</th>
                        <th width="10%">Quantity</th>
                        <th width="10%">Weight</th>
                        <th width="10%">Data</th>
                        <th width="10%"></th>
                    </tr>
                </thead>
                <tbody class="tier-rewards"></tbody>
            </table>
        </div>
    </section>
</template>

<template id="tierRewardTemplate">
    <tr class="tier-reward-row">
        <td>{!! Form::select('rewardable_type[]', $rewardTypes, null, ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type', 'data-field' => 'rewardable_type']) !!}</td>
        <td class="loot-row-select"></td>
        <td>{!! Form::number('quantity[]', 1, ['class' => 'form-control', 'min' => 1, 'data-field' => 'quantity']) !!}</td>
        <td>{!! Form::number('weight[]', 1, ['class' => 'form-control', 'min' => 1, 'data-field' => 'weight']) !!}</td>
        <td>
            <a href="#" class="btn data-config btn-info"><i class="fas fa-cog"></i></a>
            {!! Form::hidden('data[]', '', ['class' => 'form-control', 'data-field' => 'data']) !!}
        </td>
        <td class="text-right"><button type="button" class="btn btn-sm btn-danger remove-tier-reward">Remove</button></td>
    </tr>
</template>
