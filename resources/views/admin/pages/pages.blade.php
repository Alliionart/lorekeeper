@extends('admin.layout')

@section('admin-title')
    Pages
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Pages' => 'admin/pages']) !!}

    <h1>Pages</h1>

    <p>Here you can create pages with custom HTML content. By default, these pages are not linked to by any other page - if you would like users to look at the pages, you will need to link them manually (e.g. in the top navigation, footer, etc.). Certain
        important pages such as the terms of service and privacy policy cannot be deleted. You can, however, edit their names and visibility.</p>

    <div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/pages/create') }}"><i class="fas fa-plus"></i> Create New Page</a></div>
    
    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
            <div class="form-group mr-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Title']) !!}
            </div>
            <div class="form-group mr-3 mb-3">
                {!! Form::select('page_category_id', $categories, Request::get('name'), ['class' => 'form-control']) !!}
            </div>
            <div class="form-group mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        {!! Form::close() !!}
    </div>
    
    @if (!count($pages))
        <p>No pages found.</p>
    @else
        {!! $pages->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Title</div>
                    </div>
                    <div class="col-3 col-md-2">
                        <div class="logs-table-cell">Category</div>
                    </div>
                    <div class="col-3 col-md-3">
                        <div class="logs-table-cell">Key</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Last Edited</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($pages as $page)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell"><a href="{{ $page->url }}">{{ $page->title }}</a></div>
                            </div>
                            <div class="col-12 col-md-2">
                                {{ $page->category ? $page->category->name : '' }}
                            </div>
                            <div class="col-3 col-md-3">
                                <div class="logs-table-cell">{{ $page->key }}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{!! pretty_date($page->updated_at) !!}</div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell"><a href="{{ url('admin/pages/edit/' . $page->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $pages->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $pages->total() }} result{{ $pages->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection
