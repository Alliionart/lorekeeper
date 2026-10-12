<!-- 
    Needs:
    - Allow adding data that will attach upon reward creation.
    - Might need modals for each award type?
-->

<h4>Static Rewards</h4>
<p>Configure the static rewards below. These rewards will be given to all players without any tiering or randomness.</p>
<hr>

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addLoot">Add {{ isset($progression) && $progression ? 'Progression' : 'Reward' }}</a>
</div>
<table class="table table-sm" id="lootTable">
    <thead>
        <tr>
            <th width="35%">{{ isset($progression) && $progression ? 'Progression' : 'Reward' }} Type</th>
            <th width="35%">{{ isset($progression) && $progression ? 'Progression' : 'Reward' }}</th>
            <th width="10%">Quantity</th>
            <th width="10%">Data</th>
            <th width="10%"></th>
        </tr>
    </thead>
    <tbody id="lootTableBody">
        @if ($raffle->rewards()->count())
            @foreach ($raffle->rewards as $loot)
                <tr class="loot-row">
                    <td>{!! Form::select(
                        'rewardable_type[]',
                        $rewardTypes,
                        $loot->rewardable_type,
                        ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type'],
                    ) !!}</td>
                    <td class="loot-row-select">
                        @if ($loot->rewardable_type == 'Item')
                            {!! Form::select('rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($loot->rewardable_type == 'Currency')
                            {!! Form::select('rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                        @elseif($loot->rewardable_type == 'Pet')
                            {!! Form::select('rewardable_id[]', $pets, $loot->rewardable_id, ['class' => 'form-control pet-select selectize', 'placeholder' => 'Select Pet']) !!}
                        @elseif($loot->rewardable_type == 'Weapon')
                            {!! Form::select('rewardable_id[]', $weapons, $loot->rewardable_id, ['class' => 'form-control weapon-select selectize', 'placeholder' => 'Select Weapon']) !!}
                        @elseif($loot->rewardable_type == 'Gear')
                            {!! Form::select('rewardable_id[]', $gears, $loot->rewardable_id, ['class' => 'form-control gear-select selectize', 'placeholder' => 'Select Gear']) !!}
                        @endif
                    </td>
                    <td>{!! Form::text('quantity[]', $loot->quantity, ['class' => 'form-control']) !!}</td>
                    <td>
                        <a href="#" class="btn data-config btn-info"><i class="fas fa-cog"></i></a>
                        {!! Form::hidden('data[]', $loot->data ?? '', ['class' => 'form-control']) !!}
                    </td>
                    <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
                </tr>
            @endforeach
        @endif
        <tr class="loot-row template">
            <td>{!! Form::select(
                'rewardable_type[]',
                $rewardTypes,
                null,
                ['class' => 'form-control reward-type', 'placeholder' => 'Select Reward Type'],
            ) !!}</td>
            <td class="loot-row-select"></td>
            <td>{!! Form::text('quantity[]', 1, ['class' => 'form-control']) !!}</td>
            <td>
                <a href="#" class="btn data-config btn-info"><i class="fas fa-cog"></i></a>
                {!! Form::hidden('data[]', $loot->data ?? '', ['class' => 'form-control']) !!}
            </td>
            <td class="text-right"><a href="#" class="btn btn-danger remove-loot-button">Remove</a></td>
        </tr>
    </tbody>
</table>

