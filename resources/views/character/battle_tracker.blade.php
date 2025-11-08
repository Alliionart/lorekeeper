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
                    @if($character->equipmennt)

                    @else
                        <p>This character has not equipped any armor or weapons.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <h4 class="card-header">Familiars</h4>
                <div class="card-body">
                    @if($character->pets)
                        @foreach ($character->pets as $pet)
                            <div class="p-2 d-flex justify-content-between align-items-center border border-dark rounded mb-2">
                                <a href="{{ $pet->pageUrl() }}" class="inventory-stack">
                                    <img src="{{ $pet->pet->variantImage($pet->id) }}" style="max-width:100px" class="rounded img-fluid" />
                                </a>
                                <div class="text-left w-100 ml-3">
                                    <div class="mb-2 h5">
                                        @if ($pet->pet_name)
                                            <a href="{{ $pet->pageUrl() }}">{!! $pet->pet_name !!}</a> the
                                        @endif
                                        {!! $pet->pet->displayName !!} {!! $pet->level ? '(' . $pet->level->levelName . ')' : '' !!}
                                    </div>
                                    @if(count($pet->pet->evolutions) > 0)
                                        <h5 class="text-muted"><span class="badge badge-secondary">Ability</span> - {!! $pet->evolution->ability->name !!}</h5>
                                        <div>
                                            {!! $pet->evolution->ability->description !!}
                                        </div>
                                    @else
                                        <h5 class="text-muted"><span class="badge badge-secondary">Ability</span> - {!! $pet->pet->ability->name !!}</h5>
                                        <div>
                                            {!! $pet->pet->ability->description !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>This character has not bonded with any battle familiars.</p>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <h4 class="card-header">Battle Skills</h4>
                <div class="card-body">
                    
                </div>
            </div>

        </div>
        <div class="col-md-4 position-relative">
            <canvas id="statChart"></canvas>

            <div class="mt-3">
                <div class="card">
                    <h4 class="card-header">Battle-Ready Progress</h4>
                    <div class="card-body">
                        <h5>Required</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success mr-2"></i> At least Exemplar status</li>
                            <li><i class="fas fa-check text-success mr-2"></i> Has High Class Task</li>
                            <li><i class="fas fa-check text-success mr-2"></i> Equipped with a full set of armor</li>
                            <li><i class="fas fa-times text-danger mr-2"></i> Equipped with a single weapon</li>
                        </ul>
                        <h5>Optional</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-times text-danger mr-2"></i> Has a Specialty Class Task</li>
                            <li><i class="fas fa-times text-danger mr-2"></i> Has upgraded their Magi Path</li>
                            <li><i class="fas fa-times text-danger mr-2"></i> Have battle skills</li>
                            <li><i class="fas {{ $character->pets ? 'fa-check text-success' : 'fa-times text-danger' }} mr-2"></i> Have battle familiars</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card mt-3">
                <h4 class="card-header">Abilities</h4>
                <div class="card-body">
                    
                </div>
            </div>
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
                            suggestedMax: 65,
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
