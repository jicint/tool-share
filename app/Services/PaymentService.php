<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Rental;
use App\Models\Discount;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function calculateRentalCosts(Rental $rental, ?string $discountCode = null)
    {
        $costs = [
            'rental_fee' => $rental->total_price,
            'security_deposit' => $this->calculateSecurityDeposit($rental),
            'late_fees' => $this->calculateLateFees($rental),
            'discount' => 0,
            'total' => 0
        ];

        if ($discountCode) {
            $discount = Discount::where('code', $discountCode)
                ->where('is_active', true)
                ->first();

            if ($discount && $discount->isValid()) {
                $costs['discount'] = $this->calculateDiscount($costs['rental_fee'], $discount);
            }
        }

        $costs['total'] = $costs['rental_fee'] + $costs['security_deposit'] + 
                         $costs['late_fees'] - $costs['discount'];

        return $costs;
    }

    public function createPaymentIntent(Rental $rental, string $paymentMethod)
    {
        $costs = $this->calculateRentalCosts($rental);

        $paymentIntent = PaymentIntent::create([
            'amount' => $costs['total'] * 100, // Convert to cents
            'currency' => 'usd',
            'payment_method_types' => [$paymentMethod],
            'metadata' => [
                'rental_id' => $rental->id,
                'user_id' => $rental->user_id
            ]
        ]);

        return $paymentIntent;
    }

    private function calculateSecurityDeposit(Rental $rental)
    {
        // Base deposit is 20% of tool value or minimum $50
        return max($rental->tool->value * 0.20, 50.00);
    }

    private function calculateLateFees(Rental $rental)
    {
        if (!$rental->returned_at || $rental->returned_at <= $rental->end_date) {
            return 0;
        }

        $daysLate = $rental->returned_at->diffInDays($rental->end_date);
        // Late fee is 20% of daily rate per day late
        return $daysLate * ($rental->tool->daily_rate * 0.20);
    }

    private function calculateDiscount($amount, Discount $discount)
    {
        if ($discount->type === 'percentage') {
            return $amount * ($discount->value / 100);
        }
        return min($discount->value, $amount); // Fixed amount discount
    }
} 