@extends('admin.layout')

@section('admin-title')
    Discord Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Discord Rewards' => 'admin/discord/rewards', ($reward->id ? 'Edit' : 'Create') . ' Discord Reward' => $reward->id ? 'admin/discord/rewards/edit/' . $reward->id : 'admin/discord/rewards/create']) !!}

    <h1>{{ $reward->id ? 'Edit' : 'Create' }} Discord Reward
        @if ($reward->id)
            <a href="#" class="btn btn-danger float-right delete-reward-button">Delete Discord Reward</a>
        @endif
    </h1>

    {!! Form::open(['url' => $reward->id ? 'admin/discord/rewards/edit/' . $reward->id : 'admin/discord/rewards/create']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Level') !!} {!! add_help('What level should these rewards be distributed at?') !!}
        {!! Form::number('level', $reward->level, ['class' => 'form-control', 'min' => 1]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Role Reward (Optional)') !!} {!! add_help('When this user reaches this rank, have the role assigned automatically. Must be the role ID.') !!}
        {!! Form::number('role_reward_id', $reward->role_reward_id, ['class' => 'form-control']) !!}
    </div>

    <h3>Rewards</h3>
    @include('widgets._loot_select', ['loots' => $reward->rewards, 'showLootTables' => true, 'showRaffles' => true])


    <div class="text-right">
        {!! Form::submit($reward->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    <script>
        $(document).ready(function() {
            $('.delete-reward-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/discord/rewards/delete') }}/{{ $reward->id }}", 'Delete Discord Reward');
            });
        });
    </script>
@endsection
