@if ($characters->count())
    <div class="row">
        @if ($myo)
            @foreach ($characters as $character)
                @include('browse._genotype_box', ['character' => $character])
            @endforeach
        @else
            @foreach ($characters as $character)
                @include('browse._character_box', ['character' => $character])
            @endforeach
        @endif
    </div>
@else
    <p>No {{ $myo ? 'Genotypes' : 'characters' }} found.</p>
@endif
