<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\PaymentRepository;
use App\Services\PaymobService;
use App\Services\PaymentWebhookService;
use App\Models\PaymentLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private PaymobService $paymobService,
        private PaymentRepository $paymentRepository,
        private PaymentWebhookService $webhookHandler
    ) {}

    /**
     * Main webhook handler - handles both TOKEN and TRANSACTION types
     * Endpoint: POST /api/paymob/webhook
     */
    public function handlePaymobWebhook(Request $request): JsonResponse
    {
        try {
            // Log the incoming webhook
            PaymentLog::info('Paymob webhook received', [$request->all()], null, null, null, 'paymob_webhook_received');

            $webhookData = $request->all();
            $type = $webhookData['type'] ?? null;

            if (empty($webhookData) || !$type) {
                PaymentLog::warning('Empty or invalid webhook data received', [
                    'ip' => $request->ip(),
                    'has_type' => !empty($type)
                ], null, null, null, 'paymob_webhook_invalid');

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook data'
                ], 400);
            }

            // Log incoming webhook data
            PaymentLog::webhook($type, $webhookData);

            // Validate HMAC signature
            if (!$this->validateHmacSignature($request, $webhookData)) {
                // Validation failed but we continue (logged as warning)
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature'
                ], 401);
            }

            // Route to appropriate handler based on type
            return match($type) {
                'TRANSACTION' => $this->handleTransactionWebhook($webhookData),
                'TOKEN' => $this->handleTokenWebhook($webhookData),
                default => $this->handleUnknownWebhookType($type, $webhookData)
            };

        } catch (\Exception $e) {
            PaymentLog::error('Webhook processing exception', [
                'type' => $request->get('type'),
                'exception' => PaymentLog::formatException($e)
            ], null, null, null, 'paymob_webhook_exception');

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notification endpoint - standalone route for TRANSACTION webhooks
     * Endpoint: POST /api/paymob/notification
     */
    public function notification(Request $request): JsonResponse
    {
        return $this->handlePaymobWebhook($request);
    }

    /**
     * Tokenized callback endpoint - standalone route for TOKEN webhooks
     * Endpoint: POST /api/paymob/tokenized-callback
     */
    public function tokenizedCallback(Request $request): JsonResponse
    {
        return $this->handlePaymobWebhook($request);
    }


    /**
     * Validate HMAC signature according to Paymob/Accept specification
     *
     * HMAC calculation steps:
     * 1. Extract fields from obj in specific order
     * 2. Concatenate values in that order
     * 3. Calculate HMAC-SHA512 of the concatenated string
     * 4. Compare with received HMAC
     */
    private function validateHmacSignature(Request $request, array $webhookData): bool
    {
        $hmacSecret = config('services.paymob.hmac_secret');

        // Get HMAC signature from query parameter (as per Paymob spec)
        $hmacSignature = $request->get('hmac')
                      ?? $webhookData['hmac']
                      ?? null;

        // Skip validation if HMAC secret is not configured
        if (!$hmacSecret) {
            PaymentLog::warning('HMAC secret not configured, skipping signature validation', [
                'signature_provided' => !empty($hmacSignature)
            ], null, null, null, 'paymob_hmac_not_configured');
            return true; // Skip validation if not configured
        }

        // Skip validation if no HMAC signature provided
        if (!$hmacSignature) {
            PaymentLog::warning('HMAC signature not provided in webhook', [
                'type' => $webhookData['type'] ?? null
            ], null, null, null, 'paymob_hmac_missing');
            return true; // Allow request to continue (some webhooks might not have HMAC)
        }

        $type = $webhookData['type'] ?? null;
        $obj = $webhookData['obj'] ?? [];

        if (empty($obj)) {
            return true; // Skip validation if obj is empty
        }

        // Build HMAC string according to Paymob specification
        if ($type === 'TRANSACTION') {
            // TRANSACTION webhook HMAC calculation
            // Order: amount_cents, created_at, currency, error_occured, has_parent_transaction,
            // obj.id, integration_id, is_3d_secure, is_auth, is_capture, is_refunded,
            // is_standalone_payment, is_voided, order.id, owner, pending,
            // source_data.pan, source_data.sub_type, source_data.type, success
            $hmacString = '';
            $hmacString .= $obj['amount_cents'] ?? '';
            $hmacString .= $obj['created_at'] ?? '';
            $hmacString .= $obj['currency'] ?? '';
            $hmacString .= isset($obj['error_occured']) ? ($obj['error_occured'] ? 'true' : 'false') : '';
            $hmacString .= isset($obj['has_parent_transaction']) ? ($obj['has_parent_transaction'] ? 'true' : 'false') : '';
            $hmacString .= $obj['id'] ?? '';
            $hmacString .= $obj['integration_id'] ?? '';
            $hmacString .= isset($obj['is_3d_secure']) ? ($obj['is_3d_secure'] ? 'true' : 'false') : '';
            $hmacString .= isset($obj['is_auth']) ? ($obj['is_auth'] ? 'true' : 'false') : '';
            $hmacString .= isset($obj['is_capture']) ? ($obj['is_capture'] ? 'true' : 'false') : '';
            $hmacString .= isset($obj['is_refunded']) ? ($obj['is_refunded'] ? 'true' : 'false') : '';
            $hmacString .= isset($obj['is_standalone_payment']) ? ($obj['is_standalone_payment'] ? 'true' : 'false') : '';
            $hmacString .= isset($obj['is_voided']) ? ($obj['is_voided'] ? 'true' : 'false') : '';
            $hmacString .= $obj['order']['id'] ?? '';
            $hmacString .= $obj['owner'] ?? '';
            $hmacString .= isset($obj['pending']) ? ($obj['pending'] ? 'true' : 'false') : '';
            $hmacString .= $obj['source_data']['pan'] ?? '';
            $hmacString .= $obj['source_data']['sub_type'] ?? '';
            $hmacString .= $obj['source_data']['type'] ?? '';
            $hmacString .= isset($obj['success']) ? ($obj['success'] ? 'true' : 'false') : '';
        } elseif ($type === 'TOKEN') {
            // TOKEN webhook HMAC calculation
            // Order: card_subtype, created_at, email, id, masked_pan, merchant_id, order_id, token
            $hmacString = '';
            $hmacString .= $obj['card_subtype'] ?? '';
            $hmacString .= $obj['created_at'] ?? '';
            $hmacString .= $obj['email'] ?? '';
            $hmacString .= $obj['id'] ?? '';
            $hmacString .= $obj['masked_pan'] ?? '';
            $hmacString .= $obj['merchant_id'] ?? '';
            $hmacString .= $obj['order_id'] ?? '';
            $hmacString .= $obj['token'] ?? '';
        } else {
            // Unknown webhook type, skip validation
            return true;
        }

        // Calculate HMAC using SHA512 (as per Paymob specification)
        $calculatedHmac = hash_hmac('sha512', $hmacString, $hmacSecret);

        // Compare signatures (time-safe comparison)
        $isValid = hash_equals($calculatedHmac, $hmacSignature);

        if (!$isValid) {
            PaymentLog::error('Invalid HMAC signature', [
                'webhook_type' => $type,
                'signature_source' => $request->get('hmac') ? 'query' : 'body',
                'signature_preview' => substr($hmacSignature, 0, 20) . '...',
                'calculated_preview' => substr($calculatedHmac, 0, 20) . '...',
                'hmac_string_length' => strlen($hmacString),
                'transaction_id' => $obj['id'] ?? null,
                'token_id' => ($type === 'TOKEN') ? ($obj['id'] ?? null) : null,
                'action' => 'Will reject request'
            ], null, null, null, 'paymob_webhook_invalid_signature');
        } else {
            PaymentLog::info('HMAC signature validated successfully', [
                'webhook_type' => $type,
                'transaction_id' => ($type === 'TRANSACTION') ? ($obj['id'] ?? null) : null,
                'token_id' => ($type === 'TOKEN') ? ($obj['id'] ?? null) : null,
                'signature_length' => strlen($hmacSignature)
            ], null, null, null, 'paymob_webhook_signature_valid');
        }

        return $isValid;
    }

    /**
     * Handle TRANSACTION type webhook
     */
    private function handleTransactionWebhook(array $webhookData): JsonResponse
    {
        $obj = $webhookData['obj'] ?? [];

        // Extract transaction details
        $transactionId = $obj['id'] ?? null; /// this is transaction id in paymob and i dont recive it in intention request
        $orderId = $obj['order']['id'] ?? null; /// this is order id in paymob and i  recive it in intention request
        $merchantOrderId = $obj['order']['merchant_order_id'] ?? null; // this my cutom order id that i create in and send in intention request
        $intentionId = $obj['integration_id'] ?? null; // this is intention id in paymob and i recive it in intention request

        $profileId = $obj['profile_id'] ?? null; // this is profile id in for our account in paymob





        $success = $obj['success'] ?? false;
        $pending = $obj['pending'] ?? false;
        $isRefund = $obj['is_refund'] ?? false;
        $refunded = $obj['is_refunded'] ?? false;
        $amountCents = $obj['amount_cents'] ?? null;
        $paidAmountCents = $obj['order']['paid_amount_cents'] ?? 0;
        $currency = $obj['order']['currency'];
        $paymentStatus = $obj['order']['payment_status'] ?? null; // expected be = 'PAID'

        // Extract payment key claims
        $paymentKeyClaims = $obj['payment_key_claims'] ?? [];
        $userId = $paymentKeyClaims['user_id'] ?? $obj['owner'] ?? null;
        $extras = $paymentKeyClaims['extra'] ?? [];
        $dataSource = $obj['source_data'] ?? [];
        $subType = $dataSource['sub_type'] ?? null;
        $typeValue = $dataSource['type'] ?? null;
        $typeString = trim($subType . ' - ' . $typeValue);

        // Verify user exists
        $validatedUserId = $this->getValidatedUserId($userId);

        // PaymentLog::info('Processing transaction webhook', [
        //     'transaction_id' => $transactionId,
        //     'order_id' => $orderId,
        //     'merchant_order_id' => $merchantOrderId,
        //     'success' => $success,
        //     'pending' => $pending,
        //     'amount_cents' => $amountCents,
        //     'user_id' => $userId,
        //     'operation_type' => $extras['operation_type'] ?? null
        // ], $validatedUserId, null, null, 'paymob_transaction_processing');

            // Process the webhook
            $result = $this->webhookHandler->handleWebhook($webhookData);

            if ($result['success']) {
            PaymentLog::info('Transaction webhook processed successfully', [
                'transaction_id' => $transactionId,
                'merchant_order_id' => $merchantOrderId
            ], $validatedUserId, null, null, 'paymob_transaction_success');

            return response()->json([
                'success' => true,
                'message' => 'Transaction webhook processed successfully'
            ], 200);
        }

        PaymentLog::error('Transaction webhook processing failed', [
            'errors' => $result['errors'] ?? null,
            'details' => $result['details'] ?? null,
            'warnings' => $result['warnings'] ?? null,
            'transaction_id' => $transactionId
        ], $validatedUserId, null, null, 'paymob_transaction_failed');

        return response()->json([
            'success' => false,
            'errors' => $result['errors'] ?? null,
            'details' => $result['details'] ?? null,
            'warnings' => $result['warnings'] ?? null
        ], 400);
    }

    /**
     * Handle TOKEN type webhook
     */
    private function handleTokenWebhook(array $webhookData): JsonResponse
    {
        $obj = $webhookData['obj'] ?? [];

        // Extract tokenized card data
        $cardToken = $obj['token'] ?? null;
        $maskedPan = $obj['masked_pan'] ?? null;
        $cardBrand = $obj['card_subtype'] ?? null;
        $orderId = $obj['order_id'] ?? null;
        $merchantId = $obj['merchant_id'] ?? null;

        // Validate required fields
        if (!$cardToken) {
            PaymentLog::error('Card token missing in webhook', [
                'obj' => $obj
            ], null, null, null, 'paymob_token_missing');

            return response()->json([
                'success' => false,
                'message' => 'Card token is required'
            ], 400);
        }

        if (!$orderId) {
            PaymentLog::error('Order ID missing in token webhook', [
                'obj' => $obj
            ], null, null, null, 'paymob_token_missing_order_id');

            return response()->json([
                'success' => false,
                'message' => 'Order ID is required'
            ], 400);
        }

        // Find user by order_id
        $intention = $this->paymentRepository->findByOrderId($orderId);

        if (!$intention) {
            PaymentLog::error('Payment intention not found for token webhook', [
                'order_id' => $orderId
            ], null, null, null, 'paymob_token_intention_not_found');

            return response()->json([
                'success' => false,
                'message' => 'Payment intention not found for this order'
            ], 404);
        }

        $userId = $intention->user_id;

        // Store the tokenized card (prevent duplicates)
        $card = \App\Models\UserCard::getOrCreateCard([
            'user_id' => $userId,
            'card_token' => $cardToken,
            'masked_pan' => $maskedPan,
            'card_brand' => $cardBrand,
            'paymob_token_id' => $obj['id'] ?? null,
            'paymob_order_id' => $orderId,
            'paymob_merchant_id' => $merchantId,
        ]);

        PaymentLog::info('Tokenized card saved successfully', [
            'user_id' => $userId,
            'card_id' => $card->id,
            'masked_pan' => $maskedPan,
            'card_brand' => $cardBrand,
            'order_id' => $orderId,
            'is_new' => $card->wasRecentlyCreated,
            'is_default' => $card->is_default
        ], $userId, $intention->id, null, 'paymob_token_saved');

        return response()->json([
            'success' => true,
            'message' => 'Card token saved successfully'
        ], 200);
    }

    /**
     * Handle unknown webhook type
     */
    private function handleUnknownWebhookType(string $type, array $webhookData): JsonResponse
    {
        PaymentLog::warning('Unknown webhook type received', [
            'type' => $type,
            'webhook_data' => $webhookData
        ], null, null, null, 'paymob_webhook_unknown_type');

        return response()->json([
            'success' => false,
            'message' => 'Unknown webhook type: ' . $type
        ], 400);
    }

    /**
     * Get validated user ID (check if user exists)
     */
    private function getValidatedUserId(?int $userId): ?int
    {
        if (!$userId) {
            return null;
        }

        $userExists = \App\Models\User::where('id', $userId)->exists();
        return $userExists ? $userId : null;
    }

}
