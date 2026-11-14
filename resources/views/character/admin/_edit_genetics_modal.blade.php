{!! Form::open(['url' => $isMyo ? 'admin/myo/' . $character->id . '/genetics' : 'admin/character/' . $character->slug . '/genetics']) !!}

@include('widgets._markingbases', [
    'is_chimera' => $is_chimera,
    'bases' => $bases,
    'base' => $is_chimera ? explode('|', $character->base_id)[0] : $character->base_id,
    'secondary_base' => $is_chimera ? explode('|', $character->base_id)[1] : null,
    'characterMarkings' => $characterMarkings,
    'markings' => $markings,
])

<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@include('widgets._markingbases_row', ['markings' => $markings])

@include('widgets._markingbases_js')
