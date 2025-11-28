# Investment Opportunity Reminder API Documentation

## Overview
This API allows investors to set reminders for coming investment opportunities. When an opportunity becomes available for investment, investors who have set reminders will be notified.

## Authentication
All endpoints require authentication using Sanctum tokens. Include the token in the Authorization header:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. Get All Reminders
**GET** `/api/investor/reminders`

Get all reminders for the authenticated investor.

**Query Parameters:**
- `per_page` (optional): Number of items per page (default: 15)
- `status` (optional): Filter by status - `all`, `active`, `sent` (default: `all`)

**Response:**
```json
{
    "success": true,
    "message": "تم جلب التذكيرات بنجاح",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "investor_profile_id": 1,
                "investment_opportunity_id": 5,
                "is_active": true,
                "reminder_sent_at": null,
                "created_at": "2025-09-20T23:30:00.000000Z",
                "updated_at": "2025-09-20T23:30:00.000000Z",
                "investment_opportunity": {
                    "id": 5,
                    "name": "Real Estate Project",
                    "category": {...},
                    "owner_profile": {...}
                }
            }
        ],
        "total": 10
    }
}
```

### 2. Add Reminder
**POST** `/api/investor/reminders`

Add a reminder for a coming investment opportunity.

**Request Body:**
```json
{
    "investment_opportunity_id": 5
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم إضافة التذكير بنجاح",
    "data": {
        "reminder": {
            "id": 1,
            "investor_profile_id": 1,
            "investment_opportunity_id": 5,
            "is_active": true,
            "reminder_sent_at": null,
            "created_at": "2025-09-20T23:30:00.000000Z",
            "updated_at": "2025-09-20T23:30:00.000000Z",
            "investment_opportunity": {
                "id": 5,
                "name": "Real Estate Project",
                "category": {...},
                "owner_profile": {...}
            }
        }
    }
}
```

### 3. Get Coming Opportunities
**GET** `/api/investor/reminders/coming-opportunities`

Get coming investment opportunities that can have reminders set.

**Query Parameters:**
- `per_page` (optional): Number of items per page (default: 15)

**Response:**
```json
{
    "success": true,
    "message": "تم جلب الفرص القادمة بنجاح",
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 5,
                "name": "Real Estate Project",
                "offering_start_date": "2025-10-01T00:00:00.000000Z",
                "has_reminder": true,
                "category": {...},
                "owner_profile": {...}
            }
        ],
        "total": 5
    }
}
```

### 4. Toggle Reminder Status
**PATCH** `/api/investor/reminders/{reminderId}/toggle`

Activate or deactivate a reminder.

**Request Body:**
```json
{
    "is_active": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم تفعيل التذكير بنجاح",
    "data": {
        "id": 1,
        "is_active": true,
        "updated_at": "2025-09-20T23:35:00.000000Z"
    }
}
```

### 5. Remove Reminder
**DELETE** `/api/investor/reminders/{reminderId}`

Remove a reminder (deactivates it).

**Response:**
```json
{
    "success": true,
    "message": "تم إزالة التذكير بنجاح"
}
```

### 6. Get Reminder Statistics
**GET** `/api/investor/reminders/stats`

Get statistics about the investor's reminders.

**Response:**
```json
{
    "success": true,
    "message": "تم جلب إحصائيات التذكيرات بنجاح",
    "data": {
        "total_reminders": 10,
        "active_reminders": 8,
        "sent_reminders": 2,
        "pending_reminders": 6
    }
}
```

## Error Responses

### 400 Bad Request
```json
{
    "success": false,
    "message": "لا يمكن إضافة تذكير لهذه الفرصة. يجب أن تكون الفرصة قادمة وليست متاحة للاستثمار بعد",
    "error_code": "INVALID_OPPORTUNITY_STATUS"
}
```

### 403 Forbidden
```json
{
    "success": false,
    "message": "لم يتم العثور على بروفايل المستثمر"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "التذكير غير موجود"
}
```

## Usage Examples

### Setting a Reminder
```bash
curl -X POST "https://your-domain.com/api/investor/reminders" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{"investment_opportunity_id": 5}'
```

### Getting Coming Opportunities
```bash
curl -X GET "https://your-domain.com/api/investor/reminders/coming-opportunities" \
  -H "Authorization: Bearer your-token"
```

### Toggling Reminder Status
```bash
curl -X PATCH "https://your-domain.com/api/investor/reminders/1/toggle" \
  -H "Authorization: Bearer your-token" \
  -H "Content-Type: application/json" \
  -d '{"is_active": false}'
```

### Getting Reminders in Home Data
```bash
curl -X GET "https://your-domain.com/api/investor/home?type=reminders&reminders_per_page=10" \
  -H "Authorization: Bearer your-token"
```

**Response:**
```json
{
    "success": true,
    "message": "Investment opportunities retrieved successfully",
    "data": {
        "reminders": {
            "data": [
                {
                    "id": 5,
                    "name": "Real Estate Project",
                    "location": "Dubai",
                    "description": "Premium real estate investment opportunity",
                    "offering_start_date": "2025-10-01T00:00:00.000000Z",
                    "offering_end_date": "2025-12-31T23:59:59.000000Z",
                    "target_amount": "1000000.00",
                    "price_per_share": "100.00",
                    "status": "open",
                    "category": {
                        "id": 1,
                        "name": "Real Estate",
                        "description": "Real estate investment opportunities"
                    },
                    "owner_profile": {
                        "id": 1,
                        "user": {
                            "id": 2,
                            "full_name": "John Doe",
                            "phone": "+1234567890"
                        }
                    },
                    "reminders": [
                        {
                            "id": 1,
                            "investor_profile_id": 1,
                            "investment_opportunity_id": 5,
                            "is_active": true,
                            "reminder_sent_at": null,
                            "created_at": "2025-09-20T23:30:00.000000Z",
                            "updated_at": "2025-09-20T23:30:00.000000Z"
                        }
                    ]
                }
            ],
            "links": {...},
            "meta": {...}
        }
    }
}
```

## Background Processing

The system includes a command to process and send reminders:

```bash
# Send reminders for opportunities that became available
php artisan reminders:send

# Send reminders and clean up old ones
php artisan reminders:send --cleanup
```

This command should be scheduled to run periodically (e.g., every hour) to check for opportunities that have become available and send notifications to investors who have set reminders.

## Database Schema

The reminders are stored in the `investment_opportunity_reminders` table:

- `id`: Primary key
- `investor_profile_id`: Foreign key to investor_profiles table
- `investment_opportunity_id`: Foreign key to investment_opportunities table
- `is_active`: Boolean indicating if reminder is active
- `reminder_sent_at`: Timestamp when reminder was sent (null if not sent)
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

## Notes

- Only coming investment opportunities (not yet available for investment) can have reminders set
- Each investor can only have one reminder per opportunity
- Reminders are automatically marked as sent when the opportunity becomes available
- The system prevents duplicate reminders for the same opportunity
