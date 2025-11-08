@extends('account.layout')

@section('account-title')
    Settings
@endsection

@section('account-content')
    {!! breadcrumbs(['My Account' => Auth::user()->url, 'Settings' => 'account/settings']) !!}

    <h1>Settings</h1>

    <div class="card p-3 mb-2">
        <h3>Avatar</h3>
        @if (Auth::user()->isStaff)
            <div class="alert alert-info">For admins - note that .GIF avatars leave a tmp file in the directory (e.g php2471.tmp). There is an automatic schedule to delete these files.
            </div>
        @endif
        {!! Form::open(['url' => 'account/avatar', 'files' => true]) !!}
        <div class="custom-file mb-1">
            {!! Form::label('avatar', 'Update Profile Image', ['class' => 'custom-file-label']) !!}
            {!! Form::file('avatar', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Banner Image</h3>
        <p>You can set a banner image here to decorate your profile with, and adjust its styling once uploaded. Filetypes supported are JPG, PNG, GIF, BMP, and WebP.</p>

        {!! Form::open(['url' => 'account/banner', 'files' => true]) !!}
        <div class="form-group row">
            {!! Form::label('banner', 'Update Header Image', ['class' => 'col-md-2 col-form-label']) !!}
            <div class="col-md-10">
                {!! Form::file('banner', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}

        @if (Auth::user()->banner)
            <h3>Banner Image Preview & Styling</h3>
            @include('widgets._user_banner', ['user' => Auth::user()])

            <div class="alert alert-info">Styling options such as <b>fixed background attachment</b> may be better previewed on your profile page, as it is fixed relative to the viewport.</div>

            {!! Form::open(['url' => 'account/banner-styling']) !!}
            <div class="row">
                <div class="col-md">
                    <div class="form-group">
                        {!! Form::label('Background Attachment') !!}
                        {!! Form::select('attachment', ['scroll' => 'Scroll', 'fixed' => 'Fixed'], isset(Auth::user()->bannerData['attachment']) ? Auth::user()->bannerData['attachment'] : 'scroll', ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-group">
                        {!! Form::label('Background Repeat') !!}
                        {!! Form::select(
                            'repeat',
                            ['repeat' => 'Repeat', 'repeat-x' => 'Repeat X', 'repeat-y' => 'Repeat Y', 'space' => 'Space', 'round' => 'Round', 'no-repeat' => 'No Repeat'],
                            isset(Auth::user()->bannerData['repeat']) ? Auth::user()->bannerData['repeat'] : null,
                            ['class' => 'form-control'],
                        ) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('Background Size Type') !!} {!! add_help('Keyword will allow you to select from keywords auto, cover, and contain. Two Values will allow you to enter up to two valid numerical values.') !!}
                        {!! Form::select('size_type', ['keyword' => 'Single Keyword', 'numerical' => 'Two Values'], isset(Auth::user()->bannerData['size_type']) ? Auth::user()->bannerData['size_type'] : null, [
                            'class' => 'form-control banner-size-select',
                            'placeholder' => 'Select Background Size Type',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="banner-size-options"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('Background Position Type') !!} {!! add_help('Keyword will allow you to select from keywords. Two Values will allow you to enter up to two valid numerical or keyword values.') !!}
                        {!! Form::select('position_type', ['keyword' => 'Single Keyword', 'numerical' => 'Two Values'], isset(Auth::user()->bannerData['position_type']) ? Auth::user()->bannerData['position_type'] : null, [
                            'class' => 'form-control banner-position-select',
                            'placeholder' => 'Select Background Position Type',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="banner-position-options"></div>
                </div>
            </div>

            <div class="d-flex flex-column flex-sm-row align-items-end align-items-sm-center justify-content-end">
                {!! Form::submit('Edit Banner Styling', ['class' => 'btn btn-primary mr-sm-2 mb-1 mb-sm-0']) !!}
                {!! Form::close() !!}

                {!! Form::open(['url' => 'account/banner-delete']) !!}
                {!! Form::submit('Delete Banner Image', ['class' => 'btn btn-danger']) !!}
                {!! Form::close() !!}
            </div>

            <div class="hide">
                <div class="form-group banner-size-keyword">
                    {!! Form::label('Keyword') !!}
                    {!! Form::select('size_1', ['auto' => 'Auto', 'cover' => 'Cover', 'contain' => 'Contain'], isset(Auth::user()->bannerData['size_1']) ? Auth::user()->bannerData['size_1'] : 'auto', ['class' => 'form-control']) !!}
                </div>

                <div class="form-group row no-gutters banner-size-numerical mb-2">
                    <div class="col pr-2">
                        {!! Form::label('Value One') !!} {!! add_help('The value must either be auto or ending with a valid unit, such as %, px, em, etc.') !!}
                        {!! Form::text('size_1', isset(Auth::user()->bannerData['size_1']) ? Auth::user()->bannerData['size_1'] : null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col">
                        {!! Form::label('Value Two (Opt.)') !!} {!! add_help('The value must either be auto or ending with a valid unit, such as %, px, em, etc. If you do not set this value, it will default to auto.') !!}
                        {!! Form::text('size_2', isset(Auth::user()->bannerData['size_2']) ? Auth::user()->bannerData['size_2'] : null, ['class' => 'form-control']) !!}
                    </div>
                </div>

                <div class="form-group banner-position-keyword">
                    {!! Form::label('Keyword') !!} {!! add_help('If a keyword is selected, then the Y axis is assumed to be 50%') !!}
                    {!! Form::select('position_x', ['top' => 'Top', 'bottom' => 'Bottom', 'left' => 'Left', 'right' => 'Right', 'center' => 'Center'], isset(Auth::user()->bannerData['position_x']) ? Auth::user()->bannerData['position_x'] : 'top', [
                        'class' => 'form-control',
                    ]) !!}
                </div>

                <div class="form-group row no-gutters banner-position-numerical mb-2">
                    <div class="col pr-2">
                        {!! Form::label('X Value') !!} {!! add_help('The value must either be left, right, or center, or end with a valid unit, such as %, px, em, etc.') !!}
                        {!! Form::text('position_x', isset(Auth::user()->bannerData['position_x']) ? Auth::user()->bannerData['position_x'] : null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col">
                        {!! Form::label('Y Value (Opt.)') !!} {!! add_help('The value must either be top, bottom, or center, such as %, px, em, etc. If you do not set this value, it will default to 50%.') !!}
                        {!! Form::text('position_y', isset(Auth::user()->bannerData['position_y']) ? Auth::user()->bannerData['position_y'] : null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if (config('lorekeeper.settings.allow_username_changes'))
        <div class="card p-3 mb-2">
            <h3>Change Username</h3>
            @if (config('lorekeeper.settings.username_change_cooldown'))
                <div class="alert alert-info">
                    You can change your username once every {{ config('lorekeeper.settings.username_change_cooldown') }} days.
                </div>
                @if (Auth::user()->logs()->where('type', 'Username Change')->orderBy('created_at', 'desc')->first())
                    <div class="alert alert-warning">
                        You last changed your username on {{ Auth::user()->logs()->where('type', 'Username Change')->orderBy('created_at', 'desc')->first()->created_at->format('F jS, Y') }}.
                        <br />
                        <b>
                            You will be able to change your username again on
                            {{ Auth::user()->logs()->where('type', 'Username Change')->orderBy('created_at', 'desc')->first()->created_at->addDays(config('lorekeeper.settings.username_change_cooldown'))->format('F jS, Y') }}.
                        </b>
                    </div>
                @endif
            @endif
            {!! Form::open(['url' => 'account/username']) !!}
            <div class="form-group row">
                <label class="col-md-2 col-form-label">Username</label>
                <div class="col-md-10">
                    {!! Form::text('username', Auth::user()->name, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="text-right">
                {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    @endif

    <div class="card p-3 mb-2">
        <h3>Profile</h3>
        {!! Form::open(['url' => 'account/profile']) !!}
        <div class="form-group">
            {!! Form::label('text', 'Profile Text') !!}
            {!! Form::textarea('text', Auth::user()->profile->text, ['class' => 'form-control wysiwyg']) !!}
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

<div class="card p-3 mb-2">
    <h3>Theme</h3>
    <p>Change the way the site looks for you! </p>
    {!! Form::open(['url' => 'account/theme']) !!}
        <div class="form-group row">
            <label class="col-md-3 col-form-label">Base Theme</label>
            <div class="col-md-9">
                {!! Form::select('theme', $themeOptions, Auth::user()->theme_id ? Auth::user()->theme_id : ($defaultTheme ? $defaultTheme->id : 0) , ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-3 col-form-label">Decorator Theme {!! add_help('A second complimentary theme that is layered over your base theme, and usually affects only a few pieces of the site.') !!}</label> 
            <div class="col-md-9">
                {!! Form::select('decorator_theme', $decoratorThemes, Auth::user()->decorator_theme_id ? Auth::user()->decorator_theme_id : null , ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>


<div class="card p-3 mb-2">
    <h3>Birthday Publicity</h3>
    {!! Form::open(['url' => 'account/dob']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Setting</label>
            <div class="col-md-10">
                {!! Form::select(
                    'birthday_setting',
                    ['0' => '0: No one can see your birthday.', '1' => '1: Members can see your day and month.', '2' => '2: Anyone can see your day and month.', '3' => '3: Full date public.'],
                    Auth::user()->settings->birthday_setting,
                    ['class' => 'form-control'],
                ) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Email Address</h3>
        <p>Changing your email address will require you to re-verify your email address.</p>
        {!! Form::open(['url' => 'account/email']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Email Address</label>
            <div class="col-md-10">
                {!! Form::text('email', Auth::user()->email, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>
        
    <div class="card p-3 mb-2">
        <h3>Change Password</h3>
        {!! Form::open(['url' => 'account/password']) !!}
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Old Password</label>
            <div class="col-md-10">
                {!! Form::password('old_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group row">
            <label class="col-md-2 col-form-label">Confirm New Password</label>
            <div class="col-md-10">
                {!! Form::password('new_password_confirmation', ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    <div class="card p-3 mb-2">
        <h3>Border</h3>
        <p>Change your onsite border.</p>
        <p>Standard borders behave as normal. Variants may be different colors or even border styles than the main border. If your chosen main border has a "layer" associated with it, you can layer that image with one of its variant's borders.</p>
        <p>Variants supersede standard borders, and layers supersede variants.</p>
        {!! Form::open(['url' => 'account/border']) !!}
        <div class="row">
            <div class="col-md-6 form-group">
                {!! Form::label('Border') !!}
                {!! Form::select('border', $borders, Auth::user()->border_id, ['class' => 'form-control', 'id' => 'border']) !!}
            </div>
            <div class="col-md-6 form-group">
                {!! Form::label('Border Variant') !!}
                {!! Form::select('border_variant_id', $border_variants, Auth::user()->border_variant_id, ['class' => 'form-control', 'id' => 'bordervariant']) !!}
            </div>
        </div>
        <div id="layers">
        </div>
        <h4>Border Style</h4>
        <h5>Flip</h5>
        <p>Flip this border (horizontally.)</p>
        <div class="row">
            <div class="col-md-6 form-group">
                {!! Form::checkbox('border_flip', 1, Auth::user()->settings->border_settings['border_flip'] ?? 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('border_flip', 'Flip Border', ['class' => 'form-check-label ml-3']) !!}
            </div>
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}

        <h3 class="text-center">Your Borders</h3>
        <div class="card p-3 mb-2 image-info-box">
            @if ($default->count())
                <h4 class="mb-0">Default</h4>
                <hr class="mt-0">
                <div class="row">
                    @foreach ($default as $border)
                        <div class="col-md-3 col-6 text-center">
                            <div class="shop-image">
                                {!! $border->preview() !!}
                            </div>
                            <div class="shop-name mt-1 text-center">
                                <h5>{!! $border->displayName !!}</h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if (Auth::user()->borders->count())
                <h4 class="mb-0">Unlocked</h4>
                <hr class="mt-0">
                <div class="row">
                    @foreach (Auth::user()->borders as $border)
                        <div class="col-md-3 col-6 text-center">
                            <div class="shop-image">
                                {!! $border->preview() !!}
                            </div>
                            <div class="shop-name mt-1 text-center">
                                <h5>{!! $border->displayName !!}</h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @if (Auth::user()->isStaff)
                @if ($admin->count())
                    <h4 class="mb-0">Staff-Only</h4>
                    <hr class="mt-0">
                    <small>You can see these as a member of staff</small>
                    <div class="row">
                        @foreach ($admin as $border)
                            <div class="col-md-3 col-6 text-center">
                                <div class="shop-image">
                                    {!! $border->preview() !!}
                                </div>
                                <div class="shop-name mt-1 text-center">
                                    <h5>{!! $border->displayName !!}</h5>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
        <div class="text-right mb-4">
            <a href="{{ url(Auth::user()->url . '/border-logs') }}">View logs...</a>
        </div>
    </div>


    <div class="card p-3 mb-2">
        <h3>Two-Factor Authentication</h3>

        <p>Two-factor authentication acts as a second layer of protection for your account. It uses an app on your phone-- such as Google Authenticator-- and information provided by the site to generate a random code that changes frequently.</p>

        <div class="alert alert-info">
            Please note that two-factor authentication is only used when logging in directly to the site (with an email address and password), and not when logging in via an off-site account. If you log in using an off-site account, consider enabling
            two-factor authentication on that site instead!
        </div>

        @if (!isset(Auth::user()->two_factor_secret))
            <p>In order to enable two-factor authentication, you will need to scan a QR code with an authenticator app on your phone. Two-factor authentication will not be enabled until you do so and confirm by entering one of the codes provided by your
                authentication app.</p>
            {!! Form::open(['url' => 'account/two-factor/enable']) !!}
            <div class="text-right">
                {!! Form::submit('Enable', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @elseif(isset(Auth::user()->two_factor_secret))
            <p>Two-factor authentication is currently enabled.</p>

            <h4>Disable Two-Factor Authentication</h4>
            <p>To disable two-factor authentication, you must enter a code from your authenticator app.</p>
            {!! Form::open(['url' => 'account/two-factor/disable']) !!}
            <div class="form-group row">
                <label class="col-md-2 col-form-label">Code</label>
                <div class="col-md-10">
                    {!! Form::text('code', null, ['class' => 'form-control']) !!}
                </div>
            </div>
            <div class="text-right">
                {!! Form::submit('Disable', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @endif
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            refreshBorder();

            var $bannerSizeKeyword = $('.banner-size-keyword');
            var $bannerSizeNumerical = $('.banner-size-numerical');
            var $bannerPositionKeyword = $('.banner-position-keyword');
            var $bannerPositionNumerical = $('.banner-position-numerical');
            var $sizeCell = $('.banner-size-options');
            var $positionCell = $('.banner-position-options');

            @if (isset(Auth::user()->bannerData['size_type']))
                if ('{{ Auth::user()->bannerData['size_type'] }}' == 'keyword') $sizeCell.append($bannerSizeKeyword);
                if ('{{ Auth::user()->bannerData['size_type'] }}' == 'numerical') $sizeCell.append($bannerSizeNumerical);
            @endif

            @if (isset(Auth::user()->bannerData['position_type']))
                if ('{{ Auth::user()->bannerData['position_type'] }}' == 'keyword') $positionCell.append($bannerPositionKeyword);
                if ('{{ Auth::user()->bannerData['position_type'] }}' == 'numerical') $positionCell.append($bannerPositionNumerical);
            @endif

            $('.banner-size-select').on('change', function(e) {
                var val = $(this).val();

                var $clone = null;
                if (val == 'keyword') $clone = $bannerSizeKeyword.clone();
                else if (val == 'numerical') $clone = $bannerSizeNumerical.clone();

                $sizeCell.html('');
                $sizeCell.append($clone);
            });

            $('.banner-position-select').on('change', function(e) {
                var val = $(this).val();

                var $clone = null;
                if (val == 'keyword') $clone = $bannerPositionKeyword.clone();
                else if (val == 'numerical') $clone = $bannerPositionNumerical.clone();

                $positionCell.html('');
                $positionCell.append($clone);
            });

            $('body').tooltip({
                selector: '.help-icon'
            });

        });

        $("#border").change(function() {
            refreshBorder();
        });

        function refreshBorder() {
            var border = $('#border').val();
            $.ajax({
                type: "GET",
                url: "{{ url('account/check-border') }}?border=" + border,
                dataType: "text"
            }).done(function(res) {
                $("#bordervariant").html(res);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
            $.ajax({
                type: "GET",
                url: "{{ url('account/check-layers') }}?border=" + border,
                dataType: "text"
            }).done(function(res) {
                $("#layers").html(res);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                alert("AJAX call failed: " + textStatus + ", " + errorThrown);
            });
        };
    </script>
@endsection
