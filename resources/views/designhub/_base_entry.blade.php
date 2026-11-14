<div class="col-12 col-sm-6 col-md-4 p-1">
    <div class="card rounded">
        <div class="card-body">
            <div class="text-center">
                <img src="{{ $base->imageUrl ?? '/images/account.png' }}" class="img-fluid mb-3" />
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 flex-grow-1 w-100"><span class="badge badge-secondary">{{ $base->id }}</span> {{ $base->name }} <span style="text-transform:none;" class="font-weight-normal">({{ $base->code }})</span></h4>
                @if ($base->imageUrl)
                    <a href="{{ $base->imageUrl }}" class="btn btn-secondary flex-shrink-1" download><i class="fas fa-download"></i></a>
                @endif
            </div>
        </div>
    </div>
</div>
