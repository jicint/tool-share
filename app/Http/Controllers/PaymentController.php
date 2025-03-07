<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function calculateCosts(Rental $rental, Request $request)
    {
        try {
            $costs = $this->paymentService->calculateRentalCosts(
                $rental, 
                $request->input('discount_code')
            );

            return response()->json($costs);
        } catch (\Exception $e) {
            Log::error('Error calculating rental costs', [
                'error' => $e->getMessage(),
                'rental_id' => $rental->id
            ]);
            return response()->json(['error' => 'Failed to calculate costs'], 500);
        }
    }

    public function createPaymentIntent(Rental $rental, Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_method' => 'required|string|in:card,ideal,sepa_debit'
            ]);

            $paymentIntent = $this->paymentService->createPaymentIntent(
                $rental,
                $validated['payment_method']
            );

            return response()->json([
                'clientSecret' => $paymentIntent->client_secret
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating payment intent', [
                'error' => $e->getMessage(),
                'rental_id' => $rental->id
            ]);
            return response()->json(['error' => 'Failed to create payment'], 500);
        }
    }
} 