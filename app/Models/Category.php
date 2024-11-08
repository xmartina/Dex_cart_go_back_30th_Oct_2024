<?php

namespace App\Models;

use App\Common\Imageable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;

class Category extends BaseModel
{
    use HasFactory, SoftDeletes, Imageable, Searchable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'category_sub_group_id',
        'slug',
        'description',
        'active',
        'order',
        'featured',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $searchable = [];
        $searchable['id'] = $this->id;
        $searchable['name'] = $this->name;
        $searchable['slug'] = $this->slug;
        $searchable['active'] = (bool) $this->active;

        return $searchable;
    }


    private $translationExists = [];

    /**
     * The boot method for the Category model.
     *
     * This method is called when the Category model is being booted.
     * It adds a global scope to the model to include translations based on the current locale.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('withTranslations', function (Builder $builder) {
            $builder->with(['translations' => function ($query) {
                $query->where('lang', app()->getLocale())->whereNotNull('translation');
            }]);
        });
    }

    /**
     * Get all listings for the category.
     */
    public function listings()
    {
        return $this->belongsToMany(Inventory::class, 'category_product', null, 'product_id', null, 'product_id')
            ->groupBy('inventories.product_id', 'inventories.shop_id');
    }

    /**
     * Get the subGroups for the category.
     */
    public function subGroup()
    {
        return $this->belongsTo(CategorySubGroup::class, 'category_sub_group_id')->withTrashed();
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Get the attributes of respective categories.
     */
    public function attrsList(): BelongsToMany
    {
        return $this->belongsToMany(Attribute::class, 'attribute_categories')
            ->orderBy('order', 'asc')->withTimestamps();
    }

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    /**
     * Check if the category has a translation for the specified language.
     *
     * @param string|null $lang The language code (e.g., 'en', 'fr'). If null, the current application locale will be used.
     * @return bool 
     */
    public function hasTranslation($lang = null)
    {
        $lang = $lang ?? app()->getLocale();

        if (!array_key_exists($lang, $this->translationExists)) {
            $this->translationExists[$lang] = $this->translations()->where('lang', $lang)->exists();
        }

        return $this->translationExists[$lang];
    }

    /**
     * Setters
     */
    public function setFeaturedAttribute($value)
    {
        $this->attributes['featured'] = (bool) $value;
    }

    // /**
    //  * Get subGroups list for the category.
    //  *
    //  * @return array
    //  */
    // public function getCatSubGrpsAttribute()
    // {
    //     if (count($this->subGroups)) return $this->subGroups->pluck('id')->toArray();
    // }

    // public static function findBySlug($slug)
    // {
    //     return $this->where('slug', $slug)->firstOrFail();
    // }

    /**
     * Scope a query to only include Featured records.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', 1);
    }

    /**
     * Setters
     */
    public function setOrderAttribute($value)
    {
        $this->attributes['order'] = $value ?? 100;
    }

    public function getNameAttribute($value)
    {
        return $this->translateAttribute('name') ?? $value;
    }

    public function getDescriptionAttribute($value)
    {
        return $this->translateAttribute('description') ?? $value;
    }

    /**
     * Translate given attributes value from translation_categories table.
     * 
     * @param string $attribute - attribute name to translate
     * 
     * @return string - translated value of the attribute
     */
    public function translateAttribute(string $attribute)
    {
        if (Route::getCurrentRoute() == 'admin.catalog.category.edit') {
            return null;
        }

        $category_translation = $this->translations->first();

        if (!$category_translation || !isset($category_translation->translation[$attribute])) {
            return null;
        }

        return $category_translation->translation[$attribute];
    }
}
