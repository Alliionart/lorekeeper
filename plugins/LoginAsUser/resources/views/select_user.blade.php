@extends('admin.layout')

@section('admin-title')
    Login As User
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Plugins' => 'admin/plugins', 'Login As User' => 'quick-login']) !!}

    <h1>Login As User</h1>

    <p>Select a user profile to instantly simulate an authenticated login session:</p>

    {!! Form::open(['url' => '/login-as']) !!}

    <div class="row">
        <div class="col-md-8">
            {!! Form::select('user_id',
                $users,
                null,
                ['class' => 'form-control selectize'],
            ) !!}
        </div>
        <div class="col-md-4">
            {!! Form::submit('Login As User', ['class' => 'btn btn-secondary']) !!}
        </div>
    </div>

    {!! Form::close() !!}
    

@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('.selectize').selectize();
        });
    </script>
@endsection
