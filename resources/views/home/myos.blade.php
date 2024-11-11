@extends('home.layout')

@section('home-title')
    Genotypes
@endsection

@section('home-content')
    {!! breadcrumbs(['Characters' => 'characters', 'Genotypes' => 'myos']) !!}

    <h1>
        My Genotypes
    </h1>

    <p>This is a list of Genomes you own - click on a genome to view details about it. Genomes can be submitted for design approval from their respective pages.</p>
    <div class="row">
        @foreach ($slots as $slot)
            <div class="col-md-3 card col-6 myo text-left m-2 p-3">
                <div class="mt-1 h5">
                    {!! $slot->displayName !!}
                </div>
                <p>G: @if ($slot->genotype)
                        {!! $slot->genotype !!}
                    @else
                        Unknown
                    @endif
                </p>
                <p>P: @if ($slot->phenotype)
                        {!! $slot->phenotype !!}
                    @else
                        Unknown
                    @endif
                </p>
                <div class="d-flex">
                    <div class="badge badge-secondary mr-1">
                        {!! $slot->image->species_id ? $slot->image->species->displayName : 'No Species' !!}
                    </div>
                    <div class="badge badge-secondary mr-1">
                        {!! $slot->image->rarity_id ? $slot->image->rarity->displayName : 'No Rarity' !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
