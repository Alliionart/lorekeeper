@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Battle Tracker
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

<?php
    $characterStats = $character->stats->sortBy('stat_id');

    $statNames = $characterStats->pluck('stat.name', 'stat.id')->toArray();
    $statData = $characterStats->pluck('count', 'stat.id')->toArray();

    $finalStats = [];
    foreach($statNames as $i => $name) {
        $finalStats[$name] = $statData[$i];
    }
?>

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Profile' => $character->url . '/profile']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Battle Tracker' => $character->url . '/battle-tracker',
        ]) !!}
    @endif

    <h1 class="mb-0">{!! $character->displayName !!}'s Battle Tracker</h1>
    
    <hr class="my-4" />

    <h4>Stats</h4>
    <div class="row">
        <div class="col-md-8">
            @if($character->stats)
                <div class="d-flex justify-content-between align-items-center">
                    @foreach($character->stats->sortBy('stat.id') as $stat)
                        <div class="p-2 border w-100 mx-2 border-primary rounded mb-2 bg-dark" style="border-color:{{ $stat->stat->colour }} !important;">
                            <h5 class="mb-0">{{ $stat->stat->abbreviation }}</h5>
                            <p class="mb-0 h3 text-center">{{ $character->totalStatCount($stat->stat->id) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="card mt-3">
                <h4 class="card-header">Equipment</h4>
                <div class="card-body">

                </div>
            </div>

            <div class="card mt-3">
                <h4 class="card-header">Familiars</h4>
                <div class="card-body">
                    <pre class="bg-white">
                        {{ print_r($pets, true) }}
                    </pre>
                </div>
            </div>

        </div>
        <div class="col-md-4">
            <canvas id="statChart"></canvas>
        </div>
    </div>    
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $('document').ready(function() {
            const ctx = document.getElementById('statChart');
            
            const data = {
            labels: @json(array_keys($finalStats)),
            datasets: [{
                    label:  '{{ $character->name }} Stats',
                    data: @json(array_values($finalStats)),
                    fill: true,
                    backgroundColor: 'rgba(165, 167, 255, 0.2)',
                    borderColor: 'rgba(60, 138, 255, 1)',
                    pointBackgroundColor: 'rgba(148, 214, 255, 0.5)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(31, 150, 255, 0.5)',
                    tension: 0.25,
                    pointHitRadius: 3,
                }]
            };

            new Chart(ctx, {
                type: 'radar',
                data: data,
                options: {
                    elements: {
                        line: {
                            borderWidth: 3
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                display: false
                            },
                            suggestedMin: 1,
                            suggestedMax: 80,
                            ticks: {
                                display: false
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.25)'
                            },
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.5)'
                            },
                        },
                        
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                },
            });
            
        });
    </script>
@endsection
