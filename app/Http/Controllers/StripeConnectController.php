<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('stripe.secret'));
    }

    /**
     * Initiate Stripe Connect onboarding for a student.
     */
    public function onboarding(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Only students can onboard
        if ($user->role !== 'student') {
            abort(403, 'Only students can connect a Stripe account.');
        }

        try {
            // Create or retrieve Stripe Connect account
            if (! $user->stripe_connect_account_id) {
                $account = $this->stripe->accounts->create([
                    'type'         => config('stripe.connect.account_type'),
                    'email'        => $user->email,
                    'capabilities' => [
                        'card_payments' => ['requested' => true],
                        'transfers'     => ['requested' => true],
                    ],
                    'metadata'     => [
                        'user_id' => $user->id,
                    ],
                ]);

                $user->update(['stripe_connect_account_id' => $account->id]);
            }

            // Create account link for onboarding
            $accountLink = $this->stripe->accountLinks->create([
                'account'     => $user->stripe_connect_account_id,
                'refresh_url' => route('stripe.connect.refresh'),
                'return_url'  => route('stripe.connect.return'),
                'type'        => 'account_onboarding',
            ]);

            return redirect($accountLink->url);
        } catch (ApiErrorException $e) {
            return back()->with('error', 'Failed to initiate Stripe Connect onboarding: ' . $e->getMessage());
        }
    }

    /**
     * Handle return from Stripe Connect onboarding.
     */
    public function return (Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->stripe_connect_account_id) {
            return redirect()->route('student.dashboard')
                ->with('error', 'No Stripe Connect account found.');
        }

        try {
            // Retrieve account to check capabilities
            $account = $this->stripe->accounts->retrieve($user->stripe_connect_account_id);

            // Check if onboarding is complete
            if ($account->details_submitted) {
                // Check if required capabilities are active
                $cardPaymentsActive = isset($account->capabilities->card_payments)
                && $account->capabilities->card_payments === 'active';
                $transfersActive = isset($account->capabilities->transfers)
                && $account->capabilities->transfers === 'active';

                if ($cardPaymentsActive && $transfersActive) {
                    return redirect()->route('student.dashboard')
                        ->with('success', 'Stripe Connect onboarding completed successfully! You can now accept orders.');
                } else {
                    return redirect()->route('student.dashboard')
                        ->with('warning', 'Stripe Connect onboarding is pending. Some capabilities are still being verified.');
                }
            } else {
                return redirect()->route('student.dashboard')
                    ->with('warning', 'Stripe Connect onboarding is incomplete. Please complete all required information.');
            }
        } catch (ApiErrorException $e) {
            return redirect()->route('student.dashboard')
                ->with('error', 'Failed to verify Stripe Connect account: ' . $e->getMessage());
        }
    }

    /**
     * Handle refresh when user needs to restart onboarding.
     */
    public function refresh(Request $request)
    {
        return redirect()->route('stripe.connect.onboarding')
            ->with('info', 'Please complete the Stripe Connect onboarding process.');
    }

    /**
     * Check if the student's Stripe Connect account is fully onboarded.
     */
    public function checkStatus(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->stripe_connect_account_id) {
            return response()->json([
                'onboarded' => false,
                'message'   => 'No Stripe Connect account found.',
            ]);
        }

        try {
            $account = $this->stripe->accounts->retrieve($user->stripe_connect_account_id);

            $cardPaymentsActive = isset($account->capabilities->card_payments)
            && $account->capabilities->card_payments === 'active';
            $transfersActive = isset($account->capabilities->transfers)
            && $account->capabilities->transfers === 'active';

            $onboarded = $account->details_submitted && $cardPaymentsActive && $transfersActive;

            return response()->json([
                'onboarded'         => $onboarded,
                'details_submitted' => $account->details_submitted,
                'card_payments'     => $account->capabilities->card_payments ?? 'inactive',
                'transfers'         => $account->capabilities->transfers ?? 'inactive',
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'onboarded' => false,
                'error'     => $e->getMessage(),
            ], 500);
        }
    }
}
