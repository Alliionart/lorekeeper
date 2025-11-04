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
    <p>No {{ $myo ? ucwords( __('lorekeeper.myos')) : ucwords( __('lorekeeper.characters')) }} found.</p>
@endif
