

<div class="row justify-content-center">
        <img src="{{ (!isset($userPlot)) ? $userArea->area->plotImageUrl : $userPlot->getStageImage($userPlot->stage) }}" style="width:100%;max-width:250px;" alt="plot" />
</div>

<div class="row justify-content-center">

    @if(!isset($userPlot))
    <div class="alert alert-warning" role="alert">
    This plot is not ready for cultivation yet. You will have to prepare it with a tool!
    </div>
    @endif
    @if(isset($userPlot) && $userPlot->stage <= 1)
    <div class="alert alert-success" role="alert">
    This plot is ready for cultivation! 
    </div>
    @endif
    @if(isset($userPlot) && $userPlot->stage > 1 && $userPlot->stage < 5)
    <div class="alert alert-success" role="alert">
    This plot is actively cultivating something...make sure to check on it every day!
    </div>
    @endif
    @if(isset($userPlot) && $userPlot->stage == 5)
    <div class="alert alert-success" role="alert">
    This plot seems about ready for collecting your rewards!
    </div>
    @endif
</div>

@if(isset($userPlot))
<div class="row justify-content-center p-3">
    <table class="table table-striped table-sm">
        <tbody>
            <tr>
            <th scope="row">Plot Type</th>
            <td>{{ $userPlot->plot->name }}</td>
            </tr>
            <tr>
            <th scope="row">Cultivated Item</th>
            <td>{{ $userPlot->item->name ?? 'None'}}</td>
            </tr>
            <tr>
            @if($userPlot->stage >= 2)
            <th scope="row">Stage</th>
            <td>{{ $userPlot->stage}}/5</td>
            </tr>
            <tr>
            <th scope="row">Progress</th>
            <td>{{ $userPlot->counter }}/{{ $userPlot->getStageProgress() }}</td>
            </tr>
            @endif
        </tbody>
    </table>
</div>
@endif

<div class="card">
    <ul class="list-group list-group-flush">
        @if(isset($userPlot))
        @if($userPlot->stage == 5)
        <li class="list-group-item">
            <a class="card-title h5 collapse-title" data-toggle="collapse" href="#harvestForm">Harvest</a>
            <div id="harvestForm" class="collapse">
                <p>It looks like your work bears fruit. Collect it here.</p>
                {!! Form::open(['url' => 'cultivation/plots/harvest/'.$userPlot->id]) !!}
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::submit('Harvest', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </li>
        @endif
        @if($userPlot->stage > 1 && $userPlot->stage < 5)
        <li class="list-group-item">
            <a class="card-title h5 collapse-title" data-toggle="collapse" href="#tendForm">Tend to plot</a>
            <div id="tendForm" class="collapse">
                <p>Tend to the plot to help its growth. Each day you tend will progress the plot until one day you can harvest from it!</p>
                {!! Form::open(['url' => 'cultivation/plots/tend/'.$userPlot->id]) !!}
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::submit('Tend', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </li>
        @endif
        <li class="list-group-item">
            <a class="card-title h5 collapse-title" data-toggle="collapse" href="#cultivateForm">Cultivate</a>
            <div id="cultivateForm" class="collapse">
                <p>Place an item to cultivate something. You may place an item even if there is already something cultivating here, but you will lose its progress.</p>
                {!! Form::open(['url' => 'cultivation/plots/cultivate/'.$plotNumber]) !!}
                <div class="row justify-content-center">
                    <div class="col-md-10 text-center">
                        <div class="form-group">
                            {!! Form::select('seed_id', $userSeeds, null, ['class' => 'form-control', 'placeholder' => 'Select Item']) !!}
                            {!! Form::hidden('area_id', $userArea->id, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="form-group">
                            {!! Form::submit('Cultivate', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </li>
        @endif
        <li class="list-group-item">
            <a class="card-title h5 collapse-title" data-toggle="collapse" href="#prepareForm">Prepare Plot</a>
            <div id="prepareForm" class="collapse">
                <p>Prepare the plot by using a tool. You may also change the plot type by using a tool on an already prepared plot. However, you will lose its progress.</p>
                {!! Form::open(['url' => 'cultivation/plots/prepare/'.$plotNumber]) !!}
                <div class="row justify-content-center">
                    <div class="col-md-10 text-center">
                        <div class="form-group">
                            {!! Form::select('tool_id', $userTools, null, ['class' => 'form-control', 'placeholder' => 'Select Tool']) !!}
                            {!! Form::hidden('area_id', $userArea->id, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="form-group">
                            {!! Form::submit('Prepare Plot', ['class' => 'btn btn-primary']) !!}
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </li>

    </ul>
</div>


