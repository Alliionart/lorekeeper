<?php
/**
 * Breeding Configurations.
 *
 * Here you will need to set your specific needs for trait categories and rarities.
 * You can set really whatever you want and is relevant to your breeding system.
 * You WILL need to change these in the code, especially the BreedingRollerService.php
 * I am leaving these here as examples but you will need to customize them for your own system.
 */

return [

    'aspect_category_id'    => 6,
    'aspect_rarity_id'      => 9,
    'eye_category_id'       => 1,
    'ear_category_id'       => 2,
    'tail_category_id'      => 3,
    'fur_category_id'       => 7,

    // Check if surrenders exist, extension not required, but if you have it, this will allow for the surrender system to be used in breeding.
    // Sellable Characters extension
    'surrender_extension_exists'    => class_exists(\App\Models\Adoption\Surrender::class),

];
