@extends('queues.layout')

@section('queues-title')
    All Queues
@endsection

@section('content')
    {!! breadcrumbs(['Queues' => 'queues', 'All Queues' => 'queues/queues']) !!}
    <h1>All Queues</h1>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
               {!! Form::select('prompt_category_id', $categories, Request::get('prompt_category_id'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-inline justify-content-end">
                <div class="form-group ml-3 mb-3">
                    {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
                </div>
            </div>
            {!! Form::close() !!}
        </div>

        {!! $submissions->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    @if (!$isClaims)
                        <div class="col-12 col-md-2">
                            <div class="logs-table-cell">Prompt</div>
                        </div>
                        <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                            <div class="logs-table-cell">User</div>
                        </div>
                        <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                            <div class="logs-table-cell">Link</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="logs-table-cell">Submitted</div>
                        </div>
                        <div class="col-6 col-md-1">
                            <div class="logs-table-cell">Status</div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($submissions as $submission)
                    <div class="logs-table-row" style="{{ $submission->user->id == Auth::user()->id ? 'background-color:rgba(48, 121, 240, 0.1);' : '' }}">
                        <div class="row flex-wrap">
                            @if (!$isClaims)
                                <div class="col-12 col-md-2">
                                    <div class="logs-table-cell">{!! $submission->prompt->displayName !!}</div>
                                </div>
                                {!! $submission->prompt->public_queue !!}
                                <div class="col-6 {{ !$isClaims ? 'col-md-2' : 'col-md-3' }}">
                                    <div class="logs-table-cell">{!! $submission->user->displayName !!}</div>
                                </div>
                                <div class="col-6 {{ !$isClaims ? 'col-md-3' : 'col-md-4' }}">
                                    <div class="logs-table-cell">
                                        <span class="ubt-texthide"><a href="{{ $submission->url }}">{{ $submission->url }}</a></span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="logs-table-cell">{!! pretty_date($submission->created_at) !!}</div>
                                </div>
                                <div class="col-3 col-md-1">
                                    <div class="logs-table-cell">
                                        <span class="btn btn-{{ $submission->status == 'Pending' ? 'secondary' : ($submission->status == 'Approved' ? 'success' : 'danger') }} btn-sm py-0 px-1">{{ $submission->status }}</span>
                                    </div>
                                </div>
                                <div class="col-3 col-md-1">
                                    <div class="logs-table-cell"></div>
                                </div>
                                @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    {!! $submissions->render() !!}
    <div class="text-center mt-4 small text-muted">{{ $submissions->total() }} result{{ $submissions->total() == 1 ? '' : 's' }} found.</div>

        
    @endsection
