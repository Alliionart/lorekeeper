@extends('layouts.app')

@section('title')
    Manage {{ $guild->name }} Applications
@endsection

@section('sidebar')
    @include('guilds._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ucwords(__('guilds.guilds')) => __('guilds.guilds'), $guild->name => $guild->viewUrl, 'Manage Applications' => 'manage-applications']) !!}

    <div class="row align-items-end">
        <div class="col-md-8">
            <h1>Manage {{ $guild->name }} Applications</h1>
            <p>Manage the applications for your {{ __('guilds.guild') }} below. Only {{ __('guilds.guild') }} owners and mods may edit the guild. Staff may manage applications as well.</p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}" href="{{ $guild->viewUrl }}/applications/pending">Pending</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'accepted' ? 'active' : '' }}" href="{{ $guild->viewUrl }}/applications/accepted">Accepted</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $status === 'rejected' ? 'active' : '' }}" href="{{ $guild->viewUrl }}/applications/rejected">Rejected</a>
        </li>
    </ul>

    @if (!$guild->open_new_users)
        <div class="alert alert-warning my-3">
            <p>This {{ __('guilds.guild') }} is not currently accepting new applications. You may still manage existing applications below.</p>
        </div>
    @endif

    {!! Form::open(['url' => '/' . __('guilds.guilds') . '/' . $guild->id . '/applications/', 'id' => 'guildAppForm']) !!}
    @if ($applications->isNotEmpty())
        <table class="table">
            <thead>
                <tr>
                    @if ($status === 'pending')
                        <th scope="col" width="10%">Select</th>
                    @endif
                    <th scope="col">User</th>
                    <th scope="col">Date Applied</th>
                    @if ($status === 'pending')
                        <th scope="col">Comment</th>
                        <th scope="col">Response</th>
                    @elseif ($status === 'accepted')
                        <th scope="col">Date Accepted</th>
                    @else
                        <th scope="col">Response</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($applications as $application)
                    <tr>
                        @if ($status === 'pending')
                            <th scope="row">{!! Form::checkbox('application_ids[]', $application->id) !!}</th>
                        @endif
                        <td>{!! $application->user->displayName !!}</td>
                        <td>{!! pretty_date($application->created_at) !!}</td>
                        @if ($status === 'pending')
                            <td>{{ $application->comment }}</td>
                            <td>
                                {!! Form::hidden('guild_comment[' . $application->id . ']') !!}
                                <a href="#" class="btn btn-sm gc-btn btn-primary">Add Comment</a>
                            </td>
                        @elseif ($status === 'accepted')
                            <td>{!! pretty_date($application->updated_at) !!}</td>
                        @else
                            <td>{{ $application->guild_comment }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No current guild applications.</p>
    @endif

    @if ($status === 'pending')
        <div class="text-right mt-4">
            <button type="submit" class="btn btn-success" formaction="{{ $guild->viewUrl }}/applications/accept">Approve Selected</button>
            <button type="submit" class="btn btn-danger" formaction="{{ $guild->viewUrl }}/applications/reject">Reject Selected</button>
        </div>
    @endif
    {!! Form::close() !!}

    <div class="modal fade" id="commentModal" tabindex="-1" role="dialog" aria-labelledby="commentModalTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mb-0" id="commentModalTitle">Add Comment</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Comment to add to the selected application:</p>
                    <div class="form-group">
                        {!! Form::textarea('temp_comment', null, ['class' => 'form-control', 'id' => 'guildCommentInput', 'rows' => 3]) !!}
                    </div>
                    <div class="text-right">
                        <button type="button" id="commentSubmit" class="btn btn-primary">Submit Comment</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $commentModal = $('#commentModal');
            var $commentSubmit = $('#commentSubmit');
            var $commentInput = $('#guildCommentInput');
            var $activeCommentField = $();

            $('.gc-btn').on('click', function(e) {
                e.preventDefault();
                var $row = $(this).closest('tr');
                $activeCommentField = $row.find('input[name^="guild_comment["]');
                $commentInput.val($activeCommentField.val());

                $commentModal.modal('show');
            });

            $commentSubmit.on('click', function() {
                if (!$activeCommentField.length) {
                    return;
                }

                $activeCommentField.val($commentInput.val());
                $commentInput.val('');
                $commentModal.modal('hide');
            });
        });
    </script>
@endsection
