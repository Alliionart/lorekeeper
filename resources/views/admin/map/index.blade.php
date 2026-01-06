@extends('admin.layout')

@section('admin-title')
    World Maps
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'World Maps' => 'admin/map']) !!}

    <h1>World Maps</h1>

    <p>Here you can edit the overall map settings with custom layers and tiles. As well as add new map items to show on your maps.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/map/settings') }}"><i class="fas fa-globe"></i> Create New Map</a>
    </div>
    @if (!count($map_items))
        <p>No maps found.</p>
    @else
        {!! $map_items->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-5">
                        <div class="logs-table-cell">Title</div>
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
                @foreach ($map_items as $map_item)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-5">
                                <div class="logs-table-cell"><a href="{{ $map_item->url }}">{{ $map_item->name }}</a></div>
                            </div>
                            <div class="col-3 col-md-3">
                                <div class="logs-table-cell">{{ $map_item->id }}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell"></div>
                            </div>
                            <div class="col-3 col-md-1 text-right">
                                <div class="logs-table-cell"><a href="{{ url('admin/map/edit/' . $map_item->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
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
