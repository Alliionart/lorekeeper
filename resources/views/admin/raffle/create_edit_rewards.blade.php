@extends('admin.layout')

@section('admin-title')
    {{ $rewards ? 'Edit' : 'Create' }} Raffle Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Raffles' => 'admin/raffles', ($rewards ? 'Edit' : 'Create') . ' Rewards' => $rewards ? 'admin/raffles/' . $raffle->id . '/rewards' : 'admin/raffles/create']) !!}

    <h1>{{ $rewards ? 'Edit' : 'Create' }} Raffle Rewards</h1>

    {!! Form::open(['url' => 'admin/raffles/' . $raffle->id . '/rewards']) !!}

    @php
        $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
        $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
            ->orderBy('name')
            ->pluck('name', 'id');

        $rewardTypes = ['Item' => 'Item', 'Currency' => 'Currency'];
        $rewardData = ['Item' => $items, 'Currency' => $currencies];

        if (config('lorekeeper.raffle_options.pets')) {
            $rewardTypes['Pet'] = 'Pet';
            $rewardData['Pet'] = \App\Models\Pet\Pet::orderBy('name')->pluck('name', 'id');
        }

        if (config('lorekeeper.raffle_options.gears')) {
            $rewardTypes['Gear'] = 'Gear';
            $rewardData['Gear'] = \App\Models\Claymore\Gear::orderBy('name')->pluck('name', 'id');
        }

        if (config('lorekeeper.raffle_options.weapons')) {
            $rewardTypes['Weapon'] = 'Weapon';
            $rewardData['Weapon'] = \App\Models\Claymore\Weapon::orderBy('name')->pluck('name', 'id');
        }

        if (config('lorekeeper.raffle_options.breedingPermissions')) {
            $rewardTypes['BreedingPermission'] = 'Breeding Permission';
            $rewardData['BreedingPermission'] = \App\Models\Character\Character::orderBy('name')
                ->get(['id', 'slug', 'name', 'is_myo_slot'])
                ->mapWithKeys(function ($character) {
                    return [$character->id => $character->fullName];
                });
        }
    @endphp

    @switch($raffle->rewards_type)
        @case('Tiered')
            @include('admin.raffle.rewards._tiered', ['rewardTypes' => $rewardTypes, 'rewardData' => $rewardData])
        @break

        @case('Random')
            @include('admin.raffle.rewards._random', ['rewardTypes' => $rewardTypes, 'rewardData' => $rewardData])
        @break

        @default
            @case('Static')
                @include('admin.raffle.rewards._static', ['rewardTypes' => $rewardTypes, 'rewardData' => $rewardData])
            @break
        @endswitch

        <div class="text-right">
            {!! Form::submit($rewards ? 'Update Rewards' : 'Create Rewards', ['class' => 'btn btn-primary']) !!}
        </div>

        {!! Form::close() !!}
    @endsection

    <div class="modal fade" id="dataModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Configure Reward Data</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Configure the additional data for this reward.</p>

                    <div id="dataConfigContainer">
                        <div class="form-group">
                            {!! Form::label('notes', 'Notes') !!} {!! add_help('Any additional notes for this reward. For items, pets, gear, etc. this will be attached to the notes. Otherwise it will go to the description of the reward.') !!}
                            {!! Form::text('notes', null, ['class' => 'form-control', 'placeholder' => 'Enter notes for this reward']) !!}
                        </div>
                    </div>

                    <div class="text-right">
                        <a href="#" id="dataSubmit" class="btn btn-primary">Save Data</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('scripts')
        @parent
        @include('admin.raffle._rewards_js')
    @endsection
