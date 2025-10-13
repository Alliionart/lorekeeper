<?php
if (!isset($i)) {
    $i = '[__INDEX__]';
}
if (!isset($type)) {
    $type = '[type]';
}
$header = $type . '_' . $i;
$effect_type = isset($fields['effect_type']) ? $fields['effect_type'] : null;
?>

<div class="ability_info p-3 border border-secondary my-2 rounded {{ isset($class) && $class ? $class : '' }}">
    <div class="row">
        <div class="col-md-6 d-flex">
            <div class="form-group w-100">
                {!! Form::label('Target') !!}
                {!! Form::select(
                    $header . '__target',
                    [
                        '' => 'Select Target Type',
                        'self' => 'Self',
                        'all_excluding_self' => 'All (Excluding Self)',
                        'all_including_self' => 'All (Including Self)',
                        'single' => 'Single Target',
                        'multi-target' => 'Multiple Targets',
                        'all_enemy' => 'All Enemies',
                        'all_ally' => 'All Allies',
                    ],
                    isset($fields) && isset($fields['target']) ? $fields['target'] : null,
                    ['class' => 'form-control target_selector'],
                ) !!}
            </div>
            <div class="form-group multi-target hide ml-3 w-100">
                {!! Form::label('Number of Targets') !!} {!! add_help('If multi-target is selected then enter in the maximum number of targets that can be selected.') !!}
                {!! Form::number($header . '__target_count', isset($fields) && isset($fields['target_count']) ? $fields['target_count'] : null, ['class' => 'form-control', 'min' => 0, 'max' => 99]) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Ability Duration') !!} {!! add_help('How long the effects of last. This should be the number of turns. IF it lasts the entire encounter, use 0.') !!}
                {!! Form::number($header . '__duration', isset($fields) && isset($fields['duration']) ? $fields['duration'] : null, ['class' => 'form-control', 'min' => 0, 'max' => 99]) !!}
            </div>
        </div>
        <div class="col-md-2">
            <a href="#" class="remove-row btn btn-danger ml-auto align-self-start mt-2">Remove Effect Row</a>
        </div>
    </div>
    <hr />
    <div class="effects-repeater">
        <div class="row" data-index="0">
            <div class="col-md-3">
                <div class="form-group w-100">
                    {!! Form::label('Effect Type') !!}
                    {!! Form::select(
                        $header . '__effect_type',
                        [
                            '' => 'Select Effect Type',
                            'stat_mod' => 'Stat Modification',
                            'dmg_mod' => 'Damage Modification',
                            'hp_mod' => 'Health Modification',
                            'immune' => 'Immunity',
                            'status' => 'Status Effect',
                            'summon' => 'Summon',
                        ],
                        isset($fields) && isset($fields['effect_type']) ? $fields['effect_type'] : null,
                        ['class' => 'form-control effect-type'],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3  {{ $effect_type === 'status' ? '' : 'hide' }}" data-type="status">
                <div class="form-group w-100">
                    {!! Form::label('Status Effect Action') !!}
                    {!! Form::select(
                        $header . '__status_action',
                        [
                            '' => 'Select Action...',
                            'inflict' => 'Inflict',
                            'cure' => 'Cure',
                        ],
                        isset($fields) && isset($fields['status_action']) ? $fields['status_action'] : null,
                        ['class' => 'form-control'],
                    ) !!}
                </div>
            </div>
            <div class="col-md-6  {{ $effect_type === 'status' ? '' : 'hide' }}" data-type="status">
                <div class="form-group w-100">
                    {!! Form::label('Status Effect(s)') !!}
                    {!! Form::select($header . '__status_effects[]', $status_effects, isset($fields) && isset($fields['status_effects']) ? $fields['status_effects'] : null, ['class' => 'form-control selectize', 'multiple']) !!}
                </div>
            </div>
            <div class="col-md-9 d-flex align-items-center {{ $effect_type === 'stat_mod' ? '' : 'hide' }}" data-type="stat_mod">
                @if ($stats)
                    {!! add_help('Include the amount of stat points to modify for the duration.') !!}
                    @foreach ($stats as $id => $name)
                        <?php
                        $stat = isset($fields) && isset($fields['stat_mod'][$id]) ? $fields['stat_mod'][$id] : null;
                        ?>
                        <div class="form-group mx-2 w-100">
                            {!! Form::label($name) !!}
                            {!! Form::number($header . '__stat_mod_' . $id, $stat, ['class' => 'form-control', 'min' => -50, 'max' => 50]) !!}
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="col-md-9 {{ $effect_type === 'dmg_mod' ? '' : 'hide' }}" data-type="dmg_mod">
                <div class="form-group w-100">
                    {!! Form::label('Damage Modifier Formula') !!}
                    {!! Form::text($header . '__dmg_mod', isset($fields) && isset($fields['dmg_mod']) ? $fields['dmg_mod'] : null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-9 {{ $effect_type === 'hp_mod' ? '' : 'hide' }}" data-type="hp_mod">
                <div class="form-group w-100">
                    {!! Form::label('Health Modifier Formula') !!}
                    {!! Form::text($header . '__hp_mod', isset($fields) && isset($fields['hp_mod']) ? $fields['hp_mod'] : null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-3 {{ $effect_type === 'immune' ? '' : 'hide' }}" data-type="immune">
                <div class="form-group w-100">
                    {!! Form::label('Immune to') !!}
                    {!! Form::select(
                        $header . '__immune_to',
                        [
                            '' => 'Select Immunity...',
                            'next_attack' => 'Next Attack (Auto-Dodge)',
                            'status' => 'Status Effect(s)',
                        ],
                        isset($fields) && isset($fields['immune_to']) ? $fields['immune_to'] : null,
                        ['class' => 'form-control'],
                    ) !!}
                </div>
            </div>
            <div class="col-md-6 {{ $effect_type === 'immune' ? '' : 'hide' }}" data-type="immune" data-sub="status">
                <div class="form-group w-100">
                    {!! Form::label('Status Effect(s)') !!}
                    {!! Form::select($header . '__immune_status[]', $status_effects, isset($fields) && isset($fields['immune_status']) ? $fields['immune_status'] : null, ['class' => 'form-control selectize', 'multiple']) !!}
                </div>
            </div>
            <div class="col-md-9 border border-secondary p-2 {{ $effect_type === 'summon' ? '' : 'hide' }}" data-type="summon">
                <h5>Let's Build a Summon!</h5>
                <p>Reoseans may only have 1 summon active at a time. Summons require HP, even if it is a summon that goes away with 1 hit, set its HP to 1. Summons may have their own sub-effects so long as they are in play. Shields are a type of
                    summon that sits in front of the Reosean always.</p>
                <div class="row">
                    <div class="col-md-3">
                        {!! Form::label('Summon Type') !!}
                        {!! Form::select(
                            $header . '__summon_type',
                            [
                                '' => 'Select Type...',
                                'default' => 'Default',
                                'shield' => 'Shield',
                            ],
                            isset($fields) && isset($fields['summon_type']) ? $fields['summon_type'] : null,
                            ['class' => 'form-control'],
                        ) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('Summon Status Effect Infliction') !!}
                        {!! Form::select($header . '__summon_status', $status_effects, isset($fields) && isset($fields['summon_status']) ? $fields['summon_status'] : null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('Chance to Inflict Status') !!}
                        {!! Form::text($header . '__summon_status_chance', isset($fields) && isset($fields['summon_status_chance']) ? $fields['summon_status_chance'] : null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('Summon Damage') !!}
                        {!! Form::text($header . '__summon_dmg', isset($fields) && isset($fields['summon_dmg']) ? $fields['summon_dmg'] : null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center">
                    @if ($stats)
                        {!! add_help('Include the amount of stat points the summon will use. Always fill in HP, however anything else left blank will default to the Summoner\'s stats.') !!}
                        @foreach ($stats as $id => $name)
                            <?php
                            $stat = isset($fields) && isset($fields['summon_stats'][$id]) ? $fields['summon_stats'][$id] : null;
                            ?>
                            <div class="form-group mx-2 w-100">
                                {!! Form::label($name) !!}
                                {!! Form::number($header . '__summon_stat_' . $id, $stat, ['class' => 'form-control', 'min' => -50, 'max' => 50]) !!}
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
