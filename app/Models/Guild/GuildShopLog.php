<?php

namespace App\Models\Guild;

use App\Models\Model;
use App\Models\Item\Item;
use App\Models\Currency\Currency;
use App\Models\Guild\Guild;


class GuildShopLog extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guild_shop_id', 'guild_id', 'item_id', 'currency_id', 'cost', 'quantity',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guild_shop_log';

    /**
     * Validation rules for guild shop log creation.
     *
     * @var array
     */
    public static $createRules = [
        'stock_id'      => 'required',
        'guild_shop_id' => 'required',
        'bank'          => 'required|in:user,character,guild',
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
     * Get the guild.
     */
    public function guild() {
        return $this->belongsTo(Guild::class, 'guild_id');
    }

    /**
     * Get the purchased item.
     */
    public function item() {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the shop the item was purchased from.
     */
    public function shop() {
        return $this->belongsTo(GuildShop::class, 'guild_shop_id');
    }

    /**
     * Get the currency used to purchase the item.
     */
    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }


    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
}
