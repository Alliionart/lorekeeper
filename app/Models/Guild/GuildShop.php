<?php

namespace App\Models\Guild;

use App\Models\Item\Item;
use App\Models\Model;

class GuildShop extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guild_id', 'name', 'has_image', 'description', 'parsed_description', 'is_active',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guild_shops';

    /**
     * Validation rules for guild shop creation.
     *
     * @var array
     */
    public static $createRules = [
        'guild_id'           => 'required',
        'name'               => 'required|unique:guild_shops|between:3,100',
        'description'        => 'nullable',
        'image'              => 'mimes:png',
    ];

    /**
     * Validation rules for guild shop updating.
     *
     * @var array
     */
    public static $updateRules = [
        'guild_id'           => 'required',
        'name'               => 'required|unique:guild_shops|between:3,100',
        'description'        => 'nullable',
        'image'              => 'mimes:png',
    ];

    /**
     * Whether the model contains timestamps to be saved and updated.
     *
     * @var string
     */
    public $timestamps = false;

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the shop stock.
     */
    public function stock() {
        return $this->hasMany(GuildShopStock::class, 'guild_shop_id')->orderBy('id', 'DESC');
    }

    /**
     * Get the shop stock (visible only).
     */
    public function visibleStock() {
        return $this->hasMany(GuildShopStock::class, 'guild_shop_id')->where('is_visible', 1)->where('quantity', '>', 0);
    }

    /**
     * Get the guild.
     */
    public function guild() {
        return $this->belongsTo(Guild::class, 'guild_id');
    }

    /**
     * Get the shop stock as items for display purposes.
     */
    public function displayStock() {
        return $this->belongsToMany(Item::class, 'guild_shop_stock')
            ->withPivot('item_id', 'currency_id', 'cost', 'quantity', 'id')
            ->wherePivot('is_visible', 1)
            ->wherePivot('quantity', '>', 0);
    }

    /**
     * Get the users who have bought from this shop.
     */
    public function buyers() {
        return $this->hasMany(GuildShopLog::class, 'guild_shop_id');
    }

    /**
     * Gets the guild's log type for logging purposes.
     *
     * @return string
     */
    public function getLogTypeAttribute() {
        return 'Guild';
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Displays the shop's name linked to the shop page.
     *
     * @return string
     */
    public function getDisplayNameAttribute() {
        return (!$this->is_active ? '<i class="fas fa-eye-slash mr-1"></i>' : '').'<a href="'.$this->url.'" class="display-shop">'.$this->name.'</a>';
    }

    /**
     * Gets the file directory containing the model's image.
     *
     * @return string
     */
    public function getImageDirectoryAttribute() {
        return $this->guild->image_directory.'/shop/';
    }

    /**
     * Gets the file name of the model's image.
     *
     * @return string
     */
    public function getShopImageFileNameAttribute() {
        return $this->id.'/-image.png/';
    }

    /**
     * Gets the path to the file directory containing the model's image.
     *
     * @return string
     */
    public function getShopImagePathAttribute() {
        return public_path($this->imageDirectory);
    }

    /**
     * Gets the URL of the model's image.
     *
     * @return string
     */
    public function getShopImageUrlAttribute() {
        if (!$this->has_image) {
            return null;
        }

        return asset($this->imageDirectory.'/'.$this->shopImageFileName);
    }

    /**
     * Gets the URL of the model's shop page.
     *
     * @return string
     */
    public function getUrlAttribute() {
        return url('guilds/view/'.$this->guild->id.'/shop/');
    }

    /**
     * Get the shop's shop sale logs.
     *
     * @param int $limit
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getShopLogs($limit = 10) {
        $guild = $this->guild;
        $query = GuildShopLog::where('guild_shop_id', $this->id)->with('shop')->with('item')->with('currency')->orderBy('id', 'DESC');
        if ($limit) {
            return $query->take($limit)->get();
        } else {
            return $query->paginate(30);
        }
    }

    /**
     * Gets the URL to edit shop.
     *
     * @return string
     */
    public function getEditUrlAttribute() {
        return url('guilds/view/'.$this->guild->id.'/shop/edit');
    }
}
