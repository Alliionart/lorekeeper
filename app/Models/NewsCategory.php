<?php

namespace App\Models;

use App\Models\User\User;
use App\Models\News;
use Carbon\Carbon;
use Illuminate\Support\Str;

class NewsCategory extends Model {
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'parsed_description', 'discord_webhook', 'sort',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news_categories';

    /**
     * Validation rules for creation.
     *
     * @var array
     */
    public static $createRules = [
        'name' => 'required|between:3,100',
        'discord_webhook' => 'nullable|url',
    ];

    /**
     * Validation rules for updating.
     *
     * @var array
     */
    public static $updateRules = [
        'name' => 'required|between:3,100',
        'discord_webhook' => 'nullable|url',
    ];

    /**********************************************************************************************

        RELATIONS

    **********************************************************************************************/

    /**
     * Get the news posts in this category.
     */
    public function news() {
        return $this->hasMany(News::class);
    }

    /**********************************************************************************************

        SCOPES

    **********************************************************************************************/

    /**********************************************************************************************

        ACCESSORS

    **********************************************************************************************/

    /**
     * Get the news category slug.
     *
     * @return bool
     */
    public function getSlugAttribute() {
        return $this->id.'.'.Str::slug($this->name);
    }

    /**
     * Gets the admin edit URL.
     *
     * @return string
     */
    public function getAdminUrlAttribute() {
        return url('admin/news/category/edit/'.$this->id);
    }

    /**
     * Gets the power required to edit this model.
     *
     * @return string
     */
    public function getAdminPowerAttribute() {
        return 'manage_news';
    }

}
