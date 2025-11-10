<?php
namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class HandleAccountUpdated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 3;
    public $backoff = [60, 120, 300];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public object $account
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Find user by Stripe Connect account ID
            $user = User::where('stripe_connect_account_id', $this->account->id)->first();

            if (! $user) {
                Log::warning('User not found for Stripe account', [
                    'account_id' => $this->account->id,
                ]);
                return;
            }

            // Check capabilities status
            $cardPaymentsActive = isset($this->account->capabilities->card_payments)
            && $this->account->capabilities->card_payments === 'active';
            $transfersActive = isset($this->account->capabilities->transfers)
            && $this->account->capabilities->transfers === 'active';

            $fullyOnboarded = $this->account->details_submitted && $cardPaymentsActive && $transfersActive;

            Log::info('Stripe Connect account updated', [
                'user_id'           => $user->id,
                'account_id'        => $this->account->id,
                'details_submitted' => $this->account->details_submitted,
                'card_payments'     => $this->account->capabilities->card_payments ?? 'inactive',
                'transfers'         => $this->account->capabilities->transfers ?? 'inactive',
                'fully_onboarded'   => $fullyOnboarded,
            ]);

            // TODO: Send notification to student if onboarding is complete
            // TODO: Update user metadata if needed

        } catch (\Exception $e) {
            Log::error('Error handling account updated', [
                'account_id' => $this->account->id,
                'error'      => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
