<?php

namespace App\Http\Resources;

use App\Helpers\ListHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopListingResource extends JsonResource
{
    protected $listings;

    public function listings($items)
    {
        $this->listings = $items;

        return $this;
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'member_since' => date('F j, Y', strtotime($this->created_at)),
            'verified' => $this->isVerified(),
            'verified_text' => $this->verifiedText(),
            'banner_image' => get_cover_img_src($this, 'shop'),
            'sold_item_count' => $this->total_item_sold,
            'total_sold_amount' => $this->total_sold_amount,
            'active_listings_count' => $this->inventories_count,
            'pickup_enabled' => $this->config->pickup_enabled,
            'member_since' => date('F j, Y', strtotime($this->created_at)),
            'rating' => $this->rating(),
            // 'feedbacks' => FeedbackResource::collection($this->feedbacks),
            'image' => get_logo_url($this, 'small'),
            'listings' => $this->listings,
        ];
    }

    public static function collection($resource)
    {
        return new ShopResourceCollection($resource);
    }
}
