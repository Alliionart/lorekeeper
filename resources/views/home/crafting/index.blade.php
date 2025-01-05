@extends('home.layout')

@section('home-title')
    Crafting
@endsection

@section('home-content')
    {!! breadcrumbs(['Crafting' => 'crafting']) !!}

    <h1>
        My Recipe Book
    </h1>
    <div class="float-right mb-4">
        <a href="{{ url(Auth::user()->url . '/recipe-logs') }}">View logs...</a>
    </div>
    <p> This is a list of recipes that you have unlocked, as well as automatically unlocked recipes. </p>

    <hr>

    <div class="row">
        <div class="col-md-3">
            <div class="card recipe-scrollbox">
                <input type="text" class="form-input text-left" placeholder="Search Recipes..." id="filterRecipes" />
                <div class="recipe-innerscroll">
                    <div id="accordion">
                        <div class="card top">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                        Free Recipes
                                    </button>
                                </h5>
                            </div>

                            <div id="collapseOne" class="collapse show py-2" aria-labelledby="headingOne" data-parent="#accordion">
                                @if ($default->count())
                                    <div class="d-flex align-items-stretch recipes-body justify-content-between flex-wrap">
                                        @foreach ($default as $recipe)
                                            @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                                        @endforeach
                                    </div>
                                @else
                                    There are no free recipes.
                                @endif
                            </div>
                        </div>
                        <div class="card top">
                            <div class="card-header" id="headingTwo">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                        Unlocked Recipes
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                                @if (Auth::user()->recipes->count())
                                    <div class="card nestedAccordions">
                                        @foreach ($userRecipes as $categoryId => $categoryrecipes)
                                            <!-- Accordion Test -->
                                            <div class="accordion w-100" id="accordionNested">
                                                <div class="card">
                                                    <div class="card-header" id="heading{!! isset($categories[$categoryId]) !!}">
                                                        <h2 class="mb-0">
                                                            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{!! isset($categories[$categoryId]) ? $categories[$categoryId]->name : 'Miscellaneous' !!}" aria-expanded="true" aria-controls="collapseOne">
                                                                {!! isset($categories[$categoryId]) ? $categories[$categoryId]->name : 'Miscellaneous' !!}
                                                            </button>
                                                        </h2>
                                                    </div>

                                                    <div id="collapse{!! isset($categories[$categoryId]) ? $categories[$categoryId]->name : 'Miscellaneous' !!}" class="collapse {{ $loop->first ? 'show' : '' }} py-2" aria-labelledby="heading{!! isset($categories[$categoryId]) !!}" data-parent="#accordionNested">
                                                        <div class="d-flex align-items-stretch recipes-body justify-content-between flex-wrap">
                                                            @foreach ($userRecipes as $categoryId => $categoryrecipes)
                                                                @foreach ($categoryrecipes->chunk(1) as $chunk)
                                                                    @foreach ($chunk as $recipe)
                                                                        @if ($recipe->recipe_category_id == ($categoryId ? $categoryId : 'NULL'))
                                                                            @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                                                                        @endif
                                                                    @endforeach
                                                                @endforeach
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- Accordion Test End -->
                                        @endforeach
                                    </div>
                            </div>
                        @else
                            You haven't unlocked any recipes!
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div id="active-craft"></div>
        </div>
    </div>
    </div>
    </div>


@endsection


@section('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-craft').on('click', function(e) {
                e.preventDefault();
                var $activeSection = $('#active-craft');
                var $parent = $(this);
                //loadModal("{{ url('crafting/craft') }}/" + $parent.data('id'), $parent.data('name'));
                $($activeSection).html('');
                $($activeSection).load("{{ url('crafting/craft') }}/" + $parent.data('id'), $parent.data('name'), function() {
                    //Find the number of base ingredients needed for x1 qty
                    var neededIngredients = $('.ingredient-row .ingredient');
                    var ingredientList = [];
                    neededIngredients.each(function() {
                        var ingredientName = $(this).find('h6 a').text();
                        var ingredientQty = $(this).find('qty').attr('base');
                        ingredientList.push({
                            [ingredientName]: parseInt(ingredientQty)
                        });
                    });

                    //Find out the total number of each ingredient in inventory
                    var inventoryRows = $('#userItems tbody tr');
                    var ingredientInventoryTotals = [];
                    inventoryRows.each(function() {
                        var itemName = $.trim($(this).find('td:nth-child(2)').text());
                        var rowTotal = $.trim($(this).find('td:nth-child(5) span').text());
                        if (checkIfKeyExists(ingredientList, itemName)) {
                            ingredientInventoryTotals.push({
                                itemN: itemName,
                                total: parseInt(rowTotal),
                            });
                        }
                    });
                    result = {};
                    ingredientInventoryTotals.forEach(item => {
                        if (result[item.itemN]) {
                            result[item.itemN] += item.total;
                        } else {
                            result[item.itemN] = item.total;
                        }
                    });
                });


            });

            $('#filterRecipes').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('.recipes-body').children().each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            function checkIfKeyExists(arr, key) {
                return arr.some(function(obj) {
                    return obj.hasOwnProperty(key);
                });
            }

        });
    </script>
@endsection
