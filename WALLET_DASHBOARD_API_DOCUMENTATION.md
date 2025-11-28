# Wallet Screen API Documentation

## Overview
This API provides comprehensive wallet screen functionality matching the mobile app interface shown in the image. It includes total balance, realized profits, pending profits, upcoming earnings, and recent transactions.

## Base URL
```
/api/wallet
```

## Authentication
All endpoints require authentication using Sanctum token:
```
Authorization: Bearer {token}
```

---

## Endpoints

### 1. Get Wallet Screen
**GET** `/`

Get comprehensive wallet screen data including balance, profits, and recent transactions.

#### Response
```json
{
    "success": true,
    "message": "Wallet screen data retrieved successfully",
    "data": {
        "total_balance": {
            "amount": 20000,
            "formatted_amount": "20,000 ريال",
            "currency": "SAR",
            "is_visible": true
        },
        "realized_profits": {
            "amount": 4000,
            "formatted_amount": "4,000 ريال",
            "currency": "SAR"
        },
        "pending_profits": {
            "amount": 4000,
            "formatted_amount": "4,000 جنية",
            "currency": "EGP"
        },
        "upcoming_earnings": {
            "amount": 5000,
            "formatted_amount": "5,000 SAR",
            "currency": "SAR",
            "next_due_date": "2024-01-15",
            "formatted_due_date": "2024-01-15"
        },
        "recent_transactions": [
            {
                "id": 1,
                "type": "deposit",
                "amount": 5000,
                "formatted_amount": "+5,000 SAR",
                "description": "عائد من مشروع التقنية المالية",
                "date": "2024-12-15",
                "formatted_date": "15 ديسمبر 2024",
                "status": "completed"
            }
        ],
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

---

### 2. Get Quick Actions
**GET** `/quick-actions`

Get available quick actions for the wallet (Add Funds, Request Funds).

#### Response
```json
{
    "success": true,
    "message": "Quick actions retrieved successfully",
    "data": {
        "actions": [
            {
                "id": "add_funds",
                "title": "إضافة أموال",
                "title_en": "Add Funds",
                "icon": "plus",
                "color": "green",
                "enabled": true,
                "route": "api.wallet.deposit",
                "method": "POST"
            },
            {
                "id": "request_funds",
                "title": "طلب أموال",
                "title_en": "Request Funds",
                "icon": "arrow-up-right",
                "color": "purple",
                "enabled": true,
                "route": "api.wallet.withdraw",
                "method": "POST"
            }
        ],
        "current_balance": 20000,
        "formatted_balance": "20,000 ريال"
    }
}
```

---

### 3. Toggle Balance Visibility
**POST** `/toggle-visibility`

Toggle the visibility of the wallet balance (for the eye icon functionality).

#### Request Body
```json
{
    "is_visible": true
}
```

#### Response
```json
{
    "success": true,
    "message": "Balance visibility updated",
    "data": {
        "is_visible": true,
        "message": "Balance is now visible"
    }
}
```

---

### 4. Get Wallet Balance
**GET** `/balance`

Get the current wallet balance.

#### Response
```json
{
    "success": true,
    "message": "Balance retrieved successfully",
    "data": {
        "balance": 20000,
        "formatted_balance": "20,000.00",
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

---

### 5. Deposit Money
**POST** `/deposit`

Add money to the wallet.

#### Request Body
```json
{
    "amount": 1000,
    "description": "إيداع مبلغ إضافي",
    "reference": "DEP001",
    "metadata": {
        "source": "bank_transfer"
    }
}
```

#### Response
```json
{
    "success": true,
    "message": "Deposit successful",
    "data": {
        "amount": 1000,
        "new_balance": 21000,
        "formatted_amount": "1,000.00",
        "formatted_balance": "21,000.00",
        "profile_type": "investor",
        "timestamp": "2024-01-15T10:30:00.000000Z"
    }
}
```

---

### 6. Withdraw Money
**POST** `/withdraw`

Withdraw money from the wallet.

#### Request Body
```json
{
    "amount": 500,
    "description": "سحب مبلغ للاستثمار",
    "reference": "WTH001"
}
```

#### Response
```json
{
    "success": true,
    "message": "Withdrawal successful",
    "data": {
        "amount": 500,
        "new_balance": 19500,
        "formatted_amount": "500.00",
        "formatted_balance": "19,500.00",
        "profile_type": "investor",
        "timestamp": "2024-01-15T10:30:00.000000Z"
    }
}
```

---

### 7. Get Transactions
**GET** `/transactions`

Get wallet transaction history with filtering options.

#### Query Parameters
- `per_page` (optional): Number of transactions per page (1-100, default: 15)
- `type` (optional): Filter by transaction type (`deposit`, `withdraw`)
- `date_from` (optional): Filter from date (YYYY-MM-DD)
- `date_to` (optional): Filter to date (YYYY-MM-DD)

#### Example Request
```
GET /api/wallet/transactions?per_page=10&type=deposit&date_from=2024-01-01
```

#### Response
```json
{
    "success": true,
    "message": "Transactions retrieved successfully",
    "data": {
        "transactions": [
            {
                "id": 1,
                "type": "deposit",
                "amount": 5000,
                "formatted_amount": "+5,000 SAR",
                "description": "عائد من مشروع التقنية المالية",
                "date": "2024-12-15",
                "formatted_date": "15 ديسمبر 2024",
                "status": "completed",
                "reference": "DEP001"
            }
        ],
        "total_count": 1,
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

---

### 8. Transfer Money
**POST** `/transfer`

Transfer money to another profile's wallet.

#### Request Body
```json
{
    "to_profile_type": "investor",
    "to_profile_id": 456,
    "amount": 1000,
    "description": "تحويل للمستثمر",
    "reference": "TRF001"
}
```

#### Response
```json
{
    "success": true,
    "message": "Transfer successful",
    "data": {
        "amount": 1000,
        "new_balance": 19000,
        "formatted_amount": "1,000.00",
        "formatted_balance": "19,000.00",
        "from_profile_type": "investor",
        "to_profile_type": "investor",
        "to_profile_id": 456,
        "timestamp": "2024-01-15T10:30:00.000000Z"
    }
}
```

---

### 9. Create Wallet
**POST** `/create`

Create a wallet for the current profile.

#### Request Body
```json
{
    "name": "المحفظة الرئيسية",
    "description": "محفظة المستثمر الرئيسية",
    "meta": {
        "currency": "SAR",
        "type": "primary"
    }
}
```

#### Response
```json
{
    "success": true,
    "message": "Wallet created successfully",
    "data": {
        "profile_type": "investor",
        "profile_id": 123,
        "wallet_attributes": {
            "name": "المحفظة الرئيسية",
            "description": "محفظة المستثمر الرئيسية",
            "meta": {
                "currency": "SAR",
                "type": "primary"
            }
        }
    }
}
```

---

## Error Responses

### 400 Bad Request
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "amount": ["The amount field is required."]
    }
}
```

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "No active profile found"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Failed to retrieve wallet screen data"
}
```

---

## Business Logic

### Realized Profits (الأرباح المحققة)
- Only includes completed investments of type 'authorize' (تفويض)
- Calculated using `getActualNetProfit()` method from Investment model

### Pending Profits (الأرباح المعلقة)
- Only includes active/pending investments of type 'authorize' (تفويض)
- Calculated using `getExpectedNetProfit()` method from Investment model

### Upcoming Earnings (الأرباح القادمة)
- Shows the next investment that will complete
- Based on `expected_distribution_date` field
- Only for authorize type investments

### Recent Transactions (اخر العمليات)
- Shows last 10 transactions by default
- Includes deposits, withdrawals, and transfers
- Formatted with Arabic date and currency

---

## Mobile App Integration

This API is designed to match the mobile app interface exactly:

1. **Total Balance Card**: Shows current wallet balance with visibility toggle
2. **Profits Section**: Displays realized and pending profits
3. **Upcoming Earnings**: Shows next expected earnings with due date
4. **Quick Actions**: Add Funds and Request Funds buttons
5. **Recent Transactions**: Scrollable list of recent wallet activities

The API provides all necessary data to render the mobile app interface without additional processing.

## Architecture

### WalletStatisticsService
A dedicated service class that handles all wallet statistics calculations:
- `getAllStatistics()`: Returns all statistics in one call
- `calculateRealizedProfits()`: Calculates realized profits from completed investments
- `calculatePendingProfits()`: Calculates pending profits from active investments
- `calculateUpcomingEarnings()`: Calculates next upcoming earnings

This service is reusable across different controllers and avoids code duplication.
