# User Deletion Request API Documentation

## Overview
This API allows users to request account deletion and manage their deletion requests. When a user re-registers, any pending deletion requests are automatically cancelled.

## Endpoints

### 1. Request Account Deletion
**POST** `/api/auth/request-deletion`

Creates a new deletion request for the authenticated user. If the user already has pending deletion requests, a new request is created (existing ones are not updated).

#### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

#### Request Body
```json
{
    "reason": "Optional reason for deletion (max 500 characters)"
}
```

#### Response (Success)
```json
{
    "success": true,
    "message": "Deletion request submitted successfully",
    "data": {
        "request_id": 1,
        "status": "pending",
        "requested_at": "2025-09-22T20:24:33.000000Z",
        "reason": "User wants to delete account"
    }
}
```

#### Response (Error)
```json
{
    "success": false,
    "message": "User not authenticated",
    "error_code": 401
}
```

---

### 2. Get User's Deletion Requests
**GET** `/api/auth/deletion-requests`

Retrieves all deletion requests for the authenticated user, ordered by creation date (newest first).

#### Headers
```
Authorization: Bearer {token}
```

#### Response (Success)
```json
{
    "success": true,
    "message": "Deletion requests retrieved successfully",
    "data": {
        "requests": [
            {
                "id": 1,
                "status": "pending",
                "reason": "User wants to delete account",
                "requested_at": "2025-09-22T20:24:33.000000Z",
                "processed_at": null,
                "admin_notes": null
            },
            {
                "id": 2,
                "status": "cancelled",
                "reason": "Changed mind",
                "requested_at": "2025-09-22T19:15:20.000000Z",
                "processed_at": "2025-09-22T20:10:15.000000Z",
                "admin_notes": null
            }
        ]
    }
}
```

#### Response (Error)
```json
{
    "success": false,
    "message": "User not authenticated",
    "error_code": 401
}
```

## Request Statuses

- **pending**: Request is waiting for admin approval
- **cancelled**: Request was cancelled (e.g., user re-registered)
- **approved**: Request was approved by admin
- **rejected**: Request was rejected by admin

## Business Logic

### Automatic Cancellation
When a user re-registers (either creates a new account or adds a new profile), any pending deletion requests are automatically cancelled. This happens in the `register` method of `UserAuthController`.

### Multiple Requests
Users can submit multiple deletion requests. Each request creates a new record rather than updating existing ones, as per the requirement.

### Database Schema
The `user_deletion_requests` table includes:
- `user_id`: Foreign key to users table
- `reason`: Optional reason for deletion
- `status`: Request status (pending/cancelled/approved/rejected)
- `requested_at`: When the request was made
- `processed_at`: When the request was processed by admin
- `admin_notes`: Admin notes when processing the request

## Security Notes

1. All endpoints require authentication via Sanctum tokens
2. Users can only access their own deletion requests
3. Admin functionality for approving/rejecting requests is not implemented in this API (would be separate admin endpoints)
4. Deletion requests are soft-cancelled (marked as cancelled) rather than deleted to maintain audit trail
