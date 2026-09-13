@php
    $userGuilds = Auth::user()->guilds->pluck('id')->toArray();
    $guilds = \App\Models\Guild\Guild::whereIn('id', $userGuilds)->where('status', 'active');
    $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
@endphp

<div class="submission-guild mb-3 card">
    <div class="card-body">
        <div class="text-right"><a href="#" class="remove-guild text-muted"><i class="fas fa-times"></i></a></div>
        <div class="row">
            <div class="col-md-2 align-items-stretch d-flex">
                <div class="d-flex text-center align-items-center">
                    <div class="guild-image-blank hide">Select the guild to reward</div>
                    <div class="guild-image-loaded">
                        @include('home._guild', ['guild' => $guild->guild ? $guild->guild : $guild])
                    </div>
                </div>
            </div>
            <div class="col-md-10">
                <div class="form-group">
                    {!! Form::label('guild_id[]', 'Guild') !!}
                    {!! Form::select('guild_id[]', $guilds, $guild ? $guild->id : null, ['class' => 'form-control guild-id', 'placeholder' => 'Select Guild']) !!}
                </div>
                <div class="guild-rewards">
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
                            @foreach ($guild->rewards ?? [] as $reward)
                                <tr class="guild-reward-row">
                                    @if ($expanded_rewards)
                                        <td>
                                            {!! Form::select('guild_rewardable_type[' . $guild->guild_id . '][]', ['Item' => 'Item', 'Currency' => 'Currency', 'LootTable' => 'Loot Table'], $reward->rewardable_type, [
                                                'class' => 'form-control guild-rewardable-type',
                                                'placeholder' => 'Select Reward Type',
                                            ]) !!}
                                        </td>
                                        <td class="lootDivs">
                                            <div class="guild-currencies  {{ $reward->rewardable_type == 'Currency' ? 'show' : 'hide' }}">{!! Form::select('guild_rewardable_id[' . $guild->guild_id . '][]', $guildCurrencies, $reward->rewardable_type == 'Currency' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control guild-currency-id',
                                                'placeholder' => 'Select Currency',
                                            ]) !!}</div>
                                            <div class="guild-items  {{ $reward->rewardable_type == 'Item' ? 'show' : 'hide' }}">{!! Form::select('guild_rewardable_id[' . $guild->guild_id . '][]', $items, $reward->rewardable_type == 'Item' ? $reward->rewardable_id : null, ['class' => 'form-control guild-item-id', 'placeholder' => 'Select Item']) !!}</div>
                                            <div class="guild-tables {{ $reward->rewardable_type == 'Loot Table' ? 'show' : 'hide' }}">{!! Form::select('guild_rewardable_id[' . $guild->guild_id . '][]', $tables, $reward->rewardable_type == 'Loot Table' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control guild-table-id',
                                                'placeholder' => 'Select Loot Table',
                                            ]) !!}</div>
                                        </td>
                                    @else
                                        <td class="lootDivs">
                                            {!! Form::hidden('guild_rewardable_type[' . $guild->guild_id . '][]', 'Currency', ['class' => 'guild-rewardable-type']) !!}
                                            {!! Form::select('guild_rewardable_id[' . $guild->guild_id . '][]', $guildCurrencies, $reward->rewardable_type == 'Currency' ? $reward->rewardable_id : null, [
                                                'class' => 'form-control guild-currency-id',
                                                'placeholder' => 'Select Currency',
                                            ]) !!}
                                        </td>
                                    @endif
                                    <td class="d-flex align-items-center">
                                        {!! Form::number('guild_rewardable_quantity[' . $guild->guild_id . '][]', $reward->quantity, ['class' => 'form-control mr-2 guild-rewardable-quantity']) !!}
                                        <a href="#" class="remove-reward d-block"><i class="fas fa-times text-muted"></i></a>
                                    </td>
                                </tr>
                            @endforeach
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
