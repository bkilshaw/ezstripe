# EZStripe

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require bkilshaw/ezstripe
```

## Setup

First, publish the assets:
```
php artisan vendor:publish --tag=ezstripe.config --tag=ezstripe.views --force
```

Run the included migrations to add the `stripe_id` field to your User table.
```php
php artisan migrate
```

In order to allow Stripe to send webhook events, update your VerifyCSRFToken.php so your Laravel application know's it doesn't need CSRF tokens on these endpoints:
```php
protected $except = [
    'ezstripe/*',
];
```

Include the EZStripe JS on all your pages by adding the included blade component before your closing `</body>`
```php
<x-ezstripe-javascript />
```

Create a new class app\Http\Controllers\WebhookController.php class that extends EZStripes WebhookController
```php
<?php
namespace App\Http\Controllers;

use bkilshaw\EZStripe\Http\Controllers\WebhookController as EZStripeController;

class WebhookController extends EZStripeController 
{
    //
}
```

Now that you have your WebhookController that can handle the webhook events, create a route in your `routes/web.php` to route the events:
```php
Route::post('ezstripe/webhooks', [WebhookController::class, 'webhooks'])->name('ezstripe.webhooks');
```

Add the following environment variables to your `.env`. You can get your API Keys from Stripe [https://dashboard.stripe.com/apikeys](https://dashboard.stripe.com/apikeys).

```php
STRIPE_KEY=Stripes Publishable key
STRIPE_SECRET=Stripes Secret key
STRIPE_WEBHOOK_SECRET=Stripes Webhook Secret
CHECKOUT_SUCCESS_URL=The URL a user will be redirected to after they have successfully subscribed
CHECKOUT_CANCEL_URL=The URL a user will be redirected to if they are in Stripe Checkout and hit 'cancel' or 'back'
BILLING_PORTAL_RETURN_URL=The URL a user will be redirected to after vising Stripes Billing Portal
```


## Setting Up Stripe

In order to receive webhook events you must create an endpoint within Stripe here: [https://dashboard.stripe.com/webhooks](https://dashboard.stripe.com/webhooks)

The Endpoint URL should point to `https://yourdomain.tld/ezstripe/webhooks`

In Events to send, you can pick which events you would like to be notified of, or click receive all events. (Note: some actions within Stripe can result in 10+ events. It's generally best practice to only send the Webhooks you need and are going to listen for within your WebhookController).

Once created, make sure you update your STRIPE_WEBHOOK_SECRET in your .env
```
STRIPE_WEBHOOK_SECRET=whsec_*
````

To enable Stripes billing portal, please visit [https://dashboard.stripe.com/settings/billing/portal](https://dashboard.stripe.com/settings/billing/portal).

Enable the following options:
```
    Billing History: Enable
    Update Subscriptions: Enable
    Cancel Subscriptions: Enable
```

Under Products, add the products and prices that the user should be able to pick from when updating their subscription.

The rest of the settings are up to you. Links to a Terms of Service and Privacy Policy are required. If you don't have one, check out [https://getterms.io/](https://getterms.io/)

## Using it

Redirecting to Stripe Checkout is simple. All you need is a form with `<form id='ezstripe'>` that submits a field with `name='price_id'`. The price_id is the ID of one of your prices from Stripe.

####Example
```html
<form id="ezstripe">
    @csrf

    <select name="price_id">
        @foreach(EZStripe::products() as $product)

            @foreach($product->prices as $price)

                {!-- EZStripe is designed for subscriptions, so we're excluding non-recurring prices here --}
                @if(isset($price->recurring))
                    <option value="{{ $price->id }}">${{ number_format($price->unit_amount/100,2) }} / {{ $price->recurring->interval }} - {{ $product->name }}</option>
                @endif

            @endforeach

        @endforeach
    </select>

    <button type="submit">Checkout</button>
</form>
```

## Handling Webhooks

EZStripe will automatically update your user and add their stripe_id when they are first added to Stripe. 
All other [Webhook Events](https://stripe.com/docs/api/events/types) you wish to handle can be done by adding a function to your WebhookController.
In order to have EZStripe handle the event, you must follow EZStripes function naming convention: camel case the event type, and prefix it with `handle`

#### Examples
___
```php
   Stripe Event: customer.created
EZStripe Method: handleCustomerCreated(array $payload){}

   Stripe Event: charge.failed
EZStripe Method: handleChargeFailed(array $payload){}

   Stripe Event: order.payment_failed
EZStripe Method: handleOrderPaymentFailed(array $payload){}

   Stripe Event: subscription_schedule.expiring
EZStripe Method: handleSubscriptionScheduleExpiring(){}
```

EZStripe comes with the following methods to interact with Stripe
```php
// Returns a collection of your products in Stripe
EZStripe::products();

// Return a collection of your products in Stripe, only if the stripe product_id matches one that's been passed in
EZStripe::products(array $product_ids);

// Accepts a Illuminate\Http\Request and will return a Stripe Checkout Session the client can use for the redirect
EZStripe::checkout(Request $request); 

// Redirect the currently authorized user to Stripes Billing Portal if they have a stripe_id set
EZStripe::billing_portal();
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Brad Kilshaw][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/bkilshaw/ezstripe.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/bkilshaw/ezstripe.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/bkilshaw/ezstripe/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/bkilshaw/ezstripe
[link-downloads]: https://packagist.org/packages/bkilshaw/ezstripe
[link-travis]: https://travis-ci.org/bkilshaw/ezstripe
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/bkilshaw
[link-contributors]: ../../contributors
