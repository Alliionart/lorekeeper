@if ($user->bannerUrl)
    <div class="mw-100 mb-2 user-banner" style="background-image: url('{{ $user->bannerUrl }}'); {{ $user->bannerStyling }}"></div>
@endif
