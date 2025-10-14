<div class="col-md-3 col-6 text-left">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center h6">
            <a href="{{ $character->url }}">
                @if (!$character->is_visible)
                    <i class="fas fa-eye-slash"></i>
                @endif {{ Illuminate\Support\Str::limit($character->fullName, 20, $end = '...') }}
            </a>
            <span class="ml-auto">{!! $character->displayOwner !!}</span>
        </div>
        <div class="mt-1 card-body pt-2">
            {!! $character->image->species_id ? $character->image->species->displayName : 'No Species' !!} ・ {!! $character->image->subtype_id ? $character->image->subtype->displayName : 'No Subtype' !!}
            <?php $features = $character->image
                ->features()
                ->with('feature.category')
                ->get();
            
            $fur = $features->first(function ($feature) {
                return $feature->feature->category && $feature->feature->category->name === 'Fur';
            });
            $ear = $features->first(function ($feature) {
                return $feature->feature->category && $feature->feature->category->name === 'Ears';
            });
            $tail = $features->first(function ($feature) {
                return $feature->feature->category && $feature->feature->category->name === 'Tails';
            });
            $eyes = $features->first(function ($feature) {
                return $feature->feature->category && $feature->feature->category->name === 'Eyes';
            });
            $corrupt = $features->first(function ($feature) {
                return $feature->feature->category && $feature->feature->category->name === 'Corrupt Mutation';
            });
            $magical = $features->first(function ($feature) {
                return $feature->feature->category && $feature->feature->category->name === 'Magical Mutation';
            });
            
            $trait_list = [];
            if ($ear) {
                $trait_list[] = '<a href="' . $ear->feature->url . '" style="text-decoration-color:#' . $ear->feature->rarity->color . '">' . $ear->feature->name . ' Ears</a>';
            }
            if ($tail) {
                $trait_list[] = '<a href="' . $tail->feature->url . '" style="text-decoration-color:#' . $tail->feature->rarity->color . '">' . $tail->feature->name . ' Tail</a>';
            }
            if ($eyes) {
                $trait_list[] = '<a href="' . $eyes->feature->url . '" style="text-decoration-color:#' . $eyes->feature->rarity->color . '">' . $eyes->feature->name . ' Eyes</a>';
            }
            ?>

            @if ($fur)
                <p class="mb-0"><strong>F: </strong>{{ $fur->feature->name }} Coat</p>
            @endif
            @if ($trait_list)
                <p class="mb-0 trait-row"><strong>T: </strong> {!! implode(', ', $trait_list) !!}</p>
            @endif
            @if ($character->getMarkings())
                <p class="mb-0"><strong>P: </strong>{!! $character->getMarkings() !!}</p>
            @endif
            @if ($character->getMarkings())
                <p class="mb-0"><strong>G: </strong>{!! $character->getMarkings('genotype') !!}</p>
            @endif
            @if ($corrupt)
                [{!! $corrupt->feature->name !!}]
            @endif
            @if ($magical)
                [{!! $magical->feature->name !!}]
            @endif
        </div>
    </div>
</div>
