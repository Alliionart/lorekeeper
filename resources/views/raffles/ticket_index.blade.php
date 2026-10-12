@extends('layouts.app')

@section('title')
    Raffle - {{ $raffle->name }}
@endsection

@section('content')
    <x-admin-edit title="Raffle" :object="$raffle" />
    {!! breadcrumbs(['Raffles' => 'raffles', 'Raffle: ' . $raffle->name => 'raffles/view/' . $raffle->id]) !!}
    <h1>Raffle: {{ $raffle->name }}</h1>
    @if ($raffle->is_active == 1)
        <div class="alert alert-success text-center">
            This raffle is currently open.
            ・ Number of winners to be drawn: {{ $raffle->winner_count }}
            @if ($raffle->ticket_cap)
                ・ This raffle has a cap of {{ $raffle->ticket_cap }} tickets per individual.
            @endif
        </div>
    @elseif($raffle->is_active == 2)
        <div class="alert alert-danger">This raffle is closed. Rolled: {!! format_date($raffle->rolled_at) !!}</div>
        <div class="card mb-3">
            <div class="card-header h3">Winner(s)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <th class="col-xs-1 text-center" style="width: 100px;">#</th>
                        <th>User</th>
                        <th>Rewards</th>
                    </thead>
                    <tbody>
                        @foreach ($raffle->tickets()->winners()->get() as $winner)
                            <tr>
                                <td class="text-center">{{ $winner->position }}</td>
                                <td class="text-left">{!! $winner->displayHolderName !!}</td>
                                <td class="text-left">
                                    {!! collect($winner->winner_data ?? [])->map(function ($reward) {
                                            $asset = $reward['rewardable_type'] === 'BreedingPermission' ? \App\Models\Character\Character::find($reward['rewardable_id']) : findReward($reward['rewardable_type'], $reward['rewardable_id'], true);
                                    
                                            return 'x' . $reward['quantity'] . ' ' . (($reward['rewardable_type'] === 'BreedingPermission' ? 'Breeding Permission: ' : '') . $asset->displayName ?? ($asset->name ?? 'Deleted reward'));
                                        })->implode(', ') !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @include('raffles._raffle_rewards', ['raffle' => $raffle])
    @include('raffles._excluded_winners', ['raffle' => $raffle])

    @if ($raffle->is_active == 1 && $raffle->rewards_type === 'Tiered' && Auth::check() && $userCount)
        <div class="mb-4">
            <h3>Prize Preferences</h3>
            <p>Select any tiered rewards you do not want to receive. This preference applies to all your tickets in this raffle.</p>
            {!! Form::open(['url' => 'raffles/view/' . $raffle->id . '/opt-out']) !!}
            @foreach ($raffle->rewards->unique(function ($reward) {
            return $reward->rewardable_type . ':' . $reward->rewardable_id;
        }) as $reward)
                @php $rewardKey = $reward->rewardable_type.':'.$reward->rewardable_id; @endphp
                <div class="form-check mb-2">
                    {!! Form::checkbox('opted_out[]', $rewardKey, in_array($rewardKey, $userOptedOut), ['id' => 'opt-out-' . $loop->index, 'class' => 'form-check-input']) !!}
                    <label class="form-check-label" for="opt-out-{{ $loop->index }}">
                        {{ $reward->rewardable_type === 'BreedingPermission' ? 'Breeding Permission: ' : '' }}{{ $reward->reward->name . (isset($reward->datas['notes']) ? ' (' . $reward->datas['notes'] . ')' : '') . ' x' . $reward->quantity ?? 'Deleted reward' }}
                    </label>
                </div>
            @endforeach
            <div class="text-right">
                {!! Form::submit('Save Preferences', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    @endif

    <h3>Tickets</h3>

    @if (Auth::check() && count($tickets))
        <?php $chance = number_format((float) (($userCount / $count) * 100), 1, '.', ''); //Change 1 to 0 if you want no decimal place. ?>
        <p class="text-center mb-0">You {{ $raffle->is_active == 2 ? 'had' : 'have' }} <strong>{{ $userCount }}</strong> out of <strong>{{ $count }} tickets</strong> in this raffle.</p>
        <p class="text-center"> That's a <strong>{{ $chance }}%</strong> chance! </p>
    @endif

    <div class="text-right">{!! $tickets->render() !!}</div>

    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-2 col-md-1 font-weight-bold">
                    <div class="logs-table-cell">#</div>
                </div>
                <div class="col-10 col-md-11 font-weight-bold">
                    <div class="logs-table-cell">User</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($tickets as $count => $ticket)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-2 col-md-1">
                            <div class="logs-table-cell">
                                {{ $page * 100 + $count + 1 }}
                                @if (Auth::check() && $ticket->user_id && $ticket->user->name == Auth::user()->name)
                                    <i class="fas fa-ticket-alt ml-2"></i>
                                @endif
                            </div>
                        </div>
                        <div class="col-10 col-md-11">
                            <div class="logs-table-cell">{!! $ticket->displayHolderName !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="text-right">{!! $tickets->render() !!}</div>
@endsection
