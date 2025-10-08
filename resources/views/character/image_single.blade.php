<div id="acttive-image">
    <a href="{{ $image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($image->imageDirectory . '/' . $image->fullsizeFileName)) ? $image->fullsizeUrl : $image->imageUrl }}" data-lightbox="entry"
        data-title="{{ $character->fullName }}">
        <img src="{{ $image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($image->imageDirectory . '/' . $image->fullsizeFileName)) ? $image->fullsizeUrl : $image->imageUrl }}" class="image"
            alt="{{ $character->fullName }}" />
    </a>
</div>
