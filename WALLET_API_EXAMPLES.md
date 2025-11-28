# Wallet API Usage Examples

This document provides practical examples of how to use the Wallet API endpoints.

## Prerequisites

1. User must be authenticated with a valid Sanctum token
2. User must have an active profile (investor or owner)
3. Wallet must be created for the profile (can be done via API)

## Example Usage

### 1. Get Wallet Balance

```bash
curl -X GET "https://your-api-domain.com/api/wallet/balance" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

**Response:**
```json
{
    "success": true,
    "message": "Balance retrieved successfully",
    "result": {
        "balance": 1000.00,
        "formatted_balance": "1,000.00",
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

### 2. Create Wallet (if needed)

```bash
curl -X POST "https://your-api-domain.com/api/wallet/create" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Investment Wallet",
    "description": "Primary wallet for investment activities"
  }'
```

### 3. Deposit Money

```bash
curl -X POST "https://your-api-domain.com/api/wallet/deposit" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 500.00,
    "description": "Initial investment deposit",
    "reference": "DEP-001",
    "metadata": {
      "payment_method": "bank_transfer",
      "transaction_id": "TXN-123456"
    }
  }'
```

**Response:**
```json
{
    "success": true,
    "message": "Deposit successful",
    "result": {
        "amount": 500.00,
        "new_balance": 1500.00,
        "formatted_amount": "500.00",
        "formatted_balance": "1,500.00",
        "profile_type": "investor",
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

### 4. Withdraw Money

```bash
curl -X POST "https://your-api-domain.com/api/wallet/withdraw" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100.00,
    "description": "Investment withdrawal",
    "reference": "WTH-001",
    "metadata": {
      "withdrawal_method": "bank_transfer",
      "account_number": "****1234"
    }
  }'
```

### 5. Transfer Money to Another Profile

```bash
curl -X POST "https://your-api-domain.com/api/wallet/transfer" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "to_profile_type": "investor",
    "to_profile_id": 456,
    "amount": 200.00,
    "description": "Investment transfer",
    "reference": "TRF-001"
  }'
```

### 6. Get Transaction History

```bash
curl -X GET "https://your-api-domain.com/api/wallet/transactions?per_page=10&type=deposit&date_from=2024-01-01&date_to=2024-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
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
                "amount": 500.00,
                "formatted_amount": "500.00",
                "confirmed": true,
                "description": "Initial investment deposit",
                "reference": "DEP-001",
                "created_at": "2024-01-15T10:30:00Z",
                "formatted_date": "2024-01-15 10:30:00",
                "human_date": "2 hours ago"
            }
        ],
        "total_count": 1,
        "profile_type": "investor",
        "profile_id": 123
    }
}
```

## JavaScript/TypeScript Examples

### Using Fetch API

```javascript
class WalletAPI {
    constructor(baseURL, token) {
        this.baseURL = baseURL;
        this.token = token;
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseURL}/api/wallet${endpoint}`;
        const config = {
            headers: {
                'Authorization': `Bearer ${this.token}`,
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        const response = await fetch(url, config);
        return response.json();
    }

    async getBalance() {
        return this.request('/balance');
    }

    async deposit(amount, description, reference, metadata = {}) {
        return this.request('/deposit', {
            method: 'POST',
            body: JSON.stringify({
                amount,
                description,
                reference,
                metadata
            })
        });
    }

    async withdraw(amount, description, reference, metadata = {}) {
        return this.request('/withdraw', {
            method: 'POST',
            body: JSON.stringify({
                amount,
                description,
                reference,
                metadata
            })
        });
    }

    async transfer(toProfileType, toProfileId, amount, description, reference) {
        return this.request('/transfer', {
            method: 'POST',
            body: JSON.stringify({
                to_profile_type: toProfileType,
                to_profile_id: toProfileId,
                amount,
                description,
                reference
            })
        });
    }

    async getTransactions(filters = {}) {
        const params = new URLSearchParams(filters);
        return this.request(`/transactions?${params}`);
    }

    async createWallet(name, description, meta = {}) {
        return this.request('/create', {
            method: 'POST',
            body: JSON.stringify({
                name,
                description,
                meta
            })
        });
    }
}

// Usage
const walletAPI = new WalletAPI('https://your-api-domain.com', 'your_token');

// Get balance
const balance = await walletAPI.getBalance();
console.log('Current balance:', balance.result.balance);

// Deposit money
const deposit = await walletAPI.deposit(
    100.00,
    'Investment deposit',
    'DEP-001',
    { payment_method: 'bank_transfer' }
);
console.log('Deposit successful:', deposit.result.new_balance);

// Withdraw money
const withdraw = await walletAPI.withdraw(
    50.00,
    'Investment withdrawal',
    'WTH-001',
    { withdrawal_method: 'bank_transfer' }
);

// Get transaction history
const transactions = await walletAPI.getTransactions({
    per_page: 10,
    type: 'deposit',
    date_from: '2024-01-01',
    date_to: '2024-01-31'
});
```

### Using Axios

```javascript
import axios from 'axios';

const walletAPI = axios.create({
    baseURL: 'https://your-api-domain.com/api/wallet',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
});

// Get balance
const getBalance = async () => {
    try {
        const response = await walletAPI.get('/balance');
        return response.data;
    } catch (error) {
        console.error('Error getting balance:', error.response?.data);
        throw error;
    }
};

// Deposit money
const deposit = async (amount, description, reference, metadata = {}) => {
    try {
        const response = await walletAPI.post('/deposit', {
            amount,
            description,
            reference,
            metadata
        });
        return response.data;
    } catch (error) {
        console.error('Error depositing money:', error.response?.data);
        throw error;
    }
};

// Withdraw money
const withdraw = async (amount, description, reference, metadata = {}) => {
    try {
        const response = await walletAPI.post('/withdraw', {
            amount,
            description,
            reference,
            metadata
        });
        return response.data;
    } catch (error) {
        console.error('Error withdrawing money:', error.response?.data);
        throw error;
    }
};
```

## PHP Examples

```php
<?php

use Illuminate\Support\Facades\Http;

class WalletAPIClient
{
    private string $baseUrl;
    private string $token;

    public function __construct(string $baseUrl, string $token)
    {
        $this->baseUrl = $baseUrl;
        $this->token = $token;
    }

    private function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $response = Http::withToken($this->token)
            ->$method("{$this->baseUrl}/api/wallet{$endpoint}", $data);

        if ($response->failed()) {
            throw new Exception('API request failed: ' . $response->body());
        }

        return $response->json();
    }

    public function getBalance(): array
    {
        return $this->makeRequest('get', '/balance');
    }

    public function deposit(float $amount, string $description = null, string $reference = null, array $metadata = []): array
    {
        return $this->makeRequest('post', '/deposit', [
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
            'metadata' => $metadata
        ]);
    }

    public function withdraw(float $amount, string $description = null, string $reference = null, array $metadata = []): array
    {
        return $this->makeRequest('post', '/withdraw', [
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
            'metadata' => $metadata
        ]);
    }

    public function transfer(string $toProfileType, int $toProfileId, float $amount, string $description = null, string $reference = null): array
    {
        return $this->makeRequest('post', '/transfer', [
            'to_profile_type' => $toProfileType,
            'to_profile_id' => $toProfileId,
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference
        ]);
    }

    public function getTransactions(array $filters = []): array
    {
        $queryString = http_build_query($filters);
        return $this->makeRequest('get', "/transactions?{$queryString}");
    }

    public function createWallet(string $name = null, string $description = null, array $meta = []): array
    {
        return $this->makeRequest('post', '/create', [
            'name' => $name,
            'description' => $description,
            'meta' => $meta
        ]);
    }
}

// Usage
$walletAPI = new WalletAPIClient('https://your-api-domain.com', 'your_token');

try {
    // Get balance
    $balance = $walletAPI->getBalance();
    echo "Current balance: " . $balance['result']['balance'] . "\n";

    // Deposit money
    $deposit = $walletAPI->deposit(
        100.00,
        'Investment deposit',
        'DEP-001',
        ['payment_method' => 'bank_transfer']
    );
    echo "New balance after deposit: " . $deposit['result']['new_balance'] . "\n";

    // Withdraw money
    $withdraw = $walletAPI->withdraw(
        50.00,
        'Investment withdrawal',
        'WTH-001',
        ['withdrawal_method' => 'bank_transfer']
    );
    echo "New balance after withdrawal: " . $withdraw['result']['new_balance'] . "\n";

    // Get transactions
    $transactions = $walletAPI->getTransactions([
        'per_page' => 10,
        'type' => 'deposit'
    ]);
    echo "Transaction count: " . $transactions['result']['total_count'] . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Error Handling

Always handle potential errors when making API calls:

```javascript
try {
    const response = await walletAPI.deposit(100.00, 'Test deposit');
    
    if (response.success) {
        console.log('Deposit successful:', response.result);
    } else {
        console.error('Deposit failed:', response.message);
    }
} catch (error) {
    console.error('Network error:', error);
}
```

## Common Error Scenarios

1. **401 Unauthorized**: Token is invalid or expired
2. **400 Bad Request**: Invalid amount, insufficient balance, or validation errors
3. **404 Not Found**: Target profile not found for transfers
4. **422 Validation Error**: Invalid input data
5. **500 Internal Server Error**: Server-side error

## Best Practices

1. Always validate amounts before making requests
2. Handle network errors gracefully
3. Implement retry logic for failed requests
4. Store and refresh tokens appropriately
5. Log all wallet operations for audit purposes
6. Use meaningful descriptions and references for transactions
7. Implement proper error handling and user feedback
