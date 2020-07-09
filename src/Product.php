<?php

namespace bkilshaw\EZStripe;

use App\Plan;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Product
{
    private $stripe;

    public $product;
    private $stripe_products;

    public function __construct(array $requested_products)
    {
        $this->stripe = new \Stripe\StripeClient(EZStripe::api_key());
        $this->product = collect();
        $this->load_products_from_stripe();
        $this->product = $this->get_products($requested_products);

    }

    public static function load_products_from_stripe()
    {
        if(!$stripe->products->all()->isEmpty())
        {
            $this->stripe_products = collect($stripe->products->all()->data);
        }
    }

    private function get_products($requested_products)
    {

        $this->products = $this->stripe_products->whereIn('id', $requested_products);
    }
}
