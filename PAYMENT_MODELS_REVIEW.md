# Payment Models - Comprehensive Review

**Date:** October 15, 2025  
**Status:** ✅ **ANALYSIS COMPLETE**

---

## 📊 Payment Models Overview

| Model | Status | Used | Table | Action Required |
|-------|--------|------|-------|-----------------|
| **PaymentIntention** | ✅ Active | Yes | payment_intentions | ✅ Updated |
| **PaymentTransaction** | ✅ Active | Yes | payment_transactions | ✅ OK |
| **PaymentLog** | ✅ Active | Yes | payment_logs | ✅ OK |
| **UserCard** | ✅ Active | Yes | user_cards | ✅ OK |
| **Payment** | ❌ Empty | No | payments | ❌ DELETE |
| **PaymentMethod** | ❌ Empty | No | payment_methods | ❌ DELETE |

---

## ✅ ACTIVE MODELS (Keep & Update)

### 1. PaymentIntention ✅

**Role:** Stores payment intentions created with Paymob before actual payment

**Current Columns:**
```php
- id
- user_id
- amount_cents
- currency
- type                   // ← NEW: 'investment' or 'wallet_charge'
- client_secret         // From Paymob
- payment_token         // From Paymob
- special_reference
- status                // 'created', 'active', 'completed', 'failed', 'expired'
- is_executed           // ← NEW: Boolean flag
- payment_methods       // JSON
- billing_data          // JSON
- items                 // JSON
- extras                // JSON - stores opportunity_id, shares for investment
- notification_url
- redirection_url
- paymob_intention_id
- paymob_order_id
- checkout_url
- expires_at
- created_at
- updated_at
```

**Status:** ✅ **UPDATED & COMPLETE**

**Relationships:**
```php
- belongsTo(User::class)
- hasMany(PaymentTransaction::class)
```

**Helper Methods:**
```php
- isExpired(): bool
- isActive(): bool
- getAmountInSarAttribute(): float
```

**Usage:**
- Created when user initiates payment
- Stores type ('investment' or 'wallet_charge')
- Tracks execution status (is_executed)
- Links to transactions

**Action:** ✅ **KEEP - Already Updated**

---

### 2. PaymentTransaction ✅

**Role:** Stores actual payment transactions from Paymob webhooks

**Current Columns:**
```php
- id
- payment_intention_id
- user_id
- transaction_id        // Paymob transaction ID
- amount_cents
- currency
- status               // 'successful', 'failed', 'pending', 'refunded'
- payment_method
- card_token
- payment_token
- merchant_order_id
- paymob_response      // JSON - full webhook data
- processed_at
- refunded_at
- refund_amount_cents
- created_at
- updated_at
```

**Status:** ✅ **COMPLETE - NO CHANGES NEEDED**

**Relationships:**
```php
- belongsTo(PaymentIntention::class)
- belongsTo(User::class)
```

**Helper Methods:**
```php
- isSuccessful(): bool
- isPending(): bool
- isFailed(): bool
- isRefunded(): bool
- getAmountInSarAttribute(): float
- getRefundAmountInSarAttribute(): float
```

**Usage:**
- Created when webhook receives transaction update
- Stores full Paymob response
- Tracks transaction status

**Action:** ✅ **KEEP - Perfect As Is**

---

### 3. PaymentLog ✅

**Role:** Comprehensive logging for payment operations and debugging

**Current Columns:**
```php
- id
- user_id
- payment_intention_id
- payment_transaction_id
- type                 // 'info', 'error', 'warning', 'debug'
- action               // Custom action identifier
- message
- context              // JSON - additional data
- ip_address
- user_agent
- created_at
- updated_at
```

**Status:** ✅ **COMPLETE - NO CHANGES NEEDED**

**Relationships:**
```php
- belongsTo(User::class)
- belongsTo(PaymentIntention::class)
- belongsTo(PaymentTransaction::class)
```

**Static Methods:**
```php
- info(message, context, userId, intentionId, transactionId, action)
- error(message, context, userId, intentionId, transactionId, action)
- warning(message, context, userId, intentionId, transactionId, action)
- debug(message, context, userId, intentionId, transactionId, action)
```

**Usage:**
- Logs all payment operations
- Debugging and auditing
- Tracks errors and warnings
- Links to intentions and transactions

**Action:** ✅ **KEEP - Excellent for Debugging**

---

### 4. UserCard ✅

**Role:** Stores saved credit cards for users (tokenized by Paymob)

**Current Columns:**
```php
- id
- user_id
- card_token           // Paymob card token
- masked_pan           // e.g., "512345******1234"
- card_brand           // e.g., "Visa", "Mastercard"
- paymob_token_id
- paymob_order_id
- paymob_merchant_id
- is_default          // Boolean
- is_active           // Boolean
- last_used_at
- created_at
- updated_at
```

**Status:** ✅ **COMPLETE - NO CHANGES NEEDED**

**Relationships:**
```php
- belongsTo(User::class)
```

**Helper Methods:**
```php
- getCardDisplayNameAttribute(): string  // "Visa ending in 1234"
- getLastFourAttribute(): string
- scopeActive($query)
- getOrCreateCard(array $data): self    // Prevents duplicates
```

**Usage:**
- Stores tokenized cards from Paymob TOKEN webhooks
- Prevents duplicate cards
- Auto-sets first card as default
- Used in payment intentions (card_tokens array)

**Action:** ✅ **KEEP - Essential for Saved Cards Feature**

---

## ❌ UNUSED MODELS (Delete)

### 5. Payment ❌

**Current State:**
```php
class Payment extends Model
{
    // Empty model - no functionality
}
```

**Table Structure:**
```sql
CREATE TABLE payments (
    id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Usage:** ❌ **NOT USED ANYWHERE**

**Why Delete:**
- Empty model with no functionality
- Table has only id and timestamps
- No relationships
- No references in code
- Replaced by PaymentIntention and PaymentTransaction

**Action:** ❌ **DELETE Model & Migration**

---

### 6. PaymentMethod ❌

**Current State:**
```php
class PaymentMethod extends Model
{
    // Empty model - no functionality
}
```

**Table Structure:**
```sql
CREATE TABLE payment_methods (
    id BIGINT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Usage:** ❌ **NOT USED ANYWHERE**

**Why Delete:**
- Empty model with no functionality
- Table has only id and timestamps
- No relationships
- No references in code
- Payment methods stored in PaymentIntention as JSON array

**Action:** ❌ **DELETE Model & Migration**

---

## 📋 Summary

### ✅ Keep These Models (4)

1. **PaymentIntention** (Primary) - Payment intentions with type tracking
2. **PaymentTransaction** (Primary) - Actual transactions from Paymob
3. **PaymentLog** (Supporting) - Logging and debugging
4. **UserCard** (Supporting) - Saved cards feature

### ❌ Delete These Models (2)

1. **Payment** - Empty, unused
2. **PaymentMethod** - Empty, unused

---

## 🔄 Database Relationships

```
User
 ├─ hasMany → PaymentIntention
 ├─ hasMany → PaymentTransaction
 ├─ hasMany → PaymentLog
 └─ hasMany → UserCard

PaymentIntention
 ├─ belongsTo → User
 └─ hasMany → PaymentTransaction

PaymentTransaction
 ├─ belongsTo → User
 └─ belongsTo → PaymentIntention

PaymentLog
 ├─ belongsTo → User
 ├─ belongsTo → PaymentIntention
 └─ belongsTo → PaymentTransaction

UserCard
 └─ belongsTo → User
```

---

## 📝 Recommended Changes

### 1. Delete Unused Models ❌

**Models to delete:**
```bash
rm app/Models/Payment.php
rm app/Models/PaymentMethod.php
```

**Migrations to delete:**
```bash
rm database/migrations/2025_10_03_204030_create_payments_table.php
rm database/migrations/2025_10_03_204031_create_payment_methods_table.php
```

**Or create migration to drop tables:**
```php
Schema::dropIfExists('payments');
Schema::dropIfExists('payment_methods');
```

---

### 2. Add Relationships to User Model ✅

**Update User.php:**
```php
class User extends Authenticatable
{
    /**
     * Payment-related relationships
     */
    public function paymentIntentions(): HasMany
    {
        return $this->hasMany(PaymentIntention::class);
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function savedCards(): HasMany
    {
        return $this->hasMany(UserCard::class);
    }
}
```

---

### 3. Add Relationships to PaymentIntention ✅

**Already has:**
```php
- user()
- transactions()
```

**Consider adding:**
```php
public function investments(): HasMany
{
    return $this->hasMany(Investment::class, 'payment_intention_id');
}
```

---

## 🎯 Model Purposes (Clear Roles)

### PaymentIntention
**Purpose:** Track payment intent before payment  
**When:** Created when user initiates payment  
**Contains:** User intent, amount, type, extras  
**Lifecycle:** created → active → completed/failed  

### PaymentTransaction
**Purpose:** Record actual payment from Paymob  
**When:** Created when webhook receives transaction  
**Contains:** Paymob transaction details, status  
**Lifecycle:** Created from webhook, updated on status change  

### PaymentLog
**Purpose:** Audit trail and debugging  
**When:** Every payment operation  
**Contains:** Logs, errors, warnings, debug info  
**Lifecycle:** Append-only logging  

### UserCard
**Purpose:** Store saved cards for users  
**When:** Paymob sends TOKEN webhook  
**Contains:** Tokenized card data  
**Lifecycle:** Created from webhook, managed by user  

---

## 📊 Data Flow

```
1. User Initiates Payment
   ↓
   PaymentIntention (created, type='investment', is_executed=false)
   
2. User Pays via Paymob
   ↓
   PaymentTransaction (status='successful')
   
3. Webhook Received
   ↓
   PaymentTransaction (updated with full data)
   PaymentIntention (status='completed')
   
4. Execute Transaction
   ↓
   Check: status='successful' AND is_executed=false
   ↓
   Execute based on type:
   - wallet_charge → WalletService::depositToWallet()
   - investment → InvestmentService::invest()
   ↓
   PaymentIntention (is_executed=true)
   
5. Throughout Process
   ↓
   PaymentLog (all operations logged)
```

---

## ✅ Final Recommendations

### Immediate Actions

1. ❌ **Delete Unused Models:**
   - Delete `app/Models/Payment.php`
   - Delete `app/Models/PaymentMethod.php`
   - Drop tables via migration

2. ✅ **Keep Active Models:**
   - PaymentIntention ← Already updated
   - PaymentTransaction ← Perfect as is
   - PaymentLog ← Perfect as is
   - UserCard ← Perfect as is

3. ✅ **Add User Relationships:**
   - Add payment relationships to User model
   - Improves code readability

### Optional Enhancements

1. **Add Investment Relationship:**
   ```php
   // PaymentIntention.php
   public function investment(): HasOne
   {
       return $this->hasOne(Investment::class, 'payment_intention_id');
   }
   ```

2. **Add Scopes to PaymentIntention:**
   ```php
   public function scopeExecuted($query)
   {
       return $query->where('is_executed', true);
   }
   
   public function scopePendingExecution($query)
   {
       return $query->where('status', 'completed')
                    ->where('is_executed', false);
   }
   
   public function scopeByType($query, string $type)
   {
       return $query->where('type', $type);
   }
   ```

---

## 🎯 Model Responsibilities (Clear Definition)

### PaymentIntention
- ✅ Store payment intent data
- ✅ Track type (investment/wallet_charge)
- ✅ Track execution status
- ✅ Store Paymob credentials (client_secret, payment_token)
- ✅ Store extras for later execution
- ✅ Link to user and transactions

### PaymentTransaction
- ✅ Store Paymob transaction data
- ✅ Track payment status
- ✅ Store merchant order ID
- ✅ Store full Paymob response
- ✅ Link to intention and user

### PaymentLog
- ✅ Log all payment operations
- ✅ Track errors and warnings
- ✅ Debugging and auditing
- ✅ Link to all related entities

### UserCard
- ✅ Store tokenized cards
- ✅ Prevent duplicate cards
- ✅ Track default card
- ✅ Track usage

### Payment ❌
- ❌ Empty, no purpose
- ❌ **DELETE**

### PaymentMethod ❌
- ❌ Empty, no purpose
- ❌ **DELETE**

---

## 📊 Database Size Analysis

```
payment_intentions ........ 112.00 KB (Active ✅)
payment_transactions ...... 48.00 KB  (Active ✅)
payment_logs .............. 64.00 KB  (Active ✅)
user_cards ................ (Active ✅)
payments .................. 16.00 KB  (Unused ❌ DELETE)
payment_methods ........... 16.00 KB  (Unused ❌ DELETE)
```

**Wasted Space:** ~32 KB from unused tables

---

## 🗑️ Cleanup Plan

### Step 1: Verify No Usage
```bash
# Check if Payment model is used anywhere
grep -r "Payment::" app/
grep -r "use App\\Models\\Payment;" app/

# Check if PaymentMethod model is used anywhere
grep -r "PaymentMethod::" app/
grep -r "use App\\Models\\PaymentMethod;" app/
```

**Result:** ✅ No usage found

### Step 2: Create Cleanup Migration
```php
php artisan make:migration drop_unused_payment_tables
```

### Step 3: Drop Tables
```php
public function up(): void
{
    Schema::dropIfExists('payment_methods');
    Schema::dropIfExists('payments');
}

public function down(): void
{
    // Recreate if needed (unlikely)
    Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });
    
    Schema::create('payment_methods', function (Blueprint $table) {
        $table->id();
        $table->timestamps();
    });
}
```

### Step 4: Delete Model Files
```bash
rm app/Models/Payment.php
rm app/Models/PaymentMethod.php
```

---

## ✅ What's Already Complete

### PaymentIntention Model ✅
- [x] Added `type` column
- [x] Added `is_executed` column
- [x] Updated $fillable array
- [x] Updated $casts array
- [x] Has proper relationships
- [x] Has helper methods

### PaymentTransaction Model ✅
- [x] All columns present
- [x] Proper relationships
- [x] Helper methods for status checks
- [x] Amount conversion helpers

### PaymentLog Model ✅
- [x] Static logging methods
- [x] Proper relationships
- [x] Context as JSON
- [x] Links to Laravel Log facade

### UserCard Model ✅
- [x] Tokenized card storage
- [x] Duplicate prevention
- [x] Default card logic
- [x] Display name helpers

---

## 📝 Summary Table

| Model | Lines | Fillable | Casts | Relations | Methods | Status |
|-------|-------|----------|-------|-----------|---------|--------|
| **PaymentIntention** | 87 | 19 fields | 5 | 2 | 3 | ✅ Complete |
| **PaymentTransaction** | 102 | 13 fields | 3 | 2 | 6 | ✅ Complete |
| **PaymentLog** | 178 | 8 fields | 1 | 3 | 4 static | ✅ Complete |
| **UserCard** | 120 | 10 fields | 3 | 1 | 5 | ✅ Complete |
| **Payment** | 11 | 0 | 0 | 0 | 0 | ❌ DELETE |
| **PaymentMethod** | 11 | 0 | 0 | 0 | 0 | ❌ DELETE |

---

## 🎯 Final Recommendations

### Immediate (Required)
1. ❌ **Delete** `Payment` model and table
2. ❌ **Delete** `PaymentMethod` model and table

### Optional (Enhancement)
1. ✅ Add payment relationships to User model
2. ✅ Add scopes to PaymentIntention
3. ✅ Add investment relationship to PaymentIntention

### Already Complete
1. ✅ PaymentIntention has `type` and `is_executed`
2. ✅ All active models have proper structure
3. ✅ All relationships defined
4. ✅ Helper methods present

---

## 🏆 Conclusion

**4 Active Models (Keep):**
- ✅ PaymentIntention - Core payment tracking
- ✅ PaymentTransaction - Transaction records
- ✅ PaymentLog - Logging and debugging
- ✅ UserCard - Saved cards

**2 Unused Models (Delete):**
- ❌ Payment - Empty, no purpose
- ❌ PaymentMethod - Empty, no purpose

**Next Step:** Delete unused models and tables to clean up the codebase.

---

**Review Status:** ✅ **COMPLETE**


