@extends('cultivation.layout')

@section('cultivation-title') {{ucfirst(__('cultivation.cultivation'))}} @endsection

@section('cultivation-content')
{!! breadcrumbs([ucfirst(__('cultivation.cultivation')) => ucfirst(__('cultivation.cultivation'))]) !!}

<div class="row">
    <div class="col">
    <h1>{{ucfirst(__('cultivation.cultivation'))}}</h1>
    </div>
    <div class="col">
    @if(Settings::get('cultivation_care_cooldown') > 0) <h4><span class="float-right badge badge-secondary m-2">Plots tended to: {{$caredPlots ?? 0}} / {{Settings::get("cultivation_care_cooldown")}}</span></h4> @endif
    </div>
</div>

<div class="row shops-row">
    @foreach($areas as $area)
        <div class="col-md-6 col-12 mb-3 text-center">
            <div class="card h-100">
                <img class="card-img-top" src="{{$area->backgroundImageUrl}}" alt="{{$area->name}}">
                <div class="card-header"><h2 class="p-0 m-0">{{$area->name}} @if(!isset($user) || !in_array($area->id, $user->areas->pluck('id')->toArray()))<i class="fa fa-lock"></i> @else <i class="fa fa-unlock"></i>  @endif</h2></div>
                <div class="card-body">
                    
                    <p class="card-text">{!! $area->parsed_description !!}</p>
                    <hr>
                    @if(isset($user) && in_array($area->id, $user->areas->pluck('id')->toArray()))
                    <a class="btn btn-primary" href="{{ $area->idUrl }}" class="h5 mb-0">Go here!</a>
                    @else
                    <small>To unlock an area, you will need to earn the related exploration item!</small>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
