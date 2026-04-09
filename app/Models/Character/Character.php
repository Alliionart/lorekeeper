<?php

namespace App\Models\Character;

use App\Facades\Notifications;
use App\Models\Award\Award;
use App\Models\Award\AwardLog;
use App\Models\Background\Background;
use App\Models\Background\BackgroundCondition;
use App\Models\Base\Base;
use App\Models\Carrier\Carrier;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;
use App\Models\Gallery\GalleryCharacter;
use App\Models\Item\Item;
use App\Models\Item\ItemLog;
use App\Models\Level\LevelLog;
use App\Models\Marking\Marking;
use App\Models\Model;
use App\Models\Rarity;
use App\Models\Stat\CountLog;
use App\Models\Stat\ExpLog;
use App\Models\Stat\Stat;
use App\Models\Stat\StatLog;
use App\Models\Stat\StatTransferLog;
use App\Models\Status\StatusEffect;
use App\Models\Status\StatusEffectLog;
use App\Models\Submission\Submission;
use App\Models\Submission\SubmissionCharacter;
use App\Models\Tracker\TrackerLog;
use App\Models\Trade;
use App\Models\User\User;
use App\Models\User\UserCharacterLog;
use App\Models\User\UserGear;
use App\Models\User\UserPet;
use App\Models\User\UserWeapon;
use App\Models\Character\CharacterClass;
use App\Models\WorldExpansion\FactionRankMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Settings;

class Character extends Model {
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'character_image_id', 'character_category_id', 'rarity_id', 'user_id',
        'owner_alias', 'number', 'slug', 'description', 'parsed_description',
        'is_sellable', 'is_tradeable', 'is_giftable',
        'sale_value', 'transferrable_at', 'is_visible',
        'is_gift_art_allowed', 'is_gift_writing_allowed', 'is_trading', 'sort',
        'is_myo_slot', 'name', 'trade_id', 'owner_url', 'base', 'class_id',
        'sex', 'citizenship', 'nickname', 'bonding', 'size',
        'home_id', 'home_changed', 'faction_id', 'faction_changed', 'is_deceased', 'deceased_at',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'characters';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'transferrable_at' => 'datetime',
        'home_changed'     => 'datetime',
        'faction_changed'  => 'datetime',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**
     * Accessors to append to the model.
     *
     * @var array
     */
    public $appends = ['is_available'];

    /**
     * Validation rules for character creation.
     *
     * @var array
     */
    public static $createRules = [
        'character_category_id' => 'required',
        'rarity_id'             => 'required',
        'user_id'               => 'nullable',
        'number'                => 'required',
        'slug'                  => 'required|alpha_dash',
        'description'           => 'nullable',
        'sale_value'            => 'nullable|decimal:0,2',
        'image'                 => 'required|mimes:jpeg,jpg,gif,png,webp|max:2500',
        'thumbnail'             => 'nullable|mimes:jpeg,jpg,gif,png,webp|max:2500',
        'owner_url'             => 'url|nullable',
        'base'                  => 'nullable',
        'sex'                   => 'nullable',
        'citizenship'           => 'nullable',
        'nickname'              => 'nullable',
        'size'                  => 'nullable',
    ];

    /**
     * Validation rules for character updating.
     *
     * @var array
     */
    public static $updateRules = [
        'character_category_id' => 'required',
        'number'                => 'required',
        'slug'                  => 'required',
        'description'           => 'nullable',
        'sale_value'            => 'nullable|decimal:0,2',
        'image'                 => 'nullable|mimes:jpeg,jpg,gif,png|max:2500',
        'thumbnail'             => 'nullable|mimes:jpeg,jpg,gif,png|max:2500',
        'base'                  => 'nullable',
        'sex'                   => 'nullable',
        'citizenship'           => 'nullable',
        'nickname'              => 'nullable',
        'size'                  => 'nullable',
        'bonding'               => 'nullable',
    ];

    /**
     * Validation rules for MYO slots.
     *
     * @var array
     */
    public static $myoRules = [
        'rarity_id'             => 'nullable',
        'user_id'               => 'nullable',
        'number'                => 'nullable',
        'slug'                  => 'nullable',
        'description'           => 'nullable',
        'sale_value'            => 'nullable',
        'name'                  => 'required',
        'image'                 => 'nullable|mimes:jpeg,gif,png|max:2500',
        'thumbnail'             => 'nullable|mimes:jpeg,gif,png|max:2500',
        'base'                  => 'nullable',
        'sex'                   => 'nullable',
        'citizenship'           => 'nullable',
        'nickname'              => 'nullable',
        'size'                  => 'nullable',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the user who owns the character.
     */
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the category the character belongs to.
     */
    public function category() {
        return $this->belongsTo(CharacterCategory::class, 'character_category_id');
    }

    /**
     * Get the masterlist image of the character.
     */
    public function image() {
        return $this->belongsTo(CharacterImage::class, 'character_image_id');
    }

    /**
     * Get all images associated with the character.
     *
     * @param mixed|null $user
     */
    public function images($user = null) {
        return $this->hasMany(CharacterImage::class, 'character_id')->images($user);
    }

    /**
     * Get the user-editable profile data of the character.
     */
    public function profile() {
        return $this->hasOne(CharacterProfile::class, 'character_id');
    }

    /**
     * Get character level.
     */
    public function level() {
        return $this->hasOne(CharacterLevel::class, 'character_id');
    }

    /**
     * Get characters stats.
     */
    public function stats() {
        return $this->hasMany(CharacterStat::class, 'character_id');
    }

    /**
     * Get the character's active design update.
     */
    public function designUpdate() {
        return $this->hasMany(CharacterDesignUpdate::class, 'character_id');
    }

    /**
     * Get the trade this character is attached to.
     */
    public function trade() {
        return $this->belongsTo(Trade::class, 'trade_id');
    }

    /**
     * Get the trade this character is attached to.
     */
    public function home() {
        return $this->belongsTo('App\Models\WorldExpansion\Location', 'home_id');
    }

    /**
     * Get the faction this character is attached to.
     */
    public function faction() {
        return $this->belongsTo('App\Models\WorldExpansion\Faction', 'faction_id');
    }

    /**
     * Get the rarity of this character.
     */
    public function rarity() {
        return $this->belongsTo(Rarity::class, 'rarity_id');
    }

    /**
     * Get the character's associated pets.
     */
    public function pets() {
        return $this->hasMany(UserPet::class, 'character_id');
    }

    /**
     * Get the character's associated gear.
     */
    public function gear() {
        return $this->hasMany(UserGear::class, 'character_id');
    }

    /**
     * Get the character's associated weapons.
     */
    public function weapons() {
        return $this->hasMany(UserWeapon::class, 'character_id');
    }

    /**
     * Gets both the character's gear and weapons.
     * Technically not a relation.
     */
    public function equipment() {
        return $this->hasMany(UserGear::class, 'character_id')->get()->concat($this->hasMany(UserWeapon::class, 'character_id')->get());
    }

    /**
     * Get the character's associated gallery submissions.
     */
    public function gallerySubmissions() {
        return $this->hasMany(GalleryCharacter::class, 'character_id');
    }

    /**
     * Get the character's items.
     */
    public function items() {
        return $this->belongsToMany(Item::class, 'character_items')->withPivot('count', 'data', 'updated_at', 'id', 'stack_name')->whereNull('character_items.deleted_at');
    }

    /**
     * Get the character's associated breeding permissions.
     */
    public function breedingPermissions() {
        return $this->hasMany('App\Models\Character\BreedingPermission', 'character_id');
    }

    /**
     * Get the lineage of the character.
     */
    public function lineage() {
        return $this->hasOne('App\Models\Character\CharacterLineage', 'character_id');
    }

    /**
     * Get the current background of the character.
     */
    public function background() {
        return $this->belongsTo(Background::class, 'background_id');
    }

    /**
     * Get the character's class.
     */
    public function class() {
        return $this->belongsTo(CharacterClass::class, 'class_id');
    }

    /**
     * Get the character's skills.
     */
    public function skills() {
        return $this->hasMany(CharacterSkill::class, 'character_id')->with('skill');
    }

    /**
     * Get the character's awards.
     */
    public function awards() {
        return $this->belongsToMany(Award::class, 'character_awards')->withPivot('count', 'data', 'updated_at', 'id')->whereNull('character_awards.deleted_at');
    }

    /**
     * Get the character's awards.
     */
    public function awardsByCategory($id = 1) {
        return $this->belongsToMany(Award::class, 'character_awards')
            ->withPivot('count', 'data', 'updated_at', 'id')
            ->whereNull('character_awards.deleted_at')
            ->where('award_category_id', $id)
            ->get();
    }

    /**
     * Get the character's active carriers.
     */
    public function carriers() {
        $carrier_ids = CharacterMarking::where('character_id', $this->id)
            ->whereNotNull('carrier_id')
            ->pluck('carrier_id')->toArray();
        $carriers = Carrier::whereIn('id', $carrier_ids)->get();
        
        return $carriers;
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scope a query to only include either characters of MYO slots.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool                                  $isMyo
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMyo($query, $isMyo = false) {
        return $query->where('is_myo_slot', $isMyo);
    }

    /**
     * Scope a query to only include visible characters.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $user
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query, $user = null) {
        if ($user && $user->hasPower('manage_characters')) {
            return $query;
        }

        return $query->where('is_visible', 1);
    }

    /**
     * Scope a query to only include characters that the owners are interested in trading.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTrading($query) {
        return $query->where('is_trading', 1);
    }

    /**
     * Scope a query to only include characters that can be traded.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTradable($query) {
        return $query->where(function ($query) {
            $query->whereNull('transferrable_at')->orWhere('transferrable_at', '<', Carbon::now());
        })->where(function ($query) {
            $query->where('is_sellable', 1)->orWhere('is_tradeable', 1)->orWhere('is_giftable', 1);
        });
    }

    /**
     * Scope a query to only include characters that have the selected markings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $markingIds
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMarkings($query, $markingIds) {
        return $query->join('character_markings', 'characters.id', '=', 'character_markings.character_id')
            ->whereIn('character_markings.marking_id', $markingIds)
            ->select('characters.*')
            ->distinct();
    }

    /**
     * Scope a query to include all relevant backgrounds.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed|null                            $location
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplicableBackgrounds($query, $location = null) {
        $applicable_bgs_raw = [];
        $applicable_bgs = [];
        if( !$location ) {
            $location = $this->home_id;
        }

        //Free to Use BGs
        $Free_bgs = BackgroundCondition::where('type', null)->where('value', null)->where('location', $location)->pluck('background_id')->toArray();

        //Personal BGs
        $User_bgs = BackgroundCondition::where('type', 'User')->where('value', $this->user_id)->where('location', $location)->pluck('background_id')->toArray();

        //Character Status BGs
        $Status_bgs = BackgroundCondition::where('type', 'Status')->where('value', $this->citizenship)->where('location', $location)->pluck('background_id')->toArray();

        //Character has Item BGs
        $unique_bg_items = BackgroundCondition::where('type', 'Item')->where('location', $location)->distinct()->pluck('value', 'background_id')->toArray();
        $item_ids = array_values($unique_bg_items);
        $characters_bg_items = $this->items()->whereIn('items.id', $item_ids)->pluck('items.id')->toArray();
        $Item_bgs = BackgroundCondition::where('type', 'Item')->whereIn('value', $characters_bg_items)->pluck('background_id')->toArray();

        //Character Award BGs
        $unique_bg_awards = BackgroundCondition::where('type', 'Award')->where('location', $location)->distinct()->pluck('value', 'background_id')->toArray();
        $award_ids = array_values($unique_bg_awards);
        $characters_bg_awards = $this->awards()->whereIn('awards.id', $award_ids)->pluck('awards.id')->toArray();
        $Award_bgs = BackgroundCondition::where('type', 'Award')->whereIn('value', $characters_bg_awards)->pluck('background_id')->toArray();

        //Character Guild BGs
        if ($Item_bgs) {
            $applicable_bgs_raw['Items'] = $Item_bgs;
        }
        if ($Award_bgs) {
            $applicable_bgs_raw['Awards'] = $Award_bgs;
        }
        if ($Status_bgs) {
            $applicable_bgs_raw['Status'] = $Status_bgs;
        }
        if ($User_bgs) {
            $applicable_bgs_raw['Personal'] = $User_bgs;
        }
        if ($Free_bgs) {
            $applicable_bgs_raw['Free to Use'] = $Free_bgs;
        }

        foreach ($applicable_bgs_raw as $cat => $bgs) {
            foreach ($bgs as $i => $bg_id) {
                $background = Background::find($bg_id);
                if( $background ) {
                    $allowed_species_ids = $background->allowedSpecies();
                    if( $allowed_species_ids && in_array( $this->image->species_id, $allowed_species_ids ) ) {
                        $applicable_bgs[$cat][$bg_id] = $background->name;
                    }
                }
                
            }
        }
        return $applicable_bgs;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the character's availabil ity for activities/transfer.
     *
     * @return bool
     */
    public function getIsAvailableAttribute() {
        if ($this->designUpdate()->active()->exists()) {
            return false;
        }
        if ($this->trade_id) {
            return false;
        }
        if (CharacterTransfer::active()->where('character_id', $this->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Display the owner's name.
     * If the owner is not a registered user on the site, this displays the owner's dA name.
     *
     * @return string
     */
    public function getDisplayOwnerAttribute() {
        if ($this->user_id) {
            return $this->user->displayName;
        } else {
            return prettyProfileLink($this->owner_url);
        }
    }

    /**
     * Gets the character's code.
     * If this is a MYO slot, it will return the MYO slot's name.
     *
     * @return string
     */
    public function getSlugAttribute() {
        if ($this->is_myo_slot) {
            return $this->name;
        } else {
            return $this->attributes['slug'];
        }
    }

    /**
     * Displays the character's name, linked to their character page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        if ($this->is_deceased) {
            return '<a href="'.$this->url.'" class="display-character">'.$this->fullName.' [DECEASED]</a>';
        }
        return '<a href="'.$this->url.'" class="display-character">'.$this->fullName.'</a>';
    }

    /**
     * Gets the character's name, including their code and user-assigned name.
     * If this is a MYO slot, simply returns the slot's name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        if ($this->is_myo_slot) {
            return $this->name;
        } else {
            return $this->slug.($this->name ? ': '.$this->name : '');
        }
    }

    /**
     * Gets the character's page's URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        if ($this->is_myo_slot) {
            return url('myo/'.$this->id);
        } else {
            return url('character/'.$this->slug);
        }
    }

    /**
     * Gets the character's asset type for asset management.
     *
     * @return string
     */
    public function getAssetTypeAttribute() {
        return 'characters';
    }

    /**
     * Gets the character's log type for log creation.
     *
     * @return string
     */
    public function getLogTypeAttribute() {
        return 'Character';
    }

    /**
     * Gets the character's maximum number of breeding permissions.
     *
     * @return int
     */
    public function getMaxBreedingPermissionsAttribute() {
        $currencies = $this->getCurrencies(true)->where('id', Settings::get('breeding_permission_currency'))->first();
        if (!$currencies) {
            return 0;
        }

        return $currencies->quantity;
    }

    /**
     * Gets the character's number of available breeding permissions.
     *
     * @return int
     */
    public function getAvailableBreedingPermissionsAttribute() {

        return $this->maxBreedingPermissions - $this->breedingPermissions->sum('full_quantity');
    }

    /**
     * Get character's home location setting.
     */
    public function getHomeSettingAttribute() {
        return intval(Settings::get('WE_character_locations'));
    }

    /**
     * Get character's location attribute.
     */
    public function getLocationAttribute() {
        $setting = $this->homeSetting;

        switch ($setting) {
            case 1:
                if (!$this->user) {
                    return null;
                } elseif (!$this->user->home) {
                    return null;
                } else {
                    return $this->user->home->fullDisplayName;
                }

            case 2:
                if (!$this->home) {
                    return null;
                } else {
                    return $this->home->fullDisplayName;
                }

            case 3:
                if (!$this->home) {
                    return null;
                } else {
                    return $this->home->fullDisplayName;
                }

            default:
                return null;
        }
    }

    /**
     * Get character's faction setting.
     */
    public function getFactionSettingAttribute() {
        return intval(Settings::get('WE_character_factions'));
    }

    /**
     * Get character's current faction attribute.
     */
    public function getCurrentFactionAttribute() {
        $setting = $this->factionSetting;

        switch ($setting) {
            case 1:
                if (!$this->user) {
                    return null;
                } elseif (!$this->user->faction) {
                    return null;
                } else {
                    return $this->user->faction->fullDisplayName;
                }

            case 2:
                if (!$this->faction) {
                    return null;
                } else {
                    return $this->faction->fullDisplayName;
                }

            case 3:
                if (!$this->faction) {
                    return null;
                } else {
                    return $this->faction->fullDisplayName;
                }

            default:
                return null;
        }
    }

    /**
     * Get character's faction rank.
     */
    public function getFactionRankAttribute() {
        if (!isset($this->faction_id) || !$this->faction->ranks()->count()) {
            return null;
        }
        if (FactionRankMember::where('member_type', 'character')->where('member_id', $this->id)->first()) {
            return FactionRankMember::where('member_type', 'character')->where('member_id', $this->id)->first()->rank;
        }
        if ($this->faction->ranks()->where('is_open', 1)->count()) {
            $standing = $this->getCurrencies(true)->where('id', Settings::get('WE_faction_currency'))->first();
            if (!$standing) {
                return $this->faction->ranks()->where('is_open', 1)->where('breakpoint', 0)->first();
            }

            return $this->faction->ranks()->where('is_open', 1)->where('breakpoint', '<=', $standing->quantity)->orderBy('breakpoint', 'DESC')->first();
        }
    }

    /**
     * Get the character's active carriers.
     */
    public function getCarriers() {
        $carrier_ids = CharacterMarking::where('character_id', $this->id)
            ->whereNotNull('carrier_id')
            ->pluck('carrier_id')->toArray();
        $carriers = Carrier::whereIn('id', $carrier_ids)->pluck('name')->toArray();
        
        return $carriers ? implode(', ', $carriers) : false;
    }

    /**********************************************************************************************

        OTHER FUNCTIONS

    **********************************************************************************************/

    /**
     * Checks if the character's owner has registered on the site and updates ownership accordingly.
     */
    public function updateOwner() {
        // Return if the character has an owner on the site already.
        if ($this->user_id) {
            return;
        }

        // Check if the owner has an account and update the character's user ID for them.
        $owner = checkAlias($this->owner_url);
        if (is_object($owner)) {
            $this->user_id = $owner->id;
            $this->owner_url = null;
            $this->save();

            $owner->settings->is_fto = 0;
            $owner->settings->save();
        }
    }

    /**
     * Get the character's held currencies.
     *
     * @param bool $showAll
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCurrencies($showAll = false) {
        // Get a list of currencies that need to be displayed
        // On profile: only ones marked is_displayed
        // In bank: ones marked is_displayed + the ones the user has

        $owned = CharacterCurrency::where('character_id', $this->id)->pluck('quantity', 'currency_id')->toArray();

        $currencies = Currency::where('is_character_owned', 1);
        if ($showAll) {
            $currencies->where(function ($query) use ($owned) {
                $query->where('is_displayed', 1)->orWhereIn('id', array_keys($owned));
            });
        } else {
            $currencies = $currencies->where('is_displayed', 1);
        }

        $currencies = $currencies->orderBy('sort_character', 'DESC')->get();

        foreach ($currencies as $currency) {
            $currency->quantity = $owned[$currency->id] ?? 0;
        }

        return $currencies;
    }

    /**
     * Get the character's exp logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getExpLogs($limit = 10) {
        $character = $this;
        $query = ExpLog::where(function ($query) use ($character) {
            $query->with('sender')->where('sender_type', 'Character')->where('sender_id', $character->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's stat logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getStatTransferLogs($limit = 10) {
        $character = $this;
        $query = StatTransferLog::where(function ($query) use ($character) {
            $query->with('sender')->where('sender_type', 'Character')->where('sender_id', $character->id);
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient')->where('recipient_type', 'Character')->where('recipient_id', $character->id);
        })->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's stat logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getStatLevelLogs($limit = 10) {
        $character = $this;
        $query = StatLog::where('leveller_type', 'Character')->where('recipient_id', $character->id)->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's level logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getLevelLogs($limit = 10) {
        $character = $this;
        $query = LevelLog::where(function ($query) use ($character) {
            $query->with('recipient')->where('leveller_type', 'Character')->where('recipient_id', $character->id);
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's stat count logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getCountLogs($limit = 10) {
        $character = $this;
        $query = CountLog::where(function ($query) use ($character) {
            $query->with('sender')->where('sender_type', 'Character')->where('sender_id', $character->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($character) {
            $query->where('character_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's held currencies as an array for select inputs.
     *
     * @return array
     */
    public function getCurrencySelect() {
        return CharacterCurrency::where('character_id', $this->id)->leftJoin('currencies', 'character_currencies.currency_id', '=', 'currencies.id')->orderBy('currencies.sort_character', 'DESC')->get()->pluck('name_with_quantity', 'currency_id')->toArray();
    }

    /**
     * Get the character's current status effects.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getStatusEffects() {
        // Get a list of status effects that need to be displayed

        $owned = CharacterStatusEffect::where('character_id', $this->id)->pluck('quantity', 'status_effect_id')->toArray();

        $statuses = StatusEffect::query();

        $statuses = $statuses->orderBy('name', 'DESC')->get();

        foreach ($statuses as $status) {
            $status->quantity = $owned[$status->id] ?? 0;
        }

        $statuses = $statuses->filter(function ($status) {
            return $status->quantity > 0;
        });

        return $statuses;
    }

    /**
     * Get the character's currency logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getCurrencyLogs($limit = 10) {
        $character = $this;
        $query = CurrencyLog::with('currency')->where(function ($query) use ($character) {
            $query->with('sender.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's status effect logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getStatusEffectLogs($limit = 10) {
        $character = $this;
        $query = StatusEffectLog::with('status')->where(function ($query) use ($character) {
            $query->with('recipient.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's item logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getItemLogs($limit = 10) {
        $character = $this;

        $query = ItemLog::with('item')->where(function ($query) use ($character) {
            $query->with('sender.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's award logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getAwardLogs($limit = 10) {
        $character = $this;

        $query = AwardLog::with('award')->where(function ($query) use ($character) {
            $query->with('sender.rank')->where('sender_type', 'Character')->where('sender_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orWhere(function ($query) use ($character) {
            $query->with('recipient.rank')->where('recipient_type', 'Character')->where('recipient_id', $character->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the character's ownership logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOwnershipLogs() {
        $query = UserCharacterLog::with('sender.rank')->with('recipient.rank')->where('character_id', $this->id)->orderBy('id', 'DESC');

        return $query->paginate(30);
    }

    /**
     * Get the character's update logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCharacterLogs() {
        $query = CharacterLog::with('sender.rank')->where('character_id', $this->id)->where('log_type', '!=', 'Skill Awarded')->orderBy('id', 'DESC');

        return $query->paginate(30);
    }

    /**
     * Get the character's update logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getCharacterSkillLogs() {
        $query = CharacterLog::with('sender.rank')->where('character_id', $this->id)->where('log_type', 'Skill Awarded')->orderBy('id', 'DESC');

        return $query->paginate(30);
    }

    /**
     * Get the character's associated battle pets.
     */
    public function getBattlePets() {
        $pets = $this->pets;
        $battle_pets = [];
        foreach ($pets as $cPet) {
            $pet = $cPet->pet;
            $evolutions = $pet->evolutions;
            if(count($evolutions) > 0) {
                //If the pet has evolutions check that at least one evolutions has an ability
                foreach($evolutions as $evo) {
                    if($evo->ability_id) {
                        $battle_pets[] = $cPet;
                        break 1;
                    }
                }
            } else {
                //If the pet has no evolutions, then just check the main ability
                if($pet->ability_id) {
                    $battle_pets[] = $cPet;
                }
            }
        }
        
        return $battle_pets;
    }

    /**
     * Get the character's associated class tree.
     */
    public function getClassTree() {
        $current_class = CharacterClass::where('id', $this->class_id)->first();

        if (!$current_class) {
            return null;
        }
        $tree = [];
        if ($current_class->parent_class_id) {
            //Has parent, NOT a ICQ
            $parent_class = CharacterClass::where('id', $current_class->parent_class_id)->first();
            if ($parent_class->parent_class_id) {
                //This is an ICQ or HCT
                $grandparent_class = CharacterClass::where('id', $parent_class->parent_class_id)->first();
                $tree['sct'] = $current_class;
                $tree['hct'] = $parent_class;
                $tree['icq'] = $grandparent_class;
            } else {
                //This is a HCT
                $tree['icq'] = $parent_class;
                $tree['hct'] = $current_class;
            }
        } else {
            //This is the FIRST class / has no parent (ICQ)
            $tree['icq'] = $current_class;
        }
        return $tree;
    }

    /**
     * Get the Phenotype/Genotype of the character.
     *
     * @param mixed $type
     */
    public function getMarkings($type = 'phenotype') {
        return $this->getMarkingLinkedArray($this->getMarkingFinalArray(), $type);
    }
    
    /**     
     * Get the character's XP logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getXPLogs($limit = 10) {
        $character = $this;

        $query = TrackerLog::where(function ($query) use ($character) {
            $query->where('character_id', $character->id)->where('log_type', '!=', 'Staff Grant');
        })->orderBy('id', 'DESC');

        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get submissions that the character has been included in.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getSubmissions() {
        return Submission::with('user.rank')->with('prompt')->where('status', 'Approved')->whereIn('id', SubmissionCharacter::where('character_id', $this->id)->pluck('submission_id')->toArray())->paginate(30);

        // Untested
        //$character = $this;
        //return Submission::where('status', 'Approved')->with(['characters' => function($query) use ($character) {
        //    $query->where('submission_characters.character_id', 1);
        //}])
        //->whereHas('characters', function($query) use ($character) {
        //    $query->where('submission_characters.character_id', 1);
        //});
        //return Submission::where('status', 'Approved')->where('user_id', $this->id)->orderBy('id', 'DESC')->paginate(30);
    }

    /**
     * Notifies character's bookmarkers in case of a change.
     *
     * @param mixed $type
     */
    public function notifyBookmarkers($type) {
        // Bookmarkers will not be notified if the character is set to not visible
        if ($this->is_visible) {
            $column = null;
            switch ($type) {
                case 'BOOKMARK_TRADING':
                    $column = 'notify_on_trade_status';
                    break;
                case 'BOOKMARK_GIFTS':
                    $column = 'notify_on_gift_art_status';
                    break;
                case 'BOOKMARK_GIFT_WRITING':
                    $column = 'notify_on_gift_writing_status';
                    break;
                case 'BOOKMARK_OWNER':
                    $column = 'notify_on_transfer';
                    break;
                case 'BOOKMARK_IMAGE':
                    $column = 'notify_on_image';
                    break;
            }

            // The owner of the character themselves will not be notified, in the case that
            // they still have a bookmark on the character after it was transferred to them
            $bookmarkers = CharacterBookmark::where('character_id', $this->id)->where('user_id', '!=', $this->user_id);
            if ($column) {
                $bookmarkers = $bookmarkers->where($column, 1);
            }

            $bookmarkers = User::whereIn('id', $bookmarkers->pluck('user_id')->toArray())->get();

            // This may have to be redone more efficiently in the case of large numbers of bookmarkers,
            // but since we're not expecting many users on the site to begin with it should be fine
            foreach ($bookmarkers as $bookmarker) {
                Notifications::create($type, $bookmarker, [
                    'character_url'  => $this->url,
                    'character_name' => $this->fullName,
                ]);
            }
        }
    }

    /**********************************************************************************************

        CHARACTER GENO/PHENO

    **********************************************************************************************/

    /**
     * Gets the final array for the character genome.
     *
     * @return array
     */
    public function getMarkingFinalArray() {
        $markings = CharacterMarking::where('character_id', $this->id)->get();

        if (!$markings->count()) {
            //If no markings, return null
            return null;
        }

        $rendered = [];
        foreach ($markings as $marking) {
            $temp = Marking::where('id', $marking->marking_id)->first();
            $rendered['is_chimera'] = $marking->data === null ? 0 : 1;
            $has_multi_bases = false;
            $marking_data = $marking->data ?? 0;
            if ($marking->carrier_id) {
                //Add carriers if needed
                $rendered['carriers'][$marking->carrier_id] = Carrier::where('id', $marking->carrier_id)->pluck('name')->toArray();
            }
            if (str_contains($marking->base_id, '|')) {
                $has_multi_bases = true;
                $multi_bases = explode('|', $marking->base_id);
                foreach ($multi_bases as $base) {
                    $temp_bases[] = Base::where('id', $base)->pluck('code', 'name')->toArray();
                }
            }
            if ($temp) {
                if ($rendered['is_chimera'] === 1) {
                    $marksrender[$marking_data][$temp->order_in_genome][] = [
                        'name'          => $temp->name,
                        'is_dominant'   => $marking->is_dominant,
                        'code'          => $marking->code,
                        'link'          => $temp->getUrlAttribute(),
                        'base_info'     => $has_multi_bases ? $temp_bases : Base::where('id', $marking->base_id)->pluck('code', 'name')->toArray(),
                    ];
                } else {
                    $marksrender[0][$temp->order_in_genome][] = [
                        'name'          => $temp->name,
                        'is_dominant'   => $marking->is_dominant,
                        'code'          => $marking->code,
                        'link'          => $temp->getUrlAttribute(),
                        'base_info'     => $has_multi_bases ? $temp_bases : Base::where('id', $marking->base_id)->pluck('code', 'name')->toArray(),
                    ];
                }
            }
        }
        ksort($marksrender);
        $rendered['markings'] = $marksrender;

        return $rendered;
    }

    /**
     * Gets the phenotype for the character genome.
     *
     * @param mixed $markings
     * @param mixed $type
     *
     * @return string
     */
    public function getMarkingLinkedArray($markings, $type = 'phenotype') {
        $bases = $this->getBaseCoat();

        if ($markings === null || count($markings) < 1) {
            if (count($bases) > 1 && array_key_exists(1, $bases)) {
                //If Chimera and no marks
                if ($type === 'phenotype') {
                    return $bases[0]['name'].' // '.$bases[1]['name'];
                } else {
                    return $bases[0]['code'].'+//'.$bases[1]['code'].'+';
                }
            } elseif (count($bases) > 1 && !array_key_exists(0, $bases)) {
                //This shit is a mess, I know, I'm tired, no switchcase here :(
                if ($type === 'phenotype') {
                    return $bases['name'];
                } else {
                    return $bases['code'].'+';
                }
            } else {
                if ($type === 'phenotype') {
                    if (array_key_exists(0, $bases)) {
                        return $bases[0]['name'];
                    }

                    return $bases['name'];
                } else {
                    if (array_key_exists(0, $bases)) {
                        return $bases[0]['code'].'+';
                    }

                    return $bases['code'].'+';
                }
            }
        }

        if (!is_array($markings) || !array_key_exists('markings', $markings)) {
            return 'Unknown';
        }

        $chimera = false;
        if (array_key_exists('is_chimera', $markings) && $markings['is_chimera'] == 1) {
            $chimera = true;
            $geno_sides = [];
        }

        $html_inner = [];

        //return print_r($markings, true);

        foreach ($markings['markings'] as $side => $group) {
            //Sides - Max of 2 for chimera
            $sideInner = $this->handleMarkingGroup($group, $type);
            $geno_sides[$side] = $sideInner;

            //Get the bases per side
            $geno_sides[$side][2] = $bases[0];
            ksort($geno_sides[$side]);
        }
        if (!isset($geno_sides[1]) && isset($bases[1])) {
            //If secondary side is without markings
            $geno_sides[1][2] = $bases[1];
        }
        if (!isset($geno_sides[0]) && isset($bases[0])) {
            //If primary side is without markings
            $geno_sides[0][2] = $bases[0];
        }
        foreach ($geno_sides as $i => $side) {
            $html_inner[] = $this->renderFinalMarkingOutput($side, $type);
        }
        if (count($html_inner) == 2) {
            $seperator = ($type == 'phenotype' ? ' // ' : '//');
            $html_inner = implode($seperator, $html_inner);
        } else {
            $html_inner = implode('', $html_inner);
        }

        return $html_inner;
    }

    public function handleMarkingGroup($group, $type = 'phenotype') {
        $html_inner = [];
        foreach ($group as $id => $order_group) {
            //Inside each order group, we have the markings
            foreach ($order_group as $marking) {
                //Individual markings
                $html_inner[$id][] = $this->renderIndividualMarking($marking, $type);
            }
        }

        return $html_inner;
    }

    /**
     * Gets the individual gene template for the marking string.
     *
     * @param mixed $marking
     * @param mixed $type
     *
     * @return string
     */
    public function renderIndividualMarking($marking, $type = 'phenotype') {
        $marking = (object) $marking;
        $url = $marking->link;

        switch ($type) {
            case 'phenotype':
                $text = $marking->name;
                if ($marking->name == 'Glint') {
                    if ($marking->is_dominant) {
                        $text = array_key_first($marking->base_info[0]).'/'.array_key_first($marking->base_info[1]).' '.$text;
                    } else {
                        $text = array_key_first($marking->base_info).' '.$text;
                    }
                }
                break;
            case 'genotype':
                $text = $marking->code;
                if ($marking->name == 'Glint') {
                    if ($marking->is_dominant) {
                        $text = $text.'-'.array_values($marking->base_info[0])[0].'/'.array_values($marking->base_info[1])[0];
                    } else {
                        $text = $text.'-'.array_values($marking->base_info)[0];
                    }
                }
                break;
            default:
                $text = $marking->name;
        }

        return '<a href="'.$url.'" class="marking-link">'.$text.'</a>';
    }

    /**
     * Gets the genotype for the character genome.
     *
     * @param mixed $array
     * @param mixed $type
     *
     * @return string
     */
    public function renderFinalMarkingOutput($array, $type = 'phenotype') {
        if (!is_array($array)) {
            return 'Unknown';
        }

        switch ($type) {
            case 'phenotype':
                $base = $array[2] ?? [0 => ['name' => 'Unknown', 'code' => '??']];
                unset($array[2]);

                foreach ($array as $order => $marking_group) {
                    foreach ($marking_group as $marking) {
                        if ($order < 2) {
                            $temp[0][] = $marking;
                        } elseif ($order > 2 && $order < 9) {
                            $temp[1][] = $marking;
                        } elseif ($order >= 9) {
                            $temp[2][] = $marking;
                        }
                    }
                }
                $html = (isset($temp[0]) && count($temp[0]) > 0 ? implode(' ', $temp[0]).' ' : '');
                $html .= $base['name'];
                if (isset($temp[1]) && count($temp[1]) > 0) {
                    $html .= ' with '.implode(' ', $temp[1]);
                }
                $html .= isset($temp[2]) ? ' and '.implode(' ', $temp[2]) : '';

                break;
            case 'genotype':
                $html = $array[2]['code'].'+';
                unset($array[2]);

                foreach ($array as $order => $marking_group) {
                    foreach ($marking_group as $marking) {
                        if ($order < 9) {
                            $temp[0][] = $marking;
                        } elseif ($order >= 9) {
                            $temp[1][] = $marking;
                        }
                    }
                }
                if (!isset($temp)) {
                    $temp = [];
                }
                $html .= (isset($temp[0]) && count($temp[0]) > 0 ? implode('/', $temp[0]) : '');
                $html .= (array_key_exists(1, $temp) && count($temp[1]) > 0 ? '/'.implode('/', $temp[1]) : '');

                break;
        }

        return '<span class="marking-output">'.$html.'</span>';
    }

    /**
     * Gets the genotype for the character genome.
     *
     * @return array
     */
    public function getBaseCoat() {
        if (str_contains($this->base, '|')) {
            $chimera_bases = [];
            $bases = explode('|', $this->base);
            if (count($bases) > 1) {
                foreach ($bases as $b) {
                    $temp = Base::where('id', $b)->first();
                    $chimera_bases[] = [
                        'name' => $temp->name,
                        'code' => $temp->code,
                    ];
                }

                return $chimera_bases;
            }
        }

        //If Not Chimera, Continue as normal
        $base = Base::where('id', $this->base)->first();

        if (!$base) {
            return [
                [
                    'name'  => 'Unknown',
                    'code'  => '??',
                ],
            ];
        }

        return [0 => [
            'name'  => $base->name,
            'code'  => $base->code,
        ]];
    }

    /**********************************************************************************************

        ADDITIONAL CHARACTER FUNCTIONS

    **********************************************************************************************/

    /**
     * Finds the lineage blacklist level of this character.
     * 0 is no restriction at all
     * 1 is no ancestors but no children
     * 2 is no lineage at all.
     *
     * @param mixed $maxLevel
     *
     * @return int
     */
    public function getLineageBlacklistLevel($maxLevel = 2) {
        return CharacterLineageBlacklist::getBlacklistLevel($this, $maxLevel);
    }

    /**
     * Gets the characters stats, but only those that apply to the character's species / subtype.
     */
    public function getStatsAttribute() {
        $character = $this;
        $stats = Stat::whereHas('limits', function ($query) use ($character) {
            $query->where('species_id', $character->image->species_id)->where('is_subtype', 0);
        })->orWhereHas('limits', function ($query) use ($character) {
            $query->where('species_id', $character->image->subtype_id)->where('is_subtype', 1);
        })->orWhereDoesntHave('limits')->orderBy('name', 'ASC')->get();

        return $this->stats()->whereIn('stat_id', $stats->pluck('id')->toArray())->get();
    }

    /**
     * Propagates stats.
     */
    public function propagateStats() {
        // get all stats where the species limit is the species of the character
        $character = $this;

        // technically, we can propagate all stats, since the above function will only return stats that apply to the character's species
        $stats = Stat::whereHas('limits', function ($query) use ($character) {
            $query->where('species_id', $character->image->species_id)->where('is_subtype', 0);
        })->orWhereHas('limits', function ($query) use ($character) {
            $query->where('species_id', $character->image->subtype_id)->where('is_subtype', 1);
        })->orWhereDoesntHave('limits')->get();

        // prevents running it when unneeded. if there's an error idk lol
        if ($this->stats()->pluck('stat_id')->toArray() != $stats->pluck('id')->toArray()) {
            // we need to do this each time in case a new stat is made. It slows it down but -\(-v-)/-
            foreach ($stats as $stat) {
                if (!$this->stats->where('stat_id', $stat->id)->first()) {
                    // check if stat has a base value that is for this character's species or subtype
                    // subtype takes precedence over species, so check for subtype first
                    $base = null;
                    $base = $stat->hasBaseValue('subtype', $this->image->subtype_id);
                    if (!$base) {
                        $base = $stat->hasBaseValue('species', $this->image->species_id);
                    }

                    $this->stats()->create([
                        'character_id'  => $this->id,
                        'stat_id'       => $stat->id,
                        'count'         => $base ? $base : $stat->base,
                        'current_count' => $base ? $base : $stat->base,
                    ]);
                }
            }

            // Refresh the model instance so that newly created stats are immediately available
            $this->refresh();
        }
    }

    /**
     * Gets total stat count including bonuses etc., without acknowledging current count.
     *
     * @param mixed $stat_id
     */
    public function totalStatCount($stat_id) {
        $stat = $this->stats->where('stat_id', $stat_id)->first();
        $total = $stat->count;
        $total += $this->bonusStatCount($stat_id);

        return $total;
    }

    /**
     * gets the total stat count with current count acknowledgment.
     *
     * @param mixed $stat_id
     */
    public function currentStatCount($stat_id) {
        $stat = $this->stats()->where('stat_id', $stat_id)->first();

        if ($stat->current_count < 1) {
            return 0; // prevents, for example, hp bonuses from being applied to a character with 0 hp
        }

        $total = $stat->current_count ? $stat->current_count : $stat->count;
        $total += $this->bonusStatCount($stat_id);

        return $total;
    }

    /**
     * gets bonus stat count.
     *
     * @param mixed $stat_id
     */
    public function bonusStatCount($stat_id) {
        $total = 0;
        foreach ($this->equipment() as $equipment) {
            if ($equipment->equipment->stats()->where('stat_id', $stat_id)->first()) {
                $total += $equipment->equipment->stats()->where('stat_id', $stat_id)->first()->count;
            }
        }

        return $total;
    }

    /**
     * Gets the equipment that affects a stat.
     *
     * @param mixed $stat_id
     */
    public function getStatEquipment($stat_id) {
        return $this->equipment()->filter(function ($equipment) use ($stat_id) {
            return $equipment->equipment->stats()->where('stat_id', $stat_id)->first();
        });
    }
}
