# Withdrawal Module - Logic & Business Rules Summary

## Overview
The withdrawal module allows investors to request money withdrawal from their wallet to their bank accounts. The system ensures money security, prevents fraud, and tracks all withdrawal operations.

---

## Core Business Logic

### 1. **Money Withdrawal Timing**
- **Money is withdrawn IMMEDIATELY when a withdrawal request is created** (PENDING status)
- This prevents users from creating fake requests or spending money they've requested to withdraw
- Balance is reduced as soon as the request is submitted

### 2. **Withdrawal Request Lifecycle**

#### **Status Flow:**
```
PENDING → PROCESSING → COMPLETED
         ↓
      REJECTED / CANCELLED
```

#### **Status Definitions:**
- **PENDING**: Request created, awaiting admin approval
- **PROCESSING**: Admin approved, payment being processed
- **COMPLETED**: Money sent to user's bank account
- **REJECTED**: Admin rejected the request
- **CANCELLED**: Request was cancelled

### 3. **Money Withdrawal Tracking (`money_withdrawn` Flag)**

#### **Purpose:**
- Boolean flag to track if money was actually withdrawn from wallet
- Prevents processing/completing requests without money being withdrawn
- Ensures refunds only happen when money was actually withdrawn

#### **Logic:**
- **`money_withdrawn = true`**: Set immediately after `withdrawFromWallet()` succeeds when request is created
- **`money_withdrawn = false`**: Set after refund is processed (when request is rejected/cancelled)
- **Validation**: Cannot process or complete if `money_withdrawn == false`

---

## Detailed Business Rules

### **1. Request Creation (API: `createWithdrawalRequest`)**
**Rules:**
- ✅ User must have sufficient balance
- ✅ Only investor profiles can withdraw
- ✅ Money is withdrawn immediately (`withdrawFromWallet()`)
- ✅ `money_withdrawn` is set to `true` after successful withdrawal
- ✅ Request created with `PENDING` status

**Bank Account Handling:**
- Can use saved bank account or provide new bank details
- If `save_for_future = true`, bank account is saved for future use
- If `set_as_default = true`, bank account is set as default

---

### **2. Processing Request (Status: `PROCESSING`)**
**Rules:**
- ✅ Only admins can change status to `PROCESSING`
- ✅ **Validation**: Must have `money_withdrawn == true` (backend validation)
- ✅ `processed_at` timestamp is set
- ✅ `action_by` is set to current admin ID
- ❌ **Cannot process if `money_withdrawn == false`** (prevents processing requests without money)

**Frontend:**
- Button is disabled if `money_withdrawn == false`
- Tooltip shows: "Cannot process: Money was not withdrawn from wallet"

---

### **3. Completing Request (Status: `COMPLETED`)**
**Rules:**
- ✅ Only admins can change status to `COMPLETED`
- ✅ **Validation**: Must be in `PROCESSING` status first
- ✅ **Validation**: Must have `money_withdrawn == true` (backend validation)
- ✅ `completed_at` timestamp is set
- ✅ `action_by` is updated to current admin ID
- ❌ **Cannot complete if not in `PROCESSING` status**
- ❌ **Cannot complete if `money_withdrawn == false`**

**Frontend:**
- Button is disabled if `money_withdrawn == false`
- Tooltip shows: "Cannot complete: Money was not withdrawn from wallet"

---

### **4. Rejecting Request (Status: `REJECTED`)**
**Rules:**
- ✅ Admin must provide `rejection_reason` (required)
- ✅ Can reject from `PENDING` or `PROCESSING` status
- ✅ `action_by` is set to current admin ID
- ✅ **Refund Logic**: If `money_withdrawn == true`, refund money to wallet
- ✅ After refund, `money_withdrawn` is set to `false`
- ✅ Response message indicates if money was refunded or not

**Refund Process:**
```php
if ($withdrawal->money_withdrawn && $investor) {
    // Refund by depositing back to wallet
    $walletService->depositToWallet($investor, $amount, [...]);
    $withdrawal->money_withdrawn = false; // Mark as refunded
}
```

**Frontend Display:**
- Shows badge: "Money Refunded" (green) or "Money Not Refunded" (warning)
- Message explains if money was returned to wallet

---

### **5. Cancelling Request (Status: `CANCELLED`)**
**Rules:**
- ✅ Can cancel from `PENDING` status
- ✅ `action_by` is set to current admin ID
- ✅ **Refund Logic**: If `money_withdrawn == true`, refund money to wallet
- ✅ After refund, `money_withdrawn` is set to `false`
- ✅ Response message indicates if money was refunded or not

**Frontend Display:**
- Shows badge: "Money Refunded" (green) or "Money Not Refunded" (warning)
- Message explains if money was returned to wallet

---

## Security Features

### **1. Fraud Prevention**
- ✅ Money withdrawn immediately on request creation
- ✅ Users cannot spend money they've requested to withdraw
- ✅ Cannot create multiple fake requests (balance check prevents it)

### **2. Data Integrity**
- ✅ `money_withdrawn` flag tracks actual withdrawal status
- ✅ Backend validation prevents processing/completing without money
- ✅ Frontend UI disables buttons when actions aren't allowed

### **3. Audit Trail**
- ✅ `action_by`: Tracks which admin performed each action
- ✅ `processed_at`: Timestamp when request was processed
- ✅ `completed_at`: Timestamp when request was completed
- ✅ `rejection_reason`: Required reason for rejection
- ✅ `admin_notes`: Optional internal notes

---

## Key Database Fields

### **`withdrawal_requests` Table:**
- `id`: Primary key
- `user_id`: User who made the request (nullable, foreign key)
- `investor_id`: Investor profile (nullable, foreign key)
- `amount`: Withdrawal amount
- `available_balance`: Balance at time of request
- **`money_withdrawn`**: Boolean flag - tracks if money was withdrawn
- `status`: Current status (pending, processing, completed, rejected, cancelled)
- `rejection_reason`: Reason for rejection (if rejected)
- `admin_notes`: Admin internal notes
- `processed_at`: Timestamp when processed
- `completed_at`: Timestamp when completed
- **`action_by`**: Admin who performed the action (foreign key to admins)
- `reference_number`: Unique reference number (e.g., WR-XXXXXXXX)
- `bank_details`: JSON containing bank account details
- `bank_account_id`: Saved bank account reference (nullable)

---

## API Endpoints

### **1. Create Withdrawal Request**
```
POST /api/withdrawal/request
```
- Validates balance
- Withdraws money immediately
- Sets `money_withdrawn = true`
- Creates request with `PENDING` status

### **2. Get Withdrawal History**
```
GET /api/withdrawal/history
```
- Returns user's withdrawal request history

### **3. Get Banks List**
```
GET /api/withdrawal/banks
```
- Returns list of available banks

### **4. Get Bank Accounts**
```
GET /api/withdrawal/bank-accounts
```
- Returns user's saved bank accounts

### **5. Add Bank Account**
```
POST /api/withdrawal/bank-accounts
```
- Saves bank account for future use

### **6. Get Available Balance**
```
GET /api/withdrawal/balance
```
- Returns current available balance

---

## Admin Dashboard Features

### **1. DataTable**
- Lists all withdrawal requests
- Shows: Reference, User, Amount, Status, Bank Info, Dates
- Filters by status, amount range, date

### **2. View Details**
- Shows full withdrawal request information
- Displays money refund status for rejected/cancelled requests
- Action buttons based on status and `money_withdrawn` flag

### **3. Status Actions**
- **Approve & Process**: Changes status to `PROCESSING` (only if `money_withdrawn == true`)
- **Mark as Completed**: Changes status to `COMPLETED` (only if `PROCESSING` and `money_withdrawn == true`)
- **Reject**: Opens form, requires rejection reason, refunds money if withdrawn
- **Cancel**: Cancels request, refunds money if withdrawn

---

## Error Handling

### **1. Insufficient Balance**
- API returns error before creating request
- User cannot create request if balance is insufficient

### **2. Money Not Withdrawn**
- Backend validation prevents processing/completing
- Frontend disables buttons and shows tooltip
- Error message: "Cannot process/complete: Money was not withdrawn from wallet"

### **3. Invalid Status Transitions**
- Cannot complete before processing
- Error message: "Cannot complete withdrawal request that has not been processed yet"

---

## Workflow Example

### **Successful Withdrawal:**
1. User creates request for 1000 SAR
2. ✅ Money withdrawn immediately (`money_withdrawn = true`, balance -1000)
3. Admin approves → Status: `PROCESSING` (`processed_at` set)
4. Admin marks complete → Status: `COMPLETED` (`completed_at` set)
5. ✅ Money successfully sent to user's bank

### **Rejected Withdrawal:**
1. User creates request for 1000 SAR
2. ✅ Money withdrawn immediately (`money_withdrawn = true`, balance -1000)
3. Admin rejects with reason "Invalid bank details"
4. ✅ Money refunded to wallet (`money_withdrawn = false`, balance +1000)
5. Status: `REJECTED` (shows "Money Refunded" badge)

### **Cancelled Withdrawal:**
1. User creates request for 1000 SAR
2. ✅ Money withdrawn immediately (`money_withdrawn = true`, balance -1000)
3. Admin cancels
4. ✅ Money refunded to wallet (`money_withdrawn = false`, balance +1000)
5. Status: `CANCELLED` (shows "Money Refunded" badge)

---

## Key Takeaways

1. **Money is withdrawn IMMEDIATELY** when request is created (not when processed)
2. **`money_withdrawn` flag** prevents processing/completing without money
3. **Refunds are automatic** when rejecting/cancelling (only if money was withdrawn)
4. **Full audit trail** with `action_by`, timestamps, and reasons
5. **Security**: Prevents fraud by holding money as soon as request is created

---

## Migration Required

```bash
php artisan migrate
```

This will add the `money_withdrawn` boolean column to the `withdrawal_requests` table.

---

**Last Updated:** 2025-10-31  
**Version:** 1.0











