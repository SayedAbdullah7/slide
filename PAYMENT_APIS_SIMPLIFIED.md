# Payment APIs - Simplified Documentation

## 📋 Overview

هذا المستند يوضح جميع الـ APIs الخاصة ببوابة الدفع Paymob بعد التنظيف والتبسيط.

---

## 🔐 APIs للمستخدمين المسجلين (Authenticated)

### Base URL: `/api/payments`

| Method | Endpoint | Purpose | Request Body |
|--------|----------|---------|--------------|
| POST | `/intentions` | إنشاء نية دفع للاستثمار | `opportunity_id`, `shares`, `investment_type` |
| POST | `/wallet-intentions` | إنشاء نية دفع لشحن المحفظة | `amount` |
| GET | `/intentions` | الحصول على قائمة نيات الدفع | - |
| GET | `/transactions` | الحصول على قائمة المعاملات | - |
| GET | `/stats` | الحصول على إحصائيات الدفع | - |
| GET | `/logs` | الحصول على سجلات الدفع | - |

---

## 🌐 APIs للـ Webhooks (Public)

### Base URL: `/api/paymob`

| Method | Endpoint | Purpose | Called By |
|--------|----------|---------|-----------|
| POST | `/notification` | استقبال تحديثات حالة المعاملات | Paymob Server |
| GET | `/redirection` | إعادة توجيه المستخدم بعد الدفع | Paymob Checkout |
| POST | `/tokenized-callback` | حفظ بيانات البطاقة المرمزة | Paymob (Save Card) |

---

## 📝 API Details

### 1. Create Investment Payment Intention
**إنشاء نية دفع للاستثمار**

```http
POST /api/payments/intentions
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "opportunity_id": 1,
    "shares": 10,
    "investment_type": "partial"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Payment intention created successfully",
    "data": {
        "client_secret": "csk_...",
        "payment_token": "tok_...",
        "intention_id": 123,
        "amount_sar": 500.00,
        "opportunity_name": "مشروع الاستثمار"
    }
}
```

---

### 2. Create Wallet Charging Intention
**إنشاء نية دفع لشحن المحفظة**

```http
POST /api/payments/wallet-intentions
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "amount": 100.00
}
```

**Response:**
```json
{
    "success": true,
    "message": "Wallet charging intention created successfully",
    "data": {
        "client_secret": "csk_...",
        "payment_token": "tok_...",
        "amount_sar": 100.00,
        "operation_type": "wallet_charge"
    }
}
```

---

### 3. Get Payment Intentions
**الحصول على قائمة نيات الدفع**

```http
GET /api/payments/intentions
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "amount_sar": 500.00,
            "status": "completed",
            "created_at": "2025-10-12T10:00:00Z"
        }
    ]
}
```

---

### 4. Get Transactions
**الحصول على قائمة المعاملات**

```http
GET /api/payments/transactions
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "transaction_id": "123456",
            "amount_sar": 500.00,
            "status": "successful",
            "payment_method": "card",
            "processed_at": "2025-10-12T10:05:00Z"
        }
    ]
}
```

---

### 5. Get Payment Statistics
**الحصول على إحصائيات الدفع**

```http
GET /api/payments/stats
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "total_transactions": 50,
        "successful_transactions": 45,
        "failed_transactions": 5,
        "total_amount": 25000.00,
        "average_transaction": 500.00
    }
}
```

---

### 6. Get Payment Logs
**الحصول على سجلات الدفع**

```http
GET /api/payments/logs
Authorization: Bearer {token}
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "type": "info",
            "action": "create_intention_success",
            "message": "Payment intention created successfully",
            "created_at": "2025-10-12T10:00:00Z"
        }
    ]
}
```

---

## 🔔 Webhook APIs

### 1. Notification Webhook
**استقبال إشعارات Paymob**

```http
POST /api/paymob/notification
Content-Type: application/json
X-Paymob-Signature: {hmac_signature}
```

**Request Body (from Paymob):**
```json
{
    "obj": {
        "id": "12345678",
        "success": true,
        "pending": false,
        "order": {
            "merchant_order_id": "INV-123-456"
        },
        "amount_cents": 50000
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Webhook processed successfully"
}
```

---

### 2. Redirection URL
**إعادة توجيه المستخدم بعد الدفع**

```http
GET /api/paymob/redirection?merchant_order_id=INV-123&success=true
```

**Response:**
```json
{
    "success": true,
    "message": "Payment successful",
    "transaction": {
        "id": 1,
        "status": "successful",
        "amount": 500.00
    },
    "redirect_url": "https://yourapp.com/payment/success?transaction_id=1",
    "deep_link": "myapp://payment/success?transaction_id=1"
}
```

---

### 3. Tokenized Callback
**حفظ بيانات البطاقة المرمزة**

```http
POST /api/paymob/tokenized-callback
Content-Type: application/json
```

**Request Body (from Paymob):**
```json
{
    "user_id": 123,
    "card_token": "tok_abc123xyz",
    "masked_pan": "XXXX-XXXX-XXXX-1234",
    "card_brand": "Visa"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Card token saved successfully"
}
```

---

## 🔧 Configuration

### Environment Variables (.env)

```env
# Paymob KSA Configuration
PAYMOB_SECRET_KEY=your_secret_key
PAYMOB_PUBLIC_KEY=your_public_key
PAYMOB_INTEGRATION_ID=your_integration_id
PAYMOB_HMAC_SECRET=your_hmac_secret
PAYMOB_BASE_URL=https://ksa.paymob.com

# App URLs
APP_FRONTEND_URL=https://yourapp.com
```

---

## 📊 Payment Flow

### For Investment:
1. User selects investment opportunity
2. App calls `POST /api/payments/intentions`
3. App receives `client_secret` and payment URL
4. User completes payment on Paymob
5. Paymob calls `POST /api/paymob/notification` (webhook)
6. Paymob redirects user to `GET /api/paymob/redirection`
7. App shows success/failure screen

### For Wallet Charging:
1. User enters amount to charge
2. App calls `POST /api/payments/wallet-intentions`
3. App receives `client_secret` and payment URL
4. User completes payment on Paymob
5. Same webhook and redirection flow as investment

---

## 🗑️ Removed/Cleaned Items

### ❌ Removed Routes:
- `POST /api/payments/moto` - غير مستخدم
- `POST /api/payments/capture` - غير ضروري للتطبيق
- `POST /api/payments/void` - غير ضروري للتطبيق
- `POST /api/payments/refund` - غير ضروري للتطبيق
- `GET /api/payments/intentions/{id}/checkout-url` - غير مستخدم
- `POST /api/payments/webhooks/paymob` - مكرر (legacy)
- `GET /api/payments/webhooks/success` - مكرر (legacy)
- `GET /api/payments/webhooks/failure` - مكرر (legacy)

### ❌ Removed Controller Methods:
- `handleWebhook()` - مكرر مع `notification()`
- `handleSuccess()` - مكرر مع `redirection()`
- `handleFailure()` - مكرر مع `redirection()`
- `getCheckoutUrl()` - غير مستخدم
- `processMotoPayment()` - غير مستخدم
- `capturePayment()` - غير مستخدم
- `voidPayment()` - غير مستخدم
- `refundPayment()` - غير مستخدم

---

## ✅ Final APIs Count

### Before Cleanup:
- 13 authenticated routes
- 6 webhook routes
- **Total: 19 routes**

### After Cleanup:
- 6 authenticated routes
- 3 webhook routes
- **Total: 9 routes** ✨

**Reduction: 52% fewer routes!**

---

## 📱 Mobile Deep Links

Configure your mobile app to handle:
- `myapp://payment/success?transaction_id={id}`
- `myapp://payment/failed?transaction_id={id}`
- `myapp://payment/pending?transaction_id={id}`
- `myapp://payment/error`

---

## 🔒 Security

1. **HMAC Signature** - Validates webhook authenticity
2. **Authentication** - All user APIs require Bearer token
3. **No Sensitive Data** - Card data never logged or stored
4. **HTTPS Only** - All endpoints require HTTPS in production

---

## 📞 Support

- **Paymob Docs**: https://docs.paymob.com/
- **Paymob Support**: support@paymob.com
- **Dashboard**: https://ksa.paymob.com/

---

## Version

**v2.0.0** - Simplified & Cleaned (2025-10-12)





