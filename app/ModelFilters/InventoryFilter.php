<?php

namespace App\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class InventoryFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function rating($rating)
    {
        return $this->whereHas('feedbacks', function ($query) use ($rating) {
            return $query->select('rating')
                ->groupBy('feedbackable_id')->havingRaw('AVG(rating) >= ?', [$rating]);
        });
    }

    public function price($price)
    {
        $price = explode('-', $price);

        return $this->whereBetween('sale_price', [$price[0], $price[1]]);
    }

    public function freeShipping($free_shipping)
    {
        return $this->where('free_shipping', 1);
    }

    public function auction($auction)
    {
        return $this->where('auctionable', 1);
    }

    public function newArraivals($new_arrivals)
    {
        $range = Carbon::now()->subDays(config('system.filter.new_arrival', 7));

        return $this->where('inventories.created_at', '>', $range);
    }

    public function hasOffers($has_offers)
    {
        return $this->hasOffer();
    }

    public function sortBy($sort_by)
    {
        switch ($sort_by) {
            case 'newest':
                return $this->orderBy('inventories.created_at', 'desc');
            case 'oldest':
                return $this->orderBy('inventories.created_at', 'asc');
            case 'price_asc':
                return $this->orderBy('inventories.sale_price', 'asc');
            case 'price_desc':
                return $this->orderBy('inventories.sale_price', 'desc');
            case 'best_match':
            default:
                return;
        }
    }

    public function condition($condition)
    {
        return $this->whereIn('condition', array_keys($condition));
    }

    public function brand($brand)
    {
        return $this->whereIn('brand', array_keys($brand));
    }

    public function attribute($attributes)
    {
        // $values = array_keys($attributes);
        // $attrs = array_unique($attributes);

        // return $this->whereHas('attributes', function (Builder $query) use ($attrs, $values) {
        //     $query->whereIn('attribute_inventory.attribute_id', $attrs)
        //         ->whereIn('attribute_inventory.attribute_value_id', $values);
        // });

        $attrs = array_unique($attributes);

        return $this->whereHas('attributes', function (Builder $query) use ($attrs, $attributes) {
            foreach ($attrs as $attr) {
                $query->where('attribute_inventory.attribute_id', $attr)
                    ->whereIn('attribute_inventory.attribute_value_id', array_keys($attributes, $attr));
            }
        });
    }
}
