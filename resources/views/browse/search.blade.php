@extends('layouts.app')

@section('title') Site Search @endsection

@section('content')
{!! breadcrumbs(['Site Search' => 'search']) !!}
<div class="text-center">
    <h1>
        Site Search
    </h1>
    <p>This is a site wide search. To prevent unneccessary load on the server, this is only available to members.</p>
    <div>
        <div class="form-group mr-3 mb-3 mx-5">
            {!! Form::text('query', Request::get('query'), ['class' => 'form-control', 'id' => 'query']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary', 'id' => 'search']) !!}
        </div>
    </div>
</div>

<div class="container" id="answers">
</div>

@endsection
@section('scripts')
<script>
    $('#search').on('click', function() {
        var query = $('#query').val();
        $('#answers').fadeOut();
        $.ajax({
            url: "{{ url('search') }}/" + query,
            method: 'post',
            data:{
                _token: '{{ csrf_token() }}',
                },
            dataType: 'json',
            success: function(data) {
                results = data.results;
                $('#answers').html(results).hide().fadeIn();
            },
            error: function(error) {
                console.log('error');
                console.log(error);
            }
        });
    });
</script>
@endsection
