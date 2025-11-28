# Wallet API Documentation

This document describes the advanced wallet API endpoints for deposit and withdrawal operations from authenticated users.

## Authentication

All wallet endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your_token}
```

## Base URL

```
/api/wallet
```

## Endpoints

### 1. Get Wallet Balance

Retrieve the current balance of the authenticated user's active profile wallet.

**Endpoint:** `GET /api/wallet/balance`

**Headers:**
- `Authorization: Bearer {token}`

**Response:**
```json
{
    "success": true,
    "message": "Balance retrieved successfully",
    "result": {
        "balance": 1250.50,
        "formatted_balance": "1,250.50",
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

### 2. Deposit Money

Deposit money into the authenticated user's active profile wallet.

**Endpoint:** `POST /api/wallet/deposit`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
    "amount": 100.00,
    "description": "Initial deposit",
    "reference": "DEP-001",
    "metadata": {
        "payment_method": "bank_transfer",
        "transaction_id": "TXN-123456"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Deposit successful",
    "result": {
        "amount": 100.00,
        "new_balance": 1350.50,
        "formatted_amount": "100.00",
        "formatted_balance": "1,350.50",
        "profile_type": "investor",
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

**Validation Rules:**
- `amount`: required, numeric, min: 0.01, max: 999999.99
- `description`: optional, string, max: 255 characters
- `reference`: optional, string, max: 100 characters
- `metadata`: optional, array of key-value pairs

### 3. Withdraw Money

Withdraw money from the authenticated user's active profile wallet.

**Endpoint:** `POST /api/wallet/withdraw`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
    "amount": 50.00,
    "description": "Investment withdrawal",
    "reference": "WTH-001",
    "metadata": {
        "withdrawal_method": "bank_transfer",
        "account_number": "****1234"
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Withdrawal successful",
    "result": {
        "amount": 50.00,
        "new_balance": 1300.50,
        "formatted_amount": "50.00",
        "formatted_balance": "1,300.50",
        "profile_type": "investor",
        "timestamp": "2024-01-15T11:00:00Z"
    }
}
```

**Error Response (Insufficient Balance):**
```json
{
    "success": false,
    "message": "Insufficient balance",
    "result": {
        "current_balance": 25.00,
        "requested_amount": 50.00,
        "shortfall": 25.00
    }
}
```

### 4. Transfer Money

Transfer money from the authenticated user's wallet to another profile's wallet.

**Endpoint:** `POST /api/wallet/transfer`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
    "to_profile_type": "investor",
    "to_profile_id": 456,
    "amount": 75.00,
    "description": "Investment transfer",
    "reference": "TRF-001"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Transfer successful",
    "result": {
        "amount": 75.00,
        "new_balance": 1225.50,
        "formatted_amount": "75.00",
        "formatted_balance": "1,225.50",
        "from_profile_type": "investor",
        "to_profile_type": "investor",
        "to_profile_id": 456,
        "timestamp": "2024-01-15T11:30:00Z"
    }
}
```

**Validation Rules:**
- `to_profile_type`: required, must be "investor" or "owner"
- `to_profile_id`: required, integer, must exist in the target profile table
- `amount`: required, numeric, min: 0.01, max: 999999.99
- `description`: optional, string, max: 255 characters
- `reference`: optional, string, max: 100 characters

### 5. Get Transaction History

Retrieve the transaction history for the authenticated user's wallet.

**Endpoint:** `GET /api/wallet/transactions`

**Headers:**
- `Authorization: Bearer {token}`

**Query Parameters:**
- `per_page`: optional, integer, min: 1, max: 100 (default: 15)
- `type`: optional, must be "deposit" or "withdraw"
- `date_from`: optional, date (YYYY-MM-DD format)
- `date_to`: optional, date (YYYY-MM-DD format), must be after or equal to date_from

**Example Request:**
```
GET /api/wallet/transactions?per_page=10&type=deposit&date_from=2024-01-01&date_to=2024-01-31
```

**Response:**
```json
{
    "success": true,
    "message": "Transactions retrieved successfully",
    "result": {
        "transactions": [
            {
                "id": 1,
                "uuid": "123e4567-e89b-12d3-a456-426614174000",
                "type": "deposit",
                "amount": 100.00,
                "formatted_amount": "100.00",
                "confirmed": true,
                "meta": {
                    "description": "Initial deposit",
                    "reference": "DEP-001",
                    "user_id": 123,
                    "profile_type": "investor",
                    "timestamp": "2024-01-15T10:30:00Z"
                },
                "description": "Initial deposit",
                "reference": "DEP-001",
                "created_at": "2024-01-15T10:30:00Z",
                "formatted_date": "2024-01-15 10:30:00",
                "human_date": "2 hours ago",
                "wallet": {
                    "id": 1,
                    "name": "Default Wallet",
                    "slug": "default",
                    "balance": 1350.50,
                    "formatted_balance": "1,350.50"
                },
                "profile_info": {
                    "user_id": 123,
                    "profile_type": "investor"
                }
            }
        ],
        "total_count": 1,
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

### 6. Create Wallet

Create a wallet for the authenticated user's active profile (if it doesn't exist).

**Endpoint:** `POST /api/wallet/create`

**Headers:**
- `Authorization: Bearer {token}`
- `Content-Type: application/json`

**Request Body:**
```json
{
    "name": "My Investment Wallet",
    "description": "Primary wallet for investment activities",
    "meta": {
        "wallet_type": "investment",
        "auto_create": true
    }
}
```

**Response:**
```json
{
    "success": true,
    "message": "Wallet created successfully",
    "result": {
        "profile_type": "investor",
        "profile_id": 123,
        "wallet_attributes": {
            "name": "My Investment Wallet",
            "description": "Primary wallet for investment activities",
            "meta": {
                "wallet_type": "investment",
                "auto_create": true
            }
        }
    }
}
```

## Error Responses

### Common Error Responses

**401 Unauthorized:**
```json
{
    "success": false,
    "message": "Unauthorized"
}
```

**400 Bad Request:**
```json
{
    "success": false,
    "message": "Insufficient balance",
    "result": {
        "current_balance": 25.00,
        "requested_amount": 50.00,
        "shortfall": 25.00
    }
}
```

**422 Validation Error:**
```json
{
    "success": false,
    "message": "The given data was invalid.",
    "errors": {
        "amount": [
            "The amount field is required."
        ],
        "to_profile_type": [
            "The target profile type must be either investor or owner."
        ]
    }
}
```

**500 Internal Server Error:**
```json
{
    "success": false,
    "message": "Deposit failed. Please try again."
}
```

## Security Features

1. **Authentication Required**: All endpoints require valid Sanctum token
2. **Profile Validation**: Operations are performed on the authenticated user's active profile
3. **Balance Validation**: Withdrawal operations check for sufficient balance
4. **Transaction Logging**: All operations are logged for audit purposes
5. **Database Transactions**: All operations use database transactions for consistency
6. **Input Validation**: Comprehensive validation rules for all inputs
7. **Rate Limiting**: Can be implemented using Laravel's rate limiting middleware

## Usage Examples

### JavaScript/TypeScript Example

```typescript
const API_BASE = 'https://your-api-domain.com/api';
const token = 'your_sanctum_token';

// Get balance
const getBalance = async () => {
    const response = await fetch(`${API_BASE}/wallet/balance`, {
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        }
    });
    return response.json();
};

// Deposit money
const deposit = async (amount: number, description?: string) => {
    const response = await fetch(`${API_BASE}/wallet/deposit`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            amount,
            description,
            reference: `DEP-${Date.now()}`
        })
    });
    return response.json();
};

// Withdraw money
const withdraw = async (amount: number, description?: string) => {
    const response = await fetch(`${API_BASE}/wallet/withdraw`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            amount,
            description,
            reference: `WTH-${Date.now()}`
        })
    });
    return response.json();
};

// Get transaction history
const getTransactions = async (filters?: {
    per_page?: number;
    type?: 'deposit' | 'withdraw';
    date_from?: string;
    date_to?: string;
}) => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`${API_BASE}/wallet/transactions?${params}`, {
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });
    return response.json();
};
```

### PHP Example

```php
use Illuminate\Support\Facades\Http;

$token = 'your_sanctum_token';
$baseUrl = 'https://your-api-domain.com/api';

// Get balance
$balance = Http::withToken($token)
    ->get("$baseUrl/wallet/balance")
    ->json();

// Deposit money
$deposit = Http::withToken($token)
    ->post("$baseUrl/wallet/deposit", [
        'amount' => 100.00,
        'description' => 'Initial deposit',
        'reference' => 'DEP-' . time()
    ])
    ->json();

// Withdraw money
$withdraw = Http::withToken($token)
    ->post("$baseUrl/wallet/withdraw", [
        'amount' => 50.00,
        'description' => 'Investment withdrawal',
        'reference' => 'WTH-' . time()
    ])
    ->json();
```

## Notes

1. All monetary amounts are handled as floats with 2 decimal places
2. The system uses the Bavix Wallet package for wallet functionality
3. Each profile (investor/owner) has its own separate wallet
4. All operations are atomic and use database transactions
5. Transaction history includes comprehensive metadata
6. The API supports both investor and owner profile types
7. All timestamps are in ISO 8601 format (UTC)

