<div class="card">
    <div class="card-header">
        <h4>Pup #{{ $i + 1 }}</h4>
    </div>
    <div class="card-body">
        <p class="mb-0">{!! $pup->species->displayName !!} <span class="mx-2">●</span> {!! $pup->subtype->displayName !!}</p>
        <p class="mb-0"><strong>F: </strong>{!! $pup->traits->fur->displayGeno !!}</p>
        <p class="mb-0"><strong>T: </strong>
            {!! $pup->traits->ears->displayGeno !!},
            {!! $pup->traits->tail->displayGeno !!},
            {!! $pup->traits->eyes->displayGeno !!}
        </p>
        <p class="mb-0"><strong>P: </strong>
            @if ($pup->is_chimera)
                {!! $pup->markings[0]['p'] !!}//{!! $pup->markings[1]['p'] !!}
            @else
                {!! $pup->markings['p'] !!}
            @endif
        </p>
        <p class="mb-0"><strong>G: </strong>
            @if ($pup->is_chimera)
                {!! $pup->markings[0]['g'] !!}//{!! $pup->markings[1]['g'] !!}
            @else
                {!! $pup->markings['g'] !!}
            @endif
        </p>
        @if (isset($pup->carriers) && $pup->carriers && count($pup->carriers))
            <p class="mb-0"><strong>Carriers: </strong>{{ implode(', ', $pup->carriers->pluck('name')->toArray()) }}</p>
        @endif
        @if (isset($pup->mutation) && $pup->mutation)
            [ {{ strtoupper($pup->mutation->name) }} ]
        @endif
        @if (isset($pup->inbred) && $pup->inbred)
            [ {{ strtoupper($pup->inbred->name) }} ]
        @endif
    </div>
    @if ($showActions)
        <div class="card-footer d-flex align-items-center">
            @if (isset($pup->status) && $pup->status !== null)
                @switch($pup->status)
                    @case('Converted')
                        <p>This pup was converted to a Genotype.</p>
                    @break

                    @case('Released')
                        <p>This pup was released to the wild.</p>
                    @break

                    @case('Surrendered')
                        <p>This pup was surrendered to the Adoption Center.</p>
                    @break
                @endswitch
            @else
                <input type="hidden" name="pup_id" value="{{ $i }}" />
                <a href="#" class="btn mt-0 mr-1 btn-block btn-outline-danger releasePup">Release</a>
                @if (config('lorekeeper.breeding.surrender_extension_exists'))
                    <a href="#" class="btn mt-0 btn-block btn-outline-primary surrenderPup">Surrender</a>
                @endif
                <a href="#" class="btn mt-0 ml-1 btn-block btn-success convertPup">Convert</a>
            @endif
        </div>
    @endif
</div>
