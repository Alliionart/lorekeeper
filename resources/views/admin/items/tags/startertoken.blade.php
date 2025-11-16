<h3>Applicable Character Categories</h3>

<p>What character categories are applicable to this starter token?</p>

<div class="form-group mb-4">
    {!! Form::label('Applicable Character Categories') !!}
    {!! Form::select('character_categories[]', $character_categories, $tag->getData() ? $tag->getData() : null, [
        'class' => 'form-control selectize', 
        'multiple', 
        'aria-label' => 'Character Categories', 
        'placeholder' => 'Select one or more categories'
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
