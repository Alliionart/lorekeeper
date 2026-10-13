<h3>Applicable Character Categories</h3>

<p>What characters are applicable to this starter token?</p>

<div class="form-group mb-4">
    {!! Form::label('Applicable Characters') !!}
    {!! Form::select('characters[]', $characters, $tag->getData() ? $tag->getData() : null, [
        'class' => 'form-control selectize',
        'multiple',
        'aria-label' => 'Character',
        'placeholder' => 'Select one or more characters',
    ]) !!}
</div>

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize({
                multiple: true
            });
        });
    </script>
@endsection
