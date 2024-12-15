<nav class="navbar navbar-expand-md navbar-light">
    <div class="w-100" style="padding:50px">
        <div class="row">
            <div class="col-md-3">
                LOGO HERE
            </div>
            <div class="col-md-3">
                <ul>
                    <li class="nav-item"><a href="{{ url('info/about') }}" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="{{ url('feeds/news') }}" class="nav-link"><i class="fas fa-rss-square"></i> News</a></li>
                    <li class="nav-item"><a href="{{ url('feeds/sales') }}" class="nav-link"><i class="fas fa-rss-square"></i> Sales</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <ul>
                    <li class="nav-item"><a href="{{ url('reports/bug-reports') }}" class="nav-link">Bug Reports</a></li>
                    <li class="nav-item"><a href="{{ url('team') }}" class="nav-link">Team</a></li>
                    <li class="nav-item"><a href="mailto:{{ env('CONTACT_ADDRESS') }}" class="nav-link">Contact</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <ul>
                    <li class="nav-item"><a href="{{ url('credits') }}" class="nav-link">Credits</a></li>
                    <li class="nav-item"><a href="https://github.com/corowne/lorekeeper" class="nav-link">Lorekeeper</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<p class="d-flex justify-content-center align-items-center"><span class="copyright">&copy; {{ config('lorekeeper.settings.site_name', 'Lorekeeper') }} v{{ config('lorekeeper.settings.version') }} {{ Carbon\Carbon::now()->year }}</span> | <a
        href="{{ url('info/terms') }}" class="nav-link">Terms</a> | <a href="{{ url('info/privacy') }}" class="nav-link">Privacy</a></p>

@if (config('lorekeeper.extensions.scroll_to_top'))
    @include('widgets/_scroll_to_top')
@endif
