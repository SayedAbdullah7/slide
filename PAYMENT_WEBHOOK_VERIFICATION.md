# Payment Webhook Verification Guide

## Overview

The `PaymobWebhookData::verify()` method provides comprehensive validation and security checks for incoming Paymob webhooks.

## Features

### 11 Comprehensive Checks

1. **HMAC Signature Validation** - Verifies webhook authenticity
2. **Required Fields** - Ensures transaction_id and order_id are present
3. **Transaction Type** - Validates webhook type is "TRANSACTION"
4. **Amount Validation** - Checks amount is positive and consistent
5. **Currency Validation** - Ensures currency is SAR
6. **Status Coherence** - Validates success/pending/paid consistency
7. **Refund Validation** - Checks refund transaction logic
8. **Integration & Profile IDs** - Validates Paymob configuration
9. **Payment Method** - Ensures payment method is present
10. **User ID** - Validates user identification
11. **Operation Type** - Checks custom transaction type field

## Usage

### Basic Verification

```php
use App\DataTransferObjects\PaymobWebhookData;

$webhook = new PaymobWebhookData($requestData);

// Verify without HMAC (skip signature validation)
$result = $webhook->verify();

// Verify with HMAC (recommended for production)
$hmacSecret = config('services.paymob.hmac_secret');
$result = $webhook->verify($hmacSecret);
```

### Response Format

```php
[
    'valid' => true,           // bool: Overall validation result
    'errors' => [],            // array: Critical issues (blocks processing)
    'warnings' => [],          // array: Non-critical issues
    'summary' => 'Webhook verified successfully: PaymobWebhook[...]'
]
```

### In PaymentWebhookService

```php
public function handleWebhook(array $data): array
{
    $webhook = new PaymobWebhookData($data);
    $hmacSecret = config('services.paymob.hmac_secret');
    
    // Verify webhook
    $verification = $webhook->verify($hmacSecret);
    
    if (!$verification['valid']) {
        // Log and reject
        PaymentLog::error('Webhook verification failed', [
            'errors' => $verification['errors'],
            'warnings' => $verification['warnings']
        ]);
        
        return [
            'success' => false,
            'message' => 'Webhook verification failed',
            'errors' => $verification['errors']
        ];
    }
    
    // Process webhook...
}
```

## Verification Levels

### Errors (Block Processing)
- HMAC verification failed
- Missing transaction ID or order ID
- Invalid amount (≤ 0)
- Missing currency
- Invalid state (success + pending simultaneously)

### Warnings (Allow Processing)
- HMAC not provided
- Unexpected webhook type
- Amount mismatch (amount_cents vs paid_amount_cents)
- Currency not SAR
- Status inconsistency (success but not PAID)
- Missing integration_id or profile_id
- Missing payment method
- Missing user_id
- Missing operation_type

## HMAC Calculation

Paymob uses SHA512 HMAC with specific field concatenation:

```php
amount_cents + created_at + currency + error_occured + 
has_parent_transaction + id + integration_id + is_3d_secure + 
is_auth + is_capture + is_refunded + is_standalone_payment + 
is_voided + order.id + owner + pending + source_data.pan + 
source_data.sub_type + source_data.type + success
```

The method `calculateHmac()` handles this automatically.

## Example Scenarios

### ✅ Valid Webhook
```php
$result = $webhook->verify($hmacSecret);
// ['valid' => true, 'errors' => [], 'warnings' => [], 'summary' => 'Webhook verified...']
```

### ⚠️ Valid with Warnings
```php
$result = $webhook->verify();
// ['valid' => true, 'errors' => [], 'warnings' => ['HMAC not provided'], ...]
```

### ❌ Invalid Webhook
```php
$result = $webhook->verify($hmacSecret);
// ['valid' => false, 'errors' => ['HMAC verification failed', 'Invalid amount'], ...]
```

## Security Best Practices

1. **Always use HMAC validation in production**
   ```php
   $result = $webhook->verify(config('services.paymob.hmac_secret'));
   ```

2. **Log all verification failures**
   ```php
   if (!$verification['valid']) {
       PaymentLog::error('Verification failed', $verification);
   }
   ```

3. **Monitor warnings** - High warning frequency may indicate issues
   ```php
   if (!empty($verification['warnings'])) {
       PaymentLog::warning('Webhook has warnings', $verification['warnings']);
   }
   ```

4. **Don't process invalid webhooks**
   ```php
   if (!$verification['valid']) {
       return ['success' => false, 'errors' => $verification['errors']];
   }
   ```

## Configuration

Add HMAC secret to `config/services.php`:

```php
'paymob' => [
    'api_key' => env('PAYMOB_API_KEY'),
    'integration_id' => env('PAYMOB_INTEGRATION_ID'),
    'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
],
```

And `.env`:

```
PAYMOB_HMAC_SECRET=your_hmac_secret_from_paymob_dashboard
```

## Testing

```bash
# Test with good data
php artisan tinker
>>> $webhook = new App\DataTransferObjects\PaymobWebhookData($goodData);
>>> $result = $webhook->verify();
>>> dd($result);

# Test with bad data
>>> $webhook = new App\DataTransferObjects\PaymobWebhookData($badData);
>>> $result = $webhook->verify();
>>> dd($result);
```

## Troubleshooting

### HMAC Verification Always Fails
- Check HMAC secret in config
- Ensure all required fields are present in webhook
- Verify field types match Paymob's format (boolean as string 'true'/'false')

### Too Many Warnings
- Review Paymob integration configuration
- Ensure payment_key_claims includes required extra data
- Check that all expected fields are sent in intention request

### Missing Fields
- Verify Paymob integration is configured correctly
- Check that billing_data and items are sent in intention
- Ensure payment_key_claims includes user_id and operation_type

## Support

For issues or questions about webhook verification:
1. Check PaymentLog for detailed error messages
2. Review Paymob webhook logs in dashboard
3. Verify HMAC secret matches dashboard configuration

