# Bank Transfer API Import Guide

This guide explains how to import the Bank Transfer APIs into Postman or APIDog using the provided files.

**Base URL:** `https://slide.osta-app.com`

---

## 📦 Files Available

1. **Bank_Transfer_API_Postman_Collection.json** - Postman Collection v2.1
2. **Bank_Transfer_API_Environment.postman_environment.json** - Postman Environment
3. **Bank_Transfer_API_Documentation.md** - Complete API Documentation

---

## 🔵 Postman Import

### Method 1: Import Collection Directly

1. Open Postman
2. Click **Import** button (top left)
3. Select **File** tab
4. Choose `Bank_Transfer_API_Postman_Collection.json`
5. Click **Import**

### Setup Environment

1. Click **Environments** in left sidebar
2. Click **Import**
3. Select `Bank_Transfer_API_Environment.postman_environment.json`
4. Click **Import**
5. Select the imported environment from dropdown (top right)
6. Set your `auth_token` value after logging in

### Authentication Setup

1. Get your authentication token by logging in via:
   ```
   POST https://slide.osta-app.com/api/user/auth/verify-otp
   ```
   Or get token from your existing session

2. Copy the token from response

3. In Postman environment, update the `auth_token` variable with your token

---

## 📱 How to Use

### 1. Test Public Endpoint (No Auth)
```
GET {{base_url}}/api/bank-transfer/company-account
```
- No authentication needed
- Returns company bank details

### 2. Submit Bank Transfer Request
```
POST {{base_url}}/api/bank-transfer/request
```
**Steps:**
1. Select the request in Postman
2. Go to **Body** tab
3. Select **form-data**
4. Add key: `receipt`, Type: `File`
5. Click **Select Files** and choose your receipt (JPG/PNG/PDF)
6. Click **Send**

### 3. Get Transfer History
```
GET {{base_url}}/api/bank-transfer/history
```
- Returns all your transfer requests
- Shows status, amounts, dates

---

## ✅ Test Checklist

- [ ] Import collection into Postman
- [ ] Import environment
- [ ] Set authentication token
- [ ] Test "Get Company Bank Account" (should work without auth)
- [ ] Test "Submit Bank Transfer Request" with valid file
- [ ] Test with invalid file type (should fail)
- [ ] Test with large file > 5MB (should fail)
- [ ] Test "Get Bank Transfer History" (should show your requests)

---

## 🎯 Expected Results

### Successful Transfer Submission
```json
{
  "success": true,
  "message": "Bank transfer request submitted successfully",
  "result": {
    "bank_transfer_request": {
      "id": 1,
      "reference_number": "BT-XXXXXXXX",
      "status": "pending",
      "receipt_file": "receipt.pdf",
      "message": "Your bank transfer request has been submitted. Our team will review it shortly.",
      "created_at": "2025-11-01T12:22:00.000000Z"
    }
  }
}
```

### File Upload Error
```json
{
  "success": false,
  "message": "The receipt must be a file of type: jpg, jpeg, png, pdf."
}
```

---

## 📝 Notes

1. **Public Endpoint:** Company bank account endpoint requires no authentication
2. **File Upload:** Use form-data mode in Postman, not raw JSON
3. **File Size:** Maximum 5MB
4. **File Types:** Only JPG, PNG, PDF supported
5. **Profile:** Only investor profiles can submit transfer requests
6. **Wallet:** Money added after admin approval only

---

## 🔗 Related APIs

For full wallet operations, see:
- Withdrawal APIs: `Withdrawal_API_Postman_Collection.json`
- Wallet APIs: Built-in Laravel routes

---

## Support

For issues:
1. Check file upload size and type
2. Verify authentication token is valid
3. Ensure investor profile is active
4. Review error messages for specific issues











