@extends('admin.layout')

@section('admin-title')
    Guild Request
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Guild Requests' => 'admin/guilds/queue', 'Guild Request' => 'admin/guilds/requests/' . $request->id]) !!}

    <h1>Guild Request
        <span class="float-right badge badge-{{ $request->status == 'pending' || $request->status == 'Draft' ? 'secondary' : ($request->status == 'approved' ? 'success' : 'danger') }}">
            {{ ucfirst($request->status) }}
        </span>
    </h1>

    {!! Form::open(['url' => 'admin/guilds/queue/' . $request->id . '/update', 'files' => true, 'id' => 'requestForm']) !!}

    <div class="card mb-3">
        <h5 class="card-header">Guild Request</h5>
        <div class="card-body">
            <div class="mb-1">
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>User</h5>
                    </div>
                    <div class="col-md-10 col-8">{!! $request->user->displayName !!}</div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Type</h5>
                    </div>
                    <div class="col-md-10 col-8">{!! $request->is_update ? 'Update' : 'New Guild' !!}</div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Guild Name</h5>
                    </div>
                    <div class="col-md-10 col-8">{!! $request->name !!}</div>
                </div>
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Guild Description</h5>
                    </div>
                    <div class="col-md-10 col-8">{!! $request->description !!}</div>
                </div>
                @if ($request->has_logo)
                <div class="row">
                    <div class="col-md-2 col-4">
                        <h5>Guild Logo</h5>
                    </div>
                    <div class="col-md-10 col-8">
                        <img src="{{ $request->logoUrl }}" class="img-fluid img-thumbnail mb-2" />
                        <div class="form-check">
                            {!! Form::checkbox('remove_logo', 1, false, ['class' => 'form-check-input']) !!}
                            {!! Form::label('remove_logo', 'Remove current logo', ['class' => 'form-check-label', 'id' => 'remove_logo', 'accept' => 'image/*']) !!}
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Staff Comments (Optional)') !!}
        {!! Form::textarea('staff_comments', $request->staff_comments, ['class' => 'form-control wysiwyg']) !!}
    </div>


    <div class="text-right">
        <a href="#" class="btn btn-danger mr-2" id="rejectionButton">Reject</a>
        <a href="#" class="btn btn-success" id="approvalButton">Approve</a>
    </div>

    {!! Form::close() !!}

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content hide" id="approvalContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Approval</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will approve the {{ __('guilds.guild') . $request->is_update ? ' update' : ' creation' }}.</p>
                    <div class="text-right">
                        <a href="#" id="approvalSubmit" class="btn btn-success">Approve</a>
                    </div>
                </div>
            </div>
            <div class="modal-content hide" id="rejectionContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Rejection</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will reject the {{ __('guilds.guild') . $request->is_update ? ' update' : ' creation' }}.</p>
                    <div class="text-right">
                        <a href="#" id="rejectionSubmit" class="btn btn-danger">Reject</a>
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
            
            var $confirmationModal = $('#confirmationModal');
            var $submissionForm = $('#requestForm');

            var $approvalButton = $('#approvalButton');
            var $approvalContent = $('#approvalContent');
            var $approvalSubmit = $('#approvalSubmit');

            var $rejectionButton = $('#rejectionButton');
            var $rejectionContent = $('#rejectionContent');
            var $rejectionSubmit = $('#rejectionSubmit');

            $approvalButton.on('click', function(e) {
                e.preventDefault();
                $approvalContent.removeClass('hide');
                $rejectionContent.addClass('hide');
                $confirmationModal.modal('show');
            });

            $rejectionButton.on('click', function(e) {
                e.preventDefault();
                $rejectionContent.removeClass('hide');
                $approvalContent.addClass('hide');
                $confirmationModal.modal('show');
            });

            $approvalSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.attr('action', '{{ url()->current() }}/approve');
                $submissionForm.submit();
            });

            $rejectionSubmit.on('click', function(e) {
                e.preventDefault();
                $submissionForm.attr('action', '{{ url()->current() }}/reject');
                $submissionForm.submit();
            });

        });
    </script>
@endsection
