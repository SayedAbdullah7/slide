# Statistics Dashboard API Documentation

This document describes the API endpoints for the statistics dashboard that displays investment statistics, portfolio performance, and financial metrics for investors.

## Base URL
```
{{base_url}}/api/statistics
```

## Authentication
All endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {your_token}
```

## Endpoints

### 1. Get Statistics Dashboard Data
**GET** `/api/statistics/`

Get comprehensive statistics data for the investor dashboard.

**Cache:** This endpoint is cached for 30 minutes.

#### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `period` | string | No | `month` | Time period for data (`week`, `month`, `quarter`, `year`, `all`) |

#### Response
```json
{
  "success": true,
  "message": "تم جلب بيانات الإحصائيات بنجاح",
  "result": {
    "total_balance": {
      "amount": 20000.00,
      "formatted_amount": "20,000 ريال",
      "currency": "SAR"
    },
    "general_vision": {
      "investment": {
        "value": "1700000",
        "formatted": "1,700,000 ريال"
      },
      "realized_profits": {
        "value": "1275000",
        "formatted": "1,275,000 ريال"
      },
      "expected_profits": {
        "value": "70000",
        "formatted": "70,000 ريال"
      },
      "investment_count": {
        "value": "17",
        "formatted": "17"
      },
      "purchase_value": {
        "value": "1700000",
        "formatted": "1,700,000 ريال"
      },
      "distributed_investments": {
        "value": "14",
        "formatted": "14"
      },
      "profit_percentage": 75.0
    },
    "portfolio_performance": {
      "realized_profit_percentage": {
        "value": 75.0,
        "formatted": "75%",
        "progress": 75
      },
      "net_profits_so_far": {
        "value": 1275000.00,
        "formatted": "1,275,000 ريال"
      },
      "total_invested": {
        "value": 1700000.00,
        "formatted": "1,700,000 ريال"
      },
      "performance_summary": {
        "total_investments": 17,
        "active_investments": 3,
        "completed_investments": 14,
        "pending_investments": 0,
        "cancelled_investments": 0
      }
    },
    "time_period": "month",
    "date_range": {
      "start": "2024-01-01T00:00:00.000000Z",
      "end": "2024-01-31T23:59:59.000000Z",
      "label": "شهر"
    }
  }
}
```

### 2. Get Statistics Data for Specific Period
**GET** `/api/statistics/period/{period}`

Get statistics data for a specific time period.

**Cache:** This endpoint is cached for 30 minutes.

#### Path Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `period` | string | Yes | Time period (`week`, `month`, `quarter`, `year`, `all`) |

#### Response
Same structure as the main performance endpoint.

### 3. Get Investment Trends
**GET** `/api/statistics/trends`

Get investment trends over time with monthly breakdown.

**Cache:** This endpoint is cached for 30 minutes.

#### Query Parameters
| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| `months` | integer | No | `12` | Number of months to include (1-24) |

#### Response
```json
{
  "success": true,
  "message": "تم جلب اتجاهات الاستثمار بنجاح",
  "result": [
    {
      "month": "2024-01",
      "month_name": "يناير",
      "total_invested": 150000.00,
      "investment_count": 3,
      "realized_profits": 112500.00
    },
    {
      "month": "2024-02",
      "month_name": "فبراير",
      "total_invested": 200000.00,
      "investment_count": 4,
      "realized_profits": 150000.00
    }
  ]
}
```

### 4. Get Quick Statistics Summary
**GET** `/api/statistics/summary`

Get a quick summary of key statistics metrics.

**Cache:** This endpoint is cached for 30 minutes.

#### Response
```json
{
  "success": true,
  "message": "تم جلب الملخص السريع بنجاح",
  "result": {
    "total_balance": "20,000 ريال",
    "total_invested": "1,700,000 ريال",
    "realized_profits": "1,275,000 ريال",
    "profit_percentage": 75.0,
    "investment_count": "17"
  }
}
```

### 5. Get Statistics Comparison
**GET** `/api/statistics/comparison`

Compare statistics between two different periods.

**Cache:** This endpoint is cached for 30 minutes.

#### Query Parameters
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `current_period` | string | Yes | Current period (`week`, `month`, `quarter`, `year`) |
| `previous_period` | string | Yes | Previous period (`week`, `month`, `quarter`, `year`) |

#### Response
```json
{
  "success": true,
  "message": "تم جلب مقارنة الأداء بنجاح",
  "result": {
    "current_period": "month",
    "previous_period": "quarter",
    "total_balance": {
      "current": 20000.00,
      "previous": 15000.00,
      "change": 5000.00,
      "change_percentage": 33.33
    },
    "total_invested": {
      "current": 1700000.00,
      "previous": 1200000.00,
      "change": 500000.00,
      "change_percentage": 41.67
    },
    "realized_profits": {
      "current": 1275000.00,
      "previous": 900000.00,
      "change": 375000.00,
      "change_percentage": 41.67
    }
  }
}
```

### 6. Clear Statistics Cache
**DELETE** `/api/statistics/cache`

Clear all statistics cache for the authenticated investor.

#### Response
```json
{
  "success": true,
  "message": "تم مسح ذاكرة التخزين المؤقت للإحصائيات بنجاح"
}
```

### 7. Clear All Statistics Cache (Admin Only)
**DELETE** `/api/statistics/cache/all`

Clear all statistics cache for all investors. This endpoint is typically restricted to admin users.

#### Response
```json
{
  "success": true,
  "message": "تم مسح جميع ذاكرة التخزين المؤقت للإحصائيات بنجاح"
}
```

## Caching

All statistics endpoints are cached for **30 minutes** to improve performance and reduce database load. The cache keys are structured as follows:

- Main statistics: `investor_statistics_{investor_id}_{period}`
- Investment trends: `investor_trends_{investor_id}_{months}`
- Comparison data: `investor_comparison_{investor_id}`

### Cache Management

- **Automatic expiration**: Cache automatically expires after 30 minutes
- **Manual clearing**: Use the cache clearing endpoints to force refresh data
- **Investor-specific**: Each investor has their own cache namespace
- **Period-specific**: Different time periods have separate cache entries

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "يجب تسجيل الدخول أولاً",
  "error_code": 1
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "لم يتم العثور على بروفايل المستثمر",
  "error_code": 1
}
```

### 400 Bad Request
```json
{
  "success": false,
  "message": "فترة غير صحيحة",
  "errors": {
    "period": ["The selected period is invalid."]
  }
}
```

## Data Fields Explanation

### General Vision (الرؤية العامة)
- **investment**: Total amount invested in the selected period
- **realized_profits**: Profits from completed investments
- **expected_profits**: Expected profits from active/pending investments
- **investment_count**: Number of investments made
- **purchase_value**: Total value of purchases (same as investment)
- **distributed_investments**: Number of completed investments
- **profit_percentage**: Percentage of realized profits vs total invested

### Portfolio Performance (أداء المحفظة)
- **realized_profit_percentage**: Percentage of realized profits (for progress bar)
- **net_profits_so_far**: Net profits earned to date
- **total_invested**: Total amount invested
- **performance_summary**: Breakdown of investments by status

### Time Periods
- **week**: Last 7 days
- **month**: Last 30 days
- **quarter**: Last 90 days
- **year**: Last 365 days
- **all**: All time data

## Usage Examples

### Get monthly statistics data
```bash
curl -X GET "{{base_url}}/api/statistics/?period=month" \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

### Get investment trends for last 6 months
```bash
curl -X GET "{{base_url}}/api/statistics/trends?months=6" \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

### Compare current month vs previous month
```bash
curl -X GET "{{base_url}}/api/statistics/comparison?current_period=month&previous_period=month" \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

### Clear statistics cache
```bash
curl -X DELETE "{{base_url}}/api/statistics/cache" \
  -H "Authorization: Bearer {your_token}" \
  -H "Accept: application/json"
```

## Notes

1. All monetary values are in Saudi Riyals (SAR)
2. All text responses are in Arabic
3. Date ranges are calculated based on the current date
4. Profit calculations consider both direct investments (`myself`) and authorized investments (`authorize`)
5. The API automatically handles timezone conversions
6. Empty data returns zero values rather than null to maintain consistency
7. **Caching**: All endpoints are cached for 30 minutes to improve performance
8. **Cache Management**: Use the cache clearing endpoints to refresh data when needed

## Performance Optimization

- **30-minute caching**: Reduces database load and improves response times
- **Investor-specific cache**: Each investor's data is cached separately
- **Period-specific cache**: Different time periods have separate cache entries
- **Automatic cache expiration**: Cache automatically refreshes after 30 minutes
- **Manual cache clearing**: Force refresh data using cache clearing endpoints

## Rate Limiting

The API endpoints are subject to standard rate limiting. Please refer to the main API documentation for rate limiting details.

## Support

For technical support or questions about the Statistics Dashboard API, please contact the development team.
