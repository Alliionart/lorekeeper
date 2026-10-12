@if (!$raffle->id)
    <p>
        Enter basic information about this raffle. Tickets can be added after the raffle is created.
    </p>
@endif
{!! Form::open(['url' => 'admin/raffles/edit/raffle/' . ($raffle->id ?: '')]) !!}

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('name', 'Raffle Name') !!} {!! add_help('This is the name of the raffle. Naming it something after what is being raffled is suggested (does not have to be unique).') !!}
            {!! Form::text('name', $raffle->name, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('group_id', 'Raffle Group') !!} {!! add_help('Raffle groups must be created before you can select them here.') !!}
            {!! Form::select('group_id', $groups, $raffle->group_id, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('winner_count', 'Number of Winners to Draw') !!}
            {!! Form::text('winner_count', $raffle->winner_count, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('order', 'Raffle Order') !!} {!! add_help('Enter a number. If a group of raffles is rolled, raffles will be drawn in ascending order.') !!}
            {!! Form::text('order', $raffle->order ?: 0, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('ticket_cap', 'Ticket Cap (Optional)') !!} {!! add_help('A number of tickets per individual to cap at. Leave empty or unset to have no cap.') !!}
            {!! Form::text('ticket_cap', $raffle->ticket_cap ?: null, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('rewards_type', 'Rewards Type') !!}
    {!! Form::select('rewards_type', ['Tiered' => 'Tiered', 'Random' => 'Random', 'Static' => 'Static'], $raffle->rewards_type ?? 'Static', ['class' => 'form-control']) !!}
</div>
<div class="p-2 mb-3 border border-dark rounded">
    <p>Rewards type determines how winners are selected and what kind of rewards they receive.</p>
    <ul>
        <li><strong>Tiered:</strong> Rewards are distributed based on predefined tiers, with higher tiers typically offering better rewards.</li>
        <li><strong>Random:</strong> Each winner receives a randomly determined reward from a determined pool of rewards.</li>
        <li><strong>Static:</strong> Each winner receives a predetermined, fixed reward.</li>
    </ul>
</div>

<div class="form-group">
    {!! Form::label('excluded_winners', 'Excluded Users', ['class' => 'form-label h4']) !!}
    <p>Exclude selected users from the entire raffle or from a specific reward. Each entry can include up to 20 users.</p>
    <div id="excludedWinnersList">
        @foreach (old('excluded_winners', $raffle->excluded_winners ?? [['reward_id' => '', 'user_ids' => []]]) as $index => $exclusion)
            <div class="excluded-winner-row border rounded p-2 mb-2">
                <div class="form-row align-items-start">
                    <div class="col-md-5 mb-2">
                        {!! Form::select("excluded_winners[$index][reward_id]", ['' => 'Select raffle or reward'] + $rewardOptions, $exclusion['reward_id'] ?? '', ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-6 mb-2">
                        {!! Form::select("excluded_winners[$index][user_ids][]", $users, $exclusion['user_ids'] ?? [], ['class' => 'form-control excluded-user-select', 'multiple' => true, 'placeholder' => 'Select up to 20 users']) !!}
                    </div>
                    <div class="col-md-1 mb-2 text-right">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-exclusion" aria-label="Remove exclusion">&times;</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" class="btn btn-sm btn-outline-primary" id="addExclusion">Add Exclusion</button>
</div>

<template id="exclusionRowTemplate">
    <div class="excluded-winner-row border rounded p-2 mb-2">
        <div class="form-row align-items-start">
            <div class="col-md-5 mb-2">
                {!! Form::select('excluded_winners[__INDEX__][reward_id]', ['' => 'Select raffle or reward'] + $rewardOptions, null, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md-6 mb-2">
                {!! Form::select('excluded_winners[__INDEX__][user_ids][]', $users, [], ['class' => 'form-control excluded-user-select', 'multiple' => true, 'placeholder' => 'Select up to 20 users']) !!}
            </div>
            <div class="col-md-1 mb-2 text-right">
                <button type="button" class="btn btn-sm btn-outline-danger remove-exclusion" aria-label="Remove exclusion">&times;</button>
            </div>
        </div>
    </div>
</template>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label">
                {!! Form::checkbox('is_active', 1, $raffle->is_active, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_displayed', 'Active (visible to users)', ['class' => 'form-check-label ml-3']) !!}
            </label>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label class="control-label">
                {!! Form::checkbox('is_admin_only', 1, $raffle->is_admin_only, ['class' => 'form-check-input mr-2', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_admin_only', 'Admin Only', ['class' => 'form-check-label ml-3']) !!} {!! add_help('This raffle will only be visible and accessible to staff.') !!}
            </label>
        </div>
    </div>
</div>
<div class="text-right">
    {!! Form::submit('Confirm', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

<script>
    $(document).ready(function() {
        let exclusionIndex = {{ count(old('excluded_winners', $raffle->excluded_winners ?? [['reward_id' => '', 'user_ids' => []]])) }};

        function initializeUserSelects($container) {
            $container.find('.excluded-user-select:not(.selectized)').selectize({
                maxItems: 20,
                plugins: ['remove_button'],
            });
        }

        initializeUserSelects($(document));

        $('#addExclusion').on('click', function() {
            const row = $('#exclusionRowTemplate').html().replace(/__INDEX__/g, exclusionIndex++);
            const $row = $(row);
            $('#excludedWinnersList').append($row);
            initializeUserSelects($row);
        });

        $('#excludedWinnersList').on('click', '.remove-exclusion', function() {
            $(this).closest('.excluded-winner-row').remove();
        });
    });
</script>
