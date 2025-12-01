<?php

namespace App\Http\Controllers;

use App\Models\DonorPayment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class StripeController extends Controller
{
  
    public function success(Request $request)
    {
        $session_id = $request->query('session_id');

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $session = StripeSession::retrieve($session_id);

        $payment = DonorPayment::where('stripe_session_id', $session_id)->first();

        if ($payment) {
            $payment->update([
                'status' => 'accepted',
                'payment_date' => now(),
                'stripe_payment_intent' => $session->payment_intent,
            ]);
        }

        return view('stripe.success', compact('payment'));
    }

    public function cancel()
    {
        return view('stripe.cancel');
    }

}
