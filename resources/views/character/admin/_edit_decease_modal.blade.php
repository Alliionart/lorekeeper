{!! Form::open(['url' => 'admin/character/' . $character->id . '/decease']) !!}
<p class="alert alert-danger">Deceasing this character will render them useless in gameplay. Are you sure you want to do this?</p>
<a href="#" class="decease-confirmation btn btn-warning">Yes, I'm sure</a>
<div class="text-right submit-group" style="display: none;">
    {!! Form::submit('Decease this Character', ['class' => 'btn btn-danger']) !!}
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        $('.decease-confirmation').on('click', function(e) {
            e.preventDefault();
            $('.submit-group').show();
            $(this).remove();
        });
    });
</script>
