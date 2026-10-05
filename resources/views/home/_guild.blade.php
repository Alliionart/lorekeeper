@if ($guild)
    <div class="guild-info" data-id="{{ $guild->id }}"><img src="{{ $guild->logoUrl }}" class="mw-100" alt="Thumbnail for {{ $guild->name }}" /></div>
    <div class="text-center"><a href="{{ $guild->viewUrl }}">{{ $guild->name }}</a></div>
    @if ($guild->status !== 'active' && Auth::check() && Auth::user()->isStaff)
        <div class="text-danger guild-info" data-id="0"><i class="fas fa-eye-slash mr-1"></i> Guild hidden from public view.</div>
    @endif
@else
    <div class="text-danger guild-info" data-id="0">Guild not found.</div>
@endif
