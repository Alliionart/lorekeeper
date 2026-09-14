@php
    $guilds = \App\Models\Guild\Guild::where('status', 'active')
        ->orderBy('name', 'DESC')
        ->get()
        ->pluck('name', 'id')
        ->toArray();
    $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
@endphp

<div id="guildComponents" class="hide">
    <div class="submission-guild mb-3 card">
        <div class="card-body">
            <div class="text-right"><a href="#" class="remove-guild text-muted"><i class="fas fa-times"></i></a></div>
            <div class="row">
                <div class="col-md-2 align-items-stretch d-flex">
                    <div class="d-flex text-center align-items-center">
                        <div class="guild-image-blank">Enter guild code.</div>
                        <div class="guild-image-loaded hide"></div>
                    </div>
                </div>
                <div class="col-md-10">
                    <div class="form-group">
                        {!! Form::label('guild_id[]', 'Guild Code') !!}
                        {!! Form::select('guild_id[]', $guilds, null, ['class' => 'form-control guild-code', 'placeholder' => 'Select Guild']) !!}
                    </div>
                    <div class="guild-rewards hide">
                        <h4>Guild Rewards</h4>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    @if ($expanded_rewards)
                                        <th width="35%">Reward Type</th>
                                        <th width="35%">Reward</th>
                                    @else
                                        <th width="70%">Reward</th>
                                    @endif
                                    <th width="30%">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="guild-rewards">
                            </tbody>
                        </table>
                        <div class="text-right">
                            <a href="#" class="btn btn-outline-primary btn-sm add-reward">Add Reward</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <table>
        <tr class="guild-reward-row">

            @if ($expanded_rewards) 
                <td>
                    {!! Form::select('guild_rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + (isset($showLootTables) && $showLootTables ? ['LootTable' => 'Loot Table'] : []), null, [
                        'class' => 'form-control guild-rewardable-type',
                        'placeholder' => 'Select Reward Type',
                    ]) !!}
                </td>
                <td class="lootDivs">
                    <div class="guild-currencies hide">{!! Form::select('guild_currency_id[]', $guildCurrencies, 0, ['class' => 'form-control guild-currency-id', 'placeholder' => 'Select Currency']) !!}</div>
                    <div class="guild-items hide">{!! Form::select('guild_item_id[]', $items, 0, ['class' => 'form-control guild-item-id selectize', 'placeholder' => 'Select Item']) !!}</div>
                    @if (isset($showLootTables) && $showLootTables)
                        <div class="guild-tables hide">{!! Form::select('guild_rewardable_id[]', $tables, 0, ['class' => 'form-control guild-table-id selectize', 'placeholder' => 'Select Loot Table']) !!}</div>
                    @endif
                </td>
            @else
                <td class="lootDivs">
                    {!! Form::hidden('guild_rewardable_type[]', 'Currency', ['class' => 'guild-rewardable-type']) !!}
                    <div class="guild-currencies">{!! Form::select('guild_currency_id[]', $guildCurrencies, 0, ['class' => 'form-control guild-currency-id', 'placeholder' => 'Select Currency']) !!}</div>
                </td>
            @endif

            <td class="d-flex align-items-center">
                {!! Form::number('guild_rewardable_quantity[]', 1, ['class' => 'form-control mr-2 guild-rewardable-quantity']) !!}
                <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
            </td>
        </tr>
    </table>
</div>
