# Paymob Webhook Payload Structure

## 📋 Overview

هذا المستند يشرح بنية الـ Webhook Payloads التي يرسلها Paymob للـ backend.

---

## 🔔 1. Transaction Notification Webhook

### Endpoint
```
POST /api/paymob/notification
```

### Payload Structure

```json
{
  "type": "TRANSACTION",
  "obj": {
    "id": 973572,
    "pending": false,
    "amount_cents": 15500,
    "success": true,
    "is_auth": false,
    "is_capture": false,
    "is_standalone_payment": true,
    "is_voided": false,
    "is_refunded": false,
    "is_3d_secure": true,
    "integration_id": 16105,
    "profile_id": 11883,
    "has_parent_transaction": false,
    
    "order": {
      "id": 1018352,
      "created_at": "2025-10-12T18:51:43.264332+03:00",
      "delivery_needed": false,
      "merchant": {
        "id": 11883,
        "created_at": "2025-09-20T07:32:28.663211+03:00",
        "phones": ["+966590971717"],
        "company_emails": null,
        "company_name": "Slide",
        "state": null,
        "country": "SAU",
        "city": "temp",
        "postal_code": null,
        "street": null
      },
      "collector": null,
      "amount_cents": 15500,
      "shipping_data": {
        "id": 713970,
        "first_name": "User",
        "last_name": "Name",
        "street": "N/A",
        "building": "N/A",
        "floor": "N/A",
        "apartment": "N/A",
        "city": "Riyadh",
        "state": "Riyadh",
        "country": "Saudi Arabia",
        "email": "so@gmail.com",
        "phone_number": "966000000000",
        "postal_code": "NA",
        "extra_description": null,
        "shipping_method": "UNK",
        "order_id": 1018352,
        "order": 1018352
      },
      "currency": "SAR",
      "is_payment_locked": false,
      "is_return": false,
      "is_cancel": false,
      "is_returned": false,
      "is_canceled": false,
      "merchant_order_id": "WALLET-CHARGE-17-1760284302",
      "wallet_notification": null,
      "paid_amount_cents": 15500,
      "notify_user_with_email": false,
      "items": [
        {
          "name": "Wallet Charge",
          "description": "Wallet charging - 155 SAR",
          "amount_cents": 15500,
          "quantity": 1
        }
      ],
      "order_url": "NA",
      "commission_fees": 0,
      "delivery_fees_cents": 0,
      "delivery_vat_cents": 0,
      "payment_method": "tbc",
      "merchant_staff_tag": null,
      "api_source": "OTHER",
      "data": {
        "notification_url": "https://slide.osta-app.com/api/payments/webhooks/paymob"
      },
      "payment_status": "PAID",
      "terminal_version": null
    },
    
    "created_at": "2025-10-12T18:52:15.803661+03:00",
    "transaction_processed_callback_responses": [],
    "currency": "SAR",
    
    "source_data": {
      "pan": "0008",
      "type": "card",
      "tenure": null,
      "sub_type": "MasterCard"
    },
    
    "api_source": "SDK",
    "terminal_id": null,
    "merchant_commission": 0,
    "accept_fees": 0,
    "installment": null,
    "discount_details": [],
    "is_void": false,
    "is_refund": false,
    
    "data": {
      "gateway_integration_pk": 16105,
      "klass": "MigsPayment",
      "created_at": "2025-10-12T15:52:36.152715",
      "amount": 15500,
      "currency": "SAR",
      "migs_order": {
        "acceptPartialAmount": false,
        "amount": 155,
        "authenticationStatus": "AUTHENTICATION_SUCCESSFUL",
        "chargeback": {
          "amount": 0,
          "currency": "SAR"
        },
        "creationTime": "2025-10-12T15:52:28.737Z",
        "currency": "SAR",
        "id": "aa1018352",
        "lastUpdatedTime": "2025-10-12T15:52:36.074Z",
        "merchantAmount": 155,
        "merchantCategoryCode": "7372",
        "merchantCurrency": "SAR",
        "status": "CAPTURED",
        "totalAuthorizedAmount": 155,
        "totalCapturedAmount": 155,
        "totalRefundedAmount": 0
      },
      "merchant": "TEST601108800",
      "migs_result": "SUCCESS",
      "migs_transaction": {
        "acquirer": {
          "batch": 20251012,
          "date": "1012",
          "id": "NCB_S2I",
          "merchantId": "601108800",
          "settlementDate": "2025-10-12",
          "timeZone": "+0300",
          "transactionId": "123456789"
        },
        "amount": 155,
        "authenticationStatus": "AUTHENTICATION_SUCCESSFUL",
        "authorizationCode": "202105",
        "currency": "SAR",
        "id": "973572",
        "receipt": "528515202105",
        "source": "INTERNET",
        "stan": "202105",
        "terminal": "NCBS2I02",
        "type": "PAYMENT"
      },
      "txn_response_code": "0",
      "acq_response_code": "00",
      "message": "Approved",
      "merchant_txn_ref": "973572",
      "order_info": "aa1018352",
      "receipt_no": "528515202105",
      "transaction_no": "123456789",
      "batch_no": 20251012,
      "authorize_id": "202105",
      "card_type": "MASTERCARD",
      "card_num": "512345xxxxxx0008",
      "secure_hash": null,
      "avs_result_code": null,
      "avs_acq_response_code": "00",
      "captured_amount": 155,
      "authorised_amount": 155,
      "refunded_amount": 0,
      "acs_eci": "02",
      "txn_response_code_new": "APPROVED"
    },
    
    "is_hidden": false,
    
    "payment_key_claims": {
      "extra": {
        "amount_sar": 155,
        "operation_type": "wallet_charge",
        "merchant_order_id": "WALLET-CHARGE-17-1760284302"
      },
      "user_id": 13745,
      "currency": "SAR",
      "order_id": 1018352,
      "created_by": 13745,
      "is_partner": false,
      "amount_cents": 15500,
      "billing_data": {
        "city": "Riyadh",
        "email": "so@gmail.com",
        "floor": "N/A",
        "state": "Riyadh",
        "street": "N/A",
        "country": "Saudi Arabia",
        "building": "N/A",
        "apartment": "N/A",
        "last_name": "Name",
        "first_name": "User",
        "postal_code": "NA",
        "phone_number": "+966000000000",
        "extra_description": "NA"
      },
      "integration_id": 16105,
      "notification_url": "https://slide.osta-app.com/api/payments/webhooks/paymob",
      "lock_order_when_paid": false,
      "next_payment_intention": "pi_test_61a9a1cb00e34700b2e0264422f24dfb",
      "single_payment_attempt": false
    },
    
    "error_occured": false,
    "is_live": false,
    "other_endpoint_reference": null,
    "refunded_amount_cents": 0,
    "source_id": -1,
    "is_captured": false,
    "captured_amount": 0,
    "merchant_staff_tag": null,
    "updated_at": "2025-10-12T18:52:36.159439+03:00",
    "is_settled": false,
    "bill_balanced": false,
    "is_bill": false,
    "owner": 13745,
    "parent_transaction": null
  },
  
  "accept_fees": 0,
  "issuer_bank": null,
  "transaction_processed_callback_responses": null,
  
  "hmac": "448fc17a77234b038626c4eb97a966da6f7cc5317ba0d7f81abfa774479220e741bc301fc4ed693317818bb9d0efbfa7867c85bc41bd73229dbd9190d6f0db38"
}
```

### Important Fields

| Field Path | Description | Usage |
|------------|-------------|-------|
| `type` | Webhook type | Always "TRANSACTION" for payment notifications |
| `obj.id` | Transaction ID | Unique transaction identifier |
| `obj.success` | Payment success | `true` = successful, `false` = failed |
| `obj.pending` | Payment pending | `true` = pending, `false` = finalized |
| `obj.amount_cents` | Amount in cents | Total transaction amount (SAR * 100) |
| `obj.currency` | Currency code | Usually "SAR" |
| `obj.order.id` | Order ID | Paymob order identifier |
| `obj.order.merchant_order_id` | Your order ID | Your system's order reference |
| `obj.payment_key_claims.user_id` | User ID | Your user identifier |
| `obj.payment_key_claims.extra` | Custom data | Your custom fields (operation_type, etc.) |
| `obj.source_data` | Card info | Card type and last 4 digits |
| `obj.owner` | User ID | Alternative user identifier field |
| `hmac` | HMAC signature | For validation |

---

## 💳 2. Tokenized Card Callback

### Endpoint
```
POST /api/paymob/tokenized-callback
```

### Payload Structure

```json
{
  "type": "TOKEN",
  "obj": {
    "id": 27506,
    "token": "471a4a601f1c54ed2972bc5b7f80de88b2298603d1b81e810f6e1e28",
    "masked_pan": "xxxx-xxxx-xxxx-0008",
    "merchant_id": 11883,
    "card_subtype": "MasterCard",
    "created_at": "2025-10-12T18:52:16.459109+03:00",
    "email": "so@gmail.com",
    "order_id": "1018352",
    "user_added": false,
    "next_payment_intention": "pi_test_61a9a1cb00e34700b2e0264422f24dfb"
  },
  "hmac": "0f6656dcf83a5a4a27cd6e38020cddf921eb2b5255e66642ffb4a9143125f6325a623c2d3731140290783f2321731b0557190e7072328997f7b3b4bec525cd85"
}
```

### Important Fields

| Field Path | Description | Usage |
|------------|-------------|-------|
| `type` | Webhook type | Always "TOKEN" for card tokenization |
| `obj.id` | Token ID | Unique token identifier |
| `obj.token` | Card token | Token string for future payments |
| `obj.masked_pan` | Masked PAN | Last 4 digits of card (safe to display) |
| `obj.card_subtype` | Card brand | Visa, MasterCard, etc. |
| `obj.email` | User email | User's email address |
| `obj.order_id` | Order ID | Related order identifier |
| `obj.merchant_id` | Merchant ID | Your merchant identifier |
| `obj.next_payment_intention` | Next PI | For subsequent payments |
| `hmac` | HMAC signature | For validation |

---

## 🔍 How Our System Extracts Data

### For Transaction Notifications:

```php
// Extract from webhook payload
$type = $webhookData['type']; // "TRANSACTION"
$obj = $webhookData['obj'];

// Transaction details
$transactionId = $obj['id']; // 973572
$success = $obj['success']; // true
$pending = $obj['pending']; // false
$amountCents = $obj['amount_cents']; // 15500

// Order details
$orderId = $obj['order']['id']; // 1018352
$merchantOrderId = $obj['order']['merchant_order_id']; // "WALLET-CHARGE-17-1760284302"

// User identification
$userId = $obj['payment_key_claims']['user_id']; // 13745
// OR
$userId = $obj['owner']; // 13745

// Custom data
$extras = $obj['payment_key_claims']['extra'];
$operationType = $extras['operation_type']; // "wallet_charge"
$amountSar = $extras['amount_sar']; // 155

// Card info
$sourceData = $obj['source_data'];
$cardType = $sourceData['sub_type']; // "MasterCard"
$cardPan = $sourceData['pan']; // "0008"

// Signature validation
$hmac = $webhookData['hmac'];
```

### For Token Callbacks:

```php
// Extract from webhook payload
$type = $webhookData['type']; // "TOKEN"
$obj = $webhookData['obj'];

// Token details
$tokenId = $obj['id']; // 27506
$cardToken = $obj['token']; // "471a4a601f..."
$maskedPan = $obj['masked_pan']; // "xxxx-xxxx-xxxx-0008"
$cardBrand = $obj['card_subtype']; // "MasterCard"

// User identification
$email = $obj['email']; // "so@gmail.com"
$orderId = $obj['order_id']; // "1018352"

// Find user by email
$user = User::where('email', $email)->first();
$userId = $user->id;

// OR find user by order_id
$intention = $paymentRepository->findIntentionByPaymobOrderId($orderId);
$userId = $intention->user_id;

// Signature validation
$hmac = $webhookData['hmac'];
```

---

## 🔐 HMAC Signature Validation

### How Paymob Sends HMAC

```
POST /api/paymob/notification
Content-Type: application/json

{
  "type": "TRANSACTION",
  "obj": {...},
  "hmac": "448fc17a77234b..."
}
```

### How We Validate

```php
$hmacSecret = config('services.paymob.hmac_secret');
$hmacProvided = $request->get('hmac');
$dataToValidate = $request->except('hmac');

$payload = json_encode($dataToValidate, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
$expectedHmac = hash_hmac('sha256', $payload, $hmacSecret);

$isValid = hash_equals($expectedHmac, $hmacProvided);
```

---

## 📊 Payload Comparison

### Transaction Notification vs Token Callback

| Feature | Transaction Notification | Token Callback |
|---------|-------------------------|----------------|
| **Type** | `"TRANSACTION"` | `"TOKEN"` |
| **Purpose** | Payment status update | Save card for future |
| **Frequency** | Every payment | Only if Save Card enabled |
| **Contains User ID** | Yes (`payment_key_claims.user_id` or `owner`) | No (use email or order_id) |
| **Contains Amount** | Yes (`amount_cents`) | No |
| **Contains Card Info** | Partial (`source_data`) | Yes (`masked_pan`, `card_subtype`) |
| **Contains Token** | No | Yes (`token`) |
| **HMAC Required** | Recommended | Recommended |

---

## 🎯 Operation Types

Based on `payment_key_claims.extra.operation_type`:

### Wallet Charging:
```json
{
  "payment_key_claims": {
    "extra": {
      "operation_type": "wallet_charge",
      "amount_sar": 155,
      "merchant_order_id": "WALLET-CHARGE-17-1760284302"
    }
  }
}
```

### Investment:
```json
{
  "payment_key_claims": {
    "extra": {
      "opportunity_id": 123,
      "shares": 10,
      "investment_type": "partial",
      "price_per_share": 50,
      "opportunity_name": "مشروع الاستثمار"
    }
  }
}
```

---

## 🔄 Webhook Flow

### Transaction Notification Flow:

```
1. User completes payment on Paymob
   ↓
2. Paymob processes payment
   ↓
3. Paymob sends webhook to: POST /api/paymob/notification
   {
     "type": "TRANSACTION",
     "obj": { "id": 123, "success": true, ... },
     "hmac": "..."
   }
   ↓
4. Backend validates HMAC signature
   ↓
5. Backend extracts: user_id, order_id, merchant_order_id
   ↓
6. Backend finds transaction by merchant_order_id
   ↓
7. Backend updates transaction status
   ↓
8. Backend returns: {"success": true}
```

### Token Callback Flow:

```
1. User enables "Save Card" and completes payment
   ↓
2. Paymob tokenizes the card
   ↓
3. Paymob sends webhook to: POST /api/paymob/tokenized-callback
   {
     "type": "TOKEN",
     "obj": { "token": "abc123", "email": "...", ... },
     "hmac": "..."
   }
   ↓
4. Backend validates HMAC signature
   ↓
5. Backend finds user by email or order_id
   ↓
6. Backend stores card token in database
   ↓
7. Backend returns: {"success": true}
```

---

## ⚠️ Important Notes

### 1. User Identification Priority:

For **Transaction Notifications**:
1. Try `obj.payment_key_claims.user_id` first
2. Fallback to `obj.owner`

For **Token Callbacks**:
1. Try finding user by `obj.email`
2. Fallback to finding by `obj.order_id`

### 2. Merchant Order ID:

Your system's order reference is in:
- `obj.order.merchant_order_id` for transactions
- Example: `"WALLET-CHARGE-17-1760284302"` or `"INV-123-456-789"`

### 3. Amount Format:

- Paymob sends: `amount_cents` (e.g., 15500)
- Convert to SAR: `amount_cents / 100` (e.g., 155.00 SAR)

### 4. Card Data Security:

**NEVER log or store:**
- Full card number
- CVV
- PIN

**Safe to store:**
- `masked_pan` (e.g., "xxxx-xxxx-xxxx-0008")
- `card_subtype` (e.g., "MasterCard")
- `token` (for future payments)

---

## 🧪 Testing

### Test Transaction Notification:

```bash
curl -X POST http://localhost:8000/api/paymob/notification \
  -H "Content-Type: application/json" \
  -d '{
    "type": "TRANSACTION",
    "obj": {
      "id": 123456,
      "success": true,
      "pending": false,
      "amount_cents": 10000,
      "order": {
        "id": 789,
        "merchant_order_id": "TEST-ORDER-123"
      },
      "payment_key_claims": {
        "user_id": 1,
        "extra": {
          "operation_type": "wallet_charge"
        }
      }
    }
  }'
```

### Test Token Callback:

```bash
curl -X POST http://localhost:8000/api/paymob/tokenized-callback \
  -H "Content-Type: application/json" \
  -d '{
    "type": "TOKEN",
    "obj": {
      "token": "test_token_123",
      "masked_pan": "xxxx-xxxx-xxxx-1234",
      "card_subtype": "Visa",
      "email": "test@example.com",
      "order_id": "789"
    }
  }'
```

---

## 📚 Related Documentation

- `PAYMOB_WEBHOOKS_DOCUMENTATION.md` - Complete webhook documentation
- `PAYMENT_APIS_SIMPLIFIED.md` - Simplified API guide
- `PAYMOB_INTEGRATION_DOCUMENTATION.md` - Full integration guide

---

**Last Updated:** 2025-10-12





