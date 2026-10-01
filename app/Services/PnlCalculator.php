<?php

namespace App\Services;

use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PnlCalculator
{
    /**
     * Ek din ka P&L nikalta hai, ek Store aur uske us din ke orders/ad_cost se.
     * $rates = us sheet ki saari PnlRate rows (tarikh ke order me, oldest se newest).
     *
     * Return: array ya null (null tab jab us din ke liye koi rate na mile)
     */
    public function calculateDay(Carbon $date, int $orders, float $adCost, Collection $rates): ?array
    {
        $rate = $this->rateFor($date, $rates);

        if (!$rate) {
            return null; // is din ke liye koi P&L settings nahi hain
        }

        $delivered = $orders * ($rate->delivery_percent / 100);
        $rto = $orders - $delivered;

        $deliveredMargin = $delivered * $rate->margin;
        $deliveredCharges = $delivered * $rate->delivered_charge;
        $rtoCost = $rto * $rate->rto_charge;

        $adCostWithGst = $adCost * (1 + $rate->gst_percent / 100);

        $netPnl = $deliveredMargin - $deliveredCharges - $rtoCost - $adCostWithGst;

        $cpa = $orders > 0 ? $adCost / $orders : null;

        // Break-even CPA (without GST) — is CPA se neeche rahoge to profit, upar gaye to loss
        $deliveryFraction = $rate->delivery_percent / 100;
        $rtoFraction = 1 - $deliveryFraction;
        $breakEvenWithGst = ($deliveryFraction * ($rate->margin - $rate->delivered_charge))
                            - ($rtoFraction * $rate->rto_charge);
        $breakEvenCpa = $breakEvenWithGst / (1 + $rate->gst_percent / 100);

        return [
            'orders' => $orders,
            'delivered' => $delivered,
            'rto' => $rto,
            'ad_cost' => $adCost,
            'ad_cost_with_gst' => $adCostWithGst,
            'cpa_with_gst' => $orders > 0 ? $adCostWithGst / $orders : null,
            'break_even_cpa_with_gst' => $breakEvenWithGst,
            'delivered_margin' => $deliveredMargin,
            'delivered_charges' => $deliveredCharges,
            'rto_cost' => $rtoCost,
            'net_pnl' => $netPnl,
            'cpa' => $cpa,
            'break_even_cpa' => $breakEvenCpa,
            'rate_used' => $rate,
        ];
    }

    /**
     * Us date ke liye applicable rate dhundta hai — jo rate us date ya usse pehle se
     * effective hai unme se sabse latest wala.
     */
    public function rateFor(Carbon $date, Collection $rates)
    {
        return $rates
            ->filter(fn ($r) => $r->effective_from->lte($date))
            ->sortByDesc('effective_from')
            ->first();
    }
}