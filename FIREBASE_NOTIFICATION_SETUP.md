# Firebase Cloud Messaging (FCM) Integration

## Overview
This project now includes Firebase Cloud Messaging (FCM) integration for sending push notifications to users when investment opportunities become available. The system supports one token per user per device and automatically sends notifications when opportunities that users have set reminders for become available.

## Setup Instructions

### 1. Firebase Project Setup
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or select an existing one
3. Enable Cloud Messaging in the project settings
4. Download the service account JSON file:
   - Go to Project Settings > Service Accounts
   - Click "Generate new private key"
   - Download the JSON file

### 2. Environment Configuration
Add the following to your `.env` file:

```env
# Firebase Configuration
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_CREDENTIALS_PATH=storage/app/firebase-credentials.json
```

### 3. Place Firebase Credentials
Place the downloaded service account JSON file in `storage/app/firebase-credentials.json`

### 4. Run Migrations
```bash
php artisan migrate
```

## API Endpoints

### FCM Token Management

#### Register FCM Token
**POST** `/api/fcm/register`

Register a new FCM token for the authenticated user.

**Request Body:**
```json
{
    "token": "fcm_token_string",
    "device_id": "unique_device_id",
    "platform": "ios|android|web",
    "app_version": "1.0.0"
}
```

**Response:**
```json
{
    "success": true,
    "message": "تم تسجيل رمز الإشعارات بنجاح",
    "data": {
        "token_id": 1,
        "token": "fcm_token_string",
        "platform": "ios",
        "device_id": "unique_device_id",
        "is_active": true
    }
}
```

#### Get User's FCM Tokens
**GET** `/api/fcm/tokens`

Get all FCM tokens for the authenticated user.

#### Update FCM Token
**PUT** `/api/fcm/tokens/{tokenId}`

Update an existing FCM token.

#### Remove FCM Token
**DELETE** `/api/fcm/tokens`

Remove FCM token by token or device_id.

**Request Body:**
```json
{
    "token": "fcm_token_string"
}
```
or
```json
{
    "device_id": "unique_device_id"
}
```

#### Test Notification
**POST** `/api/fcm/test`

Send a test notification to the authenticated user.

#### Get Notification Statistics
**GET** `/api/fcm/stats`

Get notification statistics (admin only).

#### Deactivate All Tokens
**POST** `/api/fcm/deactivate-all`

Deactivate all FCM tokens for the authenticated user.

## Integration with Registration/Login

### Registration
When users register, they can include FCM token information:

```json
{
    "session_token": "session_token",
    "full_name": "John Doe",
    "email": "john@example.com",
    "national_id": "123456789",
    "birth_date": "1990-01-01",
    "answers": [...],
    "profile": "investor",
    "fcm_token": "fcm_token_string",
    "device_id": "unique_device_id",
    "platform": "ios",
    "app_version": "1.0.0"
}
```

## Automatic Notifications

### Investment Opportunity Reminders
When an investment opportunity becomes available (status changes to 'open'), the system automatically:

1. Finds all active reminders for that opportunity
2. Sends Firebase notifications to users who set reminders
3. Marks reminders as sent
4. Logs the notification results

### Notification Content
- **Title**: "فرصة استثمارية متاحة الآن!" (Investment Opportunity Available Now!)
- **Body**: "الفرصة الاستثمارية '{opportunity_name}' متاحة الآن للاستثمار"
- **Data**: Includes opportunity ID, name, start date, and reminder ID

## Database Schema

### FCM Tokens Table
```sql
CREATE TABLE fcm_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    device_id VARCHAR(255) NULL,
    platform VARCHAR(50) NULL,
    app_version VARCHAR(50) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_used_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY fcm_tokens_user_device_unique (user_id, device_id)
);
```

## Commands

### Cleanup Invalid Tokens
```bash
php artisan fcm:cleanup --days=30
```

This command removes inactive FCM tokens older than the specified number of days.

### Send Investment Reminders
```bash
php artisan reminders:send
```

This command processes and sends reminders for opportunities that became available.

## Configuration

### Firebase Configuration File
The configuration is stored in `config/firebase.php`:

```php
return [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'credentials_path' => env('FIREBASE_CREDENTIALS_PATH', storage_path('app/firebase-credentials.json')),
    'messaging' => [
        'default_sound' => 'default',
        'default_priority' => 'high',
        'default_ttl' => 3600,
    ],
    'notifications' => [
        'android' => [
            'priority' => 'high',
            'sound' => 'default',
            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        ],
        'ios' => [
            'sound' => 'default',
            'badge' => 1,
        ],
    ],
];
```

## Error Handling

The system includes comprehensive error handling:

1. **Invalid Tokens**: Automatically deactivated when Firebase returns invalid token errors
2. **Network Errors**: Logged and retried if possible
3. **Service Errors**: Gracefully handled without breaking the main application flow

## Logging

All notification activities are logged:

- Successful notifications
- Failed notifications with error details
- Token cleanup activities
- Invalid token detection

Check `storage/logs/laravel.log` for detailed logs.

## Security Considerations

1. **Token Validation**: FCM tokens are validated before sending notifications
2. **User Association**: Tokens are strictly associated with users
3. **Device Limitation**: One token per user per device (when device_id is provided)
4. **Token Cleanup**: Regular cleanup of invalid tokens

## Testing

### Test Notification
Use the test endpoint to verify FCM integration:

```bash
curl -X POST "https://your-domain.com/api/fcm/test" \
  -H "Authorization: Bearer your-token"
```

### Manual Testing
1. Register a user with FCM token
2. Set a reminder for a coming investment opportunity
3. Update the opportunity to make it available
4. Check if notification is received

## Troubleshooting

### Common Issues

1. **Invalid Credentials**: Ensure the Firebase service account JSON file is correctly placed
2. **Project ID Mismatch**: Verify the project ID in environment variables
3. **Token Issues**: Check if FCM tokens are valid and not expired
4. **Network Issues**: Ensure server can reach Firebase servers

### Debug Commands

```bash
# Check FCM token statistics
php artisan tinker
>>> app(\App\Services\FirebaseNotificationService::class)->getNotificationStats()

# Test notification sending
php artisan tinker
>>> $service = app(\App\Services\FirebaseNotificationService::class);
>>> $user = \App\Models\User::first();
>>> $service->sendToUser($user, 'Test', 'Test message');
```

## Mobile App Integration

### Android
```kotlin
// Get FCM token
FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
    if (!task.isSuccessful) {
        Log.w(TAG, "Fetching FCM registration token failed", task.exception)
        return@addOnCompleteListener
    }

    // Get new FCM registration token
    val token = task.result
    Log.d(TAG, "FCM Token: $token")
    
    // Send token to server
    sendTokenToServer(token)
}
```

### iOS (Swift)
```swift
// Get FCM token
Messaging.messaging().token { token, error in
    if let error = error {
        print("Error fetching FCM registration token: \(error)")
    } else if let token = token {
        print("FCM registration token: \(token)")
        // Send token to server
        sendTokenToServer(token: token)
    }
}
```

## Monitoring and Analytics

The system provides statistics for monitoring:

- Total registered tokens
- Active vs inactive tokens
- Tokens by platform (iOS, Android, Web)
- Notification success/failure rates

Access statistics via the API endpoint or check the logs for detailed information.
