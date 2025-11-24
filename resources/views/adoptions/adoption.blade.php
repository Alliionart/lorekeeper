@extends('adoptions.layout')

@section('title') {{ $adoption->name }} @endsection

@section('content')
{!! breadcrumbs([$adoption->name => $adoption->url]) !!}

<h1>
    {{ $adoption->name }}
</h1>

<div class="text-center">
    <img src="{{ $adoption->adoptionImageUrl }}" />
    <p>{!! $adoption->parsed_description !!}</p>
</div>

<h3>Characters</h3>
@if(!count($stocks))
    <p>No stock found.</p>
@else 
    <div class="row">
        @foreach($stocks as $stock)
            <div class="col-md-3 col-6 profile-inventory-item">
                <div class="card">
                    @include('browse._genotype_box', ['character' => $stock->character, 'show_owner' => false, 'fullwidth' => true])
                    <div class="text-left inventory-character py-2 px-3" data-id="{{ $stock->character->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Adoption Fee:</strong>
                            <br>
                            @if($stock->currency->count() > 1)
                                <?php 
                                    $currencies = []; // Create an empty array
                                    foreach($stock->currency as $currency)
                                    {
                                    $d1 = $currency->cost;
                                    $d2 = $currency->currency->name;
                                    $currencies[] = ' ' . $d1 . ' ' . $d2; // Add a new value to your array
                                    }
                                    echo implode(" or", $currencies); // implode the full array and separate the values with "or"
                                ?>
                                    <br>
                                @else
                                    @foreach($stock->currency as $currency)
                                    {!! $currency->cost !!}
                                    {!! $currency->currency->name !!}
                                    <br>
                                @endforeach
                            @endif
                            </div>
                            <div class="col-md-6">
                                <a href="#" class="btn btn-primary m-2">
                                    @if($stock->use_character_bank == 1) <i class="fas fa-paw mr-1" data-toggle="tooltip" title="Can be purchased using Character Bank"></i>@endif
                                    <strong>Purchase</strong>
                                    @if($stock->use_user_bank == 1) <i class="fas fa-user ml-1" data-toggle="tooltip" title="Can be purchased using User Bank"></i> @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<hr/>

<h3>Genotypes</h3>
@if(!count($myo_stocks))
    <p>No stock found.</p>
@else 
    <div class="row">
        @foreach($myo_stocks as $stock)
            <div class="col-md-3 col-6 profile-inventory-item">
                <div class="card">
                    @include('browse._genotype_box', ['character' => $stock->character, 'show_owner' => false, 'fullwidth' => true])
                    <div class="text-left inventory-character py-2 px-3" data-id="{{ $stock->character->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Adoption Fee:</strong>
                            <br>
                            @if($stock->currency->count() > 1)
                                <?php 
                                    $currencies = []; // Create an empty array
                                    foreach($stock->currency as $currency)
                                    {
                                    $d1 = $currency->cost;
                                    $d2 = $currency->currency->name;
                                    $currencies[] = ' ' . $d1 . ' ' . $d2; // Add a new value to your array
                                    }
                                    echo implode(" or", $currencies); // implode the full array and separate the values with "or"
                                ?>
                                    <br>
                                @else
                                    @foreach($stock->currency as $currency)
                                    {!! $currency->cost !!}
                                    {!! $currency->currency->name !!}
                                    <br>
                                @endforeach
                            @endif
                            </div>
                            <div class="col-md-6">
                                <a href="#" class="btn btn-primary m-2">
                                    @if($stock->use_character_bank == 1) <i class="fas fa-paw mr-1" data-toggle="tooltip" title="Can be purchased using Character Bank"></i>@endif
                                    <strong>Purchase</strong>
                                    @if($stock->use_user_bank == 1) <i class="fas fa-user ml-1" data-toggle="tooltip" title="Can be purchased using User Bank"></i> @endif
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@if(Settings::get('is_surrenders_open') && Auth::check())
    <div class="text-right mb-2">
        <a href="{{ url('surrenders/new') }}" class="btn btn-dark">Surrender Character to {{ $adoption->name }}</a>
    </div>
@endif

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.inventory-character').on('click', function(e) {
            e.preventDefault();
            
            loadModal("{{ url('adoptions/'.$adoption->id) }}/" + $(this).data('id'), 'Purchase Character');
        });
    });
</script>
@endsection