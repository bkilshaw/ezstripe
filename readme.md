# EZStripe

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

Easily add subscriptions to your Laravel app by relying on Stripe Checkout and Billing Portal to do the heavy lifting. 


## Introduction

This README is broken down into a three sections:
1. Installation
2. Configuring Stripe
3. Configuring your application

All steps should be covered. If you run into any issues or have any recommendations please create an Issue.

## Installation

Via Composer

``` bash
$ composer require bkilshaw/ezstripe
```



## Configuring your application

Publish EZStripes assets:
```
php artisan vendor:publish --tag=ezstripe.config --tag=ezstripe.views --force
```

Run the included migrations to add the `stripe_id` field to your `users` table.
```php
php artisan migrate
```

Update your `VerifyCSRFToken.php` to bypass CSRF protection on EZStripes endpoints:
```php
protected $except = [
    'ezstripe/*',
];
```

Include the EZStripe JS on all your pages by adding the included blade component before your closing `</body>`
```php
<x-ezstripe-javascript />
```

Create a new class `app\Http\Controllers\WebhookController.php` class that extends EZStripes WebhookController
```php
<?php
namespace App\Http\Controllers;

use bkilshaw\EZStripe\Http\Controllers\WebhookController as EZStripeController;

class WebhookController extends EZStripeController 
{
    //
}
```

Create a route in your `routes/web.php` to route the `/ezstripe/webhooks` route to your newly created `WebhookController`:
```php
Route::post('ezstripe/webhooks', [App\Http\Controllers\WebhookController::class, 'webhooks'])->name('ezstripe.webhooks');
```

Add the following environment variables to your `.env`. You can find your Stripe Keys here [https://dashboard.stripe.com/apikeys](https://dashboard.stripe.com/apikeys)

The `STRIPE_WEBHOOK_SECRET` be generated in a few steps and can be left alone for the time being

All three URLs are up to you. The Success and Cancel URL's typically return back to your product page. The Billig Portal Return URL can point back to your users home/dashboard.


```php
# Stripes Publishable key
STRIPE_KEY=pk_* 

# Stripes Secret key
STRIPE_SECRET=sk_*

# Stripes Webhook Secret
STRIPE_WEBHOOK_SECRET=whsec_*

# The URL a user will be redirected to after they have successfully subscribed
CHECKOUT_SUCCESS_URL=

# The URL a user will be redirected to if they are in Stripe Checkout and hit 'cancel' or 'back'
CHECKOUT_CANCEL_URL=

# The URL a user will be redirected to after vising Stripes Billing Portal
BILLING_PORTAL_RETURN_URL=
```

## Setting Up Stripe

### Webhooks

#### What are webhooks?
Stripe uses webhooks to notify your application when an event happens in your account.

Example events:
- Customer Created
- Invoice Paid
- Subscription Created
- Subscription Expiring
- A customer's credit card is expiring

Stripe does a great job explaining Webhooks in their docs [https://stripe.com/docs/webhooks](https://stripe.com/docs/webhooks).

#### Webhooks and EZStripe
In order to keep your application up to date with changes in Stripe, you must provide Stripe with an endpoint to send the webhooks. You do this by creating a Webhook Endpoint within Stripe here: [https://dashboard.stripe.com/webhooks](https://dashboard.stripe.com/webhooks)

When you're creating a new endpoint, the URL should point to `https://yourdomain.tld/ezstripe/webhooks` where you replace `yourdomain.tld` with your actual domain. 

Under the _Events to send_ section pick which events you would like to be notified of, or click `receive all events`. (_Note: some actions within Stripe can trigger many events to run, each one hitting your webhook endpoint. It's best practice to only send the Webhooks you need_)

Once you have created your Webhook Endpoint, copy the `Signing secret` into the `STRIPE_WEBHOOK_SECRET` section of your .env file.

> Note: If you plan on testing out your application locally, you will need to configure a local endpoint for the webhooks. If you run Laravel Valet you can run `valet share` to spawn a ngrok session and use the forwarding address in your Webhook endpoing (ie. `https://dk38alk3a.ngrok.io/ezstripe/webhooks`).
Every time you restart the ngrok tunnel it will generate a new domain, so you will need to update the Webhook endpoint in Stripe.
>
> An alternative option to ngrok is to use [Stripe CLI](https://stripe.com/docs/webhooks/test)

#### Stripe Billing Portal
To enable Stripes billing portal, please visit [https://dashboard.stripe.com/settings/billing/portal](https://dashboard.stripe.com/settings/billing/portal).

Enable the following options:
```
    Billing History: Enable
    Update Subscriptions: Enable
    Cancel Subscriptions: Enable
```

Under Products, add the products and prices that the user should be able to pick from when updating their subscription.

The rest of the settings are up to you. Links to your Terms of Service and Privacy Policy are required. If you don't have one, check out [https://getterms.io/](https://getterms.io/)


## Using it

### Stripe Checkout
Redirecting to Stripe Checkout is simple. All you need is a form with `<form id='ezstripe'>` that submits a field with `name='price_id'`. The price_id is the ID of one of your prices from Stripe.

#### Example
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

### Stripe Billing Portal
Redirecting to Stripe Billing Portal couldn't be easier. All you need is a link that points to the `ezstripe.billing_portal` route. When a user is logged in and clicks the link they will be redirected to Stripes Billing Portal.

```php
<a href="{{ route('ezstripe.billing_portal') }}">Billing Portal</a>
```

## Handling Webhooks

EZStripe will automatically update your user and add their stripe_id when they are first added to Stripe. 
All other [Webhook Events](https://stripe.com/docs/api/events/types) you wish to handle can be done by adding additional functions to your WebhookController.
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
EZStripe Method: handleSubscriptionScheduleExpiring(array $payload){}
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
