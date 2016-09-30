<?php

namespace Flosch\Bundle\StripeBundle\Stripe;

use Stripe\Stripe,
    Stripe\Charge,
    Stripe\Customer,
    Stripe\Coupon,
    Stripe\Plan,
    Stripe\Subscription;

/**
 * An extension of the Stripe PHP SDK, including an API key parameter to automatically authenticate.
 *
 * This class will provide helper methods to use the Stripe SDK
 */
class StripeClient extends Stripe
{
    public function __construct($stripeApiKey)
    {
        self::setApiKey($stripeApiKey);

        return $this;
    }

    /**
     * Retrieve a Coupon instance by its ID
     *
     * @throws HttpException:
     *     - If the couponId is invalid (the coupon does not exists...)
     *
     * @see https://stripe.com/docs/api#coupons
     *
     * @param string $couponId: The coupon ID
     *
     * @return Coupon
     */
    public function retrieveCoupon(string $couponId)
    {
        return Coupon::retrieve($couponId);
    }

    /**
     * Retrieve a Plan instance by its ID
     *
     * @throws HttpException:
     *     - If the planId is invalid (the plan does not exists...)
     *
     * @see https://stripe.com/docs/subscriptions/tutorial#create-subscription
     *
     * @param string $planId: The plan ID
     *
     * @return Plan
     */
    public function retrievePlan(string $planId)
    {
        return Plan::retrieve($planId);
    }

    /**
     * Associate a new Customer object to an existing Plan.
     *
     * @throws HttpException:
     *     - If the planId is invalid (the plan does not exists...)
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/subscriptions/tutorial#create-subscription
     *
     * @param string $planId: The plan ID as defined in your Stripe dashboard
     * @param string $paymentToken: The payment token returned by the payment form (Stripe.js)
     * @param string $customerEmail: The customer email
     * @param string|null $couponId: An optional coupon ID
     *
     * @return Customer
     */
    public function subscribeCustomerToPlan(string $planId, string $paymentToken, string $customerEmail, string $couponId = null)
    {
        $customer = Customer::create([
            'source'    => $paymentToken,
            'email'     => $customerEmail
        ]);

        $data = [
            'customer' => $customer->id,
            'plan' => $planId,
        ];

        if ($couponId) {
            $data['coupon'] = $couponId;
        }

        $subscription = Subscription::create($data);

        return $customer;
    }

    /**
     * Create a new Charge from a payment token, to a connected stripe account.
     *
     * @throws HttpException:
     *     - If the planId is invalid (the plan does not exists...)
     *     - If the payment token is invalid (payment failed)
     *
     * @see https://stripe.com/docs/subscriptions/tutorial#create-subscription
     *
     * @param int    $chargeAmount: The charge amount in cents
     * @param string $chargeCurrency: The charge currency to use
     * @param string $stripeAccountId: The connected stripe account ID
     * @param string $paymentToken: The payment token returned by the payment form (Stripe.js)
     * @param int    $applicationFee: The fee taken by the platform will take, in cents
     * @param string $description: An optional charge description
     *
     * @return Customer
     */
    public function createCharge(int $chargeAmount, string $chargeCurrency, string $paymentToken, string $stripeAccountId, int $applicationFee = 0, string $chargeDescription = '')
    {
        return Charge::create([
            'amount'            => $chargeAmount,
            'currency'          => $chargeCurrency,
            'source'            => $paymentToken,
            'application_fee'   => $applicationFee,
            'description'       => $chargeDescription
        ], [
            'stripe_account'    => $stripeAccountId
        ]);
    }
}
