@extends('breeding.layout')

@section('title')
    Breeding (#{{ $id }})
@endsection

@section('content')
    {!! breadcrumbs(['Breeding' => 'breeding'], ['Breeding #' . $id => 'breeding/' . $id]) !!}
    <h1>
        Breeding (#{{ $id }})
        <span class="float-right badge badge-{{ $breeding->status == 'Pending' || $breeding->status == 'Draft' ? 'secondary' : ($breeding->status == 'Approved' ? 'success' : 'danger') }}">{{ $breeding->status }}</span>
        @if (Auth::check() && Auth::user()->isStaff && $breeding->status != 'Draft')
            <a href="{{ url('/admin/breedings/edit/' . $breeding->id) }}" class="float-right mr-2 btn btn-warning" style="font-size: 1rem">Admin View <i class="fas fa-crown"></i></a>
        @endif
    </h1>


    <div class="row mb-3">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <span><strong>Submitted</strong> {!! pretty_date($breeding->created_at) !!}</span>
                <span class="mx-2">•</span>
                <span>{!! $breeding->user->getDisplayNameAttribute() !!}</span>
                @if ($breeding->split_user)
                    <span class="mx-2">•</span>
                    <span>{!! $breeding->split_user->getDisplayNameAttribute() !!}</span>
                @endif
            </div>
        </div>
    </div>

    <hr class="mb-3" />

    @include('breeding._details', ['breeding' => $breeding, 'show_fields' => false])

    @if (Auth::check() && Auth::user()->id === $breeding->user->id && $breeding->status === 'Pending')
        <div class="text-right mt-3">
            <a href="#" class="btn btn-secondary mr-2" id="cancelButton">Cancel</a>
        </div>
    @elseif (Auth::check() && Auth::user()->id === $breeding->user->id && $breeding->status === 'Draft')
        <div class="text-right mt-3">
            <a href="{{ url('breeding/' . $breeding->id . '/edit') }}" class="btn btn-primary mr-2" id="editButton">Edit Draft</a>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $cancelButton = $('#cancelButton');

            $('#rejectSplit').click(function(e) {
                e.preventDefault();
                loadModal("{{ url('breeding/' . $breeding->id . '/split/reject') }}", 'Reject Breeding Split');
            });
            $('#approveSplit').click(function(e) {
                e.preventDefault();
                loadModal("{{ url('breeding/' . $breeding->id . '/split/approve') }}", 'Approve Breeding Split');
            });
            $('#confirmSplit').click(function(e) {
                e.preventDefault();
                loadModal("{{ url('breeding/' . $breeding->id . '/split/confirm') }}", 'Confirm Breeding Results');
            });
            $('.releasePup').click(function(e) {
                e.preventDefault();
                console.log('test!');
                var pup_id = $(this).parent().find('input[name="pup_id"]').val();
                loadModal("{{ url('breeding/' . $breeding->id) }}/" + pup_id + "/release", 'Release Pup #' + (Number(pup_id) + 1));
            });
            $('.surrenderPup').click(function(e) {
                e.preventDefault();
                var pup_id = $(this).parent().find('input[name="pup_id"]').val();
                loadModal("{{ url('breeding/' . $breeding->id) }}/" + pup_id + "/convert/surrender", 'Surrender Pup #' + (Number(pup_id) + 1));
            });
            $('.convertPup').click(function(e) {
                e.preventDefault();
                var pup_id = $(this).parent().find('input[name="pup_id"]').val();
                loadModal("{{ url('breeding/' . $breeding->id) }}/" + pup_id + "/convert/convert", 'Convert Pup #' + (Number(pup_id) + 1) + ' to Genotype');
            });
            $cancelButton.on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('breeding/' . $breeding->id) }}/cancel", 'Cancel Breeding Request');
            });
        });
    </script>
@endsection
