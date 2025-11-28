# Paymob KSA Unified Intention API - Webhooks & Callbacks Documentation

## Overview

This document details the implementation of Paymob KSA's Unified Intention API webhooks and callbacks for our Laravel/Lumen backend. The implementation includes three main endpoints for handling payment notifications, redirections, and tokenized card callbacks.

---

## 📋 Table of Contents

1. [Endpoints Overview](#endpoints-overview)
2. [Endpoint Details](#endpoint-details)
3. [Configuration](#configuration)
4. [Security](#security)
5. [Testing](#testing)
6. [Mobile App Integration](#mobile-app-integration)
7. [Troubleshooting](#troubleshooting)

---

## Endpoints Overview

### 1. Notification Webhook
**Endpoint:** `POST /api/paymob/notification`  
**Purpose:** Receives asynchronous transaction status updates from Paymob  
**Authentication:** Public (validates HMAC signature if configured)

### 2. Redirection URL
**Endpoint:** `GET /api/paymob/redirection`  
**Purpose:** Handles user redirect after checkout completion  
**Authentication:** Public

### 3. Tokenized Callback
**Endpoint:** `POST /api/paymob/tokenized-callback`  
**Purpose:** Handles tokenized card data for Save Card feature  
**Authentication:** Public

---

## Endpoint Details

### 1. Notification Webhook

#### Endpoint
```
POST /api/paymob/notification
```

#### Purpose
Receives asynchronous transaction status updates from Paymob. This is the primary webhook that Paymob calls when a transaction status changes (successful, failed, pending).

#### Request Headers
```http
Content-Type: application/json
X-Paymob-Signature: <HMAC-SHA256 signature> (optional)
```

#### Request Body Example
```json
{
  "obj": {
    "id": "12345678",
    "success": true,
    "pending": false,
    "order": {
      "id": "87654321",
      "merchant_order_id": "INV-123-456-789"
    },
    "amount_cents": 10000,
    "currency": "SAR"
  }
}
```

#### Response
```json
{
  "success": true,
  "message": "Webhook processed successfully"
}
```

#### Workflow
1. **Receive webhook data** from Paymob
2. **Validate HMAC signature** (if configured)
3. **Extract transaction details** (transaction_id, order_id, merchant_order_id, status)
4. **Find transaction** in database
5. **Update transaction status** based on webhook data
6. **Update payment intention** status
7. **Log all actions** to database
8. **Return HTTP 200** to confirm receipt

#### Code Example
```php
// In PaymentWebhookController.php
public function notification(Request $request): JsonResponse
{
    // Log incoming notification
    PaymentLog::info('Paymob notification webhook received', [
        'body' => $request->all(),
        'ip' => $request->ip(),
    ], null, null, null, 'paymob_notification_received');

    // Validate HMAC signature
    if ($hmacSecret && $request->header('X-Paymob-Signature')) {
        $isValid = $this->paymobService->validateWebhookSignature(
            $request->header('X-Paymob-Signature'),
            $request->all()
        );
        // ... handle invalid signature
    }

    // Process webhook
    $result = $this->paymobService->handleWebhook($request->all());
    
    return response()->json(['success' => true], 200);
}
```

---

### 2. Redirection URL

#### Endpoint
```
GET /api/paymob/redirection
```

#### Purpose
Handles user redirection after completing the checkout process. The user is redirected here from Paymob's checkout page, whether payment was successful or failed.

#### Query Parameters
```
?order_id=123
&merchant_order_id=INV-123-456-789
&transaction_id=12345678
&success=true/false
&pending=false
&error=<error_message> (if failed)
```

#### Response (Success)
```json
{
  "success": true,
  "message": "Payment successful",
  "transaction": {
    "id": 1,
    "transaction_id": "12345678",
    "status": "successful",
    "amount": 100.00
  },
  "redirect_url": "https://yourapp.com/payment/success?transaction_id=1",
  "deep_link": "myapp://payment/success?transaction_id=1"
}
```

#### Response (Failure)
```json
{
  "success": false,
  "message": "Payment failed",
  "transaction": {
    "id": 1,
    "status": "failed"
  },
  "redirect_url": "https://yourapp.com/payment/failed?transaction_id=1",
  "deep_link": "myapp://payment/failed?transaction_id=1"
}
```

#### Response (Pending)
```json
{
  "success": false,
  "message": "Payment pending",
  "transaction": {
    "id": 1,
    "status": "pending"
  },
  "redirect_url": "https://yourapp.com/payment/pending?transaction_id=1",
  "deep_link": "myapp://payment/pending?transaction_id=1"
}
```

#### Workflow
1. **Extract query parameters** from URL
2. **Find transaction** by merchant_order_id or transaction_id
3. **Update transaction status** based on success flag
4. **Update payment intention** status
5. **Log the action** to database
6. **Return JSON response** with redirect URLs for web and mobile

#### Mobile App Deep Links
The response includes deep links for mobile apps:
- Success: `myapp://payment/success?transaction_id={id}`
- Failure: `myapp://payment/failed?transaction_id={id}`
- Pending: `myapp://payment/pending?transaction_id={id}`

Replace `myapp://` with your actual app's URL scheme.

---

### 3. Tokenized Callback

#### Endpoint
```
POST /api/paymob/tokenized-callback
```

#### Purpose
Handles tokenized card information callback when users opt to save their card details for future payments (Save Card feature).

#### Request Body Example
```json
{
  "user_id": 123,
  "card_token": "tok_abc123xyz",
  "masked_pan": "XXXX-XXXX-XXXX-1234",
  "card_brand": "Visa",
  "card_holder_name": "John Doe",
  "expiry_month": "12",
  "expiry_year": "2025"
}
```

#### Response
```json
{
  "success": true,
  "message": "Card token saved successfully"
}
```

#### Security Notes
⚠️ **NEVER log sensitive card data:**
- Never log `card_number`
- Never log `cvv`
- Never log `pin`
- Only log `masked_pan` and `card_token`

#### Workflow
1. **Receive tokenized card data**
2. **Validate required fields** (card_token, user_id)
3. **Store card token** in user's wallet/cards table
4. **Log action** (without sensitive data)
5. **Return HTTP 200** confirmation

#### Implementation Example
```php
// TODO: Store the tokenized card in your cards/wallet table
$card = Card::create([
    'user_id' => $userId,
    'card_token' => $cardToken,
    'masked_pan' => $maskedPan,
    'card_brand' => $cardBrand,
    'card_holder_name' => $cardHolderName,
    'expiry_month' => $expiryMonth,
    'expiry_year' => $expiryYear,
    'is_default' => false,
]);
```

---

## Configuration

### Environment Variables

Add the following to your `.env` file:

```env
# Paymob KSA Configuration
PAYMOB_API_KEY=your_api_key_here
PAYMOB_SECRET_KEY=your_secret_key_here
PAYMOB_PUBLIC_KEY=your_public_key_here
PAYMOB_INTEGRATION_ID=your_integration_id_here
PAYMOB_HMAC_SECRET=your_hmac_secret_here
PAYMOB_BASE_URL=https://ksa.paymob.com

# Webhook URLs (automatically configured)
PAYMOB_WEBHOOK_URL=https://yourapp.com/api/paymob/notification
PAYMOB_REDIRECT_URL=https://yourapp.com/api/paymob/redirection

# Frontend URLs for mobile/web redirects
APP_FRONTEND_URL=https://yourapp.com
```

### Config File (`config/services.php`)

```php
'paymob' => [
    'api_key' => env('PAYMOB_API_KEY'),
    'secret_key' => env('PAYMOB_SECRET_KEY'),
    'public_key' => env('PAYMOB_PUBLIC_KEY'),
    'integration_id' => env('PAYMOB_INTEGRATION_ID'),
    'hmac_secret' => env('PAYMOB_HMAC_SECRET', null),
    'base_url' => env('PAYMOB_BASE_URL', 'https://ksa.paymob.com'),
    'webhook_url' => env('PAYMOB_WEBHOOK_URL', config('app.url') . '/api/paymob/notification'),
    'redirect_url' => env('PAYMOB_REDIRECT_URL', config('app.url') . '/api/paymob/redirection'),
],
```

### Paymob Dashboard Configuration

1. **Login** to Paymob Dashboard
2. Navigate to **Settings** → **Webhooks**
3. Add the following URLs:
   - **Notification URL**: `https://yourapp.com/api/paymob/notification`
   - **Redirection URL**: `https://yourapp.com/api/paymob/redirection`
   - **Tokenized Callback URL**: `https://yourapp.com/api/paymob/tokenized-callback`
4. **Copy HMAC Secret** and add to `.env` as `PAYMOB_HMAC_SECRET`

---

## Security

### HMAC Signature Validation

The notification webhook validates HMAC signatures to ensure requests come from Paymob:

```php
// In PaymobService.php
public function validateWebhookSignature(string $signature, array $data): bool
{
    $hmacSecret = config('services.paymob.hmac_secret');
    
    if (!$hmacSecret) {
        return true; // Skip validation if not configured
    }

    $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $expectedSignature = hash_hmac('sha256', $payload, $hmacSecret);

    return hash_equals($expectedSignature, $signature);
}
```

### Security Best Practices

1. **Always validate HMAC signatures** in production
2. **Never log sensitive data** (card numbers, CVV, etc.)
3. **Use HTTPS** for all webhook URLs
4. **Verify transaction amounts** before processing
5. **Implement idempotency** to handle duplicate webhooks
6. **Rate limit** webhook endpoints
7. **Log all webhook attempts** for auditing

---

## Testing

### Testing Notification Webhook

```bash
curl -X POST https://yourapp.com/api/paymob/notification \
  -H "Content-Type: application/json" \
  -H "X-Paymob-Signature: <hmac_signature>" \
  -d '{
    "obj": {
      "id": "12345678",
      "success": true,
      "pending": false,
      "order": {
        "id": "87654321",
        "merchant_order_id": "INV-123-456-789"
      },
      "amount_cents": 10000,
      "currency": "SAR"
    }
  }'
```

### Testing Redirection URL

```bash
curl -X GET "https://yourapp.com/api/paymob/redirection?order_id=123&merchant_order_id=INV-123&transaction_id=12345&success=true"
```

### Testing Tokenized Callback

```bash
curl -X POST https://yourapp.com/api/paymob/tokenized-callback \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 123,
    "card_token": "tok_abc123xyz",
    "masked_pan": "XXXX-XXXX-XXXX-1234",
    "card_brand": "Visa",
    "card_holder_name": "John Doe",
    "expiry_month": "12",
    "expiry_year": "2025"
  }'
```

### Postman Collection

Import the included Postman collection `PAYMOB_API_POSTMAN_COLLECTION.json` for comprehensive API testing.

---

## Mobile App Integration

### Deep Link Setup

#### iOS (Swift)
```swift
// Handle deep links in AppDelegate or SceneDelegate
func application(_ app: UIApplication, open url: URL, options: [UIApplication.OpenURLOptionsKey : Any] = [:]) -> Bool {
    if url.scheme == "myapp" {
        if url.host == "payment" {
            if url.path == "/success" {
                // Handle successful payment
                let transactionId = url.queryParameters["transaction_id"]
                navigateToPaymentSuccess(transactionId: transactionId)
            } else if url.path == "/failed" {
                // Handle failed payment
                navigateToPaymentFailed()
            }
        }
    }
    return true
}
```

#### Android (Kotlin)
```kotlin
// Handle deep links in MainActivity
override fun onCreate(savedInstanceState: Bundle?) {
    super.onCreate(savedInstanceState)
    handleIntent(intent)
}

override fun onNewIntent(intent: Intent?) {
    super.onNewIntent(intent)
    handleIntent(intent)
}

private fun handleIntent(intent: Intent?) {
    val data: Uri? = intent?.data
    if (data != null && data.scheme == "myapp") {
        when (data.host) {
            "payment" -> {
                when (data.path) {
                    "/success" -> {
                        val transactionId = data.getQueryParameter("transaction_id")
                        navigateToPaymentSuccess(transactionId)
                    }
                    "/failed" -> navigateToPaymentFailed()
                    "/pending" -> navigateToPaymentPending()
                }
            }
        }
    }
}
```

### Handling Redirections in Mobile Apps

When a mobile app user completes payment in Paymob's checkout:

1. Paymob redirects to: `https://yourapp.com/api/paymob/redirection?...`
2. Your backend processes the payment
3. Backend returns JSON with `deep_link` field
4. Mobile app intercepts the URL or polls for transaction status
5. App opens the deep link to navigate to appropriate screen

#### Alternative: Polling Approach

```javascript
// React Native / JavaScript
const pollTransactionStatus = async (transactionId) => {
  const maxAttempts = 30;
  const interval = 2000; // 2 seconds
  
  for (let i = 0; i < maxAttempts; i++) {
    try {
      const response = await fetch(`/api/payments/transactions/${transactionId}`);
      const data = await response.json();
      
      if (data.status === 'successful') {
        navigation.navigate('PaymentSuccess', { transaction: data });
        return;
      } else if (data.status === 'failed') {
        navigation.navigate('PaymentFailed', { transaction: data });
        return;
      }
      
      await new Promise(resolve => setTimeout(resolve, interval));
    } catch (error) {
      console.error('Error polling transaction:', error);
    }
  }
  
  // Timeout - payment still pending
  navigation.navigate('PaymentPending', { transactionId });
};
```

---

## Troubleshooting

### Common Issues

#### 1. Webhook Not Receiving Data

**Problem:** Notification webhook endpoint is not receiving calls from Paymob

**Solutions:**
- Verify webhook URL is publicly accessible (not localhost)
- Check firewall rules allow incoming POST requests
- Verify SSL certificate is valid
- Check Paymob dashboard webhook configuration
- Review server logs for rejected requests

#### 2. HMAC Signature Validation Failing

**Problem:** Webhook signature validation always fails

**Solutions:**
- Verify `PAYMOB_HMAC_SECRET` is correctly set in `.env`
- Check signature header name is exactly `X-Paymob-Signature`
- Verify payload encoding matches Paymob's format
- Temporarily disable validation for testing (not in production!)

```php
// Temporarily disable for testing
if (!$hmacSecret || env('APP_ENV') === 'local') {
    return true; // Skip validation in local environment
}
```

#### 3. Transaction Not Found

**Problem:** Webhook can't find transaction in database

**Solutions:**
- Verify `merchant_order_id` matches your `special_reference`
- Check transaction was created before webhook received
- Look for timing issues (webhook arrives before DB commit)
- Add retry logic for not-found transactions

#### 4. Redirection Not Working on Mobile

**Problem:** Mobile app doesn't handle redirect properly

**Solutions:**
- Verify deep link URL scheme is registered in app
- Test deep links using tools like `adb shell am start -a android.intent.action.VIEW -d "myapp://payment/success"`
- Implement polling as fallback mechanism
- Check app handles both web URLs and deep links

### Debugging

#### Enable Debug Logging

```php
// In PaymentWebhookController.php
PaymentLog::debug('Full webhook payload', [
    'headers' => $request->headers->all(),
    'body' => $request->all(),
    'raw' => $request->getContent()
], null, null, null, 'paymob_debug');
```

#### Check Logs

```bash
# View payment logs
tail -f storage/logs/laravel.log | grep "PaymentLog:"

# Filter by action
grep "paymob_notification" storage/logs/laravel.log

# View database logs
php artisan tinker
>>> PaymentLog::where('action', 'paymob_notification_received')->latest()->get();
```

#### Test Webhook Locally

Use tools like **ngrok** to expose your local server:

```bash
# Install ngrok
npm install -g ngrok

# Expose port 8000
ngrok http 8000

# Use the ngrok URL in Paymob dashboard
# Example: https://abc123.ngrok.io/api/paymob/notification
```

---

## Legacy Endpoints

For backward compatibility, the following legacy endpoints are maintained:

- `POST /api/payments/webhooks/paymob` → redirects to `notification`
- `GET /api/payments/webhooks/success` → redirects to `handleSuccess`
- `GET /api/payments/webhooks/failure` → redirects to `handleFailure`

---

## API Routes Summary

| Method | Endpoint | Purpose | Auth |
|--------|----------|---------|------|
| POST | `/api/paymob/notification` | Notification webhook | Public (HMAC) |
| GET | `/api/paymob/redirection` | User redirect | Public |
| POST | `/api/paymob/tokenized-callback` | Save card | Public |
| POST | `/api/payments/webhooks/paymob` | Legacy notification | Public |
| GET | `/api/payments/webhooks/success` | Legacy success | Public |
| GET | `/api/payments/webhooks/failure` | Legacy failure | Public |

---

## Support

For Paymob-specific issues:
- **Documentation**: https://docs.paymob.com/
- **Support**: support@paymob.com
- **Dashboard**: https://ksa.paymob.com/

For implementation issues:
- Check `payment_logs` table in database
- Review Laravel logs in `storage/logs/`
- Enable debug mode in development environment

---

## Version History

- **v1.0.0** (2025-10-12): Initial implementation with notification, redirection, and tokenized callback endpoints





