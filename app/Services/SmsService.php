<?php

namespace App\Services;

use App\Rules\SaudiPhoneNumber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsService
{
    protected $taqnyat;

    public function __construct()
    {
        $authToken = config('taqnyat.auth_token');
        $this->taqnyat = new TaqnyatSms($authToken);
    }

    /**
     * Validate Saudi phone number
     *
     * @param string $phoneNumber
     * @return array ['valid' => bool, 'error' => string|null]
     */
    protected function validateSaudiPhone($phoneNumber)
    {
        $validator = Validator::make(
            ['phone' => $phoneNumber],
            ['phone' => [new SaudiPhoneNumber()]]
        );

        if ($validator->fails()) {
            return [
                'valid' => false,
                'error' => $validator->errors()->first('phone')
            ];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Send SMS to single or multiple recipients
     *
     * @param string|array $recipients Phone number(s) - can be string with comma-separated numbers or array
     * @param string $message The message body
     * @param string|null $sender Sender name (uses default from config if not provided)
     * @param string $smsId Optional SMS ID for tracking
     * @param string $scheduled Optional scheduled datetime (format: Y-m-d H:i:s)
     * @return array Response with status and message
     */
    public function send($recipients, $message, $sender = null, $smsId = '', $scheduled = '')
    {
        try {
            // Convert array of recipients to array for validation
            $recipientArray = is_array($recipients) ? $recipients : explode(',', $recipients);

            // Validate each phone number
            foreach ($recipientArray as $phone) {
                $phone = trim($phone);
                $validation = $this->validateSaudiPhone($phone);

                if (!$validation['valid']) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => $validation['error']
                    ];
                }
            }

            // Convert array of recipients to comma-separated string
            if (is_array($recipients)) {
                $recipients = implode(',', $recipients);
            }

            // Use default sender if not provided
            if (empty($sender)) {
                $sender = config('taqnyat.default_sender', 'slide tech');
            }



            // Send the message
            $response = $this->taqnyat->sendMsg($message, $recipients, $sender, $smsId, $scheduled);

            // Decode response
            $result = json_decode($response, true);

            // Log the SMS sending attempt
            Log::info('SMS sent via Taqnyat', [
                'recipients' => $recipients,
                'sender' => $sender,
                'response' => $result
            ]);

            return [
                'success' => !empty($result) && (isset($result['statusCode']) && $result['statusCode'] == 201),
                'data' => $result,
                'message' => $result['message'] ?? 'SMS sent successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send SMS via Taqnyat', [
                'error' => $e->getMessage(),
                'recipients' => $recipients
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send OTP code to a phone number
     *
     * @param string $phoneNumber
     * @param string $otp
     * @param string|null $sender
     * @param string $type Type of OTP: 'login', 'confirm', 'register'
     * @param string $operationName Operation name for confirm type
     * @return array
     */
    public function sendOtp($phoneNumber, $otp, $sender = null, $type = 'login', $operationName = '')
    {
        // $appName = config('otp_messages.app_name', config('app.name', 'Slide Tech'));
        $appName = 'سلايد';

        $templates = config('otp_messages.templates', []);

        // Get template for the specified type
        $template = $templates[$type] ?? $templates['login'];
        $messageTemplate = $template['message'];

        // Replace placeholders
        $message = str_replace(
            ['{code}', '{app_name}', '{operation_name}'],
            [$otp, $appName, $operationName ?: 'العملية'],
            $messageTemplate
        );

        return $this->send($phoneNumber, $message, $sender);
    }

    /**
     * Send notification message
     *
     * @param string|array $recipients
     * @param string $message
     * @param string|null $sender
     * @return array
     */
    public function sendNotification($recipients, $message, $sender = null)
    {
        return $this->send($recipients, $message, $sender);
    }

    /**
     * Get account balance
     *
     * @return array
     */
    public function getBalance()
    {
        try {
            $response = $this->taqnyat->balance();
            $result = json_decode($response, true);

            return [
                'success' => !empty($result),
                'data' => $result,
                'message' => 'Balance retrieved successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Taqnyat balance', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to get balance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get list of available senders
     *
     * @return array
     */
    public function getSenders()
    {
        try {
            $response = $this->taqnyat->senders();
            $result = json_decode($response, true);

            return [
                'success' => !empty($result),
                'data' => $result,
                'message' => 'Senders retrieved successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Taqnyat senders', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to get senders: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get send status
     *
     * @return array
     */
    public function getStatus()
    {
        try {
            $response = $this->taqnyat->sendStatus();
            $result = json_decode($response, true);

            return [
                'success' => !empty($result),
                'data' => $result,
                'message' => 'Status retrieved successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get Taqnyat status', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to get status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a scheduled message
     *
     * @param string $deleteKey
     * @return array
     */
    public function deleteMessage($deleteKey)
    {
        try {
            $response = $this->taqnyat->deleteMsg($deleteKey);
            $result = json_decode($response, true);

            return [
                'success' => !empty($result),
                'data' => $result,
                'message' => 'Message deleted successfully'
            ];

        } catch (\Exception $e) {
            Log::error('Failed to delete Taqnyat message', [
                'error' => $e->getMessage(),
                'deleteKey' => $deleteKey
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => 'Failed to delete message: ' . $e->getMessage()
            ];
        }
    }
}

