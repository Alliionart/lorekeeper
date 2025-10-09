@extends('admin.layout')

@section('admin-title')
    Backgrounds
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Backgrounds' => 'admin/data/backgrounds']) !!}

    <h1>Backgrounds</h1>
    <p>This is a list of backgrounds that can be attached to characters. </p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/data/background/create') }}"><i class="fas fa-plus"></i> Create New Background</a>
    </div>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => 'form-inline justify-content-end']) !!}
        <div class="form-group mr-3 mb-3">
            {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
        <div class="form-group mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>

    @if (!count($backgrounds))
        <p>No backgrounds found.</p>
    @else
        {!! $backgrounds->render() !!}
        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">Preview</div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-12 col-md-2">
                        <div class="logs-table-cell">Location</div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="logs-table-cell">Conditions</div>
                    </div>
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">ID</div>
                    </div>
                    <div class="col-12 col-md-1">
                        <div class="logs-table-cell">Edit</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($backgrounds as $background)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell">
                                    @if ($background->imageUrl)
                                        <!-- $background->image->thumbnailUrl -->
                                        <img src="{{ $background->imageUrl }}" style="max-width:50px;" alt="{{ $background->name }}" class="img-fluid" />
                                    @endif
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="logs-table-cell">
                                    @if (!$background->is_visible)
                                        <i class="fas fa-eye-slash mr-1"></i>
                                    @endif
                                    {{ $background->name }}
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="logs-table-cell">
                                    {{ $background->location() }}
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="logs-table-cell">
                                    {{ print_r($background->getConditionTypeListAttribute(), true)}}
                                </div>
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell">
                                    {{ $background->id }}
                                </div>
                            </div>
                            <div class="col-12 col-md-1">
                                <div class="logs-table-cell"><a href="{{ url('admin/data/background/edit/' . $background->id) }}" class="btn btn-primary py-0 px-1 w-100">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        {!! $backgrounds->render() !!}
        <div class="text-center mt-4 small text-muted">{{ $backgrounds->total() }} result{{ $backgrounds->total() == 1 ? '' : 's' }} found.</div>
    @endif

@endsection

@section('scripts')
    @parent
@endsection
