@extends('admin.layout')

@section('admin-title')
    Plugins
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Plugins' => 'admin/plugins']) !!}

    <h1>Plugins</h1>

    <p>View and manage your site plugins.</p>

    {!! Form::open(['url' => 'admin/plugins/manage']) !!}

        <div class="row actions-bar mb-3">
            <div class="col-md-3">
                {!! Form::select('bulk-action', [
                    'deactivate'    => 'Deactivate',
                    'activate'      => 'Activate',
                    'remove'        => 'Remove',
                ], null, ['class' => 'form-control', 'placeholder' => 'Select bulk action...']) !!}
            </div>
            <div class="col-md-7"></div>
            <div class="col-md-2 text-right">
                {!! Form::submit('Perform Action', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>


        <table class="table table-sm">
            <thead>
                <tr>
                    <th class="text-center" width="70px">Select</th>
                    <th>Plugin</th>
                    <th width="10%">Version</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($plugins as $plugin)
                    <tr>
                        <td class="text-center'">
                            {!! Form::checkbox('plugins['.$plugin['Folder'].']', 1, null, ['class' => 'w-100']) !!}
                        </td>
                        <td>
                            <h6 class="mb-0 font-weight-bold">{{ $plugin['plugin_name'] }}</h6>
                            <small>{{ $plugin['description'] ?? '' }}</small>
                            <div class="meta">
                                <span><strong>Author: </strong>
                                    @if (isset($plugin['author_url']))
                                        <a target="_blank" href="{{ $plugin['author_url'] }}">{{ $plugin['author'] ?? 'Unknown' }}</a>
                                    @else
                                        {{ $plugin['author'] ?? 'Unknown' }}
                                    @endif
                                </span>
                            </div>
                        </td>
                        <td>
                            {{ $plugin['version'] }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {!! Form::close() !!}

@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            
        });
    </script>
@endsection
