# Paymob Payment Gateway Integration

This document provides comprehensive documentation for the Paymob payment gateway integration built for the Laravel application.

## Overview

The Paymob integration provides a complete payment solution with support for:
- Payment intentions creation
- Checkout URL generation
- MOTO (Mail Order/Telephone Order) payments
- Payment capture, void, and refund operations
- Webhook handling for payment callbacks
- Transaction management and reporting

## Configuration

### Environment Variables

Add the following environment variables to your `.env` file:

```env
# Paymob Configuration
PAYMOB_API_KEY=your_api_key_here
PAYMOB_SECRET_KEY=your_secret_key_here
PAYMOB_PUBLIC_KEY=your_public_key_here
PAYMOB_INTEGRATION_ID=your_integration_id_here
PAYMOB_BASE_URL=https://ksa.paymob.com
PAYMOB_WEBHOOK_URL=https://yourdomain.com/api/payments/webhooks/paymob
PAYMOB_REDIRECT_URL=https://yourdomain.com/api/payments/webhooks/success
```

### Service Configuration

The Paymob service is configured in `config/services.php`:

```php
'paymob' => [
    'api_key' => env('PAYMOB_API_KEY'),
    'secret_key' => env('PAYMOB_SECRET_KEY'),
    'public_key' => env('PAYMOB_PUBLIC_KEY'),
    'integration_id' => env('PAYMOB_INTEGRATION_ID'),
    'base_url' => env('PAYMOB_BASE_URL', 'https://ksa.paymob.com'),
    'webhook_url' => env('PAYMOB_WEBHOOK_URL'),
    'redirect_url' => env('PAYMOB_REDIRECT_URL'),
],
```

## Database Setup

Run the migrations to create the required tables:

```bash
php artisan migrate
```

This will create:
- `payment_intentions` table
- `payment_transactions` table

## API Endpoints

### Payment Intentions

#### Create Payment Intention
```http
POST /api/payments/intentions
Authorization: Bearer {token}
Content-Type: application/json

{
    "amount_cents": 10000,
    "currency": "SAR",
    "payment_methods": [123456],
    "items": [
        {
            "name": "Investment Opportunity",
            "amount": 10000,
            "description": "Investment in XYZ project",
            "quantity": 1
        }
    ],
    "billing_data": {
        "first_name": "John",
        "last_name": "Doe",
        "phone_number": "+966501234567",
        "email": "john.doe@example.com",
        "apartment": "Apt 1",
        "street": "Main Street",
        "building": "Building 1",
        "city": "Riyadh",
        "country": "Saudi Arabia",
        "floor": "1",
        "state": "Riyadh"
    },
    "special_reference": "INV-2024-001",
    "extras": {
        "custom_field": "value"
    }
}
```

#### Get Payment Intentions
```http
GET /api/payments/intentions?status=active&per_page=10
Authorization: Bearer {token}
```

#### Get Checkout URL
```http
GET /api/payments/intentions/{intentionId}/checkout-url
Authorization: Bearer {token}
```

### Payment Transactions

#### Process MOTO Payment
```http
POST /api/payments/moto
Authorization: Bearer {token}
Content-Type: application/json

{
    "payment_intention_id": 1,
    "card_token": "card_token_here",
    "payment_token": "payment_token_here"
}
```

#### Capture Payment
```http
POST /api/payments/capture
Authorization: Bearer {token}
Content-Type: application/json

{
    "transaction_id": "transaction_id_here",
    "amount_cents": 10000
}
```

#### Void Payment
```http
POST /api/payments/void
Authorization: Bearer {token}
Content-Type: application/json

{
    "transaction_id": "transaction_id_here"
}
```

#### Refund Payment
```http
POST /api/payments/refund
Authorization: Bearer {token}
Content-Type: application/json

{
    "transaction_id": "transaction_id_here",
    "amount_cents": 5000
}
```

#### Get Transactions
```http
GET /api/payments/transactions?status=successful&payment_method=moto&per_page=10
Authorization: Bearer {token}
```

#### Get Payment Statistics
```http
GET /api/payments/stats
Authorization: Bearer {token}
```

### Webhook Endpoints

#### Paymob Webhook
```http
POST /api/payments/webhooks/paymob
Content-Type: application/json

{
    "obj": {
        "id": "transaction_id",
        "success": true,
        "merchant_order_id": "order_123"
    }
}
```

#### Payment Success Redirect
```http
GET /api/payments/webhooks/success?transaction_id=123&merchant_order_id=order_123&success=true
```

#### Payment Failure Redirect
```http
GET /api/payments/webhooks/failure?transaction_id=123&merchant_order_id=order_123&error=payment_failed
```

## Usage Examples

### Basic Payment Flow

```php
use App\Services\PaymobService;
use App\Models\PaymentIntention;

// Create payment intention
$paymobService = new PaymobService();

$intentionData = [
    'user_id' => auth()->id(),
    'amount_cents' => 10000, // 100 SAR
    'currency' => 'SAR',
    'billing_data' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '+966501234567',
        'email' => 'john.doe@example.com',
    ],
    'items' => [
        [
            'name' => 'Investment',
            'amount' => 10000,
            'description' => 'Investment in project',
            'quantity' => 1
        ]
    ],
    'special_reference' => 'INV-' . time()
];

$result = $paymobService->createIntention($intentionData);

if ($result['success']) {
    $intention = $result['intention'];
    
    // Get checkout URL
    $checkoutResult = $paymobService->getCheckoutUrl($intention->client_secret);
    
    if ($checkoutResult['success']) {
        // Redirect user to checkout URL
        return redirect($checkoutResult['checkout_url']);
    }
}
```

### MOTO Payment

```php
// Process MOTO payment
$motoData = [
    'payment_intention_id' => $intention->id,
    'user_id' => auth()->id(),
    'card_token' => 'saved_card_token',
    'payment_token' => $intention->payment_token
];

$result = $paymobService->processMotoPayment($motoData);

if ($result['success']) {
    // Payment processed successfully
    $transaction = $result['transaction'];
}
```

### Payment Management

```php
// Get user's payment intentions
$intentions = PaymentIntention::where('user_id', auth()->id())
    ->with(['transactions'])
    ->orderBy('created_at', 'desc')
    ->paginate(15);

// Get payment statistics
$stats = [
    'total_intentions' => PaymentIntention::where('user_id', auth()->id())->count(),
    'successful_intentions' => PaymentIntention::where('user_id', auth()->id())
        ->where('status', 'completed')->count(),
    'total_transactions' => PaymentTransaction::where('user_id', auth()->id())->count(),
    'successful_transactions' => PaymentTransaction::where('user_id', auth()->id())
        ->where('status', 'successful')->count(),
];
```

## Models

### PaymentIntention Model

The `PaymentIntention` model represents a payment intention with the following key attributes:

- `user_id`: The user who created the intention
- `amount_cents`: Amount in cents (e.g., 10000 = 100 SAR)
- `currency`: Currency code (default: SAR)
- `client_secret`: Paymob client secret for checkout
- `payment_token`: Payment token for MOTO payments
- `status`: Intention status (created, active, completed, failed, expired)
- `billing_data`: Customer billing information
- `items`: Array of items being purchased
- `special_reference`: Custom reference for tracking
- `expires_at`: Expiration timestamp

### PaymentTransaction Model

The `PaymentTransaction` model represents individual payment transactions:

- `payment_intention_id`: Related payment intention
- `user_id`: User who made the payment
- `transaction_id`: Paymob transaction ID
- `amount_cents`: Transaction amount in cents
- `status`: Transaction status (pending, successful, failed, captured, voided, refunded)
- `payment_method`: Payment method used
- `card_token`: Saved card token (for MOTO)
- `merchant_order_id`: Merchant order reference
- `paymob_response`: Full Paymob API response
- `processed_at`: When the transaction was processed
- `refunded_at`: When the transaction was refunded
- `refund_amount_cents`: Amount refunded in cents

## Error Handling

The service includes comprehensive error handling:

```php
$result = $paymobService->createIntention($data);

if (!$result['success']) {
    // Handle error
    $error = $result['error'];
    $details = $result['details'] ?? null;
    
    // Log error or show to user
    Log::error('Payment intention creation failed', [
        'error' => $error,
        'details' => $details,
        'user_id' => auth()->id()
    ]);
}
```

## Webhook Security

For production, implement webhook signature validation:

```php
// In PaymentWebhookController
public function handleWebhook(Request $request): JsonResponse
{
    // Validate webhook signature
    $signature = $request->header('X-Paymob-Signature');
    $payload = $request->getContent();
    
    if (!$this->paymobService->validateWebhookSignature($signature, $payload)) {
        return response()->json(['error' => 'Invalid signature'], 401);
    }
    
    // Process webhook...
}
```

## Testing

### Test Payment Flow

1. Create a payment intention
2. Get the checkout URL
3. Use Paymob's test environment to complete payment
4. Verify webhook is received
5. Check transaction status in database

### Test MOTO Payment

1. Create payment intention
2. Use test card token
3. Process MOTO payment
4. Verify transaction is created

## Production Considerations

1. **Security**: Implement proper webhook signature validation
2. **Logging**: Enable comprehensive logging for debugging
3. **Monitoring**: Set up monitoring for failed payments
4. **Retry Logic**: Implement retry logic for failed API calls
5. **Rate Limiting**: Implement rate limiting for API endpoints
6. **Data Retention**: Implement data retention policies for old transactions

## Support

For issues or questions regarding the Paymob integration:

1. Check the logs in `storage/logs/laravel.log`
2. Verify environment configuration
3. Test with Paymob's sandbox environment
4. Contact Paymob support for API-related issues

## Changelog

- **v1.0.0**: Initial implementation with basic payment flow
- Support for payment intentions, MOTO payments, and webhooks
- Database models and migrations
- Comprehensive API endpoints
- Error handling and logging


