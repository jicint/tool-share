import React, { useState, useEffect } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, CardElement, useStripe, useElements } from '@stripe/react-stripe-js';
import axios from 'axios';

const stripePromise = loadStripe(process.env.MIX_STRIPE_KEY);

// Card element styling
const cardStyle = {
    style: {
        base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
                color: '#aab7c4'
            }
        },
        invalid: {
            color: '#fa755a',
            iconColor: '#fa755a'
        }
    }
};

const CheckoutForm = ({ rental, onSuccess }) => {
    const stripe = useStripe();
    const elements = useElements();
    const [error, setError] = useState(null);
    const [cardError, setCardError] = useState(null);
    const [processing, setProcessing] = useState(false);
    const [costs, setCosts] = useState(null);
    const [discountCode, setDiscountCode] = useState('');
    const [paymentStatus, setPaymentStatus] = useState('');

    useEffect(() => {
        loadCosts();
    }, []);

    const loadCosts = async (code = '') => {
        setError(null);
        try {
            const response = await axios.get(`/api/rentals/${rental.id}/costs`, {
                params: { discount_code: code }
            });
            setCosts(response.data);
        } catch (error) {
            setError('Failed to load rental costs. Please try again.');
        }
    };

    const handleCardChange = (event) => {
        setCardError(event.error ? event.error.message : '');
    };

    const handleDiscountSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        if (!discountCode.trim()) {
            setError('Please enter a discount code');
            return;
        }
        await loadCosts(discountCode);
    };

    const handlePaymentError = (errorMessage) => {
        setProcessing(false);
        setError(errorMessage);
        setPaymentStatus('failed');
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        
        if (!stripe || !elements) {
            return;
        }

        if (cardError) {
            setError('Please fix card information before proceeding.');
            return;
        }

        setProcessing(true);
        setError(null);
        setPaymentStatus('processing');

        try {
            // Create payment intent
            const { data: { clientSecret } } = await axios.post(
                `/api/rentals/${rental.id}/payment-intent`,
                { 
                    payment_method: 'card',
                    discount_code: discountCode
                }
            );

            // Confirm card payment
            const result = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: elements.getElement(CardElement),
                    billing_details: {
                        name: rental.user.name, // Assuming user info is available in rental
                    }
                }
            });

            if (result.error) {
                // Handle specific error cases
                switch (result.error.code) {
                    case 'card_declined':
                        handlePaymentError('Your card was declined. Please try another card.');
                        break;
                    case 'expired_card':
                        handlePaymentError('Your card has expired. Please try another card.');
                        break;
                    case 'incorrect_cvc':
                        handlePaymentError('Your card\'s security code is incorrect.');
                        break;
                    case 'processing_error':
                        handlePaymentError('An error occurred while processing your card. Please try again.');
                        break;
                    case 'insufficient_funds':
                        handlePaymentError('Your card has insufficient funds.');
                        break;
                    case 'authentication_required':
                        handlePaymentError('Your card requires authentication. Please complete the verification process.');
                        break;
                    default:
                        handlePaymentError(result.error.message);
                }
            } else if (result.paymentIntent.status === 'succeeded') {
                setPaymentStatus('succeeded');
                onSuccess(result.paymentIntent);
            }
        } catch (error) {
            handlePaymentError('An unexpected error occurred. Please try again.');
            console.error('Payment error:', error);
        }

        setProcessing(false);
    };

    if (!costs) return (
        <div className="flex items-center justify-center p-4">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
        </div>
    );

    return (
        <form onSubmit={handleSubmit} className="space-y-4">
            <div className="bg-white p-4 rounded-lg shadow">
                {/* Cost Breakdown */}
                <div className="space-y-2 mb-4">
                    <h3 className="text-lg font-semibold mb-4">Payment Summary</h3>
                    <div className="flex justify-between">
                        <span>Rental Fee:</span>
                        <span>${costs.rental_fee.toFixed(2)}</span>
                    </div>
                    <div className="flex justify-between">
                        <span>Security Deposit:</span>
                        <span>${costs.security_deposit.toFixed(2)}</span>
                    </div>
                    {costs.late_fees > 0 && (
                        <div className="flex justify-between text-red-600">
                            <span>Late Fees:</span>
                            <span>${costs.late_fees.toFixed(2)}</span>
                        </div>
                    )}
                    {costs.discount > 0 && (
                        <div className="flex justify-between text-green-600">
                            <span>Discount:</span>
                            <span>-${costs.discount.toFixed(2)}</span>
                        </div>
                    )}
                    <div className="flex justify-between font-bold border-t pt-2">
                        <span>Total:</span>
                        <span>${costs.total.toFixed(2)}</span>
                    </div>
                </div>

                {/* Discount Code Section */}
                <div className="mb-6">
                    <div className="flex gap-2">
                        <input
                            type="text"
                            value={discountCode}
                            onChange={(e) => setDiscountCode(e.target.value)}
                            placeholder="Discount Code"
                            className="border rounded px-3 py-2 flex-grow focus:outline-none focus:ring-2 focus:ring-blue-500"
                            disabled={processing}
                        />
                        <button
                            type="button"
                            onClick={handleDiscountSubmit}
                            className="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 disabled:opacity-50"
                            disabled={processing || !discountCode.trim()}
                        >
                            Apply
                        </button>
                    </div>
                </div>

                {/* Card Input */}
                <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 mb-2">
                        Card Information
                    </label>
                    <div className="border rounded-md p-3">
                        <CardElement options={cardStyle} onChange={handleCardChange} />
                    </div>
                    {cardError && (
                        <p className="mt-2 text-sm text-red-600">{cardError}</p>
                    )}
                </div>

                {/* Error Messages */}
                {error && (
                    <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                        <p className="text-red-600">{error}</p>
                    </div>
                )}

                {/* Payment Status Messages */}
                {paymentStatus === 'processing' && (
                    <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                        <p className="text-blue-600">Processing your payment...</p>
                    </div>
                )}

                {/* Submit Button */}
                <button
                    type="submit"
                    disabled={!stripe || processing || !!cardError}
                    className={`w-full bg-green-500 text-white py-3 rounded-md font-medium
                        hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
                        ${(processing || !stripe || !!cardError) ? 'opacity-50 cursor-not-allowed' : ''}`}
                >
                    {processing ? (
                        <span className="flex items-center justify-center">
                            <svg className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    ) : (
                        `Pay $${costs.total.toFixed(2)}`
                    )}
                </button>

                {/* Test Card Information */}
                {process.env.NODE_ENV === 'development' && (
                    <div className="mt-4 p-3 bg-gray-50 border border-gray-200 rounded-md">
                        <p className="text-sm text-gray-600 mb-2">Test Card Numbers:</p>
                        <ul className="text-sm text-gray-500 space-y-1">
                            <li>Success: 4242 4242 4242 4242</li>
                            <li>Requires Authentication: 4000 0000 0000 3220</li>
                            <li>Declined: 4000 0000 0000 0002</li>
                            <li>Insufficient Funds: 4000 0000 0000 9995</li>
                        </ul>
                    </div>
                )}
            </div>
        </form>
    );
};

const PaymentForm = ({ rental, onSuccess }) => (
    <Elements stripe={stripePromise}>
        <CheckoutForm rental={rental} onSuccess={onSuccess} />
    </Elements>
);

export default PaymentForm; 