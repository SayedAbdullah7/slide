# Taqnyat SMS - Quick Reference Guide

## Quick Start

### 1. Setup Environment Variables
```env
TAQNYAT_AUTH_TOKEN=your_token_here
TAQNYAT_DEFAULT_SENDER=YourApp
```

### 2. Basic Usage
```php
use App\Services\SmsService;

$smsService = new SmsService();
$result = $smsService->send('966512345678', 'Your message here');
```

## Common Use Cases

### Send Single SMS
```php
$smsService = new SmsService();
$result = $smsService->send('966512345678', 'Hello!');
```

### Send to Multiple Recipients
```php
$recipients = ['966512345678', '966587654321'];
$result = $smsService->send($recipients, 'Bulk message');
```

### Send OTP
```php
$otp = rand(100000, 999999);
$result = $smsService->sendOtp('966512345678', $otp);

// Store OTP for verification
cache()->put('otp_966512345678', $otp, now()->addMinutes(5));
```

### Send Notification
```php
$result = $smsService->sendNotification(
    '966512345678',
    'Your order has been confirmed!'
);
```

### Schedule SMS
```php
$scheduledTime = now()->addHours(2)->format('m/d/Y H:i:s');
$result = $smsService->send(
    '966512345678',
    'Scheduled message',
    null,
    '',
    $scheduledTime
);
```

## Controller Integration

```php
use App\Services\SmsService;

class YourController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendNotification(Request $request)
    {
        $result = $this->smsService->send(
            $request->phone,
            $request->message
        );

        return response()->json($result);
    }
}
```

## Response Format

All methods return:
```php
[
    'success' => true/false,
    'data' => [...],
    'message' => 'Status message'
]
```

## Available Methods

| Method | Description | Usage |
|--------|-------------|-------|
| `send()` | Send SMS | `$smsService->send($phone, $message)` |
| `sendOtp()` | Send OTP code | `$smsService->sendOtp($phone, $otp)` |
| `sendNotification()` | Send notification | `$smsService->sendNotification($phone, $message)` |
| `getBalance()` | Check balance | `$smsService->getBalance()` |
| `getSenders()` | Get sender list | `$smsService->getSenders()` |
| `getStatus()` | Check service status | `$smsService->getStatus()` |
| `deleteMessage()` | Delete scheduled SMS | `$smsService->deleteMessage($key)` |

## Error Handling

```php
$result = $smsService->send($phone, $message);

if ($result['success']) {
    // Success
    $data = $result['data'];
} else {
    // Error
    Log::error('SMS failed: ' . $result['message']);
}
```

## Testing

### Via Route
```php
// routes/web.php or routes/test.php
Route::get('/test-sms', function () {
    $sms = new \App\Services\SmsService();
    return $sms->send('YOUR_PHONE', 'Test message');
});
```

### Via Artisan Tinker
```bash
php artisan tinker
```
```php
$sms = new \App\Services\SmsService();
$sms->send('966512345678', 'Test message');
```

### Via Test Script
```bash
php test_taqnyat_sms.php
```

## Important Notes

✅ **Phone Format**: Use international format (e.g., 966512345678)  
✅ **Sender Name**: Must be approved in Taqnyat account  
✅ **OTP Expiry**: Set appropriate expiration (typically 5 minutes)  
✅ **Rate Limiting**: Implement for OTP endpoints  
✅ **Logging**: All operations are auto-logged  

❌ **Never**: Commit .env with real tokens  
❌ **Never**: Send SMS without user consent  
❌ **Never**: Store OTP permanently  

## Files Created

- `app/Services/TaqnyatSms.php` - Base SMS class
- `app/Services/SmsService.php` - Laravel wrapper
- `config/taqnyat.php` - Configuration file
- `test_taqnyat_sms.php` - Test script
- `TAQNYAT_SMS_INTEGRATION.md` - Full documentation
- `TAQNYAT_ENV_SETUP.md` - Environment setup guide

## Support

- Full Documentation: `TAQNYAT_SMS_INTEGRATION.md`
- Taqnyat API Docs: https://taqnyat.sa/docs


