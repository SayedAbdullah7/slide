# Saved Investment Opportunities API Documentation

This document describes the API endpoints for managing saved investment opportunities for investors.

## Base URL
All endpoints are prefixed with `/api/investor/saved-opportunities`

## Authentication
All endpoints require authentication using Laravel Sanctum. Include the Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

## Endpoints

### 1. Get Saved Opportunities
**GET** `/api/investor/saved-opportunities`

Retrieve all saved investment opportunities for the authenticated investor.

#### Query Parameters
- `per_page` (optional): Number of items per page (default: 15)
- `page` (optional): Page number (default: 1)

#### Response
```json
{
  "success": true,
  "message": "Saved opportunities retrieved successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "investor_profile_id": 1,
        "investment_opportunity_id": 5,
        "created_at": "2025-01-21T10:30:00.000000Z",
        "updated_at": "2025-01-21T10:30:00.000000Z",
        "investment_opportunity": {
          "id": 5,
          "name": "Tech Startup Investment",
          "description": "Innovative technology startup",
          "status": "open",
          "target_amount": 1000000,
          "price_per_share": 100,
          "min_investment": 1000,
          "category": {
            "id": 1,
            "name": "Technology"
          },
          "owner_profile": {
            "id": 1,
            "user": {
              "id": 1,
              "name": "John Doe"
            }
          }
        }
      }
    ],
    "first_page_url": "http://localhost/api/investor/saved-opportunities?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost/api/investor/saved-opportunities?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost/api/investor/saved-opportunities",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

### 2. Save Investment Opportunity
**POST** `/api/investor/saved-opportunities`

Save an investment opportunity to the investor's saved list.

#### Request Body
```json
{
  "investment_opportunity_id": 5
}
```

#### Response
```json
{
  "success": true,
  "message": "Investment opportunity saved successfully",
  "data": {
    "id": 1,
    "investor_profile_id": 1,
    "investment_opportunity_id": 5,
    "created_at": "2025-01-21T10:30:00.000000Z",
    "updated_at": "2025-01-21T10:30:00.000000Z",
    "investment_opportunity": {
      "id": 5,
      "name": "Tech Startup Investment",
      "description": "Innovative technology startup",
      "status": "open",
      "category": {
        "id": 1,
        "name": "Technology"
      },
      "owner_profile": {
        "id": 1,
        "user": {
          "id": 1,
          "name": "John Doe"
        }
      }
    }
  }
}
```

### 3. Remove Saved Investment Opportunity
**DELETE** `/api/investor/saved-opportunities`

Remove an investment opportunity from the investor's saved list.

#### Request Body
```json
{
  "investment_opportunity_id": 5
}
```

#### Response
```json
{
  "success": true,
  "message": "Investment opportunity removed from saved list"
}
```

### 4. Toggle Save Status
**POST** `/api/investor/saved-opportunities/toggle`

Toggle the save status of an investment opportunity. If saved, it will be removed; if not saved, it will be added.

#### Request Body
```json
{
  "investment_opportunity_id": 5
}
```

#### Response (if not previously saved)
```json
{
  "success": true,
  "message": "Investment opportunity saved successfully",
  "data": {
    "saved": true,
    "investment_opportunity_id": 5,
    "saved_at": "2025-01-21T10:30:00.000000Z"
  }
}
```

#### Response (if previously saved)
```json
{
  "success": true,
  "message": "Investment opportunity removed from saved list",
  "data": {
    "saved": false,
    "investment_opportunity_id": 5
  }
}
```

### 5. Check Save Status
**POST** `/api/investor/saved-opportunities/check-status`

Check the save status for multiple investment opportunities at once.

#### Request Body
```json
{
  "investment_opportunity_ids": [1, 2, 3, 4, 5]
}
```

#### Response
```json
{
  "success": true,
  "message": "Save status retrieved successfully",
  "data": {
    "saved_status": {
      "1": false,
      "2": true,
      "3": false,
      "4": true,
      "5": true
    }
  }
}
```

### 6. Get Save Statistics
**GET** `/api/investor/saved-opportunities/stats`

Get statistics about saved opportunities.

#### Response
```json
{
  "success": true,
  "message": "Save statistics retrieved successfully",
  "data": {
    "total_saved": 15,
    "saved_by_status": {
      "open": 8,
      "coming": 4,
      "closed": 3
    }
  }
}
```

## Error Responses

### Validation Error
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "investment_opportunity_id": [
      "The investment opportunity id field is required."
    ]
  }
}
```

### Not Found Error
```json
{
  "success": false,
  "message": "Investment opportunity already saved",
  "status": 422
}
```

### Server Error
```json
{
  "success": false,
  "message": "Failed to save investment opportunity: Database connection error",
  "status": 500
}
```

## Usage Examples

### Save an Opportunity
```bash
curl -X POST "http://localhost/api/investor/saved-opportunities" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "investment_opportunity_id": 5
  }'
```

### Get All Saved Opportunities
```bash
curl -X GET "http://localhost/api/investor/saved-opportunities?per_page=10&page=1" \
  -H "Authorization: Bearer {your_token}"
```

### Toggle Save Status
```bash
curl -X POST "http://localhost/api/investor/saved-opportunities/toggle" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "investment_opportunity_id": 5
  }'
```

### Check Save Status for Multiple Opportunities
```bash
curl -X POST "http://localhost/api/investor/saved-opportunities/check-status" \
  -H "Authorization: Bearer {your_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "investment_opportunity_ids": [1, 2, 3, 4, 5]
  }'
```

## Integration with Home Data

The saved opportunities can also be retrieved as part of the home data endpoint:

**GET** `/api/investor/home?types[]=saved`

This will include saved opportunities in the response alongside other data types like `available`, `coming`, `my`, `reminders`, etc.

### Home Data Response with Saved Opportunities
```json
{
  "success": true,
  "message": "Home data retrieved successfully",
  "data": {
    "saved": {
      "current_page": 1,
      "data": [
        {
          "id": 5,
          "name": "Tech Startup Investment",
          "description": "Innovative technology startup",
          "status": "open",
          "target_amount": 1000000,
          "price_per_share": 100,
          "min_investment": 1000,
          "category": {
            "id": 1,
            "name": "Technology"
          },
          "owner_profile": {
            "id": 1,
            "user": {
              "id": 1,
              "name": "John Doe"
            }
          }
        }
      ],
      "total": 15
    }
  }
}
```

## Notes

1. **Unique Constraint**: Each investor can only save an investment opportunity once. Attempting to save the same opportunity will return an error.

2. **Cascade Deletion**: When an investment opportunity is deleted, all related saved records are automatically removed.

3. **Authentication Required**: All endpoints require a valid authentication token.

4. **Investor Profile Required**: The authenticated user must have an investor profile to use these endpoints.

5. **Pagination**: The `index` endpoint supports pagination with customizable page size.

6. **Relationships**: Saved opportunities include full investment opportunity data with related models (category, owner profile, etc.).

