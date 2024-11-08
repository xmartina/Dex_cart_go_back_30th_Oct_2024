<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Repositories\BaseRepository;
use App\Repositories\EloquentRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EloquentCart extends EloquentRepository implements BaseRepository, CartRepository
{
    protected $model;

    public function __construct(Cart $cart)
    {
        $this->model = $cart;
    }

    public function all()
    {
        if (!Auth::user()->isFromPlatform()) {
            return $this->model->mine()->with('customer')->whereHas('customer')->get();
        }

        return $this->model->with('customer')->whereHas('customer')->get();
    }

    public function trashOnly()
    {
        if (!Auth::user()->isFromPlatform()) {
            return $this->model->with('customer')->mine()->onlyTrashed()->get();
        }

        return $this->model->with('customer')->onlyTrashed()->get();
    }

    public function store(Request $request)
    {
        setAdditionalCartInfo($request); //Set some system information using helper function

        $cart = parent::store($request);

        $this->syncCartItems($cart, $request->input('cart'));

        return $cart;
    }

    public function update(Request $request, $id)
    {
        setAdditionalCartInfo($request); //Set some system information using helper function

        $cart = parent::update($request, $id);

        $this->syncCartItems($cart, $request->input('cart'));

        return $cart;
    }

    /**
     * Sync up the list of items for the cart
     * @param  Cart $cart
     * @param  array $items
     * @return void
     */
    public function syncCartItems($cart, array $items)
    {
        $temp = [];

        foreach ($items as $item) {
            $item = (object) $item;

            $temp[$item->inventory_id] = [
                'item_description' => $item->item_description,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        }

        if (!empty($temp)) {
            $cart->inventories()->sync($temp);
        }

        return true;
    }
}
