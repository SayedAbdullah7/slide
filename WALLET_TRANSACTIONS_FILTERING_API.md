# Wallet Transactions Filtering and Pagination API

## Overview
The wallet index endpoint now supports period filtering and pagination for transactions.

## Endpoint
`GET /api/wallet`

## Parameters

### Period Filtering
- **period** (optional): Filter transactions by time period
  - `day` - Today's transactions
  - `week` - This week's transactions  
  - `month` - This month's transactions
  - `quarter` - This quarter's transactions
  - `year` - This year's transactions
  - `all` - All transactions (default)

### Type Filtering
- **type** (optional): Filter transactions by type
  - `deposit` - Only deposit transactions
  - `withdraw` - Only withdrawal transactions
  - `all` - All transaction types (default)

### Pagination
- **page** (optional): Page number (default: 1)
- **per_page** (optional): Items per page (default: 10, max: 100)

## Example Requests

### Get all transactions with default pagination
```
GET /api/wallet
```

### Get today's transactions
```
GET /api/wallet?period=day
```

### Get this week's transactions with 20 items per page
```
GET /api/wallet?period=week&per_page=20
```

### Get this month's transactions, page 2
```
GET /api/wallet?period=month&page=2
```

### Get this quarter's transactions with custom pagination
```
GET /api/wallet?period=quarter&page=1&per_page=50
```

### Get this year's transactions
```
GET /api/wallet?period=year
```

### Get only deposit transactions
```
GET /api/wallet?type=deposit
```

### Get only withdrawal transactions with pagination
```
GET /api/wallet?type=withdraw&per_page=20
```

### Get today's deposits only
```
GET /api/wallet?period=day&type=deposit
```

### Get this month's withdrawals, page 2
```
GET /api/wallet?period=month&type=withdraw&page=2
```

### Get this quarter's deposits with custom pagination
```
GET /api/wallet?period=quarter&type=deposit&per_page=50
```

## Response Format

```json
{
  "success": true,
  "message": "Wallet screen data retrieved successfully",
  "data": {
    "total_balance": {
      "amount": 15000,
      "formatted_amount": "15,000 ريال",
      "currency": "SAR",
      "is_visible": true
    },
    "realized_profits": {
      // ... existing statistics
    },
    "pending_profits": {
      // ... existing statistics  
    },
    "upcoming_earnings": {
      // ... existing statistics
    },
    "transactions": {
      "data": [
        // Array of WalletTransactionResource objects
      ],
      "pagination": {
        "current_page": 1,
        "per_page": 10,
        "total": 25,
        "last_page": 3,
        "from": 1,
        "to": 10,
        "has_more_pages": true
      },
      "filters": {
        "period": "month",
        "type": "deposit",
        "applied_filters": {
          "period": "month",
          "type": "deposit",
          "per_page": 10
        }
      }
    },
    "profile_type": "investor",
    "profile_id": 123
  }
}
```

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Invalid request parameters",
  "data": null
}
```

### No Active Profile (400)
```json
{
  "success": false,
  "message": "No active profile found",
  "data": null
}
```

## Implementation Details

### Period Filtering Logic
- **day**: Transactions from today (00:00:00 to 23:59:59)
- **week**: Transactions from start of current week to end of current week
- **month**: Transactions from current month and year
- **quarter**: Transactions from current quarter (3-month periods: Jan-Mar, Apr-Jun, Jul-Sep, Oct-Dec)
- **year**: Transactions from current year
- **all**: No date filtering applied

### Type Filtering Logic
- **deposit**: Only transactions where `type = 'deposit'`
- **withdraw**: Only transactions where `type = 'withdraw'`
- **all**: No type filtering applied

### Pagination
- Uses Laravel's built-in pagination
- Default: 10 items per page
- Maximum: 100 items per page
- Returns standard pagination metadata

### Performance Considerations
- All queries are optimized with proper indexing on `created_at` column
- Pagination reduces memory usage for large transaction sets
- Period filtering reduces query scope for better performance
