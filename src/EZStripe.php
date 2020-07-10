<?php

namespace bkilshaw\EZStripe;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EZStripe
{
    private $stripe;
    private $stripe_products;
    private $stripe_prices;

    public $products;

    private function stripeOptions(array $options = [])
    {
        return [
            'api_key' => config('ezstripe.stripe_secret')
        ];
    }

    private function api_key()
    {
        return config('ezstripe.stripe_secret');
    }


    public function __construct()
    {
        $this->stripe = new \Stripe\StripeClient($this->api_key());
        $this->products = collect();
        $this->load_from_stripe();
    }

    private function load_from_stripe()
    {
        $this->load_products_from_stripe();
        $this->load_prices_from_stripe();
        $this->associate_product_prices();
    }

    private function associate_product_prices()
    {
        $products = collect();

        foreach($this->stripe_products as $product)
        {
            $product['prices'] = $this->stripe_prices->where('product', $product->id);
            $products[] = $product;
        }
        $this->products = $products;
    }

    protected function get_authenticated_user()
    {
        if(Auth::check())
        {
            return Auth::user();
        } else {
            abort(500, 'No user is authenticated');
        }
    }

    private function load_products_from_stripe()
    {
        if(!$this->stripe->products->all()->isEmpty())
        {
            $this->stripe_products = collect($this->stripe->products->all()->data);
            $this->products = $this->stripe_products;
        }
    }

    private function load_prices_from_stripe()
    {
        if(!$this->stripe->prices->all()->isEmpty())
        {
            $this->stripe_prices = collect($this->stripe->prices->all()->data);
        }
    }

    public function products(array $product_ids = [])
    {
        return !empty($product_ids) ? $this->products->whereIn('id', $product_ids) : $this->products;
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'price_id' => ['required', 'string']
        ]);

        $price_id = $request->input('price_id');

        return $this->generate_checkout_session($price_id);
    }

    public function generate_checkout_session(string $price_id){

        $user = $this->get_authenticated_user();

        $checkout = [
            'payment_method_types'  => ['card'],
            'subscription_data'     => [
                'items'             => [[ 'plan' => $price_id ]],
            ],
            'mode' 					=> 'subscription',
            'client_reference_id'   => $user->id,
            'success_url'           => $this->checkout_success_url(),
            'cancel_url'            => $this->checkout_cancel_url(),
        ];

        if(!empty($user->stripe_id))
        {
            $checkout['customer'] = $user->stripe_id;
        } else {
            $checkout['customer_email'] = $user->email;
        }

        return with(\Stripe\Checkout\Session::create(
            $checkout,
            $this->stripeOptions()
        ), fn ($session) => $session->id);
    }


    public function billing_portal()
    {
        $user = $this->get_authenticated_user();

        if(empty($user->stripe_id))
        {
            abort(500, 'User has no stripe_id');
        }

        $stripe_session = \Stripe\BillingPortal\Session::create([
            'customer' => $user->stripe_id,
            'return_url' => $this->billing_portal_return_url(),
        ], $this->stripeOptions());

        return redirect($stripe_session->url);
    }

    public function checkout_success_url()
    {
        $url = config('ezstripe.checkout_success_url') ?? config('app.url');
        return $url."?session_id={CHECKOUT_SESSION_ID}";
    }

    public function checkout_cancel_url()
    {
        return config('ezstripe.checkout_cancel_url') ?? config('app.url');
    }

    public function billing_portal_return_url()
    {
        return config('ezstripe.billing_portal_return_url') ?? config('app.url');
    }

}
