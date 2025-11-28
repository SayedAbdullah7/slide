# OTP SMS Integration Guide

## Overview

The `OtpService` has been integrated with Taqnyat SMS to automatically send OTP codes to users via SMS when they are generated.

## How It Works

When you call `OtpService::generate($phone)`:
1. A random 6-digit OTP code is generated
2. The OTP is stored in the database with a 5-minute expiration
3. The OTP is automatically sent to the user's phone via Taqnyat SMS
4. The process is logged for debugging and monitoring

## Usage in Your Application

### Basic OTP Flow

```php
use App\Services\OtpService;

class AuthController extends Controller
{
    protected $otpService;

    public function __construct(OtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|regex:/^966[0-9]{9}$/'
        ]);

        try {
            // This will generate OTP AND send it via SMS automatically
            $otp = $this->otpService->generate($request->phone);

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully',
                'expires_at' => $otp->expires_at
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP'
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6'
        ]);

        $session = $this->otpService->verify($request->phone, $request->code);

        if ($session) {
            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully',
                'token' => $session->token
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }
}
```

## SMS Message Format

The OTP is sent with a professional message in Arabic (default) or English:

### Arabic (Default)
```
كود التحقق الخاص بك هو: 123456
صالح لمدة 5 دقائق
```

### English
```
Your verification code is: 123456
Valid for 5 minutes
```

## Configuration

### OTP Settings

The OTP configuration is in `OtpService`:
- **Code Length**: 6 digits (100000-999999)
- **Expiration**: 5 minutes
- **SMS Language**: Arabic (default)

### Testing Mode

For development, you can temporarily use a fixed OTP code:

```php
// In OtpService::generate()
$code = random_int(100000, 999999);
$code = '1234'; // Uncomment this line for testing (bypasses random generation)
```

**Important**: Comment out the fixed code in production!

## Testing

### Test Routes Available

#### 1. Test OTP Sending
```bash
GET /test/send-otp?phone=966512345678
```

Response:
```json
{
  "success": true,
  "message": "OTP generated and sent",
  "data": {
    "phone": "966512345678",
    "otp_id": 1,
    "code": "123456",
    "expires_at": "2025-10-21 12:35:00"
  }
}
```

#### 2. Test SMS Status
```bash
GET /test/sms-status
```

#### 3. Test Account Balance
```bash
GET /test/sms-balance
```

#### 4. Test Available Senders
```bash
GET /test/sms-senders
```

#### 5. Test Direct SMS
```bash
GET /test/send-sms?phone=966512345678
```

## Complete Authentication Flow Example

```php
// Step 1: User requests OTP
POST /api/auth/send-otp
{
  "phone": "966512345678"
}

// Response
{
  "success": true,
  "message": "OTP sent successfully",
  "expires_at": "2025-10-21 12:35:00"
}

// Step 2: User receives SMS with OTP code

// Step 3: User submits OTP for verification
POST /api/auth/verify-otp
{
  "phone": "966512345678",
  "code": "123456"
}

// Response
{
  "success": true,
  "message": "OTP verified successfully",
  "token": "abc123def456..."
}
```

## Rate Limiting

Implement rate limiting to prevent OTP abuse:

```php
use Illuminate\Support\Facades\RateLimiter;

public function sendOtp(Request $request)
{
    $key = 'send-otp:' . $request->phone;
    
    // Allow 3 OTP requests per 5 minutes per phone number
    if (RateLimiter::tooManyAttempts($key, 3)) {
        $seconds = RateLimiter::availableIn($key);
        
        return response()->json([
            'success' => false,
            'message' => "Too many attempts. Try again in {$seconds} seconds."
        ], 429);
    }

    $otp = $this->otpService->generate($request->phone);
    
    RateLimiter::hit($key, 300); // 5 minutes

    return response()->json([
        'success' => true,
        'message' => 'OTP sent successfully'
    ]);
}
```

## Logging

All OTP operations are automatically logged:

### Success Log
```
[INFO] OTP sent successfully
{
  "phone": "966512345678",
  "otp_id": 1
}
```

### Warning Log
```
[WARNING] Failed to send OTP via SMS
{
  "phone": "966512345678",
  "error": "Invalid authentication token"
}
```

### Error Log
```
[ERROR] Exception while sending OTP
{
  "phone": "966512345678",
  "error": "Connection timeout"
}
```

Check logs in `storage/logs/laravel.log`

## Error Handling

The OTP service continues to create the OTP record even if SMS sending fails. This ensures:
1. The user can still verify if they receive the SMS later
2. You can retry sending the SMS manually if needed
3. The system remains functional even if the SMS service is down

```php
// OTP is always created in database
$otpRecord = OtpCode::create([...]);

// SMS sending is wrapped in try-catch
try {
    $result = $this->smsService->sendOtp($phone, $code);
    // Log success/failure but don't throw exception
} catch (\Exception $e) {
    // Log error but continue
}

return $otpRecord; // Always return the OTP record
```

## Best Practices

### 1. Phone Number Validation
```php
$request->validate([
    'phone' => [
        'required',
        'string',
        'regex:/^966[0-9]{9}$/', // Saudi format: 966XXXXXXXXX
        'unique:users,phone' // If for registration
    ]
]);
```

### 2. Security Measures
- ✅ Implement rate limiting
- ✅ Use HTTPS only
- ✅ Log all OTP attempts
- ✅ Set appropriate expiration (5 minutes)
- ✅ Mark OTP as used after verification
- ✅ Don't send OTP code in API response (except for testing)

### 3. User Experience
- Send clear SMS messages
- Inform user about expiration time
- Provide option to resend OTP
- Handle edge cases (expired, already used, etc.)

### 4. Monitoring
```php
// Monitor OTP success rate
$totalOtps = OtpCode::count();
$usedOtps = OtpCode::where('is_used', true)->count();
$successRate = ($usedOtps / $totalOtps) * 100;

// Monitor SMS delivery failures (check logs)
// Set up alerts for high failure rates
```

## Troubleshooting

### OTP Generated But Not Received

1. **Check SMS balance**:
```bash
GET /test/sms-balance
```

2. **Check logs**:
```bash
tail -f storage/logs/laravel.log | grep OTP
```

3. **Verify phone format**: Must be `966XXXXXXXXX`

4. **Check sender approval**: Ensure "Slide App" is approved in Taqnyat

### SMS Sending Fails

1. **Verify API token**: Check `config/taqnyat.php`
2. **Test connection**: Visit `/test/sms-status`
3. **Check credentials**: Ensure TAQNYAT_AUTH_TOKEN is correct

### OTP Verification Fails

Common reasons:
- OTP expired (>5 minutes)
- OTP already used
- Wrong code entered
- Phone number mismatch

## Database Schema

The `otp_codes` table:
```php
Schema::create('otp_codes', function (Blueprint $table) {
    $table->id();
    $table->string('phone');
    $table->string('code');
    $table->boolean('is_used')->default(false);
    $table->timestamp('expires_at');
    $table->timestamps();
});
```

## Environment Variables

Ensure these are set in your `.env`:
```env
TAQNYAT_AUTH_TOKEN=d1afc623f4ae6ed1f12bba1d1b94e549
TAQNYAT_DEFAULT_SENDER=Slide App
```

## Production Checklist

Before deploying to production:

- [ ] Remove/comment fixed OTP code (`$code = '1234'`)
- [ ] Set proper rate limiting
- [ ] Configure monitoring and alerts
- [ ] Test with real phone numbers
- [ ] Verify sender name is approved
- [ ] Set up log rotation
- [ ] Add OTP cleanup job (remove old/expired OTPs)
- [ ] Remove OTP code from API responses
- [ ] Test failure scenarios
- [ ] Document incident response procedures

## Cleanup Old OTPs (Optional)

Create a scheduled job to clean up old OTPs:

```php
// app/Console/Commands/CleanupOldOtps.php
php artisan make:command CleanupOldOtps

// In the command
OtpCode::where('expires_at', '<', now()->subHours(24))->delete();

// Schedule in app/Console/Kernel.php
$schedule->command('otp:cleanup')->daily();
```

## Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Taqnyat API: https://taqnyat.sa/docs
- Project documentation: `TAQNYAT_SMS_INTEGRATION.md`


