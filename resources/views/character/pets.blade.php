@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    {{ $character->fullName }}'s Familiars
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Familiars' => $character->url . '/pets']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Pets' => $character->url . '/pets',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    <h1>Familiars</h1>

    @if (Auth::check() && (Auth::user()->id == $character->user_id || Auth::user()->hasPower('manage_characters')))
        <p>
            Currently up to {{ config('lorekeeper.pets.display_pet_count') }} familiar{{ config('lorekeeper.pets.display_pet_count') != 1 ? 's' : '' }} are displayed on the character's page.
            <br />You can determine which familiar are displayed by dragging and dropping them in the order you want.
        </p>


        {!! Form::open(['url' => 'characters/' . $character->slug . '/pets/sort', 'class' => 'text-right']) !!}
        {!! Form::hidden('sort', null, ['id' => 'sortableOrder']) !!}
        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
        {!! Form::close() !!}
    @endif

    <div id="sortable" class="row mt-4 sortable justify-content-center">
        @foreach ($character->pets()->orderBy('sort', 'DESC')->get() as $pet)
            <div class="col-md-12 col-12 mb-3" data-id="{{ $pet->id }}">
                <div class="card mb-3 inventory-category h-100" data-id="{{ $pet->id }}">
                    <div class="card-body inventory-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <a href="{{ $pet->pageUrl() }}" class="inventory-stack">
                                    <img src="{{ $pet->pet->variantImage($pet->id) }}" class="rounded img-fluid" />
                                </a>
                            </div>
                            <div class="col-md-9">
                                @if ($pet->pet->category)
                                    <p>{!! $pet->pet->category->displayName !!}</p>
                                    <hr/>
                                @endif
                                <div class="mb-2 h4">
                                    @if ($pet->pet_name)
                                        <a href="{{ $pet->pageUrl() }}">{!! $pet->pet_name !!}</a> the
                                    @endif
                                    {!! $pet->pet->displayName !!} {!!  $pet->level ? '('.$pet->level->levelName.')' : '' !!}
                                </div>
                                <p><strong>Bonded on: </strong> {{ date('M jS, Y', strtotime($pet->attached_at)) }}</p>
                                @if ($pet->pet->variant)
                                    <p><strong>Variant: </strong> {{ $pet->pet->variant->name }}</p>
                                @endif

                                @if (config('lorekeeper.pets.pet_bonding_enabled'))
                                    @if ($pet->pet->category->name === 'Legendary')
                                        <div class="progress mb-2">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                                style="width: {{ ($pet->level?->nextLevel?->bonding_required ? ($pet->level?->bonding / $pet->level?->nextLevel?->bonding_required) * 100 : 100) . '%' }}" aria-valuenow="{{ $pet->level?->bonding }}" aria-valuemin="0"
                                                aria-valuemax="{{ $pet->level?->nextLevel?->bonding_required ?? 100 }}">
                                                {{ $pet->level?->nextLevel?->bonding_required ? $pet->level?->bonding . '/' . $pet->level?->nextLevel?->bonding_required : $pet->level?->levelName }}
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                {{ $pet->level?->bonding_required }}
                            </div>
                        </div>                   
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $("#sortable").sortable({
                characters: '.sort-item',
                placeholder: "sortable-placeholder col-md-3 col-6",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
