<?php
/**
 * Raffle Extensions.
 *
 * Adds the ability to reward prizes through Raffles. Each raffle has a "type" for configuring prizes.
 */

return [

    /*
     * EXTENSIONS ACTIVE
     */

    'pets'                  => class_exists(\App\Models\Pet\Pet::class),
    'weapons'               => class_exists(\App\Models\Claymore\Weapon::class),
    'gears'                 => class_exists(\App\Models\Claymore\Gear::class),
    'breedingPermissions'   => class_exists(\App\Models\Character\BreedingPermission::class),

];
