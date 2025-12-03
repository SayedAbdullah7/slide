<?php

namespace App\Services;

use App\Models\InvestmentOpportunity;
use App\Models\PaymentLog;
use Illuminate\Http\JsonResponse;

class PaymentResponseService
{
    /**
     * Success response for investment intention
     */
    public function investmentIntentionCreated(array $result, array $data, InvestmentOpportunity $opportunity): JsonResponse
    {
        $intention = $result['intention'];
        $amountCents = $data['shares'] * $opportunity->price_per_share * 100;

        PaymentLog::info('Investment intention created successfully', [
            'intention_id' => $intention->id,
            'opportunity_id' => $data['opportunity_id'],
            'amount_cents' => $amountCents,
            'shares' => $data['shares']
        ], $data['user_id'], $intention->id, null, 'intention_created');

        return response()->json([
            'success' => true,
            'message' => 'Payment intention created successfully',
            'data' => array_merge($result['data'], [
                'opportunity_id' => $data['opportunity_id'],
                'shares' => $data['shares'],
                'investment_type' => $data['investment_type'],
                'amount_sar' => $amountCents / 100,
                'price_per_share' => $opportunity->price_per_share,
                'opportunity_name' => $opportunity->name,
            ])
        ], 201);
    }

    /**
     * Success response for wallet intention
     */
    public function walletIntentionCreated(array $result, array $data): JsonResponse
    {
        $intention = $result['intention'];

        PaymentLog::info('Wallet intention created successfully', [
            'intention_id' => $intention->id,
            'amount_cents' => $data['amount_cents']
        ], $data['user_id'], $intention->id, null, 'wallet_intention_created');

        return response()->json([
            'success' => true,
            'message' => 'Wallet charging intention created successfully',
            'data' => array_merge($result['data'], [
                'amount_sar' => $data['amount_cents'] / 100,
                'operation_type' => 'wallet_charge',
            ])
        ], 201);
    }

    /**
     * Error response for failed intention
     */
    public function intentionFailed(array $result, int $userId, ?int $opportunityId = null): JsonResponse
    {
        PaymentLog::error('Failed to create payment intention', [
            'error' => $result['error'],
            'details' => $result['details'] ?? null,
            'opportunity_id' => $opportunityId
        ], $userId, null, null, 'intention_failed');

        return response()->json([
            'success' => false,
            'message' => $result['error'],
            'details' => $result['details'] ?? null
        ], 400);
    }

    /**
     * Validation error response
     */
    public function validationError(array $errors, int $userId): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }

    /**
     * Generic error response
     */
    public function error(string $message, int $code = 400, ?array $details = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($details) {
            $response['details'] = $details;
        }

        return response()->json($response, $code);
    }

    /**
     * Exception response
     */
    public function exception(\Exception $e, int $userId, ?array $context = null): JsonResponse
    {
        PaymentLog::error('Exception occurred', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'context' => $context
        ], $userId, null, null, 'exception');

        // Determine HTTP status code
        $statusCode = 500;
        if (is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600) {
            $statusCode = (int) $e->getCode();
        }

        return response()->json([
            'success' => false,
            'message' => $e->getMessage() ?: 'An error occurred',
            'error' => config('app.debug') ? $e->getMessage() : null
        ], $statusCode);
    }

    /**
     * Success response with data
     */
    public function success(string $message, $data = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Not found response
     */
    public function notFound(string $resource = 'Resource'): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => "{$resource} not found"
        ], 404);
    }
}

