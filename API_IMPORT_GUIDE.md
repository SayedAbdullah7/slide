# API Import Guide - Withdrawal APIs

This guide explains how to import the Withdrawal APIs into Postman or APIDog using the provided files.

**Base URL:** `https://slide.osta-app.com`

---

## 📦 Files Available

1. **Withdrawal_API_Postman_Collection.json** - Postman Collection v2.1
2. **Withdrawal_API_Environment.postman_environment.json** - Postman Environment
3. **Withdrawal_API_OpenAPI.yaml** - OpenAPI 3.0 Specification (works with both Postman and APIDog)

---

## 🔵 Postman Import

### Method 1: Import Collection Directly

1. Open Postman
2. Click **Import** button (top left)
3. Select **File** tab
4. Choose `Withdrawal_API_Postman_Collection.json`
5. Click **Import**

### Method 2: Import OpenAPI Spec

1. Open Postman
2. Click **Import** button
3. Select **File** tab
4. Choose `Withdrawal_API_OpenAPI.yaml`
5. Click **Import**
6. Postman will automatically create the collection

### Setup Environment

1. Click **Environments** in left sidebar
2. Click **Import**
3. Select `Withdrawal_API_Environment.postman_environment.json`
4. Click **Import**
5. Select the imported environment from dropdown (top right)
6. Set your `auth_token` value after logging in

### Authentication Setup

1. Get your authentication token by logging in via:
   ```
   POST https://slide.osta-app.com/api/auth/login
   ```
2. Copy the `access_token` from response
3. In Postman environment, set `auth_token` variable to your token value
4. Collection is pre-configured to use Bearer token authentication

---

## 🟢 APIDog Import

### Import OpenAPI Specification

1. Open APIDog
2. Click **Import** or **+ New** > **Import**
3. Select **OpenAPI/Swagger**
4. Choose **File** and select `Withdrawal_API_OpenAPI.yaml`
5. Click **Import**
6. APIDog will automatically create the API project

### Setup Environment Variables

1. Go to **Environments** section
2. Create new environment or edit default
3. Add variables:
   - `base_url`: `https://slide.osta-app.com`
   - `auth_token`: (your token after login)

### Configure Authentication

1. In APIDog project settings
2. Go to **Authentication**
3. Select **Bearer Token** type
4. Set token variable: `{{auth_token}}`

---

## 📋 Available Endpoints

### 1. Get Available Balance
```
GET /api/withdrawal/available-balance
```

### 2. Get Banks List
```
GET /api/withdrawal/banks
```

### 3. Get Saved Bank Accounts
```
GET /api/withdrawal/bank-accounts
```

### 4. Add Bank Account
```
POST /api/withdrawal/bank-accounts
Body: {
  "bank_id": 1,
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519",
  "save_for_future": true,
  "set_as_default": false
}
```

### 5. Create Withdrawal Request
```
POST /api/withdrawal/request
Body (Option 1 - Using saved account): {
  "amount": 5000.00,
  "bank_account_id": 1
}

Body (Option 2 - New bank details): {
  "amount": 5000.00,
  "bank_id": 1,
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519"
}
```

### 6. Get Withdrawal History
```
GET /api/withdrawal/history?per_page=15&status=pending
```

---

## 🔑 Authentication

All endpoints require authentication using Laravel Sanctum:

**Header:**
```
Authorization: Bearer {your_token}
```

**Get Token:**
```bash
POST https://slide.osta-app.com/api/auth/login
Body: {
  "email": "user@example.com",
  "password": "password"
}
```

Response contains `access_token` to use in subsequent requests.

---

## 🧪 Testing Workflow

### Recommended Flow:

1. **Login** → Get `access_token`
2. **Set token** in environment variable
3. **Get Available Balance** → Check wallet balance
4. **Get Banks List** → Select a bank
5. **Add Bank Account** → Save bank account (or skip if using new details)
6. **Create Withdrawal Request** → Submit withdrawal
7. **Get Withdrawal History** → Check request status

---

## 📝 Example Requests

### Add Bank Account (Save)
```json
{
  "bank_id": 1,
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519",
  "save_for_future": true,
  "set_as_default": true
}
```

### Create Withdrawal (Saved Account)
```json
{
  "amount": 5000.00,
  "bank_account_id": 1
}
```

### Create Withdrawal (New Account)
```json
{
  "amount": 5000.00,
  "bank_id": 1,
  "account_holder_name": "أحمد محمد علي",
  "iban": "SA0380000000608010167519"
}
```

---

## ⚠️ Notes

1. **IBAN Format**: Must be 24 characters, starting with "SA"
2. **Minimum Amount**: 0.01 SAR
3. **Investor Only**: Only users with investor profiles can withdraw
4. **Balance Check**: System validates sufficient balance before creating request
5. **Processing Time**: 2-5 business days typically

---

## 🔄 Status Values

- `pending` - Request submitted, awaiting admin review
- `processing` - Admin approved, funds being transferred
- `completed` - Funds transferred successfully
- `rejected` - Request denied (with reason)
- `cancelled` - Request cancelled

---

**Last Updated:** October 29, 2025


