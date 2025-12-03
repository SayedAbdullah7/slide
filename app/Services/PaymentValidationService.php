<?php

namespace App\Services;

use App\Models\InvestmentOpportunity;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PaymentValidationService
{
    /**
     * Validate investment intention request
     */
    public function validateInvestmentIntention(array $data, int $userId): array
    {
        $validator = Validator::make($data, [
            'opportunity_id' => 'required|exists:investment_opportunities,id',
            'shares' => 'required|integer|min:1',
            'investment_type' => 'required|string|in:full,partial',
        ]);

        if ($validator->fails()) {
            PaymentLog::error('Investment intention validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $data
            ], $userId, null, null, 'validation_failed');

            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return [
            'user_id' => $userId,
            'opportunity_id' => $data['opportunity_id'],
            'shares' => $data['shares'],
            'investment_type' => $data['investment_type'],
        ];
    }

    /**
     * Validate wallet intention request
     */
    public function validateWalletIntention(array $data, int $userId): array
    {
        $validator = Validator::make($data, [
            'amount' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            PaymentLog::error('Wallet intention validation failed', [
                'errors' => $validator->errors()->toArray(),
                'data' => $data
            ], $userId, null, null, 'validation_failed');

            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return [
            'user_id' => $userId,
            'amount_cents' => (int) round($data['amount'] * 100),
        ];
    }

    /**
     * Validate opportunity availability
     */
    public function validateOpportunity(int $opportunityId, int $userId): InvestmentOpportunity
    {
        $opportunity = InvestmentOpportunity::find($opportunityId);

        if (!$opportunity) {
            PaymentLog::error('Investment opportunity not found', [
                'opportunity_id' => $opportunityId
            ], $userId, null, null, 'opportunity_not_found');

            throw new \Exception('Investment opportunity not found', 404);
        }

        if ($opportunity->status !== 'active' || !$opportunity->is_fundable) {
            PaymentLog::error('Investment opportunity not available', [
                'opportunity_id' => $opportunityId,
                'status' => $opportunity->status,
                'is_fundable' => $opportunity->is_fundable
            ], $userId, null, null, 'opportunity_not_available');

            throw new \Exception('Investment opportunity is not available for investment', 400);
        }

        return $opportunity;
    }

    /**
     * Validate shares availability
     */
    public function validateShares(int $shares, InvestmentOpportunity $opportunity, int $userId): void
    {
        if ($shares > $opportunity->available_shares) {
            PaymentLog::error('Insufficient shares available', [
                'requested_shares' => $shares,
                'available_shares' => $opportunity->available_shares,
                'opportunity_id' => $opportunity->id
            ], $userId, null, null, 'insufficient_shares');

            throw new \Exception(
                "Insufficient shares available. Available: {$opportunity->available_shares}, Requested: {$shares}",
                400
            );
        }
    }
}

