<div class="ability_info template p-3 border border-secondary my-2 rounded {{ $class ? $class : '' }}">
    <div class="row">
        <div class="col-md-6 d-flex">
            <div class="form-group w-100">
                {!! Form::label('Target') !!}
                {!! Form::select('[type]_target', [
                    ''                      => 'Select Target Type',
                    'self'                  => 'Self',
                    'all_excluding_self'    => 'All (Excluding Self)',
                    'all_including_self'    => 'All (Including Self)', 
                    'single'                => 'Single Target', 
                    'multi-target'          => 'Multiple Targets', 
                    'all_enemy'             => 'All Enemies', 
                    'all_ally'              => 'All Allies',   
                ], null, ['class' => 'form-control']) !!}
            </div>
            <div class="form-group multi-target ml-3 w-100">
                {!! Form::label('Number of Targets') !!} {!! add_help('If multi-target is selected then enter in the maximum number of targets that can be selected.') !!}
                {!! Form::number('[type]_target_count', null, ['class' => 'form-control', 'min' => 0, 'max' => 99]) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Ability Duration') !!} {!! add_help('How long the effects of last. This should be the number of turns. IF it lasts the entire encounter, use 0.') !!}
                {!! Form::number('[type]_duration', null, ['class' => 'form-control', 'min' => 0, 'max' => 99]) !!}
            </div> 
        </div>
        <div class="col-md-2">
            <a href="#" class="remove-row btn btn-danger ml-auto align-self-start mt-2">Remove Effect Row</a>
        </div>
    </div>
    <hr/>
    <div class="effects-repeater">
        <div class="row" data-index="0">
            <div class="col-md-3">
                <div class="form-group w-100">
                    {!! Form::label('Effect Type') !!}
                    {!! Form::select('[type]_[__INDEX__]__effect_type', [
                        ''                      => 'Select Effect Type',
                        'stat_mod'              => 'Stat Modification',
                        'dmg_mod'               => 'Damage Modification', 
                        'hp_mod'                => 'Health Modification', 
                        'immune'                => 'Immunity', 
                        'status'                => 'Status Effect',
                        'summon'                => 'Summon' 
                    ], null, ['class' => 'form-control effect-type']) !!}
                </div>
            </div>
            <div class="col-md-3 hide" data-type="status">
                <div class="form-group w-100">
                    {!! Form::label('Status Effect Action') !!}
                    {!! Form::select('[type]_[__INDEX__]__stat_mod_action', [
                        ''                      => 'Select Action...',
                        'inflict'               => 'Inflict',
                        'cure'                  => 'Cure',
                    ], null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6 hide" data-type="status">
                <div class="form-group w-100">
                    {!! Form::label('Status Effect(s)') !!}
                    {!! Form::select('[type]_[__INDEX__]__status_action[]', $status_effects, null, ['class' => 'form-control selectize', 'multiple']) !!}
                </div>
            </div>
            <div class="col-md-9 d-flex align-items-center hide" data-type="stat_mod">
                @if ($stats)
                    {!! add_help('Include the amount of stat points to modify for the duration.') !!}
                    @foreach ($stats as $id => $name)
                        <div class="form-group mx-2 w-100">
                            {!! Form::label($name) !!}
                            {!! Form::number('[type]_[__INDEX__]__stat_mod_'.$id, 0, ['class' => 'form-control', 'min' => -50, 'max' => 50]) !!}
                        </div> 
                    @endforeach
                @endif
            </div>
            <div class="col-md-9 hide" data-type="dmg_mod">
                <div class="form-group w-100">
                    {!! Form::label('Damage Modifier Formula') !!}
                    {!! Form::text('[type]_[__INDEX__]__dmg_mod', null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-9 hide" data-type="hp_mod">
                <div class="form-group w-100">
                    {!! Form::label('Health Modifier Formula') !!}
                    {!! Form::text('[type]_[__INDEX__]__hp_mod', null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-3 hide" data-type="immune">
                <div class="form-group w-100">
                    {!! Form::label('Immune to') !!}
                    {!! Form::select('[type]_[__INDEX__]__immune_to', [
                        ''                      => 'Select Immunity...',
                        'next_attack'           => 'Next Attack (Auto-Dodge)',
                        'status'                => 'Status Effect(s)',
                    ], null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="col-md-6 hide" data-type="immune" data-sub="status">
                <div class="form-group w-100">
                    {!! Form::label('Status Effect(s)') !!}
                    {!! Form::select('[type]_[__INDEX__]__immune_status[]', $status_effects, null, ['class' => 'form-control selectize', 'multiple']) !!}
                </div>
            </div>
            <div class="col-md-9 border border-secondary p-2 hide" data-type="summon">
                <h5>Let's Build a Summon!</h5>
                <p>Reoseans may only have 1 summon active at a time. Summons require HP, even if it is a summon that goes away with 1 hit, set its HP to 1. Summons may have their own sub-effects so long as they are in play. Shields are a type of summon that sits in front of the Reosean always.</p>
                <div class="row">
                    <div class="col-md-6">
                        {!! Form::label('Summon Type') !!}
                        {!! Form::select('[type]_[__INDEX__]__summon_type', [
                            ''                      => 'Select Type...',
                            'default'               => 'Default',
                            'shield'                => 'Shield',
                        ], null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('Summon Status Effect Infliction') !!}
                        {!! Form::select('[type]_[__INDEX__]__summon_status', $status_effects, null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-3">
                        {!! Form::label('Chance to Inflict Status') !!}
                        {!! Form::text('[type]_[__INDEX__]__summon_status_chance', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="mt-2 d-flex align-items-center">
                    @if ($stats)
                        {!! add_help('Include the amount of stat points the summon will use. Always fill in HP, however anything else left blank will default to the Summoner\'s stats.') !!}
                        @foreach ($stats as $id => $name)
                            <div class="form-group mx-2 w-100">
                                {!! Form::label($name) !!}
                                {!! Form::number('[type]_[__INDEX__]__summon_stat_'.$id, null, ['class' => 'form-control', 'min' => -50, 'max' => 50]) !!}
                            </div> 
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>