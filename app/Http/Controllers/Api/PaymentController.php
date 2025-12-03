<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\Helpers\ApiResponseTrait;
use App\Models\PaymentLog;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;
use App\Services\PaymobService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private PaymentRepository $paymentRepository,
        private PaymentService $paymentService,
        private PaymobService $paymobService
    ) {}

    /**
     * Create payment intention (unified endpoint)
     * Handles both investment and wallet intentions
     */
    public function createIntention(Request $request): JsonResponse|array
    {
        try {
            $userId = Auth::id();
            $type = $request->input('type', 'investment'); // Default to investment for backward compatibility
            $payBy = $request->input('pay_by', 'card'); // Default to card

            // Validate pay_by parameter
            if (!in_array($payBy, ['card', 'apple_pay'])) {
                return $this->respondBadRequest('Invalid pay_by parameter. Must be "card" or "apple_pay"');
            }

            $this->logRequest($type, $request, $userId);

            $result = $this->paymentService->createIntention($request->all(), $userId, $type);

            // return $result;

            if ($result['success']) {
                PaymentLog::info("{$type} intention created successfully", [
                    'intention_id' => $result['intention']->id ?? null,
                    'type' => $type
                ], $userId, $result['intention']->id ?? null, null, 'intention_created');


                $intention = $result['intention'];

                return response()->json([
                    'success' => true,
                            'message' => 'Wallet charging intention created successfully',
                    'data' => array_merge($result['data'], [
                                'amount_sar' => 100 / 100,
                                'operation_type' => 'wallet_charge',
                    ])
                ], 201);

                return $this->respondCreated([
                    'success' => true,
                    'message' => 'Payment intention created successfully',
                    'result' => $result['data']
                ]);
            }

            PaymentLog::error('Failed to create payment intention', [
                'error' => $result['error'],
                'type' => $type
            ], $userId, null, null, 'intention_failed');

            return $this->respondBadRequest($result['error'], $result['details'] ?? []);

        } catch (ValidationException $e) {
            return $this->respondValidationErrors($e);
        } catch (\Exception $e) {
            PaymentLog::error('Exception occurred', [
                'exception' => PaymentLog::formatException($e)
            ], Auth::id(), null, null, 'exception');

            $statusCode = is_numeric($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600
                ? (int) $e->getCode()
                : 500;

            return $this->respondError($e->getMessage() ?: 'An error occurred', $statusCode);
        }
    }

    /**
     * Create investment payment intention (dedicated endpoint)
     */
    public function createInvestmentIntention(Request $request): JsonResponse|array
    {
        // Add type to request and call unified method
        $request->merge(['type' => 'investment']);
        return $this->createIntention($request);
    }

    /**
     * Create wallet charging intention (dedicated endpoint)
     */
    public function createWalletIntention(Request $request): JsonResponse
    {
        // Add type to request and call unified method
        $request->merge(['type' => 'wallet_charge']);
        return $this->createIntention($request);
    }

    /**
     * Get checkout URL for payment intention
     */
    public function getCheckoutUrl(Request $request, int $intentionId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $intention = $this->paymentRepository->findIntentionByUser($intentionId, $userId);

        if (!$intention) {
                return $this->respondNotFound('Payment intention not found');
        }

        if ($intention->isExpired()) {
                return $this->respondBadRequest('Payment intention has expired');
        }

        if (!$intention->client_secret) {
                return $this->respondBadRequest('Client secret not available for this intention');
        }

        $result = $this->paymobService->getCheckoutUrl($intention->client_secret);

        if ($result['success']) {
                // Update status to active (checkout URL is generated on-demand, not stored)
            $this->paymentRepository->updateIntention($intention, [
                'status' => 'active'
            ]);

                return $this->respondSuccessWithData('Checkout URL generated successfully', $result);
            }

            return $this->respondBadRequest($result['error'], $result['details'] ?? []);

        } catch (\Exception $e) {
            return $this->respondError($e->getMessage() ?: 'An error occurred', 500);
        }
    }

    /**
     * Get payment intentions for the authenticated user
     */
    public function getIntentions(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'type', 'from_date', 'to_date']);
        $intentions = $this->paymentRepository->getUserIntentions(
            Auth::id(),
            $filters,
            $request->get('per_page', 15)
        );

            return $this->respondSuccessWithData('Payment intentions retrieved successfully', $intentions);

        } catch (\Exception $e) {
            return $this->respondError($e->getMessage() ?: 'An error occurred', 500);
        }
    }

    /**
     * Get payment transactions for the authenticated user
     */
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'payment_method', 'from_date', 'to_date']);
        $transactions = $this->paymentRepository->getUserTransactions(
            Auth::id(),
            $filters,
            $request->get('per_page', 15)
        );

            return $this->respondSuccessWithData('Transactions retrieved successfully', $transactions);

        } catch (\Exception $e) {
            return $this->respondError($e->getMessage() ?: 'An error occurred', 500);
        }
    }

    /**
     * Get payment statistics for the authenticated user
     */
    public function getPaymentStats(Request $request): JsonResponse
    {
        try {
        $stats = $this->paymentRepository->getUserPaymentStats(Auth::id());
            return $this->respondSuccessWithData('Payment statistics retrieved successfully', $stats);

        } catch (\Exception $e) {
            return $this->respondError($e->getMessage() ?: 'An error occurred', 500);
        }
    }

    /**
     * Get payment logs for the authenticated user
     */
    public function getPaymentLogs(Request $request): JsonResponse
    {
        try {
        $query = PaymentLog::where('user_id', Auth::id())
            ->with(['paymentIntention', 'paymentTransaction'])
            ->orderBy('created_at', 'desc');

            // Apply filters
            foreach (['type', 'action', 'intention_id', 'from_date', 'to_date'] as $filter) {
                if ($request->has($filter)) {
                    match($filter) {
                        'type' => $query->where('type', $request->get($filter)),
                        'action' => $query->where('action', $request->get($filter)),
                        'intention_id' => $query->where('payment_intention_id', $request->get($filter)),
                        'from_date' => $query->where('created_at', '>=', $request->get($filter)),
                        'to_date' => $query->where('created_at', '<=', $request->get($filter)),
                        default => null
                    };
                }
        }

        $logs = $query->paginate($request->get('per_page', 15));

            return $this->respondSuccessWithData('Payment logs retrieved successfully', $logs);

        } catch (\Exception $e) {
            return $this->respondError($e->getMessage() ?: 'An error occurred', 500);
        }
    }

    /**
     * Log incoming request
     */
    private function logRequest(string $type, Request $request, int $userId): void
    {
        PaymentLog::info("Payment {$type} intention request received", [
            'request_data' => $request->all(),
            'user_id' => $userId
        ], $userId, null, null, "create_{$type}_intention_request");
    }
}
