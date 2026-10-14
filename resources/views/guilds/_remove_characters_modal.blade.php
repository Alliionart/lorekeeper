{!! Form::open(['url' => $guild->viewUrl . '/characters/remove']) !!}
<div class="form-group">
    {!! Form::label('characters[]', 'Characters to Remove') !!}
    {!! Form::select('characters[]', $characters, null, ['class' => 'form-control selectize', 'multiple', 'placeholder' => 'Select characters...']) !!}
</div>
<div class="text-right">
    {!! Form::submit('Remove Character(s)', ['class' => 'btn btn-danger']) !!}
</div>
{!! Form::close() !!}
<script>
    $(document).ready(function() {
        $('.selectize').selectize();
    });
</script>
