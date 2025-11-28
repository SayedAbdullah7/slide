# Taqnyat SMS Environment Setup

## Add to your .env file

Add these environment variables to your `.env` file:

```env
# Taqnyat SMS Configuration
TAQNYAT_AUTH_TOKEN=your_api_token_here
TAQNYAT_DEFAULT_SENDER=YourAppName
TAQNYAT_BASE_URL=https://api.taqnyat.sa
```

## Getting Your API Token

1. Log in to your Taqnyat account at https://taqnyat.sa
2. Navigate to API settings or developer section
3. Copy your API authentication token
4. Paste it as the value for `TAQNYAT_AUTH_TOKEN` in your `.env` file

## Setting Default Sender

The `TAQNYAT_DEFAULT_SENDER` should be a sender name that you have registered and approved in your Taqnyat account. Common formats:
- Company name (e.g., "MyCompany")
- App name (e.g., "MyApp")
- Service name (e.g., "MyService")

**Note**: Sender names must be approved by Taqnyat before they can be used.

## Testing Your Setup

After adding the environment variables, test your setup:

```bash
# Clear config cache
php artisan config:clear

# Test the integration
php artisan tinker
```

Then in tinker:

```php
$sms = new \App\Services\SmsService();
$result = $sms->getBalance();
print_r($result);
```

If configured correctly, you should see your account balance.

## Security Notes

- **Never commit your `.env` file** to version control
- Keep your API token secure and private
- Rotate your API token periodically
- Use different tokens for development and production environments

## Example Production vs Development Setup

### Development (.env.local)
```env
TAQNYAT_AUTH_TOKEN=dev_token_here
TAQNYAT_DEFAULT_SENDER=DevApp
```

### Production (.env.production)
```env
TAQNYAT_AUTH_TOKEN=prod_token_here
TAQNYAT_DEFAULT_SENDER=ProductionApp
```


