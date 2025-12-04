# Bank Transfer API Documentation

## Overview
Complete API documentation for bank transfer deposit functionality. Investors can view company bank details, submit transfer requests with receipts, and view their transfer history.

**Base URL:** `https://slide.osta-app.com/api`

---

## Authentication
All authenticated endpoints require Bearer Token authentication:
```
Authorization: Bearer {token}
```

---

## API Endpoints

### 1. Get Company Bank Account Details
**Public endpoint - No authentication required**

```
GET /bank-transfer/company-account
```

**Description:**  
Get company bank account details that investors use to transfer money.

**Request:**  
No parameters required.

**Response:**
```json
{
  "success": true,
  "message": "Company bank account details retrieved successfully",
  "result": {
    "bank_name": "البنك السعودي الأول",
    "bank_name_en": "Saudi National Bank",
    "bank_code": "SNB",
    "account_number": "123456789012345",
    "iban": "SA1234567890123456789012",
    "company_name": "شركة الاستثمارات المتقدمة المحدودة",
    "company_name_en": "Advanced Investments Company Limited"
  }
}
```

**Example:**
```bash
curl -X GET https://slide.osta-app.com/api/bank-transfer/company-account
```

---

### 2. Submit Bank Transfer Request

**Authentication Required**

```
POST /bank-transfer/request
```

**Description:**  
Submit a bank transfer request with receipt upload. Money will be added to wallet after admin approval.

**Request:**
- **Content-Type:** `multipart/form-data`

**Form Data:**
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| receipt | file | Yes | Receipt file (JPG, PNG, or PDF - Max 5MB) |

**Validation Rules:**
- File must be JPG, JPEG, PNG, or PDF
- Max file size: 5MB
- Only investor profiles can submit
- User must have active profile

**Response (Success):**
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

**Error Responses:**

*Invalid file type:*
```json
{
  "success": false,
  "message": "The receipt must be a file of type: jpg, jpeg, png, pdf."
}
```

*File too large:*
```json
{
  "success": false,
  "message": "The receipt may not be greater than 5120 kilobytes."
}
```

*No active profile:*
```json
{
  "success": false,
  "message": "No active profile found"
}
```

*Not investor profile:*
```json
{
  "success": false,
  "message": "Only investor profiles can deposit funds"
}
```

**Example:**
```bash
curl -X POST https://slide.osta-app.com/api/bank-transfer/request \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "receipt=@/path/to/receipt.pdf"
```

**Postman Example:**
1. Set method to `POST`
2. Go to `Body` tab
3. Select `form-data` or `binary` mode
4. Add key `receipt` with type `File`
5. Select your receipt file
6. Send request

---

### 3. Get Bank Transfer History

**Authentication Required**

```
GET /bank-transfer/history
```

**Description:**  
Get all bank transfer requests for the authenticated user.

**Request:**  
No parameters required.

**Response:**
```json
{
  "success": true,
  "message": "Bank transfer history retrieved successfully",
  "result": {
    "history": [
      {
        "id": 1,
        "reference_number": "BT-XXXXXXXX",
        "status": "approved",
        "status_label": "Approved",
        "amount": "1,000.00",
        "bank_name": "البنك السعودي الأول",
        "receipt_file_name": "receipt.pdf",
        "receipt_url": "http://domain.com/storage/bank_transfer_receipts/receipt.pdf",
        "admin_notes": null,
        "rejection_reason": null,
        "processed_at": "2025-11-01T14:30:00.000000Z",
        "created_at": "2025-11-01T12:22:00.000000Z",
        "created_at_formatted": "2025-11-01 12:22:00"
      }
    ],
    "total": 1
  }
}
```

**Status Values:**
- `pending` - Awaiting admin review
- `approved` - Transfer approved, money added to wallet
- `rejected` - Transfer rejected by admin

**Example:**
```bash
curl -X GET https://slide.osta-app.com/api/bank-transfer/history \
  -H "Authorization: Bearer {token}"
```

---

## Complete Workflow

### For Investors (Mobile App)

#### Step 1: View Company Bank Details
```bash
GET /bank-transfer/company-account
```
Display bank account details to user for transfer.

#### Step 2: User Transfers Money
User transfers money to company bank account using:
- Bank name (from API)
- Account number
- IBAN
- Company name

#### Step 3: Upload Receipt & Submit Request
```bash
POST /bank-transfer/request
Body: receipt file (multipart/form-data)
```
Create withdrawal request. Returns unique reference number.

#### Step 4: Check Status
```bash
GET /bank-transfer/history
```
View all requests and their status.

### For Admins (Dashboard)

1. Admin reviews request in dashboard
2. Admin views uploaded receipt
3. Admin fills in transfer details:
   - Bank used
   - Transfer reference number
   - Amount
   - Admin notes (optional)
4. Admin approves → Money automatically added to wallet
5. OR Admin rejects → Request denied

---

## Postman Import

### Import Collection
1. Open Postman
2. Click **Import**
3. Select `Bank_Transfer_API_Postman_Collection.json`

### Import Environment
1. Click **Environments** in Postman
2. Click **Import**
3. Select `Bank_Transfer_API_Environment.postman_environment.json`

### Set Up Authentication
1. Import collection and environment
2. Set environment variable `auth_token` with your Bearer token
3. Get token from login endpoint or user dashboard

---

## Testing Guide

### Test Scenario 1: Public Endpoint (No Auth)
1. Call `GET /bank-transfer/company-account`
2. Should return company bank details
3. No authentication required

### Test Scenario 2: Submit Transfer Request
1. Login to get auth token
2. Call `POST /bank-transfer/request`
3. Upload a receipt file (JPG/PNG/PDF)
4. Should return request ID and reference number

### Test Scenario 3: Get History
1. Use auth token
2. Call `GET /bank-transfer/history`
3. Should return all user's transfer requests

### Test Scenario 4: File Validation
1. Try uploading unsupported file type (e.g., .txt)
2. Should return validation error
3. Try uploading file > 5MB
4. Should return file size error

### Test Scenario 5: Permission Check
1. Try submitting request with non-investor profile
2. Should return "Only investor profiles can deposit funds" error

---

## Error Codes

| HTTP Status | Description |
|-------------|-------------|
| 200 | Success |
| 400 | Bad Request - Validation error |
| 401 | Unauthorized - Invalid or missing token |
| 403 | Forbidden - Permission denied |
| 404 | Not Found |
| 422 | Unprocessable Entity - Validation failed |
| 500 | Internal Server Error |

---

## File Upload Specifications

**Supported Formats:**
- JPG / JPEG
- PNG
- PDF

**Max File Size:** 5MB

**Storage:**  
Files stored in `storage/app/public/bank_transfer_receipts/`

**Access:**  
Receipt URLs: `{base_url}/storage/bank_transfer_receipts/{filename}`

---

## Notes

1. **Public Endpoint:** Company bank account endpoint is public - no authentication needed
2. **Profile Type:** Only investor profiles can submit transfer requests
3. **Wallet Access:** User must have a wallet created
4. **Admin Approval:** Money is NOT added to wallet until admin approves
5. **Unique Reference:** Each request gets auto-generated unique reference number
6. **Status Tracking:** Full status history in transfer history

---

## Support

For issues or questions:
- Check dashboard for request status
- Verify file upload requirements
- Ensure valid authentication token
- Confirm investor profile is active














