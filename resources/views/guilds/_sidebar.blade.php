<ul>
    <li class="sidebar-header"><a href="{{ $guild->getViewUrlAttribute() }}" class="card-link">{{ $guild->name }}</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Inventory</div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/inventory' }}" class="{{ set_active('*inventory') }}">Inventory</a></div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/bank' }}" class="{{ set_active('*bank') }}">Bank</a></div>
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Members</div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/members' }}" class="{{ set_active('*members') }}">Members</a></div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/characters' }}" class="{{ set_active('*characters') }}">Characters</a></div>
    </li>
    @if( $guild->owner_id === Auth::user()->id )
        <li class="sidebar-section">
            <div class="sidebar-section-header">Admin</div>
            <div class="sidebar-item"><a href="{{ $guild->getEditUrlAttribute() }}" class="{{ set_active('*settings') }}">Settings</a></div>
        </li>
    @endif
</ul>
