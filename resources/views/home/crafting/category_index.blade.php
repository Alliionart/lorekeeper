@extends('home.layout')

@section('home-title')
    Crafting
@endsection

@section('home-content')
    {!! breadcrumbs(['Crafting' => 'crafting', $category->name => $category->id]) !!}

    <h1>{{ $category->name }}</h1>
    @if($category->parsed_description)
        {!! $category->parsed_description !!}
    @endif

    <hr>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <h3 class="card-header">Free Recipes</h3>
                <div class="card-body scrollock">
                    <input type="text" placeholder="Search recipes by name..." class="searchBar rounded border-0 mb-4 form-control" data-id="freeSearch" />
                    @if ($default->count())
                        <div class="row mx-0 searchContent" data-id="freeSearch">
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
                <h3 class="card-header">Unlocked Recipes</h3>
                <div class="card-body scrollock">
                    @if(Auth::user()->recipes->count())
                    <input type="text" placeholder="Search recipes by name..." class="searchBar rounded border-0 mb-4 form-control" data-id="unlockedSearch" />
                    @if ($default->count())
                        <div class="row mx-0 searchContent" data-id="unlockedSearch">
                            @foreach (Auth::user()->recipes as $recipe)
                                @include('home.crafting._smaller_recipe_card', ['recipe' => $recipe])
                            @endforeach
                        </div>
                    @else
                        There are no free recipes.
                    @endif
                @else
                    You haven't unlocked any recipes!
                @endif
                </div>
            </div>     
        </div>
        <div class="col-md-8">
            <div id="crafting-area"></div>
        </div>
    </div>

@endsection


@section('scripts')
    <script>
        $(document).ready(function() {

            var $craftingArea = $('#crafting-area');

            $('.btn-craft').on('click', function(e) {
                e.preventDefault();
                var $parent = $(this).parent().parent().parent();
                $craftingArea.addClass('loading');

                if(!$parent.hasClass('active')){
                    $parent.addClass('active');
                    $($craftingArea).html('');
                    $craftingArea.load("{{ url('crafting/craft') }}/" + $parent.data('id'), $parent.data('name')).removeClass('loading');
                }
            });

            $('.searchBar').on('keyup', function(e) {
                var sTerm = $(this).val().toLowerCase();
                var type = $(this).attr('data-id');
                console.log(type)
                console.log(sTerm);
                $('.searchContent[data-id="' + type + '"]').children().each(function() {
                    console.log($(this))
                    $(this).toggle($(this).text().toLowerCase().indexOf(sTerm) > -1);
                });
            });

        });
    </script>
@endsection
