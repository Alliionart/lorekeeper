@extends('admin.layout')

@section('admin-title')
    Breeding Requests
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Breeding Requests' => 'admin/breedings']) !!}

    <h1>Breeding Requests</h1>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/breedings/pending*') }} {{ set_active('admin/breedings') }}" href="{{ url('admin/breedings/pending') }}">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/breedings/approved*') }}" href="{{ url('admin/breedings/approved') }}">Approved</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ set_active('admin/breedings/rejected*') }}" href="{{ url('admin/breedings/rejected') }}">Rejected</a>
        </li>
    </ul>

    {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
    <div class="form-inline justify-content-end mb-3" style="min-width:150px">
        {!! Form::select('user_ids', $users, Request::get('user_ids'), ['class' => 'form-control selectize w-100']) !!}
    </div>

    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select(
                'sort',
                [
                    'newest' => 'Newest First',
                    'oldest' => 'Oldest First',
                ],
                Request::get('sort') ?: 'oldest',
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::select('month', $months, Request::get('month') ?: '', ['class' => 'form-control']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    {!! $breedings->render() !!}
    <div class="mb-4 logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-6 col-md-1">
                    <div class="logs-table-cell">Month</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">User</div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="logs-table-cell">Parents</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Link</div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">Submitted</div>
                </div>
                <div class="col-6 col-md-1">
                    <div class="logs-table-cell">Type</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($breedings as $breeding)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-6 col-md-1">
                            <div class="logs-table-cell">{{ $breeding->created_at->format('F Y') }}</div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">{!! $breeding->user->displayName !!}</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">
                                {!! $breeding->parent(1)->first()->displayName !!}
                                <span class="mx-1">x</span>
                                {!! $breeding->parent(2)->first()->displayName !!}
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">
                                <span class="ubt-texthide"><a href="{{ $breeding->url }}">Request #{{ $breeding->id }}</a></span>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="logs-table-cell">{!! pretty_date($breeding->created_at) !!}</div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell">
                                <span class="btn btn-{{ $breeding->type == 'Full' ? 'warning' : 'success' }} btn-sm py-0 px-1">{{ $breeding->type }}</span>
                            </div>
                        </div>
                        <div class="col-3 col-md-1">
                            <div class="logs-table-cell"><a href="{{ $breeding->adminUrl }}" class="btn btn-primary btn-sm py-0 px-1">Details</a></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    {!! $breedings->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $breedings->total() }} result{{ $breedings->total() == 1 ? '' : 's' }} found.</div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>
@endsection
