@if (!$recipe)
    <div class="text-center">Invalid recipe selected.</div>
@else
    @if ($recipe->imageUrl)
        <div class="text-center">
            <div class="mb-3"><img class="recipe-image" src="{{ $recipe->imageUrl }}" /></div>
        </div>
    @endif
    <div id="recipeDetails">
        <div class="row" id="recipeDetails">
            @if ($recipe->is_limited)
                <div class="col-md-12">
                    <h5>Ingredients</h5>

                    <div class="alert alert-warning">
                        <?php
                        $limits = [];
                        foreach ($recipe->limits as $limit) {
                            $name = $limit->reward->name;
                            $quantity = $limit->quantity > 1 ? $limit->quantity . ' ' : '';
                            $limits[] = $quantity . $name;
                        }
                        echo implode(', ', $limits);
                        ?>
                    </div>
                </div>
            @endif
            <div class="col-md-12">
                <h5>Ingredients</h5>
                <div class="ingredient-row d-flex justify-content-center align-items-start justify-content-center">
                    @foreach ($recipe->ingredients as $ingredient)
                        <div class="ingredient d-flex flex-column align-items-center">
                            @include('home.crafting._recipe_ingredient_entry', ['ingredient' => $ingredient])
                        </div>
                    @endforeach
                </div>
            </div>
            <i class="fa fa-chevron-down text-center special mx-auto my-4" aria-hidden="true"></i>
            <div class="col-md-12 mt-4">
                <div class="reward-row d-flex flex-column align-items-center">
                    <h5>Rewards</h5>
                    @foreach ($recipe->reward_items as $type)
                        @foreach ($type as $item)
                            <div class="ingredient d-flex flex-column align-items-center">
                                @include('home.crafting._recipe_reward_entry', ['reward' => $item])
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @if ($selected || $recipe->onlyCurrency)
        {{-- Check if sufficient ingredients have been selected? --}}
        {!! Form::open(['url' => 'crafting/craft/' . $recipe->id]) !!}
        <div class="d-flex submit-craft justify-content-center align-content-center">
            <div class="d-flex qtybx align-items-center">
                <label for="craftQty">QTY</label>
                <input type="number" id="craftQty" min="1" max="100" value="1"></input>
            </div>
            {!! Form::submit('Craft', ['class' => 'btn btn-primary']) !!}
        </div>
        @include('widgets._inventory_select', ['user' => Auth::user(), 'inventory' => $inventory, 'categories' => $categories, 'selected' => $selected, 'page' => $page])
        {!! Form::close() !!}
    @else
        <div class="alert alert-danger">You do not have all of the required recipe ingredients.</div>
    @endif
@endif

@include('widgets._inventory_select_js')
<script>
    $(document).keydown(function(e) {
        var code = e.keyCode || e.which;
        if (code == 13)
            return false;
    });

    $('#craftQty').on('change', function() {
        let qty = 1;
        qty = $(this).val();

        $('#recipeDetails .qty span').each(function(index, element) {
            let base = $(this).parent().attr('base');
            let newQty = base * qty;
            let itemName = $(this).parent().attr('item');
            $(this).text('x' + newQty);
            $('.reward-row .ingredient .qty').text('x' + qty);

            if(newQty > result[itemName]) {
                //If greater than the current inventory amount
                console.log('NOT ENOUGH!')
                $('.reward-row .ingredient .qty').css('color', 'rgb(255 80 80)');
                $(this).css('color', 'rgb(255 80 80)');
                $('.btn[type=submit]').prop('disabled', true);
            } else if (newQty <= result[itemName]) {
                //If less than or eqal to the current inventory amount
                $('.reward-row .ingredient .qty').css('color', '');
                $(this).css('color', '');
                $('.btn[type=submit]').prop('disabled', false);
            }
        });
    });
</script>
