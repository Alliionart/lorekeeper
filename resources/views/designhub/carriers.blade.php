@extends('layouts.app')


@section('title')
    Carriers
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'design-hub', 'Carriers' => 'Carriers']) !!}
    <h1>Carriers</h1>
    <a class="btn btn-primary" href="/design-hub/" role="button">Back to Design Hub</a>

    @if ($carrier_info)
        <div class="w-100 mt-4">
            {!! $carrier_info->text !!}
        </div>
    @endif

    <div class="card rounded my-4">
        <div class="card-body">
            <input type="text" placeholder="Search carriers..." class="searchBar bg-dark rounded border-0 mb-4 form-control" data-id="carrierSearch" />

            @foreach ($rarities as $rarity)
                @if (array_key_exists($rarity->id, $carriers))
                    <div class="card mb-3">
                        <div class="card-header">
                            <h3><span class="rarity-indicator" style="background-color:#{{ $rarity->color }}"></span> {{ $rarity->name }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between searchContent carrier-card-container" data-id="carrierSearch">
                                @foreach ($carriers[$rarity->id] as $carrier)
                                    <div class="card w-100 mb-2">
                                        <div class="card-header">
                                            <h5 class="mb-0 flex-grow-1" style="width:100%;"> {{ $carrier['name'] }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <p><strong>Affected Markings: </strong>{!! implode(', ', $carrier['markings']) !!}</p>
                                            {!! $carrier['description'] !!}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
            @if ($carriers['Special'])
                <div class="card mb-3">
                    <div class="card-header">
                        <h3><span class="rarity-indicator" style="background-color:yellow"></span> Special</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap justify-content-between searchContent carrier-card-container" data-id="carrierSearch">
                            @foreach ($carriers['Special'] as $carrier)
                                <div class="card w-100 mb-2">
                                    <div class="card-header">
                                        <h5 class="mb-0 flex-grow-1" style="width:100%;"> {{ $carrier['name'] }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>Affected Markings: </strong>{!! implode(', ', $carrier['markings']) !!}</p>
                                        {!! $carrier['description'] !!}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.searchBar').on('keyup', function(e) {
                var sTerm = $(this).val().toLowerCase();
                var type = $(this).attr('data-id');
                console.log(type)
                console.log(sTerm);
                $('.searchContent[data-id="' + type + '"]').children().each(function() {
                    console.log($(this))
                    $(this).toggle($(this).text().toLowerCase().indexOf(sTerm) > -1);
                });
            });
        });
    </script>
@endsection
