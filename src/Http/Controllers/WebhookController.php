<?php

namespace bkilshaw\EZStripe\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stripe\Event;
use Stripe\UsageRecord;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;

class WebhookController
{
    public function webhooks(Request $request)
    {
        $this->verify_webhook($request);
        $this->handle_webhook($request);
    }

    public function verify_webhook(Request $request)
    {
        $endpoint_secret = config('ezstripe.stripe_webhook_secret');
        $stripe_signature = $request->header('stripe-signature');
        $payload = $request->getContent();


        try {
            \Stripe\Webhook::constructEvent(
                $payload, $stripe_signature, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            throw new AccessDeniedException($e->getMessage(), $e);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            throw new AccessDeniedException($e->getMessage(), $e);
        }
    }

    public function handle_webhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

        if(method_exists($this, $method))
        {
            return $this->{$method}($payload);
        }

        return $this->method_does_not_exist($method);
    }


    protected function handleCheckoutSessionCompleted(array $payload)
    {
        $stripe = $payload['data']['object'];

        $user = User::findOrFail($stripe['client_reference_id']);
        $user->stripe_id = $stripe['customer'];
        $user->save();

        return $this->webhook_successful();
    }

    protected function webhook_successful()
    {
        return new Response('Success', 200);
    }

    protected function method_does_not_exist()
    {
        return new Response;
    }


}
