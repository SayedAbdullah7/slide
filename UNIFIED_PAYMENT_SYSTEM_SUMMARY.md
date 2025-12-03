# Unified Payment System - Final Implementation

**Date:** October 15, 2025  
**Status:** ✅ **COMPLETE**

---

## 🎯 Key Improvements

### 1. ✅ **Renamed Service for Clarity**
- **Before:** `WebhookHandlerService`
- **After:** `PaymentWebhookService`
- **Why:** Makes it immediately clear this service handles payment webhooks

### 2. ✅ **Unified Payment Intention Method**
Created ONE main method that handles both types:

```php
// Main unified method
public function createIntention(Request $request): JsonResponse
{
    $type = $request->input('type', 'investment'); // investment or wallet_charge
    $result = $this->paymentService->createIntention($request->all(), $userId, $type);
    // ...
}

// Dedicated endpoints (NO code duplication)
public function createInvestmentIntention(Request $request): JsonResponse
{
    $request->merge(['type' => 'investment']);
    return $this->createIntention($request); // Calls main method
}

public function createWalletIntention(Request $request): JsonResponse
{
    $request->merge(['type' => 'wallet_charge']);
    return $this->createIntention($request); // Calls main method
}
```

### 3. ✅ **Type Stored in Database**
Added `type` column to `payment_intentions` table:
- Values: `investment` or `wallet_charge`
- Indexed for fast queries
- Used to determine which action to execute on success

### 4. ✅ **Auto-Execute Transactions on Success**
When payment is successful (webhook notification):

**For Wallet Charge:**
```php
private function executeWalletCharge($intention, $transaction): void
{
    $user = User::find($intention->user_id);
    $amountSar = $intention->amount_cents / 100;
    
    // Add balance to user wallet
    $user->increment('balance', $amountSar);
    
    PaymentLog::info('Wallet charged successfully', [
        'amount_sar' => $amountSar,
        'new_balance' => $user->fresh()->balance
    ]);
}
```

**For Investment:**
```php
private function executeInvestment($intention, $transaction): void
{
    $extras = $intention->extras;
    $opportunityId = $extras['opportunity_id'];
    $shares = $extras['shares'];
    
    // Create investment record
    $investment = Investment::create([
        'user_id' => $intention->user_id,
        'investment_opportunity_id' => $opportunityId,
        'shares' => $shares,
        'total_amount' => $intention->amount_cents / 100,
        'status' => 'active',
    ]);
    
    // Update opportunity shares
    $opportunity->decrement('available_shares', $shares);
    $opportunity->increment('invested_shares', $shares);
    
    PaymentLog::info('Investment created successfully', [
        'investment_id' => $investment->id,
        'shares' => $shares
    ]);
}
```

---

## 📊 Complete Flow

### 1. User Initiates Payment

**Option A: Investment**
```http
POST /api/payments/intentions/investment
{
    "opportunity_id": 1,
    "shares": 10,
    "investment_type": "full"
}
```

**Option B: Wallet Charge**
```http
POST /api/payments/intentions/wallet
{
    "amount": 1000
}
```

**Option C: Unified Endpoint**
```http
POST /api/payments/intentions
{
    "type": "investment",  // or "wallet_charge"
    "opportunity_id": 1,
    "shares": 10
}
```

### 2. System Creates Payment Intention

```php
PaymentService::createIntention()
    → Validates data
    → Creates Paymob intention
    → Stores in DB with TYPE
    → Returns client_secret for checkout
```

**Database Record:**
```json
{
    "id": 123,
    "user_id": 1,
    "type": "investment",  // ← Stored!
    "amount_cents": 100000,
    "status": "created",
    "extras": {
        "opportunity_id": 1,
        "shares": 10
    }
}
```

### 3. User Completes Payment (Paymob)

User pays via Paymob checkout → Payment successful

### 4. Webhook Receives Notification

```php
PaymentWebhookService::handleWebhook($data)
    → Updates transaction status to "successful"
    → Updates intention status to "completed"
    → Executes transaction based on TYPE ← Key!
```

### 5. System Executes Transaction

**Match on Type:**
```php
match($intention->type) {
    'wallet_charge' => executeWalletCharge(),  // Adds balance
    'investment' => executeInvestment(),       // Creates investment
}
```

**For Wallet:**
```sql
UPDATE users SET balance = balance + 1000 WHERE id = 1;
```

**For Investment:**
```sql
-- Create investment
INSERT INTO investments (user_id, opportunity_id, shares, amount)
VALUES (1, 1, 10, 1000);

-- Update opportunity
UPDATE investment_opportunities 
SET available_shares = available_shares - 10,
    invested_shares = invested_shares + 10
WHERE id = 1;
```

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────────┐
│                 PaymentController                    │
│                                                      │
│  ┌────────────────────────────────────────────┐    │
│  │ createIntention() - UNIFIED                │    │
│  │   ├─ Sets type from request                │    │
│  │   └─ Calls PaymentService                  │    │
│  └────────────────────────────────────────────┘    │
│                                                      │
│  ┌────────────────────────────────────────────┐    │
│  │ createInvestmentIntention()                │    │
│  │   ├─ Adds type = 'investment'              │    │
│  │   └─ Calls unified method ↑                │    │
│  └────────────────────────────────────────────┘    │
│                                                      │
│  ┌────────────────────────────────────────────┐    │
│  │ createWalletIntention()                    │    │
│  │   ├─ Adds type = 'wallet_charge'           │    │
│  │   └─ Calls unified method ↑                │    │
│  └────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────┐
│                  PaymentService                      │
│                                                      │
│  createIntention(type)                              │
│    match(type) {                                    │
│      'investment' → createInvestmentIntention()     │
│      'wallet_charge' → createWalletIntention()      │
│    }                                                │
│                                                      │
│  Stores type in payment_intentions.type ←─────      │
└─────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────┐
│                  Paymob Payment                      │
│              (User pays externally)                  │
└─────────────────────────────────────────────────────┘
                        │
                        ▼
┌─────────────────────────────────────────────────────┐
│              PaymentWebhookService                   │
│                                                      │
│  handleWebhook()                                    │
│    ├─ Updates transaction status                    │
│    ├─ Updates intention status                      │
│    └─ executeTransaction()                          │
│         │                                           │
│         └─ match($intention->type) {                │
│              'wallet_charge' → executeWalletCharge()│
│              'investment' → executeInvestment()     │
│            }                                        │
└─────────────────────────────────────────────────────┘
```

---

## 📝 API Endpoints

### Create Payment Intention

**1. Unified Endpoint**
```http
POST /api/payments/intentions
Content-Type: application/json
Authorization: Bearer {token}

{
    "type": "investment",  // or "wallet_charge"
    "opportunity_id": 1,   // for investment
    "shares": 10,          // for investment
    "amount": 1000         // for wallet_charge
}
```

**2. Investment-Specific Endpoint**
```http
POST /api/payments/intentions/investment
Content-Type: application/json
Authorization: Bearer {token}

{
    "opportunity_id": 1,
    "shares": 10,
    "investment_type": "full"
}
```

**3. Wallet-Specific Endpoint**
```http
POST /api/payments/intentions/wallet
Content-Type: application/json
Authorization: Bearer {token}

{
    "amount": 1000
}
```

### Response (All endpoints)
```json
{
    "success": true,
    "message": "Payment intention created successfully",
    "result": {
        "intention_id": 123,
        "client_secret": "...",
        "payment_token": "...",
        "type": "investment",
        "amount_sar": 1000
    }
}
```

---

## 🗄️ Database Schema

### payment_intentions
```sql
CREATE TABLE payment_intentions (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    type VARCHAR(255) DEFAULT 'investment',  -- ← NEW!
    amount_cents INT,
    currency VARCHAR(3),
    status VARCHAR(255),
    extras JSON,  -- Stores opportunity_id, shares for investment
    client_secret TEXT,
    payment_token TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_status (status),
    INDEX idx_user_type (user_id, type)
);
```

**Sample Records:**

**Investment Intention:**
```json
{
    "id": 1,
    "user_id": 1,
    "type": "investment",
    "amount_cents": 100000,
    "status": "completed",
    "extras": {
        "opportunity_id": 5,
        "shares": 10,
        "price_per_share": 1000
    }
}
```

**Wallet Intention:**
```json
{
    "id": 2,
    "user_id": 1,
    "type": "wallet_charge",
    "amount_cents": 100000,
    "status": "completed",
    "extras": {
        "operation_type": "wallet_charge",
        "amount_sar": 1000
    }
}
```

---

## ✅ Benefits

### 1. **No Code Duplication**
- Separate methods call the unified method
- DRY principle maintained
- Single source of truth

### 2. **Clear Type Tracking**
- Type stored in database
- Easy to filter by type
- No ambiguity about intention purpose

### 3. **Automatic Transaction Execution**
- Payment success triggers business logic
- Wallet automatically charged
- Investment automatically created
- Opportunity shares automatically updated

### 4. **Flexible API**
- Can use unified endpoint
- Can use dedicated endpoints
- Backward compatible

### 5. **Better Logging**
```php
PaymentLog::info('Wallet charged successfully', [
    'user_id' => 1,
    'amount_sar' => 1000,
    'new_balance' => 5000,
    'intention_id' => 123,
    'type' => 'wallet_charge'
]);

PaymentLog::info('Investment created successfully', [
    'investment_id' => 456,
    'opportunity_id' => 5,
    'shares' => 10,
    'intention_id' => 124,
    'type' => 'investment'
]);
```

---

## 🔄 Example Scenarios

### Scenario 1: User Charges Wallet

```
1. POST /api/payments/intentions/wallet
   {amount: 1000}

2. System creates intention
   type = 'wallet_charge'
   status = 'created'

3. User pays via Paymob → Success

4. Webhook received → executeWalletCharge()
   users.balance += 1000
   
5. User sees new balance immediately
```

### Scenario 2: User Invests in Opportunity

```
1. POST /api/payments/intentions/investment
   {opportunity_id: 5, shares: 10}

2. System creates intention
   type = 'investment'
   status = 'created'
   extras = {opportunity_id: 5, shares: 10}

3. User pays via Paymob → Success

4. Webhook received → executeInvestment()
   - Create Investment record
   - Decrement available_shares
   - Increment invested_shares
   
5. User sees investment in dashboard
```

---

## 🧪 Testing

### Test Wallet Charge
```bash
curl -X POST http://localhost/api/payments/intentions/wallet \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"amount": 1000}'
```

### Test Investment
```bash
curl -X POST http://localhost/api/payments/intentions/investment \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "opportunity_id": 1,
    "shares": 10,
    "investment_type": "full"
  }'
```

### Test Unified Endpoint
```bash
curl -X POST http://localhost/api/payments/intentions \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "type": "wallet_charge",
    "amount": 1000
  }'
```

---

## 📊 File Summary

| File | Lines | Purpose |
|------|-------|---------|
| **PaymentController.php** | 220 | Handles API requests, unified + dedicated methods |
| **PaymentService.php** | 275 | Business logic + validation |
| **PaymentWebhookService.php** | 250 | Webhook handling + transaction execution |
| **PaymobService.php** | 220 | Paymob API communication |

**Total:** 965 lines of clean, organized code

---

## 🎯 Conclusion

✅ **Service renamed** for clarity: `PaymentWebhookService`  
✅ **Unified method** with dedicated endpoints (no duplication)  
✅ **Type stored** in database for tracking  
✅ **Auto-execution** of transactions on payment success  
✅ **Wallet charges** applied automatically  
✅ **Investments created** automatically  
✅ **Clean architecture** following DRY principles  

**Status:** Production Ready! 🚀


