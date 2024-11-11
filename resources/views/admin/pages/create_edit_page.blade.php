@extends('admin.layout')

@section('admin-title')
    {{ $page->id ? 'Edit' : 'Create' }} Page
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Pages' => 'admin/pages', ($page->id ? 'Edit' : 'Create') . ' Page' => $page->id ? 'admin/pages/edit/' . $page->id : 'admin/pages/create']) !!}

    <h1>{{ $page->id ? 'Edit' : 'Create' }} Page
        @if ($page->id && !config('lorekeeper.text_pages.' . $page->key))
            <a href="#" class="btn btn-danger float-right delete-page-button">Delete Page</a>
        @endif
        @if ($page->id)
            <a href="{{ $page->url }}" class="btn btn-info float-right mr-md-2">View Page</a>
        @endif
    </h1>

    {!! Form::open(['url' => $page->id ? 'admin/pages/edit/' . $page->id : 'admin/pages/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Title') !!}
                {!! Form::text('title', $page->title, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Key') !!} {!! add_help('This is a unique name used to form the URL of the page. Only alphanumeric characters, dash and underscore (no spaces) can be used.') !!}
                {!! Form::text('key', $page->key, ['class' => 'form-control']) !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Page Category (Optional)') !!}
                {!! Form::select('page_category_id', $categories, $page->page_category_id, ['class' => 'form-control page_category_id']) !!}
            </div>
        </div>
        <div class="col-md-4 marking-rarity-selector">
            <div class="form-group">
                {!! Form::label('Marking Rarity (Enter if Marking)') !!}
                <select name="page_rarity" id="rarity" class="form-control" placeholder="Select Rarity">
                    <option value="0" data-code="">Select Rarity</option>
                    <option value="1" data-code="common">Common</option>
                    <option value="2" data-code="uncommon">Uncommon</option>
                    <option value="3" data-code="rare">Rare</option>
                    <option value="4" data-code="legendary">Legendary</option>
                </select>
            </div>
        </div>
        <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('Page Image') !!} {!! add_help('This is for the page header and parent section.') !!}
            <div>{!! Form::file('image', ['id' => 'mainImage']) !!}</div>
            <div class="text-muted">Recommended size: 200px x 200px</div>
                <div class="form-check">
                    {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                    {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
                </div>
        </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Page Content') !!}
        {!! Form::textarea('text', $page->text, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $page->id ? $page->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_visible', 'Is Viewable', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, users will not be able to view the page even if they have the link to it.') !!}
            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::checkbox('can_comment', 1, $page->id ? $page->can_comment : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('can_comment', 'Commentable', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned on, users will be able to comment on the page.') !!}
            </div>
            @if (!Settings::get('comment_dislikes_enabled'))
                <div class="form-group">
                    {!! Form::checkbox('allow_dislikes', 1, $page->id ? $page->allow_dislikes : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                    {!! Form::label('allow_dislikes', 'Allow Dislikes On Comments?', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this is turned off, users cannot dislike comments.') !!}
                </div>
            @endif
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit($page->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-page-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/pages/delete') }}/{{ $page->id }}", 'Delete Page');
            });

            $(".page_category_id").change(function() {
                var page = $('.page_category_id').val();
                if (page == 1) {
                    $('.marking-rarity-selector').show();
                } else {
                    $('.marking-rarity-selector').hide();
                    $('#rarity').val(0);
                }
            });
        });
    </script>
@endsection
