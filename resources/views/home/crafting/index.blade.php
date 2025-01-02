@extends('home.layout')

@section('home-title')
    Crafting
@endsection

@section('home-content')
    {!! breadcrumbs(['Crafting' => 'crafting']) !!}

    <h1>
        My Recipe Book
    </h1>
    <p> This is a list of recipes that you have unlocked, as well as automatically unlocked recipes. </p>

    <hr>

    <div class="row">
        <div class="col-md-3">
            <div class="card recipe-scrollbox">
                <div id="accordion">
                    <div class="card">
                        <div class="card-header" id="headingOne">
                            <h5 class="mb-0">
                                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Free Recipes
                                </button>
                            </h5>
                        </div>

                        <div id="collapseOne" class="collapse show pt-2" aria-labelledby="headingOne" data-parent="#accordion">
                            @if ($default->count())
                                <div class="row mx-0">
                                    @foreach ($default as $recipe)
                                        @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                                    @endforeach
                                </div>
                            @else
                                There are no free recipes.
                            @endif
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingTwo">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Collapsible Group Item #2
                                </button>
                            </h5>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
                            <div class="card-body">
                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf
                                moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur
                                butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header" id="headingThree">
                            <h5 class="mb-0">
                                <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Collapsible Group Item #3
                                </button>
                            </h5>
                        </div>
                        <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                            <div class="card-body">
                                Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf
                                moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur
                                butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-9">Crafting Section</div>
    </div>

    <h3>Your Unlocked Recipes</h3>
    @if (Auth::user()->recipes->count())
    <div class="card character-bio">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            @foreach($userRecipes as $categoryId=>$categoryrecipes)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="categoryTab-{{ isset($categories[$categoryId]) ? $categoryId : 'misc'}}" data-toggle="tab" href="#category-{{ isset($categories[$categoryId]) ? $categoryId : 'misc'}}" role="tab">
                        {!! isset($categories[$categoryId]) ? $categories[$categoryId]->name : 'Miscellaneous' !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="card-body tab-content">
        @foreach($userRecipes as $categoryId=>$categoryrecipes)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="category-{{ isset($categories[$categoryId]) ? $categoryId : 'misc'}}">
                @foreach($categoryrecipes->chunk(4) as $chunk)
                @foreach($chunk as $recipe)
                @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                @endforeach
                @endforeach
            </div>
        @endforeach
    </div>
</div>
    @else
        You haven't unlocked any recipes!
    @endif
    <div class="text-right mb-4">
        <a href="{{ url(Auth::user()->url . '/recipe-logs') }}">View logs...</a>
    </div>


@endsection


@section('scripts')
    <script>
        $(document).ready(function() {
            $('.btn-craft').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent().parent();
                loadModal("{{ url('crafting/craft') }}/" + $parent.data('id'), $parent.data('name'));
            });
        });
    </script>
@endsection
