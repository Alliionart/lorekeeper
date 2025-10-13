<h3>Applicable Pet Categories</h3>

<p>For each trap, what pet categories can this trap be used to remove from a character.</p>

<div class="input-group mb-4">
    <div class="input-group-prepend">
        <span class="input-group-text">Category</span>
    </div>
    {!! Form::select('pet_category', $pet_categories, $tag->getData() ? $tag->getData()['pet_category'] : null, ['class' => 'form-control', 'aria-label' => 'Pet Category', 'placeholder' => 'Select Category']) !!}
    {!! Form::number('chance', $tag->getData() ? $tag->getData()['chance'] : 100, ['class' => 'form-control', 'aria-label' => 'Chance to Return Pet', 'min' => 0, 'max' => 100]) !!}
    <div class="input-group-append">
        <span class="input-group-text">Chance to Return Pet (%)</span>
    </div>
</div>
