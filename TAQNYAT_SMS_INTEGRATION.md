# Taqnyat SMS Integration

This document explains how to use the Taqnyat SMS service in your Laravel application.

## Setup

### 1. Configuration

Add your Taqnyat credentials to your `.env` file:

```env
TAQNYAT_AUTH_TOKEN=your_taqnyat_api_token_here
TAQNYAT_DEFAULT_SENDER=YourAppName
```

### 2. Configuration File

The configuration is stored in `config/taqnyat.php`. You can publish and modify it if needed.

## Usage

### Basic Usage in Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Services\SmsService;

class NotificationController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendWelcomeSms($phoneNumber)
    {
        $result = $this->smsService->send(
            $phoneNumber,
            'Welcome to our application!',
            'YourApp' // Optional: sender name
        );

        if ($result['success']) {
            return response()->json([
                'message' => 'SMS sent successfully',
                'data' => $result['data']
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to send SMS',
                'error' => $result['message']
            ], 500);
        }
    }
}
```

### Send SMS to Multiple Recipients

```php
use App\Services\SmsService;

$smsService = new SmsService();

// Using array
$recipients = ['966512345678', '966587654321'];
$result = $smsService->send($recipients, 'Your message here');

// Using comma-separated string
$recipients = '966512345678,966587654321';
$result = $smsService->send($recipients, 'Your message here');
```

### Send OTP Code

```php
use App\Services\SmsService;

$smsService = new SmsService();
$otp = rand(1000, 9999); // Generate OTP

$result = $smsService->sendOtp('966512345678', $otp);

if ($result['success']) {
    // OTP sent successfully
    // Store OTP in session or database for verification
    session(['otp' => $otp, 'otp_phone' => '966512345678']);
}
```

### Send Scheduled SMS

```php
use App\Services\SmsService;

$smsService = new SmsService();

$scheduledTime = now()->addHours(2)->format('m/d/Y H:i:s');
$result = $smsService->send(
    '966512345678',
    'This is a scheduled message',
    null, // sender (uses default)
    '', // smsId
    $scheduledTime
);
```

### Check Account Balance

```php
use App\Services\SmsService;

$smsService = new SmsService();
$result = $smsService->getBalance();

if ($result['success']) {
    $balance = $result['data']['balance'] ?? 'Unknown';
    echo "Your balance: " . $balance;
}
```

### Get Available Senders

```php
use App\Services\SmsService;

$smsService = new SmsService();
$result = $smsService->getSenders();

if ($result['success']) {
    $senders = $result['data'];
    // Display available sender names
}
```

### Check Service Status

```php
use App\Services\SmsService;

$smsService = new SmsService();
$result = $smsService->getStatus();

if ($result['success']) {
    // Service is operational
    $status = $result['data'];
}
```

## Usage in Authentication Flow

### Example: Send OTP During Registration

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SmsService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string'
        ]);

        // Generate OTP
        $otp = rand(100000, 999999);

        // Send OTP via SMS
        $result = $this->smsService->sendOtp($request->phone, $otp);

        if ($result['success']) {
            // Store OTP in cache with expiration (5 minutes)
            cache()->put('otp_' . $request->phone, $otp, now()->addMinutes(5));

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send OTP'
        ], 500);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string'
        ]);

        $cachedOtp = cache()->get('otp_' . $request->phone);

        if ($cachedOtp && $cachedOtp == $request->otp) {
            // OTP is valid
            cache()->forget('otp_' . $request->phone);

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }
}
```

## Usage in Notifications

### Create a Custom Notification

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use App\Services\SmsService;

class InvestmentApproved extends Notification
{
    protected $investment;

    public function __construct($investment)
    {
        $this->investment = $investment;
    }

    public function via($notifiable)
    {
        return ['sms']; // Custom channel
    }

    public function toSms($notifiable)
    {
        $smsService = new SmsService();
        
        $message = "Your investment of " . $this->investment->amount . 
                   " has been approved!";

        return $smsService->send(
            $notifiable->phone,
            $message
        );
    }
}
```

## Direct Usage with TaqnyatSms Class

If you need more control, you can use the base `TaqnyatSms` class directly:

```php
use App\Services\TaqnyatSms;

$taqnyat = new TaqnyatSms(config('taqnyat.auth_token'));

// Send message
$response = $taqnyat->sendMsg(
    'Message body',
    '966512345678', // recipients
    'SenderName',   // sender
    '',             // smsId
    '',             // scheduled
    ''              // deleteId
);

$result = json_decode($response, true);
```

## Helper Function (Optional)

You can create a global helper function for easier access. Add to `app/Helpers/helpers.php`:

```php
<?php

if (!function_exists('send_sms')) {
    /**
     * Send SMS helper function
     *
     * @param string|array $recipients
     * @param string $message
     * @param string|null $sender
     * @return array
     */
    function send_sms($recipients, $message, $sender = null)
    {
        $smsService = new \App\Services\SmsService();
        return $smsService->send($recipients, $message, $sender);
    }
}

if (!function_exists('send_otp')) {
    /**
     * Send OTP helper function
     *
     * @param string $phoneNumber
     * @param string $otp
     * @return array
     */
    function send_otp($phoneNumber, $otp)
    {
        $smsService = new \App\Services\SmsService();
        return $smsService->sendOtp($phoneNumber, $otp);
    }
}
```

Then ensure this file is loaded in `composer.json`:

```json
"autoload": {
    "files": [
        "app/Helpers/helpers.php"
    ]
}
```

Run `composer dump-autoload` after adding.

### Using Helper Functions

```php
// Send SMS
$result = send_sms('966512345678', 'Hello from our app!');

// Send OTP
$otp = rand(1000, 9999);
$result = send_otp('966512345678', $otp);
```

## Testing

### Test SMS Sending

Create a test route in `routes/web.php` or `routes/test.php`:

```php
Route::get('/test-sms', function () {
    $smsService = new \App\Services\SmsService();
    
    $result = $smsService->send(
        'YOUR_PHONE_NUMBER',
        'Test message from Taqnyat integration'
    );
    
    return response()->json($result);
});
```

Visit `/test-sms` to test the integration.

## Error Handling

All methods in `SmsService` return a standardized response:

```php
[
    'success' => true/false,
    'data' => [...],  // Response data from Taqnyat API
    'message' => 'Status message'
]
```

Always check the `success` key before processing:

```php
$result = $smsService->send($phone, $message);

if ($result['success']) {
    // Success - process the result
    $responseData = $result['data'];
} else {
    // Error - handle the error
    \Log::error('SMS sending failed: ' . $result['message']);
}
```

## Best Practices

1. **Phone Number Format**: Ensure phone numbers are in international format (e.g., 966512345678 for Saudi Arabia)

2. **Rate Limiting**: Implement rate limiting for OTP requests to prevent abuse:
```php
use Illuminate\Support\Facades\RateLimiter;

if (RateLimiter::tooManyAttempts('send-otp:'.$request->phone, 3)) {
    return response()->json(['error' => 'Too many attempts'], 429);
}

RateLimiter::hit('send-otp:'.$request->phone, 300); // 5 minutes
```

3. **OTP Storage**: Use cache or Redis for OTP storage with expiration
4. **Message Templates**: Create reusable message templates for consistency
5. **Logging**: All SMS operations are logged automatically in `storage/logs/laravel.log`

## Troubleshooting

### Common Issues

1. **Authentication Error**: Verify your `TAQNYAT_AUTH_TOKEN` in `.env`
2. **cURL Not Enabled**: Ensure PHP cURL extension is installed and enabled
3. **Invalid Phone Number**: Check phone number format (should be in international format)
4. **Sender Not Approved**: Ensure the sender name is approved in your Taqnyat account

### Debug Mode

To debug API responses:

```php
$result = $smsService->send($phone, $message);
dd($result); // Dump and die to see full response
```

## API Response Examples

### Successful Send Response
```json
{
    "success": true,
    "data": {
        "statusCode": 200,
        "message": "تم الإرسال بنجاح",
        "messageId": "123456789"
    },
    "message": "SMS sent successfully"
}
```

### Error Response
```json
{
    "success": false,
    "data": null,
    "message": "Failed to send SMS: Invalid authentication token"
}
```

## Support

For Taqnyat API documentation and support:
- Website: https://taqnyat.sa
- API Docs: https://taqnyat.sa/docs
- Support: Contact Taqnyat support team


