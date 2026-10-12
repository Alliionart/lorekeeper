@extends('user.layout')

@section('profile-title')
    {{ $user->name }}'s Breeding Logs
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Breeding Logs' => $user->url . '/breeding-logs']) !!}

    <h1>
        {!! $user->displayName !!}'s Breeding Logs
    </h1>

    {!! $logs->render() !!}
    <div class="row ml-md-2 mb-4">
        <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
            <div class="col-6 col-md-1 font-weight-bold">Sender</div>
            <div class="col-6 col-md-1 font-weight-bold">Month</div>
            <div class="col-6 col-md-2 font-weight-bold">Breeding</div>
            <div class="col-6 col-md-3 font-weight-bold">Parents</div>
            <div class="col-6 col-md-3 font-weight-bold">Log</div>
            <div class="col-6 col-md-2 font-weight-bold">Date</div>
        </div>
        @foreach ($logs as $log)
            <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                <div class="col-6 col-md-1">
                    {!! $log->sender ? $log->sender->displayName : '' !!}
                </div>
                <div class="col-6 col-md-1">
                    {{ $log->created_at->format('F Y') }}
                </div>
                <div class="col-6 col-md-2">{!! $log->breeding ? $log->breeding->displayName : '(Deleted Breeding)' !!}</div>
                <div class="col-6 col-md-3">{!! $log->breeding ? $log->breeding->parentsX : '' !!}</div>
                <div class="col-6 col-md-3">{!! $log->log !!}
                    @if ($log->data)
                        <?php $data = json_decode($log->data, true); ?>
                        @if (isset($data['count']))
                            <span class="ml-2">({{ $data['count'] }} Pups)</span>
                        @endif
                    @endif
                </div>
                <div class="col-6 col-md-2">{!! pretty_date($log->created_at) !!}</div>
            </div>
        @endforeach
    </div>
    {!! $logs->render() !!}
@endsection
