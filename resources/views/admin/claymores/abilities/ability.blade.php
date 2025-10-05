@extends('admin.layout')

@section('admin-title')
    Abilities
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Abilities' => 'admin/abilities']) !!}

    <h1>Abilities</h1>

    <p>This is a list of abilities that Character Classes can inherit.</p>

    <div class="text-right mb-3"><a class="btn btn-primary mr-3" href="{{ url('admin/abilities/types') }}"><i class="fas fa-folder mr-2"></i>Ability Types</a><a class="btn btn-primary" href="{{ url('admin/abilities/create') }}"><i class="fas fa-plus"></i> Create New Ability</a></div>
    @if (!count($abilities))
        <p>No abilities found.</p>
    @else
        <table class="table table-sm category-table">
            <thead>
                <tr>
                    <th>Ability</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($abilities as $ability)
                    <tr>
                        <td>
                            {!! $category->displayName !!}
                        </td>
                        <td class="text-right">
                            <a href="{{ url('admin/ability/edit/' . $ability->id) }}" class="btn btn-primary">Edit</a>
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
