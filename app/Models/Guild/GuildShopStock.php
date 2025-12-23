<?php

namespace App\Models\Guild;

use App\Models\Currency\Currency;
use App\Models\Item\Item;
use App\Models\Model;

class GuildShopStock extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'guild_shop_id', 'item_id', 'currency_id', 'cost', 'guild_cost', 'data', 'quantity', 'stock_type', 'is_visible', 'is_limited_stock', 'purchase_limit', 'guild_only',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'guild_shop_stock';

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the data attributes as an array.
     *
     * @param mixed $value
     *
     * @return array
     */
    public function getDataAttribute($value) {
        return json_decode($this->attributes['data'], true);
    }

    /**
     * Checks if the stack is transferrable.
     *
     * @return bool
     */
    public function getIsTransferrableAttribute() {
        if (!isset($this->data['disallow_transfer']) && $this->item->allow_transfer) {
            return true;
        }

        return false;
    }

    /**
     * Get the item being stocked.
     */
    public function item() {
        return $this->belongsTo(Item::class, 'item_id');
    }

    /**
     * Get the shop that holds this item.
     */
    public function shop() {
        return $this->belongsTo(GuildShop::class, 'guild_shop_id');
    }

    /**
     * Get the currency the item must be purchased with.
     */
    public function currency() {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**
     * Scopes active stock.
     *
     * @param mixed $query
     */
    public function scopeActive($query) {
        return $query->where('is_visible', 1);
    }

    /**
     * Scopes active stock.
     */
    public function getDisplayCostAttribute() {
        return (int) $this->cost;
    }

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/
}
