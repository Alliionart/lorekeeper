<nav class="navbar navbar-expand-md navbar-dark bg-dark" id="headerNav">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ url('/') }}">
            {{ config('lorekeeper.settings.site_name', 'Lorekeeper') }}
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <!-- Left Side Of Navbar -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    @if (Auth::check() && Auth::user()->is_news_unread && config('lorekeeper.extensions.navbar_news_notif'))
                        <a class="nav-link d-flex text-warning" href="{{ url('news') }}"><strong>News</strong><i class="fas fa-bell"></i></a>
                    @else
                        <a class="nav-link" href="{{ url('news') }}"><i class="fas fa-newspaper"></i> News</a>
                    @endif
                </li>
                <li class="nav-item">
                    @if (Auth::check() && Auth::user()->is_sales_unread && config('lorekeeper.extensions.navbar_news_notif'))
                        <a class="nav-link d-flex text-warning" href="{{ url('sales') }}"><strong>Sales</strong><i class="fas fa-bell"></i></a>
                    @else
                        <a class="nav-link" href="{{ url('sales') }}"><i class="fas fa-coins"></i> Sales</a>
                    @endif
                </li>
                @if (Auth::check())
                    <li class="nav-item dropdown">
                        <a id="inventoryDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fas fa-home"></i> Home
                        </a>

                        <div class="dropdown-menu" aria-labelledby="inventoryDropdown">
                            <a class="dropdown-item" href="{{ url('characters') }}">
                                My {{ ucwords( __('lorekeeper.characters')) }}
                            </a>
                            <a class="dropdown-item" href="{{ url('breeding-permissions') }}">
                                Breeding Permissions
                            </a>
                            <a class="dropdown-item" href="{{ url('characters/myos') }}">
                                My {{ ucwords( __('lorekeeper.myos')) }}
                            </a>
                            <a class="dropdown-item" href="{{ url('pets') }}">
                                My Pets
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ url('inventory') }}">
                                Inventory
                            </a>
                            <a class="dropdown-item" href="{{ url('bank') }}">
                                Bank
                            </a>
                            <a class="dropdown-item" href="{{ url('awardcase') }}">
                                {{ ucfirst(__('awards.awards')) }}
                            </a>
                            <a class="dropdown-item" href="{{ url('userstats') }}">
                                Stat Information
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ url('comments/liked') }}">
                                Liked Comments
                            </a>
                            <a class="dropdown-item" href="{{ url('surrenders') }}">
                                My Surrenders
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="queueDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fas fa-seedling"></i> Activities
                        </a>
                        <div class="dropdown-menu mega-menu" aria-labelledby="queueDropdown">
                            <div class="row">
                                <div class="col-md-4">
                                    <h5><i class="fas fa-th-list mr-2"></i> Submissions</h5>
                                    <a class="dropdown-item" href="{{ url('submissions') }}">
                                        Prompt Submissions
                                    </a>
                                    <a class="dropdown-item" href="{{ url('queues') }}">
                                        Queues
                                    </a>
                                    <a class="dropdown-item" href="{{ url('claims') }}">
                                        Claims
                                    </a>
                                    <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">
                                        Character Transfers
                                    </a>
                                    <a class="dropdown-item" href="{{ url('trades/open') }}">
                                        Trades
                                    </a>

                                    <div class="dropdown-divider"></div>
                                    <h5><i class="fas fa-hammer mr-2"></i> Crafting</h5>
                                    <a class="dropdown-item" href="{{ url('crafting/2') }}">Alchemy</a>
                                    <a class="dropdown-item" href="{{ url('crafting/4') }}">Blacksmithing</a>
                                    <a class="dropdown-item" href="{{ url('crafting/3') }}">Artisan Crafts</a>
                                    <a class="dropdown-item" tabindex="-1" href="{{ url('crafting/1') }}">Cooking</a>
                                </div>
                                <div class="col-md-4">
                                    <h5><i class="fas fa-medal mr-2"></i> Core</h5>
                                    <a class="dropdown-item" href="#">
                                        Purity Trials
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Training
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Bonding
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Classes
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Magic Awakening
                                    </a>

                                    <div class="dropdown-divider"></div>
                                    <h5><i class="fas fa-chess-knight mr-2"></i> Other</h5>
                                    <a class="dropdown-item" href="{{ url('designs') }}">
                                        Design Approvals
                                    </a>
                                    <a class="dropdown-item" href="{{ url(__('dailies.dailies')) }}">
                                        {{__('dailies.dailies')}}
                                    </a>
                                    <a class="dropdown-item" href="{{ url('foraging') }}">
                                        Foraging
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Breeding
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <h5><i class="fas fa-redo-alt mr-2"></i> Repeat</h5>
                                    <a class="dropdown-item" href="#">
                                        Questing
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Hunting
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        Monthly Prompt
                                    </a>
                                    <a class="dropdown-item" href="{{ url('adoptions') }}">
                                        Adoption Center
                                    </a>
                                </div>
                            </div>
                    
                        </div>
                    </li>
                @endif
                <li class="nav-item dropdown">
                    <a id="designhubDropdown" class="nav-link dropdown-toggle" href="{{ url('design-hub') }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-horse-head"></i> Design Hub
                    </a>
                    <div class="dropdown-menu" aria-labelledby="designhubDropdown">
                        <a class="dropdown-item" href="{{ url('info/designing-your-import') }}">
                            Designing Your Import
                        </a>
                        <a class="dropdown-item" href="{{ url('design-hub/base-coats') }}">
                            Base Coats
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('design-hub') }}">
                            Design Hub
                        </a>
                        <a class="dropdown-item" href="{{ url('info/design-approval-checklist') }}">
                            Design Approval Checklist
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a id="browseDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-list"></i> Browse
                    </a>

                    <div class="dropdown-menu" aria-labelledby="browseDropdown">
                        <a class="dropdown-item" href="{{ url('users') }}">
                            Users
                        </a>
                        <a class="dropdown-item" href="{{ url('masterlist') }}">
                            {{ ucwords( __('lorekeeper.characters')) }} Masterlist
                        </a>
                        <a class="dropdown-item" href="{{ url('myos') }}">
                            {{ ucwords( __('lorekeeper.myos')) }} Masterlist
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('raffles') }}">
                            Raffles
                        </a>
                        <a class="dropdown-item" href="{{ url('forum') }}">
                            Forums
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('reports/bug-reports') }}">
                            Bug Reports
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a id="loreDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-globe-americas"></i> World
                    </a>

                    <div class="dropdown-menu" aria-labelledby="loreDropdown">
                        <a class="dropdown-item" href="{{ url('world') }}">
                            Codex
                        </a>
                        <a class="dropdown-item" href="{{ url('world/info') }}">
                            World Expanded
                        </a>
                        <a class="dropdown-item" href="{{ url('prompts/prompts') }}">
                            Prompts
                        </a>
                        <div class="dropdown-divider"></div>
                        <div class="dropdown-submenu">
                            <a class="dropdown-item" tabindex="-1" href="{{ url('shops') }}">Shops</a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ url('user-shops/shop-index') }}">User Shops</a></li>
                                @if (Auth::check())
                                    <li><a class="dropdown-item" href="{{ url('user-shops') }}">My Shops</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a id="loreDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-gamepad"></i> Games
                    </a>

                    <div class="dropdown-menu" aria-labelledby="loreDropdown">
                        <a class="dropdown-item" href="{{ url('higher-or-lower') }}">
                            Higher or Lower
                        </a>
                        <a class="dropdown-item" href="{{ url(__('cultivation.cultivation')) }}">
                            {{__('cultivation.cultivation')}}
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a id="toolDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-wrench"></i> Tools
                    </a>
                    <div class="dropdown-menu" aria-labelledby="toolDropdown">
                        <a class="dropdown-item" target="_blank" href="http://worldofreos.livard.com/ACCalc.html">
                            Adoption Calulator
                        </a>
                        <a class="dropdown-item" target="_blank" href="http://worldofreos.livard.com/breedinggenerator.html">
                            Breeding Comment Generator
                        </a>
                        <a class="dropdown-item" target="_blank" href="https://worldofreos.com/assets/external/semigenerator/index.html">
                            Semi-Custom Generator
                        </a>
                        <a class="dropdown-item" href="{{ url('adoptions') }}">
                            Adoption Center
                        </a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('gallery') }}"><i class="fas fa-images"></i> Gallery</a>
                </li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                @include('layouts._searchindexbar')
                <!-- Authentication Links -->
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    @if (Auth::user()->isStaff)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ url('admin') }}"><i class="fas fa-crown"></i></a>
                        </li>
                    @endif
                    @if (Auth::user()->notifications_unread)
                        <li class="nav-item">
                            <a class="nav-link btn btn-secondary btn-sm" href="{{ url('notifications') }}"><span class="fas fa-envelope"></span> {{ Auth::user()->notifications_unread }}</a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a id="browseDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            Submit
                        </a>

                        <div class="dropdown-menu" aria-labelledby="browseDropdown">
                            <a class="dropdown-item" href="{{ url('submissions/new') }}">
                                Submit Prompt
                            </a>
                            <a class="dropdown-item" href="{{ url('claims/new') }}">
                                Submit Claim
                            </a>
                            <a class="dropdown-item" href="{{ url('surrenders/new') }}">
                                Submit Surrender
                            </a>
                            <a class="dropdown-item" href="{{ url('reports/new') }}">
                                Submit Report
                            </a>
                        </div>
                    </li>

                    <li class="nav-item dropdown">
                        <a id="navbarDropdown" class="nav-link dropdown-toggle" href="{{ Auth::user()->url }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            {{ Auth::user()->name }} <span class="caret"></span>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="{{ Auth::user()->url }}">
                                Profile
                            </a>
                            <a class="dropdown-item" href="{{ url('notifications') }}">
                                Notifications
                            </a>
                            <a class="dropdown-item" href="{{ url('account/bookmarks') }}">
                                Bookmarks
                            </a>
                            <a class="dropdown-item" href="{{ url('account/settings') }}">
                                Settings
                            </a>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                                document.getElementById('logout-form').submit();">
                                {{ __('Logout') }}
                            </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
