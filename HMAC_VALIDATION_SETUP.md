# HMAC Signature Validation Setup Guide

## 🔴 Current Issue

HMAC signature validation is **failing** because the HMAC secret in configuration doesn't match the one Paymob is using.

### Evidence:
```
Received HMAC from Paymob: 2c89c91fad5cb95b6f399536284155339b931e42998123ee59e967ebcb4e8f0f7f81aa93ffab06d372e4b67b05c04e29f965cd3be8ef94fbe77158daf4440eb3
Calculated HMAC (SHA-512): b7e2f0ac8c52e716413423bc38e466cc05678468c8bd1c0de2bebb2aaa67249b4f2448c1b26a1db9ce3f504d7b13a482fca22b0d4f07aad51f730a07826bc130
Calculated HMAC (SHA-256): 5e63a6d58d643868f472428ed8c0f3d3703cc0a420034b215dd921eb4eb89388

❌ NO MATCH - Wrong HMAC secret!
```

---

## 🔧 Solutions

### Option 1: Disable HMAC Validation (Temporary - Not Recommended)

For testing purposes only, you can disable HMAC validation:

**In `.env`:**
```env
# Temporarily disable HMAC validation
PAYMOB_HMAC_SECRET=
```

**Or in `config/services.php`:**
```php
'hmac_secret' => env('PAYMOB_HMAC_SECRET', null), // null = skip validation
```

⚠️ **Warning:** This is NOT secure for production! Only use for testing.

---

### Option 2: Get Correct HMAC Secret from Paymob (Recommended)

#### Steps to Get HMAC Secret:

1. **Login to Paymob Dashboard**
   - URL: https://ksa.paymob.com/
   - Use your merchant credentials

2. **Navigate to Webhooks Settings**
   - Go to: Settings → Webhooks
   - Or: Developers → Webhooks

3. **Find HMAC Secret**
   - Look for "HMAC Secret" or "Webhook Secret"
   - Copy the exact value
   - It should be a 32-character hexadecimal string

4. **Update `.env` File**
   ```env
   PAYMOB_HMAC_SECRET=YOUR_ACTUAL_HMAC_SECRET_HERE
   ```

5. **Clear Config Cache**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

### Option 3: Contact Paymob Support

If you can't find the HMAC secret in the dashboard:

**Contact Details:**
- Email: support@paymob.com
- Subject: "HMAC Secret for Webhooks - Merchant ID 11883"
- Request: "Please provide the HMAC secret for webhook signature validation"

---

## 🧪 Testing HMAC Validation

### Quick Test Script

Create a test file `test_hmac.php`:

```php
<?php

$payload = [
    "type" => "TOKEN",
    "obj" => [
        "id" => 27911,
        "token" => "5fe444640033d1c5696ac76f2360af7f2c38f6c72fd18c0f5c644ac0",
        "masked_pan" => "xxxx-xxxx-xxxx-0008",
        "merchant_id" => 11883,
        "card_subtype" => "MasterCard",
        "created_at" => "2025-10-14T22:46:57.977092+03:00",
        "email" => "sayed@gmail.com",
        "order_id" => "1037965",
        "user_added" => false,
        "next_payment_intention" => "pi_test_4c022580ecca4f1f9ae38f6d9778c835",
    ]
];

$receivedHmac = "2c89c91fad5cb95b6f399536284155339b931e42998123ee59e967ebcb4e8f0f7f81aa93ffab06d372e4b67b05c04e29f965cd3be8ef94fbe77158daf4440eb3";

// Try your HMAC secret here
$hmacSecret = "YOUR_HMAC_SECRET_HERE";

$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$calculated = hash_hmac('sha512', $json, $hmacSecret);

if (hash_equals($calculated, $receivedHmac)) {
    echo "✅ SUCCESS! HMAC secret is correct!\n";
} else {
    echo "❌ FAILED! HMAC secret is incorrect.\n";
    echo "Received:   $receivedHmac\n";
    echo "Calculated: $calculated\n";
}
```

Run:
```bash
php test_hmac.php
```

---

## 🔍 Debugging HMAC Issues

### Check Current Configuration

```bash
php artisan tinker
```

```php
config('services.paymob.hmac_secret')
```

### View Webhook Logs

```sql
SELECT * FROM payment_logs 
WHERE action LIKE '%hmac%' 
OR action LIKE '%signature%'
ORDER BY id DESC 
LIMIT 10;
```

---

## 📊 HMAC Validation Flow

```
Paymob sends webhook with HMAC
         ↓
Extract HMAC from:
  1. X-Paymob-Signature header
  2. ?hmac= query parameter
  3. body['hmac'] field
         ↓
Calculate expected HMAC:
  hash_hmac('sha512', json_payload, hmac_secret)
         ↓
Compare using hash_equals()
         ↓
  ✅ Match = Valid
  ❌ No Match = Invalid (reject webhook)
```

---

## ⚙️ Current Implementation

### PaymentWebhookController.php

```php
private function validateHmacSignature(Request $request, array $webhookData): bool
{
    $hmacSecret = config('services.paymob.hmac_secret');
    $hmacSignature = $request->header('X-Paymob-Signature') 
                  ?? $request->get('hmac') 
                  ?? $webhookData['hmac'] 
                  ?? null;
    
    if (!$hmacSecret || !$hmacSignature) {
        return true; // Skip validation if not configured
    }

    $dataToValidate = $webhookData;
    unset($dataToValidate['hmac']);
    
    $isValid = $this->paymobService->validateWebhookSignature(
        $hmacSignature,
        $dataToValidate
    );

    if (!$isValid) {
        PaymentLog::error('Invalid HMAC signature', [...]);
        // Currently REJECTS the webhook (returns false)
    }

    return $isValid; // ← Will reject if false
}
```

### PaymobService.php

```php
public function validateWebhookSignature(string $signature, array $data): bool
{
    $hmacSecret = config('services.paymob.hmac_secret');
    
    if (!$hmacSecret) {
        return true; // Skip if no secret configured
    }

    // Calculate expected signature
    $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $expectedSignature = hash_hmac('sha256', $payload, $hmacSecret); // ← Currently SHA-256

    // Compare (time-safe)
    return hash_equals($expectedSignature, $signature);
}
```

---

## 🔧 Fix Required

### Update PaymobService to use SHA-512

The HMAC length is 128 characters, which indicates **SHA-512**, not SHA-256.

**File: `app/Services/PaymobService.php`**

```php
public function validateWebhookSignature(string $signature, array $data): bool
{
    $hmacSecret = config('services.paymob.hmac_secret');
    
    if (!$hmacSecret) {
        return true;
    }

    $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    // Use SHA-512 instead of SHA-256
    $expectedSignature = hash_hmac('sha512', $payload, $hmacSecret);

    return hash_equals($expectedSignature, $signature);
}
```

---

## ✅ Action Items

### Immediate (Required):

1. ✅ **Get correct HMAC secret** from Paymob dashboard
2. ✅ **Update `.env`** with correct secret
3. ✅ **Change algorithm to SHA-512** in PaymobService
4. ✅ **Test** with the test script above
5. ✅ **Clear config cache** (`php artisan config:clear`)

### Temporary (If needed):

- ⚠️ Disable HMAC validation by setting `PAYMOB_HMAC_SECRET=` (empty)
- ⚠️ This allows webhooks to work while you get the correct secret
- ⚠️ **Must re-enable before production!**

---

## 🔒 Security Notes

### Why HMAC Validation is Important:

1. **Prevents spoofing** - Ensures webhook is from Paymob
2. **Prevents tampering** - Ensures data wasn't modified
3. **Prevents replay attacks** - Signature won't match if data changed

### Production Requirements:

- ✅ HMAC secret MUST be configured
- ✅ Validation MUST be enabled
- ✅ Failed validations MUST be rejected
- ✅ All failures MUST be logged

---

## 📞 Support

### Paymob Support:
- **Email:** support@paymob.com
- **Phone:** +966 (if available in dashboard)
- **Dashboard:** https://ksa.paymob.com/

### What to Request:
```
Subject: HMAC Secret for Webhook Validation

Hello,

I need the HMAC secret for webhook signature validation.

Merchant Details:
- Merchant ID: 11883
- Company Name: Slide
- Integration ID: 16105
- Environment: Test

Please provide the HMAC secret used to sign webhook payloads.

Thank you!
```

---

## 📝 Summary

### Current Status:
- ❌ HMAC validation is **failing**
- ❌ Wrong HMAC secret in configuration
- ❌ Webhooks are being **rejected** (401 error)

### Solution:
1. Get correct HMAC secret from Paymob
2. Update `PAYMOB_HMAC_SECRET` in `.env`
3. Change hash algorithm to SHA-512
4. Test and verify

### Temporary Workaround:
- Set `PAYMOB_HMAC_SECRET=` (empty) to disable validation
- **Must fix before production!**

---

**Status:** 🔴 Action Required  
**Priority:** High  
**Impact:** Webhooks are currently failing


