<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h4>Raffle Rewards</h4>
            @if (Auth::check() && Auth::user()->hasPower('edit_raffles'))
                <a class="btn btn-primary" href="{{ $raffle->adminRewardsUrl }}">Edit Rewards</a>
            @endif
        </div>
        <hr>
        @if ($raffle->rewards)
            @switch($raffle->rewards_type)
                @case('Tiered')
                    <p>Each winner for the raffle will receive rewards based on their tier.</p>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Winner(s)</th>
                                <th>Reward</th>
                                <th>Quantity</th>
                                <th>Weight</th>
                                <th>Additional Information</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($raffle->rewards as $reward)
                                <tr>
                                    <td>{{ $reward->position }}</td>
                                    <td>
                                        @if ($reward->rewardable_type === 'BreedingPermission')
                                            Breeding Permission(s) to
                                        @endif
                                        {!! $reward->reward->displayName !!}
                                    </td>
                                    <td>{{ $reward->quantity }}</td>
                                    <td>{{ $reward->weight }}</td>
                                    @if ($raffle->rewards_type === 'Random')
                                        <td>{{ $reward->weight }}</td>
                                    @endif
                                    <td>
                                        @if ($reward->data)
                                            <ul class="mb-0 list-unstyled">
                                                @foreach ($reward->datas as $key => $value)
                                                    <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @break

                @default
                    @if ($raffle->rewards_type === 'Static')
                        <p>Each winner for the raffle will receive the same reward.</p>
                    @else
                        <p>Each winner for the raffle will have their rewards rolled individually.</p>
                    @endif

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Reward</th>
                                <th>Quantity</th>
                                @if ($raffle->rewards_type === 'Random')
                                    <th>Weight</th>
                                @endif
                                <th>Additional Information</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($raffle->rewards as $reward)
                                <tr>
                                    <td>
                                        @if ($reward->rewardable_type === 'BreedingPermission')
                                            Breeding Permission(s) to
                                        @endif
                                        {!! $reward->reward->displayName !!}
                                    </td>
                                    <td>{{ $reward->quantity }}</td>
                                    @if ($raffle->rewards_type === 'Random')
                                        <td>{{ $reward->weight }}</td>
                                    @endif
                                    <td>
                                        @if ($reward->data)
                                            <ul class="mb-0 list-unstyled">
                                                @foreach ($reward->datas as $key => $value)
                                                    <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
            @endswitch
        @else
            <p>There are no rewards for this raffle.</p>
        @endif
    </div>
</div>
