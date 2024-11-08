<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Database\Eloquent\Builder;

class Attribute extends BaseModel
{
    use SoftDeletes;

    const TYPE_COLOR = 1;         //Color/Pattern
    const TYPE_RADIO = 2;
    const TYPE_SELECT = 3;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'attributes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'shop_id',
        'name',
        'attribute_type_id',
        'order',
    ];

    private $translationExists = [];

    /**
     * The boot method for the Attribute model.
     *
     * This method is called when the Attribute model is being booted.
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
     * Get the Shop associated with the attribute.
     */
    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    /**
     * Get the AttributeType for the Attribute.
     */
    public function attributeType()
    {
        return $this->belongsTo(AttributeType::class);
    }

    /**
     * Attribute has many AttributeValue
     */
    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class)->orderBy('order', 'asc');
    }

    /**
     * Get the translations for the Attribute.
     */
    public function translations()
    {
        return $this->hasMany(AttributeTranslation::class);
    }

    /**
     * Get the inventories for the Attribute.
     */
    public function inventories()
    {
        return $this->belongsToMany(Inventory::class, 'attribute_inventory')
            ->withPivot('attribute_value_id')
            ->withTimestamps();
    }

    /**
     * Get the categories for the attributes.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'attribute_categories')->withTimestamps();
    }

    /**
     * Scope a query to only include records from the users shop.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMine($query)
    {
        return $query->where('shop_id', Auth::user()->merchantId());
    }

    /**
     * Get translated name of the attribute
     */
    public function getNameAttribute($value)
    {
        return $this->translateAttribute('name') ?? $value;
    }

    /**
     * Return extra classes to views based on type
     *
     * @return string
     */
    public function getCssClassesAttribute()
    {
        switch ($this->attribute_type_id) {
            case static::TYPE_COLOR:
                return 'color-options';
            case static::TYPE_RADIO:
                return 'radioSelect';
            case static::TYPE_SELECT:
                return 'selectBoxIt';
            default:
                return 'selectBoxIt';
        }
    }

    /**
     * Check if the Attribute has a translation for the specified language.
     *
     * @param string|null $lang The language code to check for translation. 
     * 
     * @return bool 
     */
    public function hasTranslation($lang = null)
    {
        $lang = $lang ?? app()->getLocale();  // Use application locale as default.

        if (!array_key_exists($lang, $this->translationExists)) {
            $this->translationExists[$lang] = $this->translations()->where('lang', $lang)->exists();
        }

        return $this->translationExists[$lang];
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
        if (Route::currentRouteName() == 'admin.attributes.index') {
            return null;
        }

        $translated_attribute = $this->translations->first();

        if (!$translated_attribute || !isset($translated_attribute->translation[$attribute])) {
            return null;
        }

        return $translated_attribute->translation[$attribute];
    }
}
