@extends('cultivation.layout')

@section('cultivation-title') {{ucfirst(__('cultivation.cultivation'))}} @endsection

@section('cultivation-content')
{!! breadcrumbs([ucfirst(__('cultivation.cultivation')) => ucfirst(__('cultivation.cultivation'))]) !!}

<div class="row">
    <div class="col">
        <h1>{{ucfirst(__('cultivation.cultivation'))}} Guide</h1>
    </div>
</div>


<div class="row shops-row">
    @foreach($areas as $area)
    <div class="col-12 mb-3 text-center">
        <div class="card h-100">
            <div class="row m-0 card-header">
                <div class="col-lg-2 col"><img class="" src="{{$area->backgroundImageUrl}}" alt="{{$area->name}}" style="max-height:100px;"></div>
                <div class="col-lg-9 col d-flex align-items-center">
                    <h4 class="p-0 m-0">{{$area->name}}</h4>
                </div>
            </div>
            <div class="card-body row">
                @foreach($area->allowedPlots as $plot)
                <div class="col-lg-6 col-12 mb-3 text-center">
                    <div class="card h-100">
                        <div class="row m-0 card-header">
                            <h4 class="p-0 m-0">{{$plot->name}}</h4>
                        </div>
                        <div class="card-body text-justify">
                            <div class="row m-0">
                                <div class="col-lg-4 col-12 text-center"><img class="" src="{{$plot->getStageImage(4)}}" alt="{{$plot->name}}" style="max-height:200px;"></div>
                                <div class="col-lg-7 col-12">
                                    <b> Created with: </b>
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach(App\Models\Item\ItemTag::where('tag', 'tool')->where('data', 'like', '%"plot_id":"'.$plot->id.'"%')->get() as $tag) 
                                            <tr>
                                                <td> {!! $tag->item->displayName !!} </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <b> Can Cultivate: </b>
                                    <table class="table table-sm">
                                        <tbody>
                                            @foreach($plot->allowedItems as $item)
                                            <tr>
                                                <td> {!! $item->displayName  !!} </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                @endforeach

            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection