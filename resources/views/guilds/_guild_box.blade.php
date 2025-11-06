<div class="card rounded">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                Guild_Logo_Here
            </div>
            <div class="col-md-9">
                <div class="d-flex justify-content-between align-items-center">
                    <h2>{{ $guild->name }}</h2>

                    <div>
                        @if ($guild->status === 'active')
                            <span class="h6 p-1 rounded bg-success text-white">Active</span>
                        @else
                            <span class="h6 p-1 rounded bg-secondary text-white">Inactive</span>
                        @endif
                        <span class="h6 p-1 rounded border ml-2 {{ $guild->open_new_users ? 'border-success text-success' : 'border-danger text-danger' }}">{{ $guild->open_new_users ? 'Open Applications' : 'Closed Applications' }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <h5>Formed</h5>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0">{!! pretty_date($guild->created_at) !!}</p>
                    </div>
                    <div class="col-md-2">
                        <h5>Players</h5>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0">0</p>
                    </div>
                    <div class="col-md-2">
                        <h5>Characters</h5>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0">0</p>
                    </div>
                    <div class="col-md-2">
                        <h5>Location</h5>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0">{{ $guild->location ?? 'Unknown' }}</p>
                    </div>
                </div>
                <div class="text-right mt-2">
                    <a href="{{ url()->current() }}/view/{{ $guild->id }}" class="btn btn-outline-primary">Visit Guild</a>
                    @if ($guild->open_new_users)
                        <a href="{{ url()->current() }}/application/{{ $guild->id }}" class="ml-2 btn btn-primary">Submit an Application</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
