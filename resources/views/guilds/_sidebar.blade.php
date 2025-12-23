<ul>
    <li class="sidebar-header"><a href="{{ $guild->getViewUrlAttribute() }}" class="card-link">{{ $guild->name }}</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Storage</div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/inventory' }}" class="{{ set_active('*inventory') }}">Inventory</a></div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/bank' }}" class="{{ set_active('*bank') }}">Bank</a></div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/shop' }}" class="{{ set_active('*shop') }}">Shop</a></div>
        <?php
        $pets_exists = class_exists('App\Models\Pet\Pet');
        $gear_exists = class_exists('App\Models\Claymore\Gear');
        ?>
        @if ($pets_exists)
            <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/' . strtolower(__('guilds.playpen')) }}" class="{{ set_active('*' . strtolower(__('guilds.playpen'))) }}">{{ __('guilds.playpen') }}</a></div>
        @endif
        @if ($gear_exists)
            <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/armory' }}" class="{{ set_active('*pets') }}">{{ __('guilds.playpen') }}</a></div>
        @endif
    </li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">Members</div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/members' }}" class="{{ set_active('*members') }}">Members</a></div>
        <div class="sidebar-item"><a href="{{ $guild->getViewUrlAttribute() . '/characters' }}" class="{{ set_active('*characters') }}">Characters</a></div>
    </li>
    @if ($guild->owner_id === Auth::user()->id)
        <li class="sidebar-section">
            <div class="sidebar-section-header">Admin</div>
            <div class="sidebar-item"><a href="{{ $guild->getEditUrlAttribute() }}" class="{{ set_active('*settings') }}">Settings</a></div>
            <div class="sidebar-item"><a href="{{ $guild->getEditRankUrlAttribute() }}" class="{{ set_active('*edit-ranks') }}">Edit Ranks</a></div>
        </li>
    @endif
</ul>
