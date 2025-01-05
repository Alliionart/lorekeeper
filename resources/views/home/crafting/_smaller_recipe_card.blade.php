<div class="card btn-craft recipe alert-secondary rounded-3 py-0 col-form-label" category-id="{{ $recipe->recipe_category_id }}" data-id="{{ $recipe->id }}" data-name="{{ $recipe->name }}">
    <div class="card-body d-flex flex-column align-items-center justify-content-center">
            @if (isset($recipe->image_url))
                <img src="{{ $recipe->imageUrl }}" class="recipe-image mr-2" style="max-height:65px; width:auto;">
            @endif
            <h6 class="my-1 text-center">{!! $recipe->displayName !!}</h6>
    </div>
</div>