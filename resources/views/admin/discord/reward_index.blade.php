@extends('admin.layout')

@section('admin-title')
    Discord Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Discord Rewards' => 'admin/discord/rewards']) !!}

    <h1>Discord Rewards</h1>


    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/discord/rewards/create') }}"><i class="fas fa-plus"></i> Create New Reward</a>
    </div>

    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-5 col-md-6">
                    <div class="logs-table-cell">Level</div>
                </div>
                <div class="col-5 col-md-5">
                    <div class="logs-table-cell">Rewards Count</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($rewards as $reward)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-5 col-md-6">
                            <div class="logs-table-cell">#{{ $reward->level }}</div>
                        </div>
                        <div class="col-4 col-md-5">
                            <div class="logs-table-cell">{{ count($reward->rewards) }}</div>
                        </div>
                        <div class="col-3 col-md-1 text-right">
                            <div class="logs-table-cell">
                                <a href="{{ url('admin/discord/rewards/edit/' . $reward->id) }}" class="btn btn-primary py-0 px-2">Edit</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
