@if ($raffle->excludedWinners)
    <div class="card p-3 rounded border border-warning mb-4">
        <h4>Excluded from This Raffle</h4>
        <p>The following are users who have been excluded from this raffle. They may be excluded from receiving certain rewards due to winning past raffles.</p>
        <hr>
        <div class="row">
            @foreach ($raffle->excludedWinners as $reward)
                <div class="col-md-3">
                    <h5>{{ $reward['reward'] === 'all' ? 'All Rewards' : $reward['reward']->reward->name . ( isset($reward['reward']->datas['notes']) ? ' (' . $reward['reward']->datas['notes'] . ')' : '' ) }}</h5>
                    <ul>
                        @foreach ($reward['users'] as $user)
                            <li>{!! $user->displayName !!}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endif