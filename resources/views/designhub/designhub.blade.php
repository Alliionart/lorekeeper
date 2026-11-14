@extends('layouts.app')


@section('title')
    Design Hub
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'Design Hub']) !!}
    <h1>Design Hub</h1>

    @if ($dh_start && $dh_start->is_visible)
        <div class="card dh-section mb-3">
            <div class="card-header">
                {!! $dh_start->title !!}
            </div>
            <div class="card-body">
                {!! $dh_start->text !!}
            </div>
        </div>
    @elseif(!$dh_start && Auth::user()->isStaff)
        <div class="alert alert-warning" role="alert">
            Page for design hub start is missing. Please run <code>php artisan add-text-pages</code>. This message is only visible to staff.
        </div>
    @endif

    <div class="row dh-nav mb-2">
        <div class="col-12 col-md-3 d-flex mb-2 align-items-center"><a href="#markings" class="btn btn-primary">Markings</a></div>
        <div class="col-12 col-md-3 d-flex mb-2 align-items-center"><a href="#traitguides" class="btn btn-primary">Trait Guides</a></div>
        <div class="col-12 col-md-3 d-flex mb-2 align-items-center"><a href="#importtemplates" class="btn btn-primary">Import Templates</a></div>
        <div class="col-12 col-md-3 d-flex mb-2 align-items-center"><a href="{{ url('design-hub/base-coats') }}" class="btn btn-primary">Base Coats</a></div>
    </div>

    <div class="card dh-section mb-4" id="markings">
        <div class="card-header">
            Markings
        </div>
        <div class="card-body">
            <input type="text" placeholder="Search markings by name or code..." class="searchBar rounded mb-3 form-control" data-id="markingSearch" />

            @if ($rarity_list)
                @foreach ($markings as $rarityId => $markingItems)
                    <div class="card rounded {{ $loop->last ? '' : 'mb-3' }}">
                        <div class="card-header"><span class="rarity-indicator" style="background-color:#{{ $rarity_list[$rarityId]->color }}"></span> {{ $rarity_list[$rarityId]->name }} Markings</div>
                        <div class="p-2">
                            <div class="row no-gutters flex-gap searchContent" data-id="markingSearch">
                                @foreach ($markingItems as $marking)
                                    @include('designhub._entry', [
                                        'imageUrl' => $marking->imageUrl ?? '/images/account.png',
                                        'name' => $marking->name . ' (' . $marking->recessive . '/' . $marking->dominant . ')',
                                        'description' => $marking->short_description,
                                        'url' => $marking->url,
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="card dh-section rounded mb-4" id="traitguides">
        <div class="card-header">
            Trait Guides
        </div>
        <div class="card-body">
            <input type="text" placeholder="Search traits..." class="searchBar rounded mb-3 form-control" data-id="traits" />
            <div class="card rounded">
                <div class="card-header">Trait Guides</div>
                <div class="p-2">
                    @if ($trait_lists)
                        {!! $trait_lists->render() !!}
                        <div class="row no-gutters flex-gap searchContent" data-id="traits">
                            @foreach ($trait_lists as $trait)
                                @include('designhub._entry', [
                                    'imageUrl' => $trait->imageUrl ?? '/images/account.png',
                                    'name' => $trait->name,
                                    'description' => preg_match('/^<p(?:\s[^>]*)?>(.*?)<\/p>/i', $trait->description, $short_description) ? strip_tags($short_description[0]) : '',
                                    'url' => $trait->url ?: '/world/traits?name=' . $trait->name,
                                ])
                            @endforeach
                        </div>
                        {!! $trait_lists->render() !!}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card dh-section mb-4" id="importtemplates">
        <div class="card-header">
            Import Templates
        </div>
        <div class="card-body">
            @foreach ($specieses as $species)
                <div class="card {{ $loop->last ? '' : 'mb-3' }}">
                    <div class="card-header">{!! $species->displayName !!} Templates</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap flex-column justify-content-between">
                            @foreach ($subtypes as $subtype)
                                @if ($subtype->species_id === $species->id)
                                    <div class="card item flex-fill {{ $loop->last ? '' : 'mb-3' }}">
                                        <div class="card-body">
                                            @if ($subtype->subtypeImageUrl)
                                                <a href="{{ $subtype->subtypeImageUrl }}" data-lightbox="entry" data-title="{{ $subtype->name }}">
                                                    <img src="{{ $subtype->subtypeImageUrl }}" class="world-entry-image" alt="{{ $subtype->name }}" />
                                                </a>
                                            @endif
                                            <h3>{!! $subtype->displayName !!}</h3>
                                            <div>{!! $subtype->description !!}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($dh_end && $dh_end->is_visible)
        <div class="card dh-section">
            <div class="card-header">
                {!! $dh_end->title !!}
            </div>
            <div class="card-body">
                {!! $dh_end->text !!}
            </div>
        </div>
    @elseif(!$dh_end && Auth::user()->isStaff)
        <div class="alert alert-warning" role="alert">
            Page for design hub end is missing. Please run <code>php artisan add-text-pages</code>. This message is only visible to staff.
        </div>
    @endif


@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.searchBar').on('keyup', function(e) {
                var sTerm = $(this).val().toLowerCase();
                var type = $(this).attr('data-id');
                $('.searchContent[data-id="' + type + '"]').children().each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(sTerm) > -1);
                });
            });
        });
    </script>
@endsection
