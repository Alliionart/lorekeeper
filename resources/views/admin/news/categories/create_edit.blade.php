@extends('admin.layout')

@section('admin-title')
    {{ $category->id ? 'Edit' : 'Create' }} Category
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Categories' => 'admin/news/categories', ($category->id ? 'Edit' : 'Create') . ' Category' => $category->id ? 'admin/news/categories/edit/' . $category->id : 'admin/news/categories/create']) !!}
    <h1>{{ $category->id ? 'Edit' : 'Create' }} Category
        @if ($category->id)
            <a href="#" class="btn btn-danger float-right delete-category-button">Delete Category</a>
        @endif
    </h1>

    {!! Form::open(['url' => $category->id ? 'admin/news/categories/edit/' . $category->id : 'admin/news/categories/create', 'files' => true]) !!}
    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $category->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Discord Webhook URL') !!} {!! add_help('If provided, any news posted within this category will be routed to this webhook instead of the default.') !!}
        {!! Form::url('discord_webhook', $category->discord_webhook, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $category->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($category->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-category-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/categories/delete') }}/{{ $category->id }}", 'Delete Category');
            });
        });
    </script>
@endsection
