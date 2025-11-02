<div class="card mb-4 timestamp">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <i class="far fa-clock"></i> {!! format_date(Carbon\Carbon::now()) !!}
            </div>
            <div class="col-md-6">
                @include('widgets._online_count')
            </div>
        </div>
    </div>
    
</div>

<div class="d-flex justify-content-center mb-4">
    <img src="https://wor-keeper.com/files/front_page_banners/front%20page%20banner.png" class="img-fluid" alt="Reos" />
</div>
<h1>Welcome, {!! Auth::user()->displayName ?? 'traveler' !!}!</h1>
@if(Auth::check())
    <div class="row justify-content-center">
        <div class="col-md-2">
            <div class="card mb-2" style="background-color: rgba(245, 245, 245, 0.0);border:none;">
                <div class="card-body text-center">
                    <a href="{{ Auth::user()->url }}"><img src="{{ asset('images/account.png') }}" class="img-fluid" alt="Account" /></a>
                    <h5 class="card-title">Account</h5>
                </div>

            </div>
        </div>
        <div class="col-md-2">
            <div class="card mb-2" style="background-color: rgba(245, 245, 245, 0.0);border:none;">
                <div class="card-body text-center">
                    <a href="{{ url('inventory') }}"><img src="{{ asset('images/inventory.png') }}" class="img-fluid" alt="Inventory" /></a>
                    <h5 class="card-title">Inventory</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card mb-2" style="background-color: rgba(245, 245, 245, 0.0);border:none;">
                <div class="card-body text-center">
                    <a href="{{ url('awardcase') }}"><img src="{{ asset('images/awards.png') }}" class="img-fluid" /></a>
                    <h5 class="card-title">Awards</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card mb-2" style="background-color: rgba(245, 245, 245, 0.0);border:none;">
                <div class="card-body text-center">
                    <a href="{{ url('bank') }}"><img src="{{ asset('images/currency.png') }}" class="img-fluid" alt="Bank" /></a>
                    <h5 class="card-title">Bank</h5>
                </div>
            </div>
        </div>
    </div>
@endif
{{-- @include('widgets._recent_gallery_submissions', ['gallerySubmissions' => $gallerySubmissions]) --}}
<div class="row text-center">
    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-body">
                <p class="card-text" style="text-dark">Welcome to the World of Reos!
                    Reos is a heavily story oriented ARPG, a place where stories come to life. Reos is a world that
                    creatures called Vayrons and Tyrians call home - These species co-exist with the people as mounts,
                    companions and friends. Using their magical abilities they fight, survive and create incredible
                    bonds. Join in and be among those who write the history, create unforgettable memories, join or
                    create guilds and play alongside other users. Whether you're in it for the breeding, story building,
                    roleplay or purely for fun, Reos is the place for you.</p>
            </div>
        </div>
    </div>
</div>

<div class="row text-center">
    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Group Info</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="line-height: 1em;">
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/newbie-guide.html">
                            Newbie Guide
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/faq.html">
                            FAQ
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/group-rules.html">
                            Group Rules
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/admin-team.html">
                            Admin Team
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/semi-custom-sale-guide.html">
                            Sales Guide
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://sta.sh/0iug87t44nd">
                            Semi-Custom Tiers
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>World Info</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="line-height: 1em;">
                    <li class="list-group-item">

                        Reos History
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-codex.weebly.com/world-map.html">
                            Reos Locations
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/guilds.html">
                            Guilds
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/vocabulary.html">
                            Vocabulary
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Resources</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="line-height: 1em;">
                    <li class="list-group-item">
                        <a href="https://wor-keeper.com/info/CPGuide">
                            Credit Point System
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://worldofreos.com/assets/external/cpcounter/index.html">
                            CP Calculator
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/political-status-updates.html">
                            Political Status Update
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://www.deviantart.com/reos-empire/journal/Rider-Companion-info-616601061">
                            Rider/Companion Info
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://www.deviantart.com/reos-empire/journal/Familiars-648863855">
                            Familiars
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://www.deviantart.com/reos-empire/journal/Skills-and-Abilities-648864161">
                            Skills
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/custom-backgrounds.html">
                            Custom Backgrounds
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

</div>
<div class="row text-center">
    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Character Info</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="line-height: 1em;">
                    <li class="list-group-item">
                        <a href=" https://wor-directory.weebly.com/design-approval.html">
                            Design Approval
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/import-templates.html">
                            Official Templates
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://sta.sh/01mb2udepegz">
                            Template How To
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/import-text-template.html">
                            Import Text Template
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://www.deviantart.com/reos-empire/journal/Reos-Adoption-Centre-2025-1146439497">
                            Adoption Center
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/import-editing.html">
                            Import Changes
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/import-transfers.html">
                            Ownership Transfers
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Breeding</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="line-height: 1em;">
                    <li class="list-group-item"><a href="https://www.deviantart.com/reos-empire/journal/Breeding-Request-Hub-723140047">
                            Breeding Requests
                        </a>
                    </li>
                    <li class="list-group-item"><a href="https://worldofreos.com/roller">
                            Breeding Roller
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://www.deviantart.com/world-of-reos/journal/Genetics-Info-615978178">
                            Genetics Info
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://sta.sh/2i3ek90cwl9">
                            Coat Colors
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/markings--genotypes.html">
                            Markings/Genotypes
                        </a>
                    </li>
                </ul>
            </div>
        </div>

    </div>
    <div class="col md-4 text-center">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Activities</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush" style="line-height: 1em;">
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/classes.html">
                            Classes and Stats
                        </a>
                    </li>
                    <li class="list-group-item"><a href="https://wor-directory.weebly.com/questing-rules.html">
                            Questing
                        </a>
                    </li>
                    <li class="list-group-item"><a href="https://www.deviantart.com/reos-empire/journal/Monthly-Prompts-667707149">
                            Monthly Prompt
                        </a>
                    </li>
                    <li class="list-group-item"><a href="https://wor-directory.weebly.com/the-purity-trials.html">
                            The Purity Trials
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/bonding.html">
                            Bonding
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/training.html">
                            Training
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/the-path-of-magic.html">
                            Magic Awakening
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/hunting.html">
                            Hunting
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/gathering.html">
                            Gathering
                        </a>
                    </li>
                    <li class="list-group-item">
                        <a href="https://wor-directory.weebly.com/crafters-grove.html">
                            Crafting
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row text-center">
    <div class="col-12 col-md-6">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Current Event</h5>
            </div>
            <div class="card-body">
                <a href="https://wor-directory.weebly.com/reos-world-fair.html">
                    <img src="https://wor-keeper.com/files/front_page_banners/world%20fair%202025.png" class="img-fluid">
                </a>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card mb-4" style="border:none;">
            <div class="card-header">
                <h5>Crossroads</h5>
            </div>
            <div class="card-body">
                <div class="container-fluid p-0">
                    <div class="row g-0">
                        <div class="col-6 p-0">
                            <a href="https://wor-database.weebly.com/" target="_blank">
                                <img src="https://tinyurl.com/wordatabase" class="img-fluid w-100">
                            </a>
                        </div>
                        <div class="col-6 p-0">
                            <a href="http://wor-codex.weebly.com/" target="_blank">
                                <img src="https://tinyurl.com/5xt5euxf" class="img-fluid w-100">
                            </a>
                        </div>
                        <div class="col-6 p-0">
                            <a href="https://wor-directory.weebly.com/" target="_blank">
                                <img src="https://tinyurl.com/wordirectory" class="img-fluid w-100">
                            </a>
                        </div>
                        <div class="col-6 p-0">
                            <a href="https://wor-keeper.com/" target="_blank">
                                <img src="https://tinyurl.com/workeeper" class="img-fluid w-100">
                            </a>
                        </div>
                        @include('widgets._affiliates', ['affiliates' => $affiliates, 'featured' => $featured_affiliates, 'open' => $open])
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('widgets._recent_gallery_submissions', ['gallerySubmissions' => $gallerySubmissions])
