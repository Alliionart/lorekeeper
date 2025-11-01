@extends('admin.layout')

@section('admin-title')
    Class Types
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Classes' => 'admin/character-classes', 'Types' => 'admin/character-classes/types']) !!}

    <h1>Class Types</h1>

    <p>This is a list of types that connect to classes.</p>

    <div class="text-right mb-3"><a class="btn btn-primary mr-3" href="{{ url('admin/character-classes/') }}"><i class="fas fa-arrow-left mr-2"></i>Back</a><a class="btn btn-primary" href="{{ url('admin/character-classes/types/create') }}"><i class="fas fa-plus"></i> Create
            New Type</a></div>
    @if (!count($types))
        <p>No Class Types found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Parent</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($types as $type)
                    <tr>
                        <td>
                            {!! $type->displayName !!}
                        </td>
                        <td>
                            {{ $type->parent_type_id ? $type->parent->name : '' }}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/classes/edit/' . $type->id) }}" class="btn btn-primary">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                characters: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
        });
    </script>
@endsection
