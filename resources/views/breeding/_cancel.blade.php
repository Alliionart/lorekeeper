<p>This will cancel the breeding request, returning it to draft form and allowing the user to edit it again.</p>
{!! Form::open(['url' => 'breeding/' . $breeding->id . '/cancel']) !!}
<div class="text-right">
    {!! Form::submit('Cancel Request', ['class' => 'btn btn-secondary']) !!}
</div>
{!! Form::close() !!}
