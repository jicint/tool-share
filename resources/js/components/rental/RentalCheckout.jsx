import React, { useState } from 'react';
import PaymentForm from '../payment/PaymentForm';

const RentalCheckout = ({ rental }) => {
    const [paymentComplete, setPaymentComplete] = useState(false);

    const handlePaymentSuccess = async (paymentIntent) => {
        try {
            // Update rental status or handle success
            setPaymentComplete(true);
        } catch (error) {
            console.error('Error updating rental status:', error);
        }
    };

    if (paymentComplete) {
        return (
            <div className="text-center p-8">
                <h2 className="text-2xl font-bold text-green-600 mb-4">
                    Payment Successful!
                </h2>
                <p className="mb-4">
                    Your rental has been confirmed. You can pick up your tool at the arranged time.
                </p>
                <a
                    href="/dashboard"
                    className="inline-block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600"
                >
                    View My Rentals
                </a>
            </div>
        );
    }

    return (
        <div className="max-w-2xl mx-auto p-4">
            <h2 className="text-2xl font-bold mb-6">Complete Your Rental</h2>
            <PaymentForm rental={rental} onSuccess={handlePaymentSuccess} />
        </div>
    );
};

export default RentalCheckout; 