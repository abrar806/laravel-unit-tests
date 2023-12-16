<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    public function __construct(
        protected AffiliateService $affiliateService
    ) {}

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $existingOrder = Order::where('external_order_id', $data['order_id'])->first();
        if ($existingOrder) {
            return;
        }

        $merchant = Merchant::where('domain', $data['merchant_domain'])->firstOrFail();
        $affiliate = Affiliate::where('discount_code', $data['discount_code'])->first();

        if (!$affiliate) {
            $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], 0.1);
        }

        $commissionOwed = $data['subtotal_price'] * $affiliate->commission_rate;

        /*Order::create([
            'subtotal' => $data['subtotal_price'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'commission_owed' => $commissionOwed,
            // Add other order fields if needed
        ]);*/
        $orderData = [
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
            'subtotal' => $data['subtotal_price'],
            'commission_owed' => $commissionOwed,
            'payout_status' => Order::STATUS_UNPAID,
            'order_id' => $data['order_id']
        ];
        $order = Order::create($orderData);

    }
}
