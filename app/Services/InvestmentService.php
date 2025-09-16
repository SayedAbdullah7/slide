<?php

namespace App\Services;

use App\Models\Investment;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use Illuminate\Support\Facades\DB;
use App\Exceptions\InvestmentException;
use App\Events\InvestmentCreated;

class InvestmentService
{
    /**
     * Execute an investment.
     *
     * @param InvestorProfile $profile
     * @param InvestmentOpportunity $opportunity
     * @param int $shares
     * @return Investment
     * @throws InvestmentException
     */
    public function invest(InvestorProfile $investor, InvestmentOpportunity $opportunity, int $shares): Investment
    {

        // Prevent investing in own opportunity
        if ($investor->user_id === optional($opportunity->ownerProfile->investorProfile)->id) {
            throw new InvestmentException('You cannot invest in your own opportunity.');
        }

        // Check if opportunity is open for investment
//        if (! $opportunity->isInvestable()) {
//            throw new InvestmentException('This opportunity is not available for funding.');
//        }

        // Ensure shares are within limits
        $minShares = $this->calculateMinShares($opportunity);
        $maxShares = $this->calculateMaxShares($opportunity);

        if ($shares < $minShares) {
            throw new InvestmentException("Minimum allowed shares: {$minShares}.");
        }

        if ($maxShares !== null && $shares > $maxShares) {
            throw new InvestmentException("Maximum allowed shares: {$maxShares}.");
        }

        if ($shares > $opportunity->available_shares) {
            throw new InvestmentException('Insufficient shares available.');
        }

        $amount = $shares * $opportunity->price_per_share;

        return DB::transaction(function () use ($investor, $opportunity, $shares, $amount) {
            // Create investment record
            $investment = Investment::create([
                'investor_id' => $investor->id,
                'opportunity_id'      => $opportunity->id,
                'shares'              => $shares,
                'amount'              => $amount,
                'user_id'             => $investor->user_id,
            ]);

            // Reserve shares
            $opportunity->reserveShares($shares);

            // Optionally lock if fully funded
            if ($opportunity->available_shares === 0) {
                $opportunity->status = 'completed';
                $opportunity->save();
            }

            // Dispatch event for notification or analytics
            event(new InvestmentCreated($investment));

            return $investment;
        });
    }

    /**
     * Calculate the minimum number of shares allowed.
     */
    protected function calculateMinShares(InvestmentOpportunity $opportunity): int
    {
        return (int) ceil($opportunity->min_investment / $opportunity->price_per_share);
    }

    /**
     * Calculate the maximum number of shares allowed (if set).
     */
    protected function calculateMaxShares(InvestmentOpportunity $opportunity): ?int
    {
        if (! $opportunity->max_investment) {
            return null;
        }

        return (int) floor($opportunity->max_investment / $opportunity->price_per_share);
    }
}
