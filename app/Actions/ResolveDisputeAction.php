<?php
namespace App\Actions;

use App\Actions\Payments\RefundOrderAction;
use App\Actions\Payments\ReleaseEscrowAction;
use App\Models\Dispute;
use App\Notifications\DisputeResolvedNotification;
use Illuminate\Support\Facades\DB;

class ResolveDisputeAction
{
    public function execute(
        Dispute $dispute,
        string $resolutionType,
        string $resolutionNotes,
        ?float $partialAmount,
        int $adminId
    ): void {
        DB::transaction(function () use ($dispute, $resolutionType, $resolutionNotes, $partialAmount, $adminId) {
            $order = $dispute->order;

            // Process payment based on resolution type
            switch ($resolutionType) {
                case 'release':
                    // Release full payment to student
                    app(ReleaseEscrowAction::class)->execute($order);
                    $dispute->update([
                        'status'           => 'released',
                        'resolution_notes' => $resolutionNotes,
                        'resolved_by_id'   => $adminId,
                        'resolved_at'      => now(),
                    ]);
                    $order->update(['status' => 'completed']);
                    break;

                case 'refund':
                    // Full refund to client
                    app(RefundOrderAction::class)->execute($order);
                    $dispute->update([
                        'status'           => 'refunded',
                        'resolution_notes' => $resolutionNotes,
                        'resolved_by_id'   => $adminId,
                        'resolved_at'      => now(),
                    ]);
                    $order->update(['status' => 'cancelled', 'cancelled_reason' => 'Dispute resolved with refund']);
                    break;

                case 'partial':
                    // Partial payment - this requires custom Stripe logic
                    // For now, we'll mark it as resolved and handle manually
                    $dispute->update([
                        'status'           => 'resolved',
                        'resolution_notes' => $resolutionNotes . " (Partial amount: $" . number_format($partialAmount, 2) . ")",
                        'resolved_by_id'   => $adminId,
                        'resolved_at'      => now(),
                    ]);
                    // Note: Partial refunds need to be handled manually in Stripe dashboard
                    // or implement custom Stripe API calls here
                    break;
            }

            // Notify both parties
            $order->student->notify(new DisputeResolvedNotification($dispute, $resolutionType));
            $order->client->notify(new DisputeResolvedNotification($dispute, $resolutionType));
        });
    }
}
