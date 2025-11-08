@if ($user->bannerUrl)
    <div class="mw-100 user-banner rounded border border-dark" style="background-image: url('{{ $user->bannerUrl }}'); {{ $user->bannerStyling }}"></div>
@endif
