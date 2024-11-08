@foreach($results as $key => $items)
    @if(count($items) > 0)
    <div class="card p-2 my-4">
        <div class="card-header mb-0">
            <a data-toggle="collapse" href="#{{class_basename($items->first())}}">
                <h3 class="card-title">{{ ucwords(preg_replace("/[A-Z]/", ' ' . "$0", class_basename($items->first()))) }}</h3>
                {{ count($items) }} result{{ count($items) > 1 ? 's' : '' }} found.
            </a>
        </div>
        <div class="collapse" class="collapse" id="{{class_basename($items->first())}}">
            @foreach($items as $value)
                <div class="card p-2 mb-4">
                    @include('world._entry', ['imageUrl' => $value->imageUrl, 'name' => $value->name ?? $value->title, 
                    'searchUrl' => $value->url, 'description' => $value->description ?? $value->text])
                </div>
            @endforeach
        </div>
    @endif
    </div>
@endforeach