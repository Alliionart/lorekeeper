<h3>Stages</h3>
<div class="row mt-3 mb-3">
    <div class="col">
        {!! Form::label('Stage 2 days') !!} {!! add_help('How many days does stage 2 last?') !!}
        {!! Form::number('stage_2_days', isset($tag->getData()['stage_2_days'])? $tag->getData()['stage_2_days'] : 2,  ['class' => 'form-control']) !!}  
    </div>
    <div class="col">
        {!! Form::label('Stage 3 days') !!} {!! add_help('How many days does stage 3 last?') !!}
        {!! Form::number('stage_3_days', isset($tag->getData()['stage_3_days'])? $tag->getData()['stage_3_days'] : 3,  ['class' => 'form-control']) !!}  
    </div>
    <div class="col">
        {!! Form::label('Stage 4 days') !!} {!! add_help('How many days does stage 4 last?') !!}
        {!! Form::number('stage_4_days', isset($tag->getData()['stage_4_days'])? $tag->getData()['stage_4_days'] : 4,  ['class' => 'form-control']) !!}  
    </div>
</div>


<h3>Rewards</h3>

<p>These are the rewards that will be distributed to the user when they harvest from a stage 5 plot that had this seed planted.</p>

@include('widgets._loot_select', ['loots' => $tag->getData()['rewards'] ?? null, 'showLootTables' => true, 'showRaffles' => true])


<h3>
Stage 5 Image (Optional)
</h3>
<p>
    Upload an image for last stage (harvest stage) if you wish. Otherwise, the plots stage 5 image will be used as a default.
</p>

<div class="col-8 mx-auto">
    @if($tag->data && isset($tag->getData()['stage_5_image']))
        <img src="{{ url($tag->getData()['stage_5_image']) }}" class="img-fluid mb-2"/>
        {!! Form::file('image') !!}
    @else
        {!! Form::file('image') !!}
    @endif
</div>

