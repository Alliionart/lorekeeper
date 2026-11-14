@extends('layouts.app')


@section('title')
    Bases
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'design-hub', 'Bases' => 'Bases']) !!}
    <h1>Bases</h1>
    <a class="btn btn-primary" href="/design-hub/" role="button">Back to Design Hub</a>

    <div class="card rounded my-4">
        <div class="card-body">
            <input type="text" placeholder="Search base coats..." class="searchBar rounded mb-4 form-control" data-id="baseSearch" />

            <div class="row no-gutters flex-gap d-flex flex-wrap searchContent base-card-container" data-id="baseSearch">
                @if ($bases)
                    @foreach ($bases as $base)
                        @include('designhub._base_entry', ['base' => $base])
                    @endforeach
                @endif
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.searchBar').on('keyup', function(e) {
                var sTerm = $(this).val().toLowerCase();
                var type = $(this).attr('data-id');
                $('.searchContent[data-id="' + type + '"]').children().each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(sTerm) > -1);
                });
            });
        });
    </script>
@endsection
