@extends('user.layout')

@section('profile-title') {{ $user->name }}'s Username Logs @endsection

@section('profile-content')
{!! breadcrumbs(['Users' => 'users', $user->name => $user->url, 'Username Logs' => $user->url.'/username-logs']) !!}

<h1>
    {!! $user->displayName !!}'s Username Logs
</h1>

@if(count($logs))
  {!! $logs->render() !!}
  <div class="row ml-md-2 mb-4">
    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-bottom">
      <div class="col-3 font-weight-bold">Date</div>
      <div class="col-9 font-weight-bold">Changes</div>
    </div>
      @foreach($logs as $log)
      <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
        <div class="col-3">{!! pretty_date($log->updated_at) !!}</div>
        <div class="col-9">{{ $log->old_username }} to {{ $log->new_username }}</div>
      </div>
      @endforeach
  </div>
  {!! $logs->render() !!}
@else
  <p>No Username Changes Found.</p>
@endif

@endsection