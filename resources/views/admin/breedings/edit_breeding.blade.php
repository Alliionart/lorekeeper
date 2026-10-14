@extends('admin.layout')

@section('admin-title')
    Breeding (#{{ $breeding->id }})
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Breeding Queue' => 'admin/breedings/pending', 'Breeding (#' . $breeding->id . ')' => $breeding->viewUrl]) !!}

    <h1>
        Breeding Request (#{{ $breeding->id }})
        <div class="float-right">
            @if ($breeding->rerolls > 1)
                <span class="badge badge-warning">Rerolled x{{ $breeding->rerolls }}</span>
            @endif
            <span class="badge badge-{{ $breeding->type == 'full' ? 'warning' : 'success' }}">{{ $breeding->type }}</span>
            <span class="badge badge-{{ $breeding->status == 'Pending' ? 'secondary' : ($breeding->status == 'Rejected' ? 'danger' : 'success') }}">{{ $breeding->status }}</span>
        </div>
    </h1>
    <div class="mb-1">
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>User</h5>
            </div>
            <div class="col-md-10 col-8">{!! $breeding->user->displayName !!}</div>
        </div>
        <div class="row">
            <div class="col-md-2 col-4">
                <h5>Submitted</h5>
            </div>
            <div class="col-md-10 col-8">{!! format_date($breeding->created_at) !!} ({{ $breeding->created_at->diffForHumans() }})</div>
        </div>
    </div>

    {!! Form::open(['url' => url()->current(), 'id' => 'breedingForm', 'class' => 'mt-4']) !!}

    @include('breeding._details', ['breeding' => $breeding, 'show_fields' => $breeding->status == 'Pending', 'userCanTakeAction' => false])

    <input type="hidden" name="breeding_id" readonly value="{{ $breeding->id }}" />
    @if ($breeding->status === 'Approved')
        <input type="hidden" name="reroll" readonly value="1" />
    @endif
    <div class="text-right mt-3 pt-2">
        <hr />
        <a href="#" class="btn btn-secondary mr-2" id="cancelButton">Cancel</a>
        <a href="#" class="btn btn-danger mr-2" id="rejectButton">Reject</a>
        <a href="#" class="btn btn-success" id="approvalButton">{{ $breeding->status === 'Approved' ? 'Re-' : '' }}Roll Breeding</a>
    </div>
    {!! Form::close() !!}

    <div class="modal fade" id="confirmationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content hide" id="approvalContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Breeding</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will roll the breeding and notify the player of their litter.</p>
                    @if ($breeding->rerolls > 1)
                        <div>This breeding has been rerolled {!! $breeding->rerolls !!} times, are you sure you want to reroll this breeding?</div>
                    @endif
                    <div class="text-right">
                        <a href="#" id="approvalSubmit" class="btn btn-success">Roll Breeding</a>
                    </div>
                </div>
            </div>
            <div class="modal-content hide" id="cancelContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Cancellation</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will cancel the breeding.</p>
                    <div class="text-right">
                        <a href="#" id="cancelSubmit" class="btn btn-secondary">Cancel Breeding</a>
                    </div>
                </div>
            </div>
            <div class="modal-content hide" id="rejectContent">
                <div class="modal-header">
                    <span class="modal-title h5 mb-0">Confirm Rejection</span>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>This will reject the breeding.</p>
                    <div class="text-right">
                        <a href="#" id="rejectSubmit" class="btn btn-danger">Reject Breeding</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@if ($breeding->status !== 'Closed')
    @section('scripts')
        @parent
        <script>
            $(document).ready(function() {

                var $confirmationModal = $('#confirmationModal');
                var $trackerForm = $('#breedingForm');

                var $approvalButton = $('#approvalButton');
                var $approvalContent = $('#approvalContent');
                var $approvalSubmit = $('#approvalSubmit');

                var $cancelButton = $('#cancelButton');
                var $cancelContent = $('#cancelContent');
                var $cancelSubmit = $('#cancelSubmit');

                var $rejectButton = $('#rejectButton');
                var $rejectContent = $('#rejectContent');
                var $rejectSubmit = $('#rejectSubmit');

                $approvalButton.on('click', function(e) {
                    e.preventDefault();
                    $approvalContent.removeClass('hide');
                    $cancelContent.addClass('hide');
                    $rejectContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $cancelButton.on('click', function(e) {
                    e.preventDefault();
                    $cancelContent.removeClass('hide');
                    $approvalContent.addClass('hide');
                    $rejectContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $rejectButton.on('click', function(e) {
                    e.preventDefault();
                    $rejectContent.removeClass('hide');
                    $approvalContent.addClass('hide');
                    $cancelContent.addClass('hide');
                    $confirmationModal.modal('show');
                });

                $approvalSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url()->current() }}/approve');
                    $trackerForm.submit();
                });

                $cancelSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url()->current() }}/cancel');
                    $trackerForm.submit();
                });

                $rejectSubmit.on('click', function(e) {
                    e.preventDefault();
                    $trackerForm.attr('action', '{{ url()->current() }}/reject');
                    $trackerForm.submit();
                });

            });
        </script>
    @endsection
@endif
