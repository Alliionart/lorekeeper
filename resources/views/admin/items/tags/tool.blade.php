<h3>Plot</h3>
<div class="row">
    <div class="col">
        {!! Form::label('Unlock Plot') !!} {!! add_help('Choose the plot that should be unlocked once the user utilizes this tool on an empty/unusable plot.') !!}
    </div>
    <div class="col">
        {{ Form::select('plot_id', $plots, $tag->getData()['plot_id'] ?? 0, ['class' => 'form-control mr-2', 'placeholder' => 'Select Plot']) }}
    </div>
</div>