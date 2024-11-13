<li class="list-group-item">
    <a class="card-title h5 collapse-title"  data-toggle="collapse" href="#openBoxForm"> Explore Area</a>
    <div id="openBoxForm" class="collapse">
        {!! Form::hidden('tag', $tag->tag) !!}
        <p>This action is not reversible. Are you sure you want to explore this area and unlock it for cultivation?</p>
        <div class="text-right">
            {!! Form::button('Explore', ['class' => 'btn btn-primary', 'name' => 'action', 'value' => 'act', 'type' => 'submit']) !!}
        </div>
    </div>
</li>