@if ($type)
    {!! Form::open(['url' => 'breeding/' . $breeding->id . '/' . $pup . '/convert/convert']) !!}
    <p>This will convert the #{{ $pup + 1 }} pup of this litter to a genotype and transfer ownership to the selected user.</p>
    @if ($breeding->split_user_id)
        <div class="form-group">
            {!! Form::label('user_id', 'Select a user to transfer the pup to:') !!}
            {!! Form::select(
                'user_id',
                [
                    '' => 'Select a user...',
                    $breeding->user->id => $breeding->user->name . ' (Creator)',
                    $breeding->split_user_id => $breeding->split_user->name . ' (Split User)',
                ],
                null,
                ['class' => 'form-control', 'required' => 'required'],
            ) !!}
        </div>
    @endif
    <div class="text-right">
        {!! Form::submit('Convert', ['class' => 'btn btn-success']) !!}
    </div>
    {!! Form::close() !!}
@elseif (config('lorekeeper.breeding.surrender_extension_exists'))
    {!! Form::open(['url' => 'breeding/' . $breeding->id . '/' . $pup . '/convert/surrender']) !!}
    <p>This will convert the #{{ $pup + 1 }} pup of this litter to a genotype send to the Adoption Center.</p>
    <div class="form-group">
        {!! Form::label('What would you like to recieve for this pup?') !!}
        {!! Form::select(
            'exchange_type',
            [
                'full_krones' => 'The value of the pup in krones',
                'flat_trinkets' => '1 Trinket',
            ],
            null,
            ['class' => 'form-control'],
        ) !!}
    </div>
    <div class="text-right">
        {!! Form::submit('Send to Adoption Center', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}
@else
    <p>This pup cannot be converted to a genotype.</p>
@endif
