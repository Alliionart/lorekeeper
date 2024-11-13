<div class="row home">
    <div class="col-md-3">
        <h1>Welcome to <span>Entia Paradoxium</span></h1>
        <p class="mb-1">Welcome back, {!! Auth::user()->displayName !!}!</p>

        @if (isset($featured) && $featured)
        <div class="card main mt-5 mb-4">
            <div class="card-body">
                <h4>Featured Character</h4>
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ $featured->url }}" class="h5 mb-0">
                            @if (!$featured->is_visible)
                            <i class="fas fa-eye-slash"></i>
                            @endif {{ $featured->fullName }}
                        </a>
                    </div>
                    <div class="col-md-6">
                        {!! $featured->displayOwner !!}
                    </div>
                </div>
                <a href="{{ $featured->url }}"><img src="{{ $featured->image->thumbnailUrl }}" class="img-fluid" /></a>
            </div>
        </div>
        @endif

        <div class="card main">
            <div class="card-body">
                <h4 class="mb-3">Featured Artwork</h4>
                <div class="row mb-2">
                    <div class="col-md-6">
                        <a href="#" class="h5 mb-0">Artwork Title</a>
                    </div>
                    <div class="col-md-6">
                        Owner Name
                    </div>
                </div>
                <a href="#"><img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" class="img-fluid" /></a>
            </div>
        </div>

    </div>
    <div class="col-md-9">
    <div class="row d-flex align-items-end">
            <div class="col-md-8">
                <div class="card main dark">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 d-flex align-items-center">
                                <div>
                                    <h5 class="card-title">Current Bonuses</h5>
                                    <p class="card-text">Some quick example text to build on the card title and make up
                                        the bulk of the card's content.</p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    class="img-fluid bonus-img" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card main">
                    <div class="card-body">
                        <h5>Join Our Discord!</h5>
                        <iframe src="https://discord.com/widget?id=216711510792208392&theme=dark" width="350"
                            height="400" allowtransparency="true" frameborder="0"
                            sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin allow-scripts"></iframe>
                    </div>
                </div>
            </div>
        </div>

        <!-- Your Account -->
        <div class="card main my-4">
            <div class="card-body dark-boxes">
                <h5>Your Account</h5>
                <div class="d-flex">
                    <div class="card">
                        <a href="#">
                            <div class="card-body text-center">
                                <img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    style="max-width:200px" class="img-thumbnail mb-3">
                                <h6>My Characters</h6>
                            </div>
                        </a>
                    </div>
                    <div class="card">
                        <a href="#">
                            <div class="card-body text-center">
                                <img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    style="max-width:200px" class="img-thumbnail mb-3">
                                <h6>My Bank</h6>
                            </div>
                        </a>
                    </div>
                    <div class="card">
                        <a href="#">
                            <div class="card-body text-center">
                                <img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    style="max-width:200px" class="img-thumbnail mb-3">
                                <h6>My Inventory</h6>
                            </div>
                        </a>
                    </div>
                    <div class="card">
                        <a href="#">
                            <div class="card-body text-center">
                                <img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    style="max-width:200px" class="img-thumbnail mb-3">
                                <h6>My Pets</h6>
                            </div>
                        </a>
                    </div>
                    <div class="card">
                        <a href="#">
                            <div class="card-body text-center">
                                <img src="https://plus.unsplash.com/premium_photo-1663937576055-a1d89f3895ca?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"
                                    style="max-width:200px" class="img-thumbnail mb-3">
                                <h6>My Genotypes</h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card main my-4">
            <div class="card-body">
                <h5>Recent Characters</h5>
                <div class="d-flex">

                    @foreach ($characters->chunk(1) as $chunk)
                    <div class="card">
                        @foreach ($chunk as $character)
                        <div class="card-body">
                            <a href="{{ $character->url }}"><img src="{{ $character->image->thumbnailUrl }}" class="img-thumbnail" alt="Thumbnail for {{ $character->fullName }}" /></a>
                            <div class="d-flex justify-content-between mt-3">
                                <a href="{{ $character->url }}" class="h5 mb-0">
                                    @if (!$character->is_visible)
                                    <i class="fas fa-eye-slash"></i>
                                    @endif {{ Illuminate\Support\Str::limit($character->fullName, 20, $end = '...') }}
                                </a>
                                <div class="small">
                                    {!! $character->displayOwner !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Factions -->

<div class="card main dark my-4">
    <div class="card-body">
        <H5>HELPFUL LINKS</H5>
        <p>Find your place in Antidonum below!</p>
        <div class="d-flex justify-content-between guide-nav">
            <div class="card w-25">
                <h6 class="text-center p-3 rounded">Activities</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ Auth::user()->url }}">Breeding Rites</a></li>
                    <li class="list-group-item"><a href="{{ url('account/settings') }}">Farming</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Fishing</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Hunting</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Gathering</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Journies</a></li>
                </ul>
            </div>
            <div class="card w-25">
                <h6 class="text-center p-3 rounded">Guides</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ Auth::user()->url }}">New Players</a></li>
                    <li class="list-group-item"><a href="{{ url('account/settings') }}">Breeding Rates</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Point Counting System</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">How to Battle</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Rules</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Suggestion Hub</a></li>
                </ul>
            </div>
            <div class="card w-25">
                <h6 class="text-center p-3 rounded">Design Hub</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ Auth::user()->url }}">Markings</a></li>
                    <li class="list-group-item"><a href="{{ url('account/settings') }}">Base Coats</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Layering Rules</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Eyes, Mouth & Features</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Cross Traits & Mutations</a></li>
                </ul>
            </div>
            <div class="card w-25">
                <h6 class="text-center p-3 rounded">World</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><a href="{{ Auth::user()->url }}">Map</a></li>
                    <li class="list-group-item"><a href="{{ url('account/settings') }}">Locations</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Factions</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Figures</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Fauna</a></li>
                    <li class="list-group-item"><a href="{{ url('trades/open') }}">Flora</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>

@include('widgets._recent_gallery_submissions', ['gallerySubmissions' => $gallerySubmissions])
