<div class="col-md-3 col-6 text-center mb-3">
    <div class="">
        <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" style="{{ $character->background ? 'background-image:url( ' . $character->background->imageUrl . ' );' : 'background-image:none;' }}background-size:cover;"
                class="img-thumbnail character-bg" alt="Thumbnail for {{ $character->fullName }}" /></a>
    </div>
    <div class="mt-1">
        <a href="{{ $character->url }}" class="h5 mb-0">
            @if (!$character->is_visible)
                <i class="fas fa-eye-slash"></i>
            @endif {{ Illuminate\Support\Str::limit($character->fullName, 20, $end = '...') }}
        </a>
    </div>
    <div class="small">
        {!! $character->image->species_id ? $character->image->species->displayName : 'No Species' !!} ・ {!! $character->displayOwner !!}
    </div>
</div>
