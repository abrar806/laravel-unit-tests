<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        if ($merchant->user->email === $email) {
            throw new AffiliateCreateException('Email is already in use by the merchant.');
        }

        if (Affiliate::whereHas('user', function ($query) use ($email) {
            $query->where('email', $email);
        })->exists()) {
            throw new AffiliateCreateException('Email is already in use by an affiliate.');
        }

        $discountCodeDetails = $this->apiService->createDiscountCode($merchant);

        $user = User::create([
            'email' => $email,
            'name' => $name,
            'type' => User::TYPE_MERCHANT,
        ]);

        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'commission_rate' => $commissionRate,
            'discount_code' => $discountCodeDetails['code'],
            'merchant_id' => $merchant->id,
        ]);

        Mail::to($user)->send(new AffiliateCreated($affiliate));

        return $affiliate;
    }
}
