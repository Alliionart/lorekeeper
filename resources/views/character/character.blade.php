@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection


@section('profile-content')
    <div class="page-header">
        @if ($character->is_myo_slot)
            {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url]) !!}
        @else
            {!! breadcrumbs([
                $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
                $character->fullName => $character->url,
            ]) !!}
        @endif

        @include('character._header', ['character' => $character])
    </div>


    {{-- Main Image --}}
    <div class="row mb-3" id="main-tab">
        <div class="col-md-8 pl-5 mt-5">
            <div class="text-center">
                @if ($character->images()->where('is_valid', 1)->whereNotNull('transformation_id')->exists())
                    <div class="card-header mb-2">
                        <ul class="nav nav-tabs card-header-tabs">
                            @foreach ($character->images()->where('is_valid', 1)->get() as $image)
                                <li class="nav-item">
                                    <a class="nav-link form-data-button {{ $image->id == $character->image->id ? 'active' : '' }}" data-toggle="tab" role="tab" data-id="{{ $image->id }}">
                                        {{ $image->transformation_id ? $image->transformation->name : 'Main' }} {{ $image->transformation_info ? ' (' . $image->transformation_info . ')' : '' }}
                                    </a>
                                </li>
                            @endforeach
                            <li>
                                <h3>{!! add_help('Click on a ' . __('transformations.transformation') . ' to view the image. If you don\'t see the ' . __('transformations.transformation') . ' you\'re looking for, it may not have been uploaded yet.') !!}</h3>
                            </li>
                        </ul>
                    </div>
                @endif
                <a href="{{ $character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)) ? $character->image->fullsizeUrl : $character->image->imageUrl }}"
                    data-lightbox="entry" data-title="{{ $character->fullName }}">
                    <img src="{{ $character->image->canViewFull(Auth::check() ? Auth::user() : null) && file_exists(public_path($character->image->imageDirectory . '/' . $character->image->fullsizeFileName)) ? $character->image->fullsizeUrl : $character->image->imageUrl }}"
                        class="image main-image rounded p-4" alt="{{ $character->fullName }}" />
                </a>
            </div>
        </div>

        <div class="col-md-4 character-sidebar mt-5 pr-5">
            @if (!$character->is_myo_slot && config('lorekeeper.extensions.previous_and_next_characters.display') && isset($extPrevAndNextBtnsUrl))
                @if ($extPrevAndNextBtns['prevCharName'] || $extPrevAndNextBtns['nextCharName'])
                    <div class="row mb-4">
                        <div class="col-md-12 text-right">
                            @if ($extPrevAndNextBtns['prevCharName'])
                                <a class="btn prev" href="{{ $extPrevAndNextBtns['prevCharUrl'] }}{!! $extPrevAndNextBtnsUrl !!}">
                                    <i class="fas fa-caret-left"></i> PREV: {!! $extPrevAndNextBtns['prevCharName'] !!}
                                </a>
                            @endif
                            @if ($extPrevAndNextBtns['nextCharName'])
                                <a class="btn next" href="{{ $extPrevAndNextBtns['nextCharUrl'] }}{!! $extPrevAndNextBtnsUrl !!}">
                                    NEXT: {!! $extPrevAndNextBtns['nextCharName'] !!} <i class="fas fa-caret-right"></i><br />
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
            <!-- Tab Group -->
            @include('character._image_info', ['image' => $character->image])
        </div>

        {{-- Info --}}
        <div class="col-md-12 mt-4 character-rest">
            <div class="px-5">
                <!-- profile content -->
                @if ($character->profile->parsed_text)
                    <div class="card mb-3">
                        <div class="card-body parsed-text">
                            {!! $character->profile->parsed_text !!}
                        </div>
                    </div>
                    <div class="row my-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">@include('character._tab_skills', ['character' => $character, 'skills' => $skills])</div>
                            </div>
                        </div>
                    </div>
                @endif
                <!-- Sidebar Tabs -->
                <div class="card character-bio">
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                            <li class="nav-item">
                                <a class="nav-link active" id="statsTab" data-toggle="tab" href="#stats" role="tab">Stats</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="notesTab" data-toggle="tab" href="#notes" role="tab">Description</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="skillsTab" data-toggle="tab" href="#skills" role="tab">Skills</a>
                            </li>
                            @if (Auth::check() && Auth::user()->hasPower('manage_characters'))
                                <li class="nav-item">
                                    <a class="nav-link" id="settingsTab" data-toggle="tab" href="#settings-{{ $character->slug }}" role="tab"><i class="fas fa-cog"></i></a>
                                </li>
                            @endif
                        </ul>
                    </div>
                    <div class="card-body tab-content">
                        <div class="tab-pane fade show active" id="stats">
                            @include('character._tab_stats', ['character' => $character])
                        </div>
                        <div class="tab-pane fade" id="notes">
                            @include('character._tab_notes', ['character' => $character])
                        </div>
                        <div class="tab-pane fade" id="skills">

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
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @section('scripts')
        @parent
        @include('character._image_js', ['character' => $character])
        @include('character._transformation_js')
    @endsection