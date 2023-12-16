<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\MerchantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Order;

class MerchantController extends Controller
{
    public function __construct(
        MerchantService $merchantService
    ) {}

    /**
     * Useful order statistics for the merchant API.
     *
     * @param Request $request Will include a from and to date
     * @return JsonResponse Should be in the form {count: total number of orders in range, commission_owed: amount of unpaid commissions for orders with an affiliate, revenue: sum order subtotals}
     */
    public function orderStats(Request $request): JsonResponse
    {
        // TODO: Complete this method
        $fromDate = $request->input('from');
        $toDate = $request->input('to');

        $orders = Order::whereBetween('created_at', [$fromDate, $toDate])->get();

        $count = $orders->count();
        $commissionOwed = $orders->where('payout_status', Order::STATUS_UNPAID)
            ->whereNotNull('affiliate_id')
            ->sum('commission_owed');
        $revenue = $orders->sum('subtotal');

        $response = [
            'count' => $count,
            'commissions_owed' => $commissionOwed,
            'revenue' => $revenue
        ];

        return response()->json($response);
    }
}
