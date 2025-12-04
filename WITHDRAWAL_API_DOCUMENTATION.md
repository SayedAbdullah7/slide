# Withdrawal API Documentation

**Date:** October 29, 2025  
**Status:** ✅ **COMPLETE**

---

## 📋 Overview

This API provides endpoints for investors to withdraw money from their wallet to bank accounts. The system supports saving bank accounts for future use and creating withdrawal requests that require admin approval.

**Base URL:** `/api/withdrawal`  
**Authentication:** All endpoints require `auth:sanctum` middleware

---

## 🏦 Available Endpoints

### 1. Get Available Balance
Get the available balance for withdrawal (investor profile only)

**Endpoint:** `GET /api/withdrawal/available-balance`

**Authentication:** Required (Bearer Token)

**Response:**
```json
{
  "success": true,
  "message": "Available balance retrieved successfully",
  "result": {
    "available_balance": 20000.00,
    "formatted_balance": "20,000.00 ريال",
    "currency": "SAR",
    "processing_time": "معالجة العملية تستغرق من يومين إلى ٥ أيام عمل",
    "processing_time_en": "Processing takes 2 to 5 business days"
  }
}
```

---

### 2. Get List of Saudi Banks
Get the list of available Saudi banks

**Endpoint:** `GET /api/withdrawal/banks`

**Authentication:** Required (Bearer Token)

**Response:**
```json
{
  "success": true,
  "message": "Banks list retrieved successfully",
  "result": {
    "banks": [
      {
        "id": 1,
        "code": "RIBL",
        "name_ar": "بنك الرياض",
        "name_en": "Riyad Bank",
        "icon": null
      },
      {
        "id": 2,
        "code": "NCBK",
        "name_ar": "البنك الأهلي السعودي",
        "name_en": "Saudi National Bank",
        "icon": null
      },
      // ... more banks
    ]
  }
}
```

**Available Banks:**
- Riyad Bank (بنك الرياض)
- Saudi National Bank (البنك الأهلي السعودي)
- Al Rajhi Bank (مصرف الراجحي)
- Alinma Bank (مصرف الإنماء)
- Bank Albilad (بنك البلاد)
- Banque Saudi Fransi (بنك السعودية الفرنسي)
- SABB (البنك السعودي البريطاني)
- Saudi Investment Bank (بنك السعودية للاستثمار)
- Bank AlJazira (بنك الجزيرة)
- Injaz Bank (بنك إنجاز)

---

### 3. Get Saved Bank Accounts
Get all saved bank accounts for the authenticated user

**Endpoint:** `GET /api/withdrawal/bank-accounts`

**Authentication:** Required (Bearer Token)

**Response:**
```json
{
  "success": true,
  "message": "Bank accounts retrieved successfully",
  "result": {
    "bank_accounts": [
      {
        "id": 1,
        "bank_name": "البنك الأهلي السعودي",
        "bank_name_en": "Saudi National Bank",
        "bank_code": "NCBK",
        "masked_account_number": "****1234",
        "account_number": "****1234",
        "is_default": true
      },
      {
        "id": 2,
        "bank_name": "مصرف الراجحي",
        "bank_name_en": "Al Rajhi Bank",
        "bank_code": "RJHI",
        "masked_account_number": "****5678",
        "account_number": "****5678",
        "is_default": false
      }
    ],
    "count": 2
  }
}
```

---

### 4. Add New Bank Account
Add a new bank account to user's saved accounts

**Endpoint:** `POST /api/withdrawal/bank-accounts`

**Authentication:** Required (Bearer Token)

**Request Body:**
```json
{
  "bank_name": "البنك الأهلي السعودي",
  "bank_name_en": "Saudi National Bank",
  "bank_code": "NCBK",
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519",
  "account_number": "1234",
  "save_for_future": true,
  "set_as_default": false
}
```

**Validation Rules:**
- `bank_name` (required): Bank name in Arabic
- `bank_name_en` (optional): Bank name in English
- `bank_code` (optional): Bank code/identifier
- `account_holder_name` (required): Full name of account holder
- `iban` (required): Saudi IBAN (24 characters, starts with SA)
- `account_number` (optional): Last 4 digits for display
- `save_for_future` (optional, boolean): Whether to save this account
- `set_as_default` (optional, boolean): Set as default account

**IBAN Format:**
- Must be 24 characters
- Must start with "SA"
- Format: `SA` + 22 digits
- Example: `SA0380000000608010167519`

**Response (if saved):**
```json
{
  "success": true,
  "message": "Bank account saved successfully",
  "result": {
    "bank_account": {
      "id": 1,
      "bank_name": "البنك الأهلي السعودي",
      "bank_name_en": "Saudi National Bank",
      "masked_account_number": "****1234",
      "is_default": false
    }
  }
}
```

**Response (if not saved, just validated):**
```json
{
  "success": true,
  "message": "Bank account validated successfully",
  "result": {
    "bank_details": {
      "bank_name": "البنك الأهلي السعودي",
      "account_holder_name": "أحمد محمد علي",
      "iban": "SA0380000000608010167519"
    }
  }
}
```

---

### 5. Create Withdrawal Request
Create a withdrawal request from wallet to bank account

**Endpoint:** `POST /api/withdrawal/request`

**Authentication:** Required (Bearer Token)

**Request Body (using saved account):**
```json
{
  "amount": 5000.00,
  "bank_account_id": 1,
  "terms_accepted": true
}
```

**Request Body (with new bank details):**
```json
{
  "amount": 5000.00,
  "bank_name": "البنك الأهلي السعودي",
  "bank_name_en": "Saudi National Bank",
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519",
  "terms_accepted": true
}
```

**Validation Rules:**
- `amount` (required): Withdrawal amount (min: 0.01)
- `bank_account_id` (optional): ID of saved bank account (if not provided, bank details required)
- `bank_name` (required if no bank_account_id): Bank name in Arabic
- `bank_name_en` (optional): Bank name in English
- `bank_code` (optional): Bank code
- `account_holder_name` (required if no bank_account_id): Account holder name
- `iban` (required if no bank_account_id): Valid Saudi IBAN
- `terms_accepted` (required): Must be `true` (user accepted terms)

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal request created successfully",
  "result": {
    "withdrawal_request": {
      "id": 1,
      "reference_number": "WR-A1B2C3D4",
      "amount": 5000.00,
      "formatted_amount": "5,000.00 ريال",
      "status": "pending",
      "available_balance": 20000.00,
      "bank_details": {
        "bank_name": "البنك الأهلي السعودي",
        "bank_name_en": "Saudi National Bank",
        "account_holder_name": "أحمد محمد علي",
        "iban": "SA0380000000608010167519",
        "masked_account_number": "****1234"
      },
      "processing_time": "معالجة العملية تستغرق من يومين إلى ٥ أيام عمل",
      "created_at": "2025-10-29T17:30:00.000000Z"
    }
  }
}
```

**Error Responses:**
```json
// Insufficient balance
{
  "success": false,
  "message": "Insufficient balance",
  "errors": {
    "available_balance": 1000.00,
    "requested_amount": 5000.00,
    "shortfall": 4000.00
  }
}
```

---

### 6. Get Withdrawal History
Get withdrawal request history for the authenticated user

**Endpoint:** `GET /api/withdrawal/history`

**Authentication:** Required (Bearer Token)

**Query Parameters:**
- `per_page` (optional): Items per page (default: 15)
- `status` (optional): Filter by status (`pending`, `processing`, `completed`, `rejected`, `cancelled`)

**Example:** `GET /api/withdrawal/history?per_page=10&status=pending`

**Response:**
```json
{
  "success": true,
  "message": "Withdrawal history retrieved successfully",
  "result": {
    "withdrawal_requests": [
      {
        "id": 1,
        "reference_number": "WR-A1B2C3D4",
        "amount": 5000.00,
        "formatted_amount": "5,000.00 ريال",
        "status": "pending",
        "status_label": {
          "ar": "قيد الانتظار",
          "en": "Pending"
        },
        "bank_details": {
          "bank_name": "البنك الأهلي السعودي",
          "account_holder_name": "أحمد محمد علي",
          "iban": "SA0380000000608010167519"
        },
        "available_balance": 20000.00,
        "rejection_reason": null,
        "created_at": "2025-10-29T17:30:00.000000Z",
        "processed_at": null,
        "completed_at": null
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 1,
      "last_page": 1
    }
  }
}
```

**Status Values:**
- `pending`: قيد الانتظار (Waiting)
- `processing`: قيد المعالجة (Processing)
- `completed`: مكتمل (Completed)
- `rejected`: مرفوض (Rejected)
- `cancelled`: ملغي (Cancelled)

---

## 🔐 Authentication

All endpoints require authentication using Laravel Sanctum:

```
Authorization: Bearer {token}
```

Get token from login endpoint: `POST /api/auth/login`

---

## 📝 Notes

1. **Investor Profile Only:** Only users with investor profiles can withdraw funds
2. **Balance Check:** The system checks available balance before creating withdrawal request
3. **Processing Time:** Withdrawal requests typically take 2-5 business days to process
4. **Bank Account Storage:** Bank accounts can be saved for future use or used one-time
5. **Status Flow:** 
   - `pending` → `processing` → `completed` / `rejected`
   - User can cancel `pending` requests
6. **IBAN Validation:** Saudi IBAN must be 24 characters starting with "SA"

---

## 🧪 Example Usage Flow

### Step 1: Get Available Balance
```bash
GET /api/withdrawal/available-balance
Authorization: Bearer {token}
```

### Step 2: Get Saved Bank Accounts (optional)
```bash
GET /api/withdrawal/bank-accounts
Authorization: Bearer {token}
```

### Step 3a: Use Saved Account
```bash
POST /api/withdrawal/request
Authorization: Bearer {token}
Content-Type: application/json

{
  "amount": 5000.00,
  "bank_account_id": 1,
  "terms_accepted": true
}
```

### Step 3b: Or Add New Account and Use It
```bash
POST /api/withdrawal/bank-accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "bank_name": "البنك الأهلي السعودي",
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519",
  "save_for_future": true
}

# Then use the returned bank_account.id in withdrawal request
```

### Step 4: Check Withdrawal History
```bash
GET /api/withdrawal/history
Authorization: Bearer {token}
```

---

## 🗄️ Database Models

### BankAccount Model
- Stores user's saved bank accounts
- Supports default account flag
- Masks account numbers for display

### WithdrawalRequest Model
- Tracks all withdrawal requests
- Stores bank details at time of request
- Tracks status and processing times
- Generates unique reference numbers

---

## ⚠️ Error Responses

All error responses follow this format:

```json
{
  "success": false,
  "message": "Error message",
  "error_code": 1,
  "errors": {
    "field_name": ["Error message"]
  }
}
```

**Common Error Codes:**
- `400`: Bad Request (validation errors, insufficient balance)
- `401`: Unauthorized (invalid or missing token)
- `404`: Not Found (bank account not found)
- `422`: Validation Error
- `500`: Internal Server Error

---

## 📅 Processing Timeline

Withdrawal requests follow this timeline:
1. **Pending:** Request created, awaiting admin review
2. **Processing:** Admin approved, funds being transferred
3. **Completed:** Funds transferred successfully (2-5 business days)
4. **Rejected:** Request denied (with reason)

---

**Last Updated:** October 29, 2025





















