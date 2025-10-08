@extends('character.layout', ['isMyo' => $character->is_myo_slot])

@section('profile-title')
    Editing {{ $character->fullName }}'s Profile
@endsection

@section('meta-img')
    {{ $character->image->thumbnailUrl }}
@endsection

@section('profile-content')
    @if ($character->is_myo_slot)
        {!! breadcrumbs(['MYO Slot Masterlist' => 'myos', $character->fullName => $character->url, 'Editing Profile' => $character->url . '/profile/edit']) !!}
    @else
        {!! breadcrumbs([
            $character->category->masterlist_sub_id ? $character->category->sublist->name . ' Masterlist' : 'Character masterlist' => $character->category->masterlist_sub_id ? 'sublist/' . $character->category->sublist->key : 'masterlist',
            $character->fullName => $character->url,
            'Editing Profile' => $character->url . '/profile/edit',
        ]) !!}
    @endif

    @include('character._header', ['character' => $character])

    @if ($character->user_id != Auth::user()->id)
        <div class="alert alert-warning">
            You are editing this character as a staff member.
        </div>
    @endif

    {!! Form::open(['url' => $character->url . '/profile/edit']) !!}
    @if (!$character->is_myo_slot)
        <div class="form-group">
            {!! Form::label('name', 'Name') !!}
            {!! Form::text('name', $character->name, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('nickname', 'Nickname(s)') !!}
            {!! Form::text('nickname', $character->nickname, ['class' => 'form-control']) !!}
        </div>
        <div class="form-group location-form">
            {!! Form::label('location', 'Location') !!}
            {!! Form::select('location', $locations, $character->location ?? null, ['class' => 'form-control selectize', 'required']) !!}
            <div class="alert mt-2 p-2 border-warning alert-warning" style="display:none;">Changing your character's location requires x1 {!! $lItem->displayName !!}. You currently have {{ $user_item_amount }} available. Upon editing your character it will be automatically removed from your inventory.</div>
        </div>
        <div class="form-group background-refresh">
            {!! Form::label('background', 'Background') !!}
            {!! Form::select('background', $character->applicableBackgrounds(), null, ['class' => 'form-control selectize', 'required']) !!}
            <div class="alert mt-2 p-2 border-warning alert-warning" style="display:none;">Changing your character's background requires {{ $bg_amount }} {!! $bg_currency->displayName !!}. You currently have {{ $user_cur_amount }} available.</div>
        </div>

        @if (config('lorekeeper.extensions.character_TH_profile_link'))
            <div class="form-group">
                {!! Form::label('link', 'Profile Link') !!}
                {!! Form::text('link', $character->profile->link, ['class' => 'form-control']) !!}
            </div>
        @endif
    @endif
    <div class="form-group">
        {!! Form::label('text', 'Profile Content') !!}
        {!! Form::textarea('text', $character->profile->text, ['class' => 'wysiwyg form-control']) !!}
    </div>

    @if ($character->user_id == Auth::user()->id)
        @if (!$character->is_myo_slot)
            <div class="row">
                <div class="col-md form-group">
                    {!! Form::label('is_gift_art_allowed', 'Allow Gift Art', ['class' => 'form-check-label mb-3']) !!} {!! add_help('This will place the character on the list of characters that can be drawn for gift art. This does not have any other functionality, but allow users looking for characters to draw to find your character easily.') !!}
                    {!! Form::select('is_gift_art_allowed', [0 => 'No', 1 => 'Yes', 2 => 'Ask First'], $character->is_gift_art_allowed, ['class' => 'form-control user-select']) !!}
                </div>
                <div class="col-md form-group">
                    {!! Form::label('is_gift_writing_allowed', 'Allow Gift Writing', ['class' => 'form-check-label mb-3']) !!} {!! add_help(
                        'This will place the character on the list of characters that can be written about for gift writing. This does not have any other functionality, but allow users looking for characters to write about to find your character easily.',
                    ) !!}
                    {!! Form::select('is_gift_writing_allowed', [0 => 'No', 1 => 'Yes', 2 => 'Ask First'], $character->is_gift_writing_allowed, ['class' => 'form-control user-select']) !!}
                </div>
            </div>
        @endif
        @if ($character->is_tradeable || $character->is_sellable)
            <div class="form-group disabled">
                {!! Form::checkbox('is_trading', 1, $character->is_trading, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_trading', 'Up For Trade', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will place the character on the list of characters that are currently up for trade. This does not have any other functionality, but allow users looking for trades to find your character easily.') !!}
            </div>
        @else
            <div class="alert alert-secondary">Cannot be set to "Up for Trade" as character cannot be traded or sold.</div>
        @endif
    @endif
    @if ($character->user_id != Auth::user()->id)
        <div class="form-group">
            {!! Form::checkbox('alert_user', 1, true, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'data-onstyle' => 'danger']) !!}
            {!! Form::label('alert_user', 'Notify User', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This will send a notification to the user that their character profile has been edited. A notification will not be sent if the character is not visible.') !!}
        </div>
    @endif
    <div class="text-right">
        {!! Form::submit('Edit Profile', ['class' => 'btn btn-primary']) !!}
    </div>
    {!! Form::close() !!}

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var currentLocation = '{{ $character->location }}';
            var currentBg = {{ $character->background_id }};
            var $bgSelect = $('#background').selectize({
                allowClear: true,
            });
            $('#location').selectize({
                allowClear: true,
            });

            $('#location').on('change', function() {
                refreshBackgroundOptions();
                if(currentLocation !== $(this).val()) {
                    $('.location-form .alert').show();
                } else {
                    $('.location-form .alert').hide();
                }
            });

            $('#background').on('change', function() {
                if(currentBg !== $(this).val() && currentLocation === $('#location').val()) {
                    $('.background-refresh .alert').show();
                } else {
                    $('.background-refresh .alert').hide();
                }
            });

            //On change location prop the alerts and refresh the background list
            function refreshBackgroundOptions() {
                var location = $('#location').val();
                var id = {{ $character->id }};
                $.ajax({
                    type: 'GET',
                    url: "{{ url('character/'.$character->slug.'/get-bg-options') }}?id=" + id + '&location=' + location,
                    dataType: 'text',
                }).done(function(res) {
                    $('.background-refresh').html(res);
                    $('.background-refresh .selectize').selectize({
                        allowClear: true,
                    });

                }).fail(function(jqXHR, textStatus, errorThrown) {
                    alert("AJAX call failed: " + textStatus + ", " + errorThrown);
                });
            }
        });
    </script>
@endsection