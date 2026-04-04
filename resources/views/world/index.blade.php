@extends('world.layout')

@section('world-title')
    Home
@endsection

@section('content')
    {!! breadcrumbs(['Encyclopedia' => 'world']) !!}

    <h1>World</h1>
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <h3 class="card-header">Characters</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card-body text-center">
                            <img src="{{ asset('images/characters.png') }}" class="img-fluid mb-3" alt="Characters" />
                            <h5 class="card-title">Characters</h5>
                        </div>
                    </div>
                    <div class="col-md-9 p-4">
                        <div class="row">
                            <a href="{{ url('world/species') }}" class="col-md-6 my-2">
                                <i class="fas fa-horse h2 mr-3 mb-0"></i>
                                <span class="h4">Species</span>
                            </a>
                            <a href="{{ url('world/subtypes') }}" class="col-md-6 my-2">
                                <i class="fas fa-horse-head h2 mr-3 mb-0"></i>
                                <span class="h4">Subtypes</span>
                            </a>
                            <a href="{{ url('world/rarities') }}" class="col-md-6 my-2">
                                <i class="fas fa-star h2 mr-3 mb-0"></i>
                                <span class="h4">Rarities</span>
                            </a>
                            <a href="{{ url('world/traits') }}" class="col-md-6 my-2">
                                <i class="fas fa-dna h2 mr-3 mb-0"></i>
                                <span class="h4">Traits</span>
                            </a>
                            <a href="{{ url('world/trait-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-dna h2 mr-3 mb-0"></i>
                                <span class="h4">Trait Categories</span>
                            </a>
                            <a href="{{ url('world/trait-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-dna h2 mr-3 mb-0"></i>
                                <span class="h4">Trait Categories</span>
                            </a>
                            @if (config('lorekeeper.extensions.visual_trait_index.enable_universal_index'))
                                <li class="list-group-item"><a href="{{ url('world/universaltraits') }}">Universal Trait Index</a></li>
                                <a href="{{ url('world/universaltraits') }}" class="col-md-6 my-2">
                                    <i class="fas fa-dna h2 mr-3 mb-0"></i>
                                    <span class="h4">Universal Trait Index</span>
                                </a>
                            @endif
                            <a href="{{ url('world/character-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-folder-open h2 mr-3 mb-0"></i>
                                <span class="h4">Character Categories</span>
                            </a>
                            <a href="{{ url('world/character-classes') }}" class="col-md-6 my-2">
                                <i class="fas fa-shield-alt h2 mr-3 mb-0"></i>
                                <span class="h4">Character Classes</span>
                            </a>
                            <a href="{{ url('world/' . __('transformations.transformations')) }}" class="col-md-6 my-2">
                                <i class="fas fa-tshirt h2 mr-3 mb-0"></i>
                                <span class="h4">{{ ucfirst(__('transformations.transformations')) }}</span>
                            </a>
                            <a href="{{ url('world/levels') }}" class="col-md-6 my-2">
                                <i class="fas fa-signal h2 mr-3 mb-0"></i>
                                <span class="h4">Levels</span>
                            </a>
                            <a href="{{ url('world/stats') }}" class="col-md-6 my-2">
                                <i class="fas fa-chart-pie h2 mr-3 mb-0"></i>
                                <span class="h4">Stats</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card mb-4">
                <h3 class="card-header">Items, Awards, Companions & More</h3>
                <div class="row">
                    <div class="col-md-3">
                        <div class="card-body text-center">
                            <img src="{{ asset('images/inventory.png') }}" class="img-fluid mb-3" alt="Items & Companions" />
                            <h5 class="card-title">Items & Companions</h5>
                        </div>
                    </div>
                    <div class="col-md-9 p-4">
                        <div class="row">
                            <a href="{{ url('world/items') }}" class="col-md-6 my-2">
                                <i class="fas fa-toolbox h2 mr-3 mb-0"></i>
                                <span class="h4">Items</span>
                            </a>
                            <a href="{{ url('world/subtypes') }}" class="col-md-6 my-2">
                                <i class="fas fa-folder-open h2 mr-3 mb-0"></i>
                                <span class="h4">Item Categories</span>
                            </a>
                            <a href="{{ url('world/loot-tables') }}" class="col-md-6 my-2">
                                <i class="fas fa-dice h2 mr-3 mb-0"></i>
                                <span class="h4">Loot Tables</span>
                            </a>
                            <a href="{{ url('world/currencies') }}" class="col-md-6 my-2">
                                <i class="fas fa-coins h2 mr-3 mb-0"></i>
                                <span class="h4">Currencies</span>
                            </a>
                            <a href="{{ url('world/skills') }}" class="col-md-6 my-2">
                                <i class="fas fa-level-up-alt h2 mr-3 mb-0"></i>
                                <span class="h4">Skills</span>
                            </a>
                            <a href="{{ url('world/skill-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-folder-open h2 mr-3 mb-0"></i>
                                <span class="h4">Skill Categories</span>
                            </a>
                            <a href="{{ url('world/' . __('awards.awards')) }}" class="col-md-6 my-2">
                                <i class="fas fa-trophy h2 mr-3 mb-0"></i>
                                <span class="h4">{{ ucfirst(__('awards.awards')) }}</span>
                            </a>
                            <a href="{{ url('world/' . __('awards.award') . '-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-folder-open h2 mr-3 mb-0"></i>
                                <span class="h4">{{ ucfirst(__('awards.award')) }} Categories</span>
                            </a>
                            <a href="{{ url('world/pets') }}" class="col-md-6 my-2">
                                <i class="fas fa-paw h2 mr-3 mb-0"></i>
                                <span class="h4">Familiars</span>
                            </a>
                            <a href="{{ url('world/pet-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-paw h2 mr-3 mb-0"></i>
                                <span class="h4">Familiar Categories</span>
                            </a>
                            <a href="{{ url('world/weapons') }}" class="col-md-6 my-2">
                                <i class="fas fa-exclamation h2 mr-3 mb-0"></i>
                                <span class="h4">Weapons</span>
                            </a>
                            <a href="{{ url('world/gear') }}" class="col-md-6 my-2">
                                <i class="fas fa-cogs h2 mr-3 mb-0"></i>
                                <span class="h4">Gear</span>
                            </a>
                            <a href="{{ url('world/recipes') }}" class="col-md-6 my-2">
                                <i class="fas fa-cookie h2 mr-3 mb-0"></i>
                                <span class="h4">Recipes</span>
                            </a>
                            <a href="{{ url('world/recipe-categories') }}" class="col-md-6 my-2">
                                <i class="fas fa-folder-open h2 mr-3 mb-0"></i>
                                <span class="h4">Recipe Categories</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
