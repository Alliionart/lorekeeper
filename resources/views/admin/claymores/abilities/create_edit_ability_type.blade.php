@extends('admin.layout')

@section('admin-title')
    Ability Type
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Abilities' => 'admin/abilities', 'Types' => 'admin/abilities/types' ]) !!}

    <h1>{{ $ability_type->id ? 'Edit' : 'Create' }} Ability Type
        @if ($ability_type->id)
            <a href="#" class="btn btn-danger float-right delete-ability-button">Delete Type</a>
        @endif
    </h1>

    {!! Form::open(['url' => $ability_type->id ? 'admin/abilities/edit/' . $ability_type->id : 'admin/abilities/create']) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $ability_type->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Action') !!}
                {!! Form::select('action', ['Attacker', 'Defender', 'Summon', 'Shield', 'Utility'], $ability_type->action, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($ability_type->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($ability_type->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._entry', ['name' => $ability_type->displayName, 'description' => $ability_type->parsed_description, 'visible' => $ability_type->is_visible])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-ability-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/abilities/delete') }}/{{ $ability_type->id }}", 'Delete Class');
            });
        });
    </script>
@endsection
