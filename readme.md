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
</body>
</html>
```

Create a new WebhookController.php class that extends EZStripes WebhookController
```php
<?php

use \bkilshaw\EZStripe\Http\Controllers\WebhookController as EZStripeController;

WebhookController extends EZStripeController 
{
    //
}
```

Now that you have your WebhookController that can handle the webhook events, create a route in your `routes/web.php` to route the events:
```php
Route::post('ezstripe/webhooks', [WebhookController::class, 'webhooks'])->name('ezstripe.webhooks');
```

Make sure you update your `config/ezstripe.php`. You can get your API Keys from Stripe [https://dashboard.stripe.com/apikeys](https://dashboard.stripe.com/apikeys).

```php
STRIPE_KEY=pk_*                // Stripes Publishable key
STRIPE_SECRET=sk_*             // Stripes Secret key
STRIPE_WEBHOOK_SECRET=whsec_*  // Stripes Webhook Secret
CHECKOUT_SUCCESS_URL=          // The URL a user will be redirected to after they have successfully subscribed
CHECKOUT_CANCEL_URL=           // The URL a user will be redirected to if they are in Stripe Checkout and hit 'cancel' or 'back'
BILLING_PORTAL_RETURN_URL=     // The URL a user will be redirected to after vising Stripes Billing Portal

```


## Setting Up Stripe

In order to receive webhook events you must create an endpoint within Stripe here: [https://dashboard.stripe.com/webhooks](https://dashboard.stripe.com/webhooks)

The Endpoint URL should point to `https://yourdomain.tld/ezstripe/webhooks`

In Events to send, you can pick which events you would like to be notified of, or click receive all events. (Note: some actions within Stripe can result in 10+ events. It's generally best practice to only send the Webhooks you need and are going to listen for within your WebhookController).

Once created, make sure you copy your Webhook Signing secret into your .env `STRIPE_WEBHOOK_SECRET=whsec_*`

https://dashboard.stripe.com/test/settings/billing/portal

Inside Stripe, setup your webhooks to post to your webhook URL (https://domain.tld/ezstripe/webhooks)

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
EZStripe Method: handleOrderPaymentFailed(array $payload)

   Stripe Event: subscription_schedule.expiring
EZStripe Method: handleSubscriptionScheduleExpiring()
```

EZStripe comes with the following methods to interact with Stripe
```php
EZStripe::products(); // Lists all products you have created in stripe
EZStripe::products([..stripe product ids...]); // EZStripe will only return the products with the ID's you passed in
EZStripe::checkout(); //
EZStripe::billing_portal(); // Redirects the currently authorized user to Stripes Billing Portal.
```


In order for the included javascript to properly process your form, please make sure you name your product field `price_id` and set the id of your form to `ezstripe`
```html
<form id="ezstripe">
    @csrf
    <input type="hidden" value="1" name="user_id" />

    <select name="price_id">
        @foreach($products as $product)
            @foreach($product->prices as $price)
                {!-- EZStripe is designed for subscriptions, so we're only including recurring prices here --}
                @if(isset($price->recurring))
                    <option value="{{ $price->id }}">${{ number_format($price->unit_amount/100,2) }} / {{ $price->recurring->interval }} - {{ $product->name }}</option>
                @endif
            @endforeach
        @endforeach
    </select>
    <br />
    <button type="submit">Purchase</button>
</form>
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
