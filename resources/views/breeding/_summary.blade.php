<div class="card rounded p-3 my-2">
    <div class="row">
        <div class="col-md-3">
            <div class="d-flex align-items-center">
                <a href="{{ $breeding->parent1->url }}">
                    <img class="img-fluid border rounded" src="{{ $breeding->parent1->image->thumbnailUrl }}" />
                </a>
                <span class="h5 mx-2"><i class="fas fa-times"></i></span>
                <a href="{{ $breeding->parent2->url }}">
                    <img class="img-fluid border rounded" src="{{ $breeding->parent2->image->thumbnailUrl }}" />
                </a>
            </div>
        </div>
        <div class="col-md-9">
            <h4>
                {!! $breeding->displayName !!} <span class="badge {{ $breeding->type === 'Full' ? 'badge-info' : 'badge-warning' }}">{{ $breeding->type }}</span>
                <span class="float-right h4 badge badge-{{ $breeding->status == 'Pending' ? 'secondary' : ($breeding->status == 'Rejected' ? 'danger' : 'success') }}">{{ $breeding->status }}</span>
            </h4>
            <div class="row">
                <div class="col-md-6">
                    @if ($breeding->status === 'Approved')
                        <div>
                            {!! $breeding->resultsCount > 0 ? '<strong>Litter Size: </strong>'.$breeding->resultsCount : 'Empty Litter' !!}
                        </div>
                        <div>
                            <strong>Available pups</strong> {!! $breeding->getAvailablePupCount() !!}
                        </div>
                    @endif
                </div>
                <div class="col-md-6 text-right">
                    <a class="btn btn-primary" href="{{ $breeding->url }}">View Breeding</a>
                </div>
            </div>
        </div>
    </div>
</div>