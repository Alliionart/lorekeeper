<?php

namespace App\Models\User;

use App\Models\Award\Award;
use App\Models\Award\AwardLog;
use App\Models\Character\Character;
use App\Models\Character\CharacterBookmark;
use App\Models\Character\CharacterImageCreator;
use App\Models\Claymore\Gear;
use App\Models\Claymore\GearLog;
use App\Models\Claymore\Weapon;
use App\Models\Claymore\WeaponLog;
use App\Models\Comment\CommentLike;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyLog;
use App\Models\User\UserCharacterLog;
use App\Models\User\UsernameLog;
use App\Models\Submission\SubmissionCharacter;
use App\Models\Gallery\GalleryCollaborator;
use App\Models\Gallery\GalleryFavorite;
use App\Models\Gallery\GallerySubmission;
use App\Models\Item\Item;
use App\Models\Item\ItemLog;
use App\Models\Level\LevelLog;
use App\Models\Notification;
use App\Models\Pet\Pet;
use App\Models\Pet\PetLog;
use App\Models\Rank\Rank;
use App\Models\Rank\RankPower;
use App\Models\Recipe\Recipe;
use App\Models\Shop\ShopLog;
use App\Models\Stat\ExpLog;
use App\Models\Stat\StatTransferLog;
use App\Models\Shop\UserShopLog;
use App\Models\Submission\Submission;
use App\Models\Theme;
use App\Traits\Commenter;
use Auth;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use App\Models\Border\Border;
use App\Models\User\UserBorder;
use App\Models\User\UserBorderLog;
use Config;

class User extends Authenticatable implements MustVerifyEmail {
    use Commenter, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'alias', 'rank_id', 'email', 'email_verified_at', 'password', 'is_news_unread', 'is_banned', 'has_alias', 'avatar', 'is_sales_unread', 'birthday', 'theme_id', 'decorator_theme_id',
        'is_deactivated', 'deactivater_id', 'last_seen',
        'border_id', 'border_variant_id', 'bottom_border_id', 'top_border_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'birthday'          => 'datetime',
    ];

    /**
     * Accessors to append to the model.
     *
     * @var array
     */
    protected $appends = [
        'verified_name',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = [
        'rank',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = true;

    /**********************************************************************************************

    RELATIONS

     **********************************************************************************************/

    /**
     * Get all of the user's update logs.
     */
    public function logs() {
        return $this->hasMany(UserUpdateLog::class);
    }

    /**
     * Get user settings.
     */
    public function settings() {
        return $this->hasOne(UserSettings::class);
    }

    /**
     * Get user theme.
     */
    public function theme() {
        return $this->belongsTo('App\Models\Theme');
    }

    /**
     * Get user decorator .
     */
    public function decoratorTheme() {
        return $this->belongsTo('App\Models\Theme', 'decorator_theme_id');
    }

    /**
     * Get User Granted Themes.
     */
    /**
     * Get user theme.
     */
    public function themes() {
        return $this->belongsToMany('App\Models\Theme', 'user_themes')->withPivot('id');
    }

    /**
     * Get user-editable profile data.
     */
    public function profile() {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Gets the account that deactivated this account.
     */
    public function deactivater() {
        return $this->belongsTo(self::class, 'deactivater_id');
    }

    /**
     * Get user settings.
     */
    public function level() {
        return $this->hasOne(UserLevel::class);
    }

    /**
     * Get the user's aliases.
     */
    public function aliases() {
        return $this->hasMany(UserAlias::class);
    }

    /**
     * Get the user's primary alias.
     */
    public function primaryAlias() {
        return $this->hasOne(UserAlias::class)->where('is_primary_alias', 1);
    }

    /**
     * Get the user's notifications.
     */
    public function notifications() {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get all the user's characters, regardless of whether they are full characters of myo slots.
     */
    public function allCharacters() {
        return $this->hasMany(Character::class)->orderBy('sort', 'DESC');
    }

    /**
     * Get the user's characters.
     */
    public function characters() {
        return $this->hasMany(Character::class)->where('is_myo_slot', 0)->orderBy('sort', 'DESC');
    }

    /**
     * Get the user's MYO slots.
     */
    public function myoSlots() {
        return $this->hasMany(Character::class)->where('is_myo_slot', 1)->orderBy('id', 'DESC');
    }

    /**
     * Get the user's rank data.
     */
    public function rank() {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Get the user's items.
     */
    public function items() {
        return $this->belongsToMany(Item::class, 'user_items')->withPivot('count', 'data', 'updated_at', 'id')->whereNull('user_items.deleted_at');
    }

    /**
     * Get the user's pets.
     */
    public function pets() {
        return $this->belongsToMany(Pet::class, 'user_pets')->withPivot('data', 'updated_at', 'id', 'variant_id', 'character_id', 'pet_name', 'has_image', 'evolution_id')->whereNull('user_pets.deleted_at');
    }

    /**
     * Get the user's awards.
     */
    public function awards() {
        return $this->belongsToMany(Award::class, 'user_awards')->withPivot('count', 'data', 'updated_at', 'id')->whereNull('user_awards.deleted_at');
    }

    /**     
     * Get the user's items.
     */
    public function recipes() {
        return $this->belongsToMany('App\Models\Recipe\Recipe', 'user_recipes')->withPivot('id');
    }

    /**     
     * Returns user's foraging stats
     */
    public function foraging() {
        return $this->hasOne('App\Models\User\UserForaging');
    }
    
    /**
     * Get all of the user's gallery submissions.
     */
    public function gallerySubmissions() {
        return $this->hasMany(GallerySubmission::class)
            ->where('user_id', $this->id)
            ->orWhereIn('id', GalleryCollaborator::where('user_id', $this->id)
                ->where('type', 'Collab')->pluck('gallery_submission_id')->toArray())
            ->orderBy('created_at', 'DESC');
    }

    /**
     * Get all of the user's favorited gallery submissions.
     */
    public function galleryFavorites() {
        return $this->hasMany(GalleryFavorite::class)->where('user_id', $this->id);
    }

    /**
     * Get the user's weapons.
     */
    public function weapons() {
        return $this->belongsToMany(Weapon::class, 'user_weapons')->withPivot('data', 'updated_at', 'id', 'character_id', 'has_image')->whereNull('user_weapons.deleted_at');
    }

    /**
     * Get the user's gears.
     */
    public function gears() {
        return $this->belongsToMany(Gear::class, 'user_gears')->withPivot('data', 'updated_at', 'id', 'character_id', 'has_image')->whereNull('user_gears.deleted_at');
    }

    /**
     * Get all of the user's character bookmarks.
     */
    public function bookmarks() {
        return $this->hasMany(CharacterBookmark::class)->where('user_id', $this->id);
    }

    /**
     * Get the user's shops.
     */
    public function shops() {
        return $this->belongsTo('App\Models\Shop\UserShop', 'user_id');
    }

    /**
     * Gets all of a user's liked / disliked comments.
     */
    public function commentLikes() {
        return $this->hasMany(CommentLike::class);
    }

    /**
     * Get the user's areas.
     */
    public function areas()
    {
        return $this->belongsToMany('App\Models\Cultivation\CultivationArea', 'user_area', 'user_id', 'area_id');
    }

    /**
     * Get user's unlocked borders.
     */
    public function borders()
    {
        return $this->belongsToMany('App\Models\Border\Border', 'user_borders')->withPivot('id');
    }

    /**
     * Get the border associated with this user.
     */
    public function border()
    {
        return $this->belongsTo('App\Models\Border\Border', 'border_id');
    }

    /**
     * Get the border associated with this user.
     */
    public function borderVariant()
    {
        return $this->belongsTo('App\Models\Border\Border', 'border_variant_id');
    }

    /**
     * Get the border associated with this user.
     */
    public function borderTopLayer()
    {
        return $this->belongsTo('App\Models\Border\Border', 'top_border_id');
    }

    /**
     * Get the border associated with this user.
     */
    public function borderBottomLayer()
    {
        return $this->belongsTo('App\Models\Border\Border', 'bottom_border_id');
    }

    /**********************************************************************************************

    SCOPES

     **********************************************************************************************/

    /**
     * Scope a query to only include visible (non-banned) users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVisible($query) {
        return $query->where('is_banned', 0)->where('is_deactivated', 0);
    }

    /**
     * Scope a query to only show deactivated accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDisabled($query) {
        return $query->where('is_deactivated', 1);
    }

    /**
     * Scope a query based on the user's primary alias.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed                                 $reverse
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAliasSort($query, $reverse = false) {
        return $query->leftJoin('user_aliases', 'users.id', '=', 'user_aliases.user_id')
            ->orderByRaw('user_aliases.alias IS NULL ASC, user_aliases.alias '.($reverse ? 'DESC' : 'ASC'));
    }

    /**********************************************************************************************

    ACCESSORS

     **********************************************************************************************/

    /**
     * Checks if the user has the named recipe.
     *
     * @param mixed $theme_id
     *
     * @return bool
     */
    public function hasTheme($theme_id) {
        $theme = Theme::find($theme_id);
        $user_has = $this->recipes && $this->recipes->contains($theme);
        $default = $theme->is_user_selectable;

        return $default ? true : $user_has;
    }

    /**
     * Get the user's alias.
     *
     * @return string
     */
    public function getVerifiedNameAttribute() {
        return $this->name.($this->hasAlias ? '' : ' (Unverified)');
    }

    /**
     * Checks if the user has an alias (has an associated dA account).
     *
     * @return bool
     */
    public function getHasAliasAttribute() {
        if (!config('lorekeeper.settings.require_alias')) {
            return true;
        }

        return $this->attributes['has_alias'];
    }

    /**
     * Checks if the user has an admin rank.
     *
     * @return bool
     */
    public function getIsAdminAttribute() {
        return $this->rank->isAdmin;
    }

    /**
     * Checks if the user is a staff member with powers.
     *
     * @return bool
     */
    public function getIsStaffAttribute() {
        return RankPower::where('rank_id', $this->rank_id)->exists() || $this->isAdmin;
    }

    /**
     * Checks if the user has the given power.
     *
     * @param mixed $power
     *
     * @return bool
     */
    public function hasPower($power) {
        return $this->rank->hasPower($power);
    }

    /**
     * Gets the powers associated with the user's rank.
     *
     * @return array
     */
    public function getPowers() {
        return $this->rank->getPowers();
    }

    /**
     * Gets the user's profile URL.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('user/'.$this->name);
    }

    /**
     * Gets the URL for editing the user in the admin panel.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/users/'.$this->name.'/edit');
    }

    /**
     * Displays the user's name, linked to their profile page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return ($this->is_banned ? '<strike>' : '').'<a href="'.$this->url.'" class="display-user" style="'.($this->rank->color ? 'color: #'.$this->rank->color.';' : '').($this->is_deactivated ? 'opacity: 0.5;' : '').'"><i class="'.($this->rank->icon ? $this->rank->icon : 'fas fa-user').' mr-1" style="opacity: 50%;"></i>'.$this->name.'</a>'.($this->is_banned ? '</strike>' : '');
    }

    /**
     * Gets the user's last username change.
     *
     * @return string
     */
    public function getPreviousUsernameAttribute() {
        // get highest id
        $log = $this->logs()->whereIn('type', ['Username Changed', 'Name/Rank Change'])->orderBy('id', 'DESC')->first();
        if (!$log) {
            return null;
        }

        return $log->data['old_name'];
    }

    /**
     * Displays the user's name, linked to their profile page.
     *
     * @return string
     */
    public function getCommentDisplayNameAttribute() {
        return ($this->is_banned ? '<strike>' : '').'<small><a href="'.$this->url.'" class="btn btn-primary btn-sm"'.($this->rank->color ? 'style="background-color: #'.$this->rank->color.'!important;color:#000!important;' : '').($this->is_deactivated ? 'opacity: 0.5;' : '').'"><i class="'.($this->rank->icon ? $this->rank->icon : 'fas fa-user').' mr-1" style="opacity: 50%;"></i>'.$this->name.'</a></small>'.($this->is_banned ? '</strike>' : '');
    }

    /**
     * Displays the user's primary alias.
     *
     * @return string
     */
    public function getDisplayAliasAttribute() {
        if (!config('lorekeeper.settings.require_alias') && !$this->attributes['has_alias']) {
            return '(No Alias)';
        }
        if (!$this->hasAlias) {
            return '(Unverified)';
        }

        return $this->primaryAlias->displayAlias;
    }

    /**
     * Displays the user's avatar.
     *
     * @return string
     */
    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * Gets the display URL for a user's avatar, or the default avatar if they don't have one.
     *
     * @return url
     */
    public function getAvatarUrlAttribute() {
        if ($this->avatar == 'default.jpg' && config('lorekeeper.extensions.use_gravatar')) {
            // check if a gravatar exists
            $hash = md5(strtolower(trim($this->email)));
            $url = 'https://www.gravatar.com/avatar/'.$hash.'??d=mm&s=200';
            $headers = @get_headers($url);

            if (!preg_match('|200|', $headers[0])) {
                return url('images/avatars/default.jpg');
            } else {
                return 'https://www.gravatar.com/avatar/'.$hash.'?d=mm&s=200';
            }
        }

        return url('images/avatars/'.$this->avatar.'?v='.filemtime(public_path('images/avatars/'.$this->avatar)));
    }

    /**
     * Gets the user's log type for log creation.
     *
     * @return string
     */
    public function getLogTypeAttribute() {
        return 'User';
    }

    // Check if user is online and display When they were online
    public function isOnline() {
        $onlineStatus = Cache::has('user-is-online-'.$this->id);
        $online = Carbon::createFromTimeStamp(strtotime(Cache::get('user-is-online-time-'.$this->id)));
        $onlineTime = isset($this->last_seen) ? Carbon::parse($this->last_seen)->diffForHumans() : 'A long time ago.';

        if ($onlineStatus) {
            $result = '<i class="fas fa-circle text-success mr-2" data-toggle="tooltip" title="This user is online."></i>';
        } else {
            $result = '<i class="far fa-circle text-secondary mr-2" data-toggle="tooltip" title="This user was last online '.$onlineTime.'."></i>';
        }

        return $result;
    }

    /**
     * Get's user birthday setting.
     */
    public function getBirthdayDisplayAttribute() {
        //
        $icon = null;
        $bday = $this->birthday;
        if (!isset($bday)) {
            return 'N/A';
        }

        if ($bday->format('d M') == Carbon::now()->format('d M')) {
            $icon = '<i class="fas fa-birthday-cake ml-1"></i>';
        }
        //
        switch ($this->settings->birthday_setting) {
            case 0:
                return null;
                break;
            case 1:
                if (Auth::check()) {
                    return $bday->format('d M').$icon;
                }
                break;
            case 2:
                return $bday->format('d M').$icon;
                break;
            case 3:
                return $bday->format('d M Y') . $icon;
            break;
            case 4:
                if(Auth::check()) return $bday->format('M');
            break;
            case 5:
                return $bday->format('M');
            break;
        }
    }

    /**
     * Check if user is of age.
     */
    public function getcheckBirthdayAttribute() {
        $bday = $this->birthday;
        if (!$bday || $bday->diffInYears(Carbon::now()) < 13) {
            return false;
        } else {
            return true;
        }
    }
    /**********************************************************************************************

    OTHER FUNCTIONS

     **********************************************************************************************/

    /**
     * Checks if the user can edit the given rank.
     *
     * @param mixed $rank
     *
     * @return bool
     */
    public function canEditRank($rank) {
        return $this->rank->canEditRank($rank);
    }

    /**
     * Get the user's held currencies.
     *
     * @param bool $showAll
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCurrencies($showAll = false) {
        // Get a list of currencies that need to be displayed
        // On profile: only ones marked is_displayed
        // In bank: ones marked is_displayed + the ones the user has

        $owned = UserCurrency::where('user_id', $this->id)->pluck('quantity', 'currency_id')->toArray();

        $currencies = Currency::where('is_user_owned', 1);
        if ($showAll) {
            $currencies->where(function ($query) use ($owned) {
                $query->where('is_displayed', 1)->orWhereIn('id', array_keys($owned));
            });
        } else {
            $currencies = $currencies->where('is_displayed', 1);
        }

        $currencies = $currencies->orderBy('sort_user', 'DESC')->get();

        foreach ($currencies as $currency) {
            $currency->quantity = $owned[$currency->id] ?? 0;
        }

        return $currencies;
    }

    /**
     * Get the user's held currencies as an array for select inputs.
     *
     * @param mixed $isTransferrable
     *
     * @return array
     */
    public function getCurrencySelect($isTransferrable = false) {
        $query = UserCurrency::query()->where('user_id', $this->id)->leftJoin('currencies', 'user_currencies.currency_id', '=', 'currencies.id')->orderBy('currencies.sort_user', 'DESC');
        if ($isTransferrable) {
            $query->where('currencies.allow_user_to_user', 1);
        }

        return $query->get()->pluck('name_with_quantity', 'currency_id')->toArray();
    }

    /**
     * Get the user's currency logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getCurrencyLogs($limit = 10) {
        $user = $this;
        $query = CurrencyLog::with('currency')->where(function ($query) use ($user) {
            $query->with('sender')->where('sender_type', 'User')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards', 'Gallery Submission Reward']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_type', 'User')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's exp logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getExpLogs($limit = 10) {
        $user = $this;
        $query = ExpLog::where(function ($query) use ($user) {
            $query->with('sender')->where('sender_type', 'User')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_type', 'User')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's stat logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getStatLogs($limit = 10) {
        $user = $this;
        $query = StatTransferLog::where(function ($query) use ($user) {
            $query->with('sender')->where('sender_type', 'User')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_type', 'User')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's level logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getLevelLogs($limit = 10) {
        $user = $this;
        $query = LevelLog::where(function ($query) use ($user) {
            $query->with('recipient')->where('leveller_type', 'User')->where('recipient_id', $user->id);
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's item logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getItemLogs($limit = 10) {
        $user = $this;
        $query = ItemLog::with('item')->where(function ($query) use ($user) {
            $query->with('sender')->where('sender_type', 'User')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_type', 'User')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's recipe logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */    
    public function getRecipeLogs($limit = 10) {
        $user = $this;
        $query = UserRecipeLog::with('recipe')->where(function ($query) use ($user) {
            $query->with('sender')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's pet logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getPetLogs($limit = 10) {
        $user = $this;
        $query = PetLog::with('sender')->with('recipient')->with('pet')->where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Staff Removal']);
        })->orWhere(function ($query) use ($user) {
            $query->where('recipient_id', $user->id);
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's weapon logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getWeaponLogs($limit = 10) {
        $user = $this;
        $query = WeaponLog::with('sender')->with('recipient')->with('weapon')->where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Staff Removal']);
        })->orWhere(function ($query) use ($user) {
            $query->where('recipient_id', $user->id);
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's gear logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getGearLogs($limit = 10) {
        $user = $this;
        $query = GearLog::with('sender')->with('recipient')->with('gear')->where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Staff Removal']);
        })->orWhere(function ($query) use ($user) {
            $query->where('recipient_id', $user->id);
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's shop purchase logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getShopLogs($limit = 10) {
        $user = $this;
        $query = ShopLog::where('user_id', $this->id)->with('character')->with('shop')->with('item')->with('currency')->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's shop purchase logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getUserShopLogs($limit = 10) {
        $user = $this;
        $query = UserShopLog::where('user_id', $this->id)->with('shop')->with('item')->with('currency')->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Get the user's character ownership logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getOwnershipLogs() {
        $user = $this;
        $query = UserCharacterLog::with('sender.rank')->with('recipient.rank')->with('character')->where(function ($query) use ($user) {
            $query->where('sender_id', $user->id)->whereNotIn('log_type', ['Character Created', 'MYO Slot Created', 'Character Design Updated', 'MYO Design Approved']);
        })->orWhere(function ($query) use ($user) {
            $query->where('recipient_id', $user->id);
        })->orderBy('id', 'DESC');

        return $query->paginate(30);
    }

    /**
     * Get the user's award logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getAwardLogs($limit = 10) {
        $user = $this;
        $query = AwardLog::with('award')->where(function ($query) use ($user) {
            $query->with('sender')->where('sender_type', 'User')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_type', 'User')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**     
     * Get the user's display name logs.
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUsernameLogs() {
        $query = UsernameLog::where('user_id', $this->id)->orderBy('updated_at', 'DESC');
        return $query->paginate(30);
    }

    /**
     * Checks if there are characters credited to the user's alias and updates ownership to their account accordingly.
     */
    public function updateCharacters() {
        if (!$this->attributes['has_alias']) {
            return;
        }

        // Pluck alias from url and check for matches
        $urlCharacters = Character::whereNotNull('owner_url')->pluck('owner_url', 'id');
        $matches = [];
        $count = 0;
        foreach ($this->aliases as $alias) {
            // Find all urls from the same site as this alias
            foreach ($urlCharacters as $key=> $character) {
                preg_match_all(config('lorekeeper.sites.'.$alias->site.'.regex'), $character, $matches[$key]);
            }
            // Find all alias matches within those, and update the character's owner
            foreach ($matches as $key=> $match) {
                if ($match[1] != [] && strtolower($match[1][0]) == strtolower($alias->alias)) {
                    Character::find($key)->update(['owner_url' => null, 'user_id' => $this->id]);
                    $count += 1;
                }
            }
        }

        //
        if ($count > 0) {
            $this->settings->is_fto = 0;
        }
        $this->settings->save();
    }

    /**
     * Checks if there are art or design credits credited to the user's alias and credits them to their account accordingly.
     */
    public function updateArtDesignCredits() {
        if (!$this->attributes['has_alias']) {
            return;
        }

        // Pluck alias from url and check for matches
        $urlCreators = CharacterImageCreator::whereNotNull('url')->pluck('url', 'id');
        $matches = [];
        foreach ($this->aliases as $alias) {
            // Find all urls from the same site as this alias
            foreach ($urlCreators as $key=> $creator) {
                preg_match_all(config('lorekeeper.sites.'.$alias->site.'.regex'), $creator, $matches[$key]);
            }
            // Find all alias matches within those, and update the relevant CharacterImageCreator
            foreach ($matches as $key=> $match) {
                if ($match[1] != [] && strtolower($match[1][0]) == strtolower($alias->alias)) {
                    CharacterImageCreator::find($key)->update(['url' => null, 'user_id' => $this->id]);
                }
            }
        }
    }

    /**
     * Get the user's submissions.
     *
     * @param mixed|null $user
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getSubmissions($user = null) {
        return Submission::with('user')->with('prompt')->viewable($user ? $user : null)->where('user_id', $this->id)->orderBy('id', 'DESC')->paginate(30);
    }

    /**
     * Checks if the user has bookmarked a character.
     * Returns the bookmark if one exists.
     *
     * @param mixed $character
     *
     * @return CharacterBookmark
     */
    public function hasBookmarked($character) {
        return CharacterBookmark::where('user_id', $this->id)->where('character_id', $character->id)->first();
    }

    /**
     * Checks if the user has the named recipe.
     *
     * @param mixed $recipe_id
     *
     * @return bool
     */
    public function hasRecipe($recipe_id) {
        $recipe = Recipe::find($recipe_id);
        $user_has = $this->recipes->contains($recipe);
        $default = !$recipe->needs_unlocking;

        return $default ? true : $user_has;
    }

    /**
     * Returned recipes listed that are owned
     * Reversal simply.
     *
     * @param mixed $ids
     * @param mixed $reverse
     *
     * @return object
     */
    public function ownedRecipes($ids, $reverse = false) {
        $recipes = Recipe::find($ids);
        $recipeCollection = [];
        foreach ($recipes as $recipe) {
            if ($reverse) {
                if (!$this->recipes->contains($recipe)) {
                    $recipeCollection[] = $recipe;
                }
            } else {
                if ($this->recipes->contains($recipe)) {
                    $recipeCollection[] = $recipe;
                }
            }
        }

        return $recipeCollection;
    }

    /**     
     * Get the user's border logs.
     *
     * @param  int  $limit
     * @return \Illuminate\Support\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBorderLogs($limit = 10) {
        $user = $this;
        $query = UserBorderLog::with('border')->where(function ($query) use ($user) {
            $query->with('sender')->where('sender_id', $user->id)->whereNotIn('log_type', ['Staff Grant', 'Prompt Rewards', 'Claim Rewards']);
        })->orWhere(function ($query) use ($user) {
            $query->with('recipient')->where('recipient_id', $user->id)->where('log_type', '!=', 'Staff Removal');
        })->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }

    }

    /**
     * Checks if the user has the named border
     *
     * @return bool
     */
    public function hasBorder($border_id) {
        $border = Border::find($border_id);
        $user_has = $this->borders->contains($border);
        $default = $border->is_default;
        
        return $default ? true : $user_has;
    }

    /**
     * display the user's icon and border styling
     *
     */
    public function UserBorder($size =  150) {
        //basically just an ugly ass string of html for copypasting use
        //would you want to keep posting this everywhere? yeah i thought so. me neither
        //there's probably a less hellish way to do this but it beats having to paste this over everywhere... EVERY SINGLE TIME.
        //especially with the checks

        //get some fun variables for later
        $avatar = '<!-- avatar -->
                <img class="avatar" src="' . $this->avatarUrl . '" alt="Avatar of ' . $this->name . '">';

        // Check if variant border or regular border is under or over
        if (isset($this->borderVariant) && $this->borderVariant->border_style == 0) {
            $layer = 'under';
        } elseif (isset($this->border) && $this->border->border_style == 0) {
            $layer = 'under';
        } else {
            $layer = null;
        }

        $styling = '<div class="user-avatar" style="width:'.$size.'px">';

        if(isset($this->settings->border_settings['border_flip']) && $this->settings->border_settings['border_flip']){
            $flip =  'transform: scaleX(-1)';
        }else{
            $flip = null;
        }

        $allStyle = $flip;

        //if the user has a border, we apply it
        if (isset($this->border) || isset($this->borderBottomLayer) && isset($this->borderTopLayer) || isset($this->borderVariant)) {
            //layers supersede variants
            //variants supersede regular borders
            if (isset($this->borderBottomLayer) && isset($this->borderTopLayer)) {
                if ($this->borderTopLayer->border_style == 0 && $this->borderBottomLayer->border_style == 0) {
                    // If both layers are UNDER layers
                    // top layer's image
                    $mainframe = '<img src="' . $this->borderTopLayer->imageUrl . '" class="avatar-border under" alt="' . $this->borderTopLayer->name . ' Avatar Frame" style="'.$allStyle.'">';
                    // bottom layer's image
                    $secondframe = '<img src="' . $this->borderBottomLayer->imageUrl . '" class="avatar-border bottom" alt="' . $this->borderBottomLayer->name . ' Avatar Frame" style="'.$allStyle.'">';
                } elseif ($this->borderTopLayer->border_style == 1 && $this->borderBottomLayer->border_style == 1) {
                    // If both layers are OVER layers
                    // top layer's image
                    $mainframe = '<img src="' . $this->borderTopLayer->imageUrl . '" class="avatar-border top" alt="' . $this->borderTopLayer->name . ' Avatar Frame" style="'.$allStyle.'">';
                    // bottom layer's image
                    $secondframe = '<img src="' . $this->borderBottomLayer->imageUrl . '" class="avatar-border" alt="' . $this->borderBottomLayer->name . ' Avatar Frame" style="'.$allStyle.'">';
                } else {
                    // If one layer is UNDER and one is OVER
                    $mainlayer = ($this->borderTopLayer->border_style == 0 ? 'under' : ' ');
                    $secondlayer = ($this->borderBottomLayer->border_style == 0 ? 'under' : ' ');

                    // top layer's image
                    $mainframe = '<img src="' . $this->borderTopLayer->imageUrl . '" class="avatar-border ' . $mainlayer . '" alt="' . $this->borderTopLayer->name . ' Avatar Frame" style="'.$allStyle.'">';
                    // bottom layer's image
                    $secondframe = '<img src="' . $this->borderBottomLayer->imageUrl . '" class="avatar-border ' . $secondlayer . '" alt="' . $this->borderBottomLayer->name . ' Avatar Frame" style="'.$allStyle.'">';
                }
                return $styling . $avatar . $mainframe . $secondframe . '</div>';
            } elseif (isset($this->borderVariant)) {
                $mainframe = '<img src="' . $this->borderVariant->imageUrl . '" class="avatar-border ' . $layer . '" alt="' . $this->borderVariant->name . ' ' . $this->border->name . ' Avatar Frame" style="'.$allStyle.'">';
            } else {
                $mainframe = '<img src="' . $this->border->imageUrl . '" class="avatar-border '. $layer .'" alt="' . $this->border->name . ' Avatar Frame" style="'.$allStyle.'">';
            }

            if (!isset($this->borderBottomLayer) && !isset($this->borderTopLayer)) {
                return $styling . $avatar . $mainframe . '</div>';
            }
        }
        //if no border return standard avatar style
        return $styling . $avatar . '</div>';
    }

}
