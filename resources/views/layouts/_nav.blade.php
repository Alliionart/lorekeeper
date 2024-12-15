<nav class="navbar sitenav navbar-expand-md navbar-dark bg-dark" id="headerNav">
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
                <li class="nav-item dropdown">
                    <a id="inventoryDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-newspaper"></i> Feed
                    </a>

                    <div class="dropdown-menu" aria-labelledby="inventoryDropdown">
                        @if (Auth::check() && Auth::user()->is_news_unread && config('lorekeeper.extensions.navbar_news_notif'))
                            <a class="dropdown-item text-warning" href="{{ url('news') }}"><strong>News</strong><i class="fas fa-bell"></i></a>
                        @else
                            <a class="dropdown-item" href="{{ url('news') }}">News</a>
                        @endif
                        @if (Auth::check() && Auth::user()->is_sales_unread && config('lorekeeper.extensions.navbar_news_notif'))
                            <a class="dropdown-item text-warning" href="{{ url('sales') }}"><strong>Sales</strong><i class="fas fa-bell"></i></a>
                        @else
                            <a class="dropdown-item" href="{{ url('sales') }}">Sales</a>
                        @endif
                    </div>
                </li>
                @if (Auth::check())
                    <li class="nav-item dropdown">
                        <a id="activityDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fas fa-gamepad"></i> Activities
                        </a>
                        <div class="dropdown-menu" aria-labelledby="activityDropdown">
                            <a class="dropdown-item" href="{{ url('submissions') }}">
                                Prompt Submissions
                            </a>
                            <a class="dropdown-item" href="{{ url('claims') }}">
                                Claims
                            </a>
                            <a class="dropdown-item" href="{{ url('reports') }}">
                                Reports
                            </a>
                            <a class="dropdown-item" href="{{ url('designs') }}">
                                Design Approvals
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">
                                Character Transfers
                            </a>
                            <a class="dropdown-item" href="{{ url('trades/open') }}">
                                Trades
                            </a>
                            <a class="dropdown-item" href="{{ url('awardcase') }}">
                                {{ ucfirst(__('awards.awards')) }}
                            </a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a id="inventoryDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fas fa-clipboard"></i> Guides
                        </a>

                        <div class="dropdown-menu" aria-labelledby="inventoryDropdown">
                            <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">New Players</a>
                            <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">Breeding Rates</a>
                            <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">Point System</a>
                            <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">Rules</a>
                            <a class="dropdown-item" href="{{ url('characters/transfers/incoming') }}">Suggestion Hub</a>
                        </div>
                    </li>
                @endif
                <li class="nav-item dropdown">
                    <a id="designDropdown" class="nav-link dropdown-toggle" href="{{ url('/world/info/design-hub') }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-paint-brush"></i> Design Hub
                    </a>
                    <div class="dropdown-menu" aria-labelledby="designDropdown">
                        <a class="dropdown-item" href="{{ url('/world/info/design-hub') }}">
                            Markings
                        </a>
                        <a class="dropdown-item" href="{{ url('myos') }}">
                            Base Coats
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('characters') }}">
                            Layering Rules
                        </a>
                        <a class="dropdown-item" href="{{ url('characters/myos') }}">
                            Eyes, Mouths & Features
                        </a>
                        <a class="dropdown-item" href="{{ url('characters/myos') }}">
                            Cross Traits & Mutations
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a id="browseDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-th"></i> Masterlist
                    </a>
                    <div class="dropdown-menu" aria-labelledby="browseDropdown">
                        <a class="dropdown-item" href="{{ url('masterlist') }}">
                            Character Masterlist
                        </a>
                        <a class="dropdown-item" href="{{ url('myos') }}">
                            Genotype Masterlist
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('characters') }}">
                            My Characters
                        </a>
                        <a class="dropdown-item" href="{{ url('characters/myos') }}">
                            My Genotypes
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a id="loreDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <i class="fas fa-book"></i> Codex
                    </a>
                    <div class="dropdown-menu" aria-labelledby="loreDropdown">
                        <a class="dropdown-item" href="{{ url('raffles') }}">Raffles</a>
                        <a class="dropdown-item" href="{{ url('gallery') }}">Gallery</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ url('world') }}">
                            Encyclopedia
                        </a>
                        <a class="dropdown-item" href="{{ url('world/info') }}">
                            World
                        </a>
                        <a class="dropdown-item" href="{{ url('shops') }}">
                            Shops
                        </a>
                        <a class="dropdown-item" href="{{ url('users') }}">
                            Users
                        </a>
                        @if (Auth::check())
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ url('search') }}">
                                Site Search
                            </a>
                        @endif
                        <a class="dropdown-item" href="{{ url(__('dailies.dailies')) }}">
                            {{ __('dailies.dailies') }}
                        </a>
                        <a class="dropdown-item" href="{{ url(__('cultivation.cultivation')) }}">
                            {{ __('cultivation.cultivation') }}
                        </a>
                    </div>
                </li>
            </ul>

            <!-- Right Side Of Navbar -->
            <ul class="navbar-nav ml-auto">
                @include('layouts._searchindexbar')

                    <!-- Clock -->
                    {!! LiveClock('America/Boise') !!}
                    <!-- Authentication Links -->
                    @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> {{ __('Login') }}</a>
                    </li>
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus"></i> {{ __('Register') }}</a>
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
                            <a class="nav-link btn btn-secondary btn-sm" href="{{ url('notifications') }}"><i class="fas fa-envelope"></i> {{ Auth::user()->notifications_unread }}</a>
                        </li>
                    @endif

                    <li class="nav-item dropdown">
                        <a id="browseDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                            <i class="fas fa-plus"></i>
                        </a>

                        <div class="dropdown-menu" aria-labelledby="browseDropdown">
                            <a class="dropdown-item" href="{{ url('submissions/new') }}">
                                Submit Prompt
                            </a>
                            <a class="dropdown-item" href="{{ url('claims/new') }}">
                                Submit Claim
                            </a>
                            <a class="dropdown-item" href="{{ url('reports/new') }}">
                                Submit Report
                            </a>
                            <a class="dropdown-item" href="{{ url('reports/bug-reports') }}">
                                Bug Reports
                            </a>
                        </div>
                    </li>

                    <li class="nav-item">
                        @if (Auth::check())
                            <a id="footer" class="nav-link accountbx dropdown-toggle card d-flex flex-row" href="{{ Auth::user()->url }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <img src="/images/avatars/{{ Auth::user()->avatar }}" class="img-fluid rounded" style="max-height:45px" alt="{{ Auth::user()->name }}'s Avatar" />
                            </a>
                        @endif

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
                            <a class="dropdown-item" href="{{ url('comments/liked') }}">
                                Liked Comments
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ url('pets') }}">
                                Pets
                            </a>
                            <a class="dropdown-item" href="{{ url('inventory') }}">
                                Inventory
                            </a>
                            <a class="dropdown-item" href="{{ url('bank') }}">
                                Bank
                            </a>
                            <a class="dropdown-item" href="{{ url('userstats') }}">
                                Stat Information
                            </a>
                            <div class="dropdown-divider"></div>
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
    <script>
        $(document).ready(function() {
            //Create Sticky Header
            $(window).scroll(function() {
                if ($(this).scrollTop() > 250) {
                    $('.sitenav').slideDown(100).addClass('scrolling');
                } else {

                    $('.sitenav').removeClass('scrolling');
                }
            });
        });
    </script>
</nav>
