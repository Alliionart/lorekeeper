@extends('cultivation.layout')

@section('cultivation-title') {{ $userArea->area->name }} @endsection

@section('cultivation-content')
{!! breadcrumbs([ucfirst(__('cultivation.cultivation')) => __('cultivation.cultivation'), $userArea->area->name => $userArea->area->idUrl]) !!}


<h1>
    {{ $userArea->area->name }}
    @if(Settings::get('cultivation_area_unlock') > 0)<a href="#" class="btn btn-outline-danger float-right abandon-area-button">Abandon Area</a> @endif
</h1>

<div class="justify-content-center">

        <div class="row d-flex align-items-end text-center justify-content-center" style="background:url('{{$userArea->area->backgroundImageUrl}}'); background-repeat:no-repeat; background-size:cover; min-height:450px;">
            @foreach(range(1 , $userArea->area->max_plots) as $i)
            @php $userPlot = \App\Models\User\UserPlot::where('plot_number', $i)->where('user_id', $user->id)->where('user_area_id', $userArea->id)->first() @endphp
            <div class="col-md-3 col-6 mb-2">
                <div class="row justify-content-center">
                    <img class="plot-button" src="{{ (isset($userPlot)) ? $userPlot->getStageImage($userPlot->stage) : $userArea->area->plotImageUrl }}" data-id="{{ $i }}" style="width:100%;max-width:250px;" alt="plot" />
                </div>
                <div class="row justify-content-center">
                    <button class="plot-button btn btn-primary btn-sm" data-id="{{ $i }}">Plot {{ $i }}</button>
                </div>
            </div>
            @endforeach
        </div>
        @if(Settings::get('cultivation_care_cooldown') > 0) <h4><span class="float-right badge badge-secondary m-2">Plots tended to: {{$caredPlots ?? 0}} / {{Settings::get("cultivation_care_cooldown")}}</span></h4> @endif

        <div class="p-5 m-auto mt-5">{!! $userArea->area->parsed_description !!}</div>

</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.plot-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('cultivation/'.$userArea->area->id) }}/" + $(this).data('id'), 'Plot ' + $(this).data('id'));
        });

        $('.abandon-area-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('cultivation/area/delete/'.$userArea->id) }}", 'Abandon Area');
        });
    });

</script>
@endsection
