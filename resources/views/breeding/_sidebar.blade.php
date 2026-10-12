<ul>
    <li class="sidebar-header">
        <a class="card-link" href="{{ url('breeding') }}">Breeding</a>
    </li>
    @if (Auth::check())
        <li class="sidebar-section">
            <div class="sidebar-section-header">{{ Auth::user()->name }}</div>
            <div class="sidebar-item"><a href="{{ url('breedings') }}" class="{{ set_active('breedings') }}">Breedings</a></div>
            <div class="sidebar-item"><a href="{{ url('breeding-permissions') }}" class="{{ set_active('breeding-permissions') }}">Breeding Permissions</a></div>
        </li>
    @endif
    <li class="sidebar-section">
        <div class="sidebar-section-header">Information & Activity</div>
        <div class="sidebar-item"><a href="{{ url('breeding') }}" class="{{ set_active('breeding') }}">Breeding Info</a></div>
        <div class="sidebar-item"><a href="{{ url('queues/breeding') }}" class="{{ set_active('queues/breeding*') }}">Breeding Queue</a></div>
    </li>
</ul>
