@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs([ __('lorekeeper.myo') . ' Masterlist' => 'myos', $character->fullName => $character->url]) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : __('lorekeeper.character') . ' masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    {{-- Main Image --}}
    <div class="row mb-3" id="main-tab">
        <div class="col-md-9">
            <div class="text-center">
                <div class="character-bg" style="{{ $character->background ? 'background-image:url( ' . $character->background->imageUrl . ' )' : 'background-image:none' }}">
                    <div id="active-image">
                        <a href="{{ $character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)) ? $character->image->fullsizeUrl : $character->image->imageUrl }}"
                            data-lightbox="entry" data-title="{{ $character->fullName }}">
                            <img src="{{ $character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)) ? $character->image->fullsizeUrl : $character->image->imageUrl }}"
                                class="image" alt="{{ $character->fullName }}" />
                        </a>
                    </div>
                </div>
            </div>
            @if ($character->image->canViewFull(Auth::user() ?? null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)))
                <div class="text-right">You are viewing the full-size image. <a href="{{ $character->image->imageUrl }}">View watermarked image</a>?</div>
            @endif
        </div>
        <div class="col-md-3">
            <div class="card mb-3 text-center">
                <div class="card-header">
                    <h3 class="mb-0">Status</h3>
                </div>
                <div class="card-body">
                    Test
                </div>
            </div>
            <!-- Edits -->
            @if ($character->images()->where('is_valid', 1)->whereNotNull('transformation_id')->exists())
                <div class="card mb-3 character-states text-center">
                    <div class="card-header d-flex align-items-center justify-content-center">
                        <h3 class="mb-0">Edits</h3>
                        {!! add_help('Click on a ' . __('transformations.transformation') . ' to view the image. If you don\'t see the ' . __('transformations.transformation') . ' you\'re looking for, it may not have been uploaded yet.') !!}
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs card-header-tabs">
                            @foreach ($character->images()->where('is_valid', 1)->get() as $image)
                                <li class="nav-item mb-2 w-100">
                                    <a class="rounded form-data-button {{ $image->id == $character->image->id ? 'active' : '' }}" data-toggle="tab" role="tab" data-id="{{ $image->id }}">
                                        <span class="h4">{{ $image->transformation_id ? $image->transformation->name : 'Main' }} {{ $image->transformation_info ? ' (' . $image->transformation_info . ')' : '' }}</span>
                                        <img src="{{ $image->thumbnailUrl }}" class="img-fluid" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="my-3 card">
        <div class="card-header">
            <h3>Cores</h3>
        </div>
        <div class="card-body">
            @if ($core_awards)
                <div class="row px-4">
                    @foreach($core_awards as $award)
                        <div class="col-md-2 text-center border rounded border-dark p-2">
                            <a href="{{ $award->getUrlAttribute() }}">
                                <img src="{{ $award->imageUrl }}" class="img-fluid" />
                                <h6>{{ $award->name }}</h6>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h3>Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @include('character._image_info', ['image' => $character->image])
                </div>
                <div class="col-md-6">
                    <div class="card mb-2">
                        <div class="card-header">
                            <h4>Familiars</h4>
                        </div>
                        <div class="card-body">
                            @if (count($image->character->pets))
                                <div class="row justify-content-center text-center">
                                    {{-- get one random pet --}}
                                    @php
                                        $pets = $image->character
                                            ->pets()
                                            ->orderBy('sort', 'DESC')
                                            ->limit(config('lorekeeper.pets.display_pet_count'))
                                            ->get();
                                    @endphp
                                    @foreach ($pets as $pet)
                                        @if (config('lorekeeper.pets.pet_bonding_enabled'))
                                            @include('character._pet_bonding_info', ['pet' => $pet])
                                        @else
                                            <div class="ml-2 mr-3">
                                                <img src="{{ $pet->pet->variantImage($pet->id) }}" style="max-width: 75px;" />
                                                <br>
                                                <span class="text-light badge badge-dark" style="font-size:95%;">{!! $pet->pet_name !!}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div class="ml-auto float-right mr-3">
                                        <a href="{{ $character->url . '/pets' }}" class="btn btn-outline-info btn-sm">View All</a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if (count($image->character->equipment()))
                        <div class="card mb-2">
                            <div class="card-header">
                                <h4>Gear</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-1 mt-4">
                                    <div class="mb-0">
                                        <h5>Equipment</h5>
                                    </div>
                                    <div class="text-center row">
                                        @foreach ($image->character->equipment()->take(5) as $equipment)
                                            <div class="col-md-2">
                                                @if ($equipment->has_image)
                                                    <img class="rounded" src="{{ $equipment->imageUrl }}" data-toggle="tooltip" title="{{ $equipment->equipment->name }}" style="max-width: 75px;" />
                                                @elseif($equipment->equipment->imageurl)
                                                    <img class="rounded" src="{{ $equipment->equipment->imageUrl }}" data-toggle="tooltip" title="{{ $equipment->equipment->name }}" style="max-width: 75px;" />
                                                @else
                                                    {!! $equipment->equipment->displayName !!}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="float-right">
                                        <a href="{{ $character->url . '/stats' }}">View All...</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if ($skills)
                        <div class="card mb-2">
                            <div class="card-header">
                                <h4>Skills</h4>
                            </div>
                            <div class="card-body">
                                <div class="row px-4">
                                    @foreach ($skills as $skill)
                                        <div class="p-2 pl-3 border-left border-secondary">
                                            <h5>{{ $skill->skill->name }}</h5>
                                            {!! $skill->skill->parsed_description !!}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Personality</h3>
        </div>
        <div class="card-body">
            @include('character._tab_notes', ['character' => $character])
        </div>
    </div>

    @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
        <div class="tab-pane fade" id="settings-{{ $character->slug }}">
            {!! Form::open(['url' => $character->is_myo_slot ? 'admin/myo/' . $character->id . '/settings' : 'admin/character/' . $character->slug . '/settings']) !!}
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $character->is_visible, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Turn this off to hide the character. Only mods with the Manage Masterlist power (that\'s you!) can view it - the owner will also not be able to see the character\'s page.') !!}
            </div>
            <div class="text-right">
                {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
            <hr />
            <div class="text-right">
                <a href="#" class="btn btn-outline-danger btn-sm delete-character" data-slug="{{ $character->slug }}">Delete</a>
            </div>
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    @include('character._image_js', ['character' => $character])
    @include('character._transformation_js')
@endsection
