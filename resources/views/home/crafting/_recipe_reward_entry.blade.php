<div class="image">
    <span class="qty badge badge-secondary icon" base={{ $reward['quantity'] }}">x{{ $reward['quantity'] }}</span>
    @if (isset($reward['asset']->image_url))
        <img class="img-responsive" src="{{ $reward['asset']->image_url }}">
    @endif
</div>
<h5>{!! $reward['asset']->displayName !!}</h5>
