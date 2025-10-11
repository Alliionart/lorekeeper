@extends('admin.layout')

@section('admin-title')
    Abilities
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Abilities' => 'admin/abilities', ($ability->id ? 'Edit' : 'Create') . ' Class' => $ability->id ? 'admin/abilities/edit/' . $ability->id : 'admin/abilities/create']) !!}

    <h1>{{ $ability->id ? 'Edit' : 'Create' }} Ability
        @if ($ability->id)
            <a href="#" class="btn btn-danger float-right delete-ability-button">Delete Ability</a>
        @endif
    </h1>

    {!! Form::open(['url' => $ability->id ? 'admin/abilities/edit/' . $ability->id : 'admin/abilities/create']) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $ability->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Ability Type') !!}
                {!! Form::select('type_id', $types, $ability->name, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description') !!}
        {!! Form::textarea('description', $ability->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>Ability Functions</h3>
            <p>Configure the ability functions and how the roller will work with it.</p>
        </div>
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('Cooldown Duration') !!} {!! add_help('The amount of turns this ability cannot be activated after being used.') !!}
                        {!! Form::number('cooldown', null, ['class' => 'form-control', 'min' => 0, 'max' => 99]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::checkbox('free_action', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('free_action', 'Free Action', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Free Action ensures this does not use an action for a round. Therefore it can be combined with an attack or item usage.') !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::checkbox('passive', 1, 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                        {!! Form::label('passive', 'Passive (Automatically On)', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If on, this ability will function constantly so long as the character brings it into battle.') !!}
                    </div>
                </div>
            </div>
            <h4 class="mt-3">Ability Effects</h4>
            <p>After this ability is activated, what this will do to the target(s), or the user. Ensure this matches the description above. Write all "chances" in a DnD format or percentage format. (1D20 or 5%)</p>
            <div class="form-group">
                {!! Form::label('Chance to Hit') !!}
                {!! Form::text('chance', null, ['class' => 'form-control']) !!}
            </div>

            <div class="p-3 mb-3 border border-success">
                <h5>Success Effects</h5>
                <p>When this ability is successful.</p>
            </div>
            <div class="p-3 border border-danger">
                <h5>Failure Effects</h5>
                <p>When this ability fails. If there is no failure affect, leave this section blank.</p>
            </div>

            <!-- Effects to Add:
                 https://docs.google.com/document/d/1_gIk1XvX0vIbRsOiw-XkG3OPELqkBFKeRW2NFfpgHGw/edit?usp=sharing
                    - Stat modifications (pull dynamically from C&C)
                    - Damage modifiers
                    - Health modifiers
                    - Other modifiers (dodge, others?)
                    - Immunities
                    - Status effects
                    - Summons

                    Other fields to add:
                    - Target
                        - All (Excluding Self)
                        - All (Including Self)
                        - Single Target
                        - Multi-Target (Needs a counter)
                        - All Enemies
                        - All Allies
                    - Chance ✓
                    - Type (aka Inflict, Cure, etc.)
                    - Check (if the ability has either a Pass/Fail effect) ✓
                        -- Success effects
                        -- Fail effects
                    - Duration of the effects
                -->
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($ability->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($ability->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._entry', ['name' => $ability->displayName, 'description' => $ability->parsed_description, 'visible' => $ability->is_visible])
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
                loadModal("{{ url('admin/abilities/delete') }}/{{ $ability->id }}", 'Delete Class');
            });
        });
    </script>
@endsection
