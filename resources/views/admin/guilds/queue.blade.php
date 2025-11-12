@extends('admin.layout')

@section('admin-title')
    Guilds
@endsection

@section('admin-content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds')]) !!}

    <h1>{{ ucwords(__('guilds.guilds')) }} Queue</h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/guilds/queue/pending*') }} {{ set_active('admin/queue-submissions') }}" href="{{ url('admin/guilds/queue/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/guilds/queue/approved*') }}" href="{{ url('admin/guilds/queue/approved') }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/guilds/queue/rejected*') }}" href="{{ url('admin/guilds/queue/rejected') }}">Rejected</a>
        </li>
    </ul>

    <div class="form-inline justify-content-end">
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::select(
                'type',
                [
                    'all' => 'All',
                    'creation' => 'New Guild Requests',
                    'update' => 'Update Requests',
                ],
                Request::get('type') ?: 'all',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group mr-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First (Default)',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'newest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (isset($requests) && $requests)
        <div class="row">
            <div class="col-md col-md-12">
                {!! $requests->render() !!}
                <div class="mb-4 logs-table">
                    <div class="logs-table-header">
                        <div class="row">
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">Queue</div>
                            </div>
                            <div class="col-6 col-md-2">
                                <div class="logs-table-cell">User</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">Submitted</div>
                            </div>
                            <div class="col-6 col-md-1">
                                <div class="logs-table-cell">Status</div>
                            </div>
                        </div>
                    </div>
                    <div class="logs-table-body">
                        @foreach ($requests as $request)
                            <div class="logs-table-row">
                                <div class="row flex-wrap">
                                    <div class="col-12 col-md-2">
                                        <div class="logs-table-cell">{!! $request->queue->displayName !!}</div>
                                    </div>
                                    <div class="col-6 col-md-2">
                                        <div class="logs-table-cell">{!! $request->user->displayName !!}</div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="logs-table-cell">{!! pretty_date($request->created_at) !!}</div>
                                    </div>
                                    <div class="col-3 col-md-1">
                                        <div class="logs-table-cell">
                                            <span class="btn btn-{{ $request->status == 'Pending' ? 'secondary' : ($request->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $request->status }}</span>
                                        </div>
                                    </div>
                                    <div class="col-3 col-md-1">
                                        <div class="logs-table-cell"><a href="{{ $request->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                {!! $requests->render() !!}
                <div class="text-center mt-4 small text-muted">{{ $requests->total() }} result{{ $requests->total() == 1 ? '' : 's' }} found.</div>
            </div>
        </div>
    @else
        <p class="text-center">No guild requests found at this time.</p>
    @endif
@endsection
