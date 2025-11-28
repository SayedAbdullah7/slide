<?php

/**
 * Test Routes
 *
 * These routes are for development and testing purposes only.
 * They should be disabled in production by not including this file
 * or by adding environment-based middleware protection.
 */

use Illuminate\Support\Facades\Route;
use App\Models\InvestmentOpportunity;
use App\Http\Controllers\Admin\AdminInvestmentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\OwnerProfile;
use App\Models\InvestorProfile;
use App\Services\TaqnyatSms;
use App\Services\SmsService;
use App\Services\OtpService;
use App\Notifications\InvestmentPurchasedNotification;
use App\Models\Investment;






Route::get('/test-webhook-ali', function () {
    // 🧾 Full Paymob Webhook Payload
    $payload = [
        "type" => "TRANSACTION",
        "obj" => [
            "id" => 1127418,
            "pending" => false,
            "amount_cents" => 5000,
            "success" => true,
            "is_auth" => false,
            "is_capture" => false,
            "is_standalone_payment" => true,
            "is_voided" => false,
            "is_refunded" => false,
            "is_3d_secure" => false,
            "integration_id" => 17269,
            "profile_id" => 11883,
            "has_parent_transaction" => false,
            "order" => [
                "id" => 1131600,
                "created_at" => "2025-10-26T21:11:02.040063+03:00",
                "delivery_needed" => false,
                "merchant" => [
                    "id" => 11883,
                    "created_at" => "2025-09-20T07:32:28.663211+03:00",
                    "phones" => ["+966590971717"],
                    "company_emails" => null,
                    "company_name" => "Slide",
                    "state" => null,
                    "country" => "SAU",
                    "city" => "temp",
                    "postal_code" => null,
                    "street" => null
                ],
                "collector" => null,
                "amount_cents" => 5000,
                "shipping_data" => [
                    "id" => 808360,
                    "first_name" => "User",
                    "last_name" => "Name",
                    "street" => "N/A",
                    "building" => "N/A",
                    "floor" => "N/A",
                    "apartment" => "N/A",
                    "city" => "Riyadh",
                    "state" => "Riyadh",
                    "country" => "Saudi Arabia",
                    "email" => "ali@gmail.com",
                    "phone_number" => "966000000000",
                    "postal_code" => "NA",
                    "extra_description" => null,
                    "shipping_method" => "UNK",
                    "order_id" => 1131600,
                    "order" => 1131600
                ],
                "currency" => "SAR",
                "is_payment_locked" => false,
                "is_return" => false,
                "is_cancel" => false,
                "is_returned" => false,
                "is_canceled" => false,
                "merchant_order_id" => "INV-16-28-1761502261",
                "wallet_notification" => null,
                "paid_amount_cents" => 5000,
                "notify_user_with_email" => false,
                "items" => [
                    [
                        "name" => "فرصة تجريبية",
                        "description" => "Investment in فرصة تجريبية ID 16 - 5 shares",
                        "amount_cents" => 5000,
                        "quantity" => 1
                    ]
                ],
                "order_url" => "NA",
                "commission_fees" => 0,
                "delivery_fees_cents" => 0,
                "delivery_vat_cents" => 0,
                "payment_method" => "tbc",
                "merchant_staff_tag" => null,
                "api_source" => "OTHER",
                "data" => [
                    "notification_url" => "https://slide.osta-app.com/api/paymob/webhook"
                ],
                "payment_status" => "PAID",
                "terminal_version" => null
            ],
            "created_at" => "2025-10-26T21:11:10.635551+03:00",
            "transaction_processed_callback_responses" => [],
            "currency" => "SAR",
            "source_data" => [
                "type" => "apple pay",
                "sub_type" => "APPLE_PAY",
                "pan" => "8269"
            ],
            "api_source" => "SDK",
            "terminal_id" => null,
            "merchant_commission" => 0,
            "accept_fees" => 0,
            "installment" => null,
            "discount_details" => [],
            "is_void" => false,
            "is_refund" => false,
            "data" => [
                "gateway_integration_pk" => 17269,
                "klass" => "MigsPayment",
                "created_at" => "2025-10-26T18:11:12.160823",
                "amount" => 5000,
                "currency" => "SAR",
                "migs_order" => [
                    "amount" => 50,
                    "authenticationStatus" => "AUTHENTICATION_NOT_IN_EFFECT",
                    "chargeback" => ["amount" => 0, "currency" => "SAR"],
                    "creationTime" => "2025-10-26T18:11:11.235Z",
                    "currency" => "SAR",
                    "id" => "aa1131600",
                    "lastUpdatedTime" => "2025-10-26T18:11:12.094Z",
                    "merchantAmount" => 50,
                    "merchantCategoryCode" => "5399",
                    "merchantCurrency" => "SAR",
                    "status" => "CAPTURED",
                    "totalAuthorizedAmount" => 50,
                    "totalCapturedAmount" => 50,
                    "totalDisbursedAmount" => 0,
                    "totalRefundedAmount" => 0,
                    "walletProvider" => "APPLE_PAY"
                ],
                "merchant" => "602395008",
                "migs_result" => "SUCCESS",
                "migs_transaction" => [
                    "acquirer" => [
                        "batch" => 20251027,
                        "date" => "1027",
                        "id" => "MADA_NCB",
                        "merchantId" => "602395008",
                        "settlementDate" => "2025-10-27",
                        "timeZone" => "+0300",
                        "transactionId" => "4QCGN7"
                    ],
                    "amount" => 50,
                    "authenticationStatus" => "AUTHENTICATION_NOT_IN_EFFECT",
                    "authorizationCode" => "290084",
                    "currency" => "SAR",
                    "id" => "1127418",
                    "receipt" => "529918175526",
                    "source" => "INTERNET",
                    "stan" => "175526",
                    "terminal" => "NCBS2I05",
                    "type" => "PAYMENT"
                ],
                "txn_response_code" => "0",
                "acq_response_code" => "00",
                "message" => "Approved",
                "merchant_txn_ref" => "1127418",
                "order_info" => "aa1131600",
                "receipt_no" => "529918175526",
                "transaction_no" => "4QCGN7",
                "batch_no" => 20251027,
                "authorize_id" => "290084",
                "card_type" => "VISA",
                "card_num" => "xxxxxxxxxxxxxxxx",
                "txn_response_code_new" => "APPROVED"
            ],
            "payment_key_claims" => [
                "user_id" => 13745,
                "amount_cents" => 5000,
                "currency" => "SAR",
                "integration_id" => 17269,
                "order_id" => 1131600,
                "billing_data" => [
                    "first_name" => "User",
                    "last_name" => "Name",
                    "city" => "Riyadh",
                    "country" => "Saudi Arabia",
                    "email" => "ali@gmail.com",
                    "phone_number" => "+966000000000"
                ],
                "extra" => [
                    "opportunity_id" => 16,
                    "shares" => 5,
                    "investment_type" => "authorize",
                    "share_price" => "10.00",
                    "opportunity_name" => "فرصة تجريبية",
                    "merchant_order_id" => "INV-16-28-1761502261",
                    "user_id" => 28,
                ],
                "notification_url" => "https://slide.osta-app.com/api/paymob/webhook"
            ],
            "is_hidden" => false,
            "error_occured" => false,
            "is_live" => true,
            "is_captured" => false,
            "captured_amount" => 0,
            "updated_at" => "2025-10-26T21:11:12.176416+03:00",
            "is_settled" => false
        ],
        "hmac" => "fe84d9b16eec5f4dd7a2fa30ea4db509644f3314f845a0c444a57853c696e353180ebc839ff6c2e46a00e4966b69f7e142fc21151df84b0cb5a4abdb09ac92a2"
    ];

    // 🔧 Create a fake JSON POST request
    $request = Request::create(
        '/api/paymob/webhook',
        'POST',
        [], [], [], ['CONTENT_TYPE' => 'application/json'],
        json_encode($payload)
    );

    // Merge JSON data into Laravel's Request bag
    $request->merge($payload);

    // 🚀 Call your actual controller method
    return app(PaymentWebhookController::class)->handlePaymobWebhook($request);
});


// test generate otp
Route::get('/test/generate-otp', function () {
    $otpService = new OtpService(new SmsService());
    $result = $otpService->generate('+966590971717');
    return $result;

    return response()->json([
        'message' => 'OTP generated',
        'result' => $result,
    ]);
});
//test sendOtp
Route::get('/test/send-otp', function () {
    $smsService = new SmsService();
    $result = $smsService->sendOtp('+966590971750', '1234');
    return response()->json([
        'message' => 'OTP sent',
        'result' => $result,
    ]);
});
//test sms status
Route::get('/test/sms-status', function () {
    $sms = new TaqnyatSms(config('taqnyat.auth_token'));
    $result = $sms->sendStatus();
    return response()->json([
        'message' => 'SMS status',
        'result' => json_decode($result, true),
    ]);
});

//test sms senders
Route::get('/test/sms-senders', function () {
    $sms = new TaqnyatSms(config('taqnyat.auth_token'));
    $result = $sms->senders();
    return response()->json([
        'message' => 'SMS senders',
        'result' => json_decode($result, true),
    ]);
});

// Test SMS sending
Route::get('/test/send-sms', function (Request $request) {
    $phone = $request->get('phone', '966XXXXXXXXX'); // Replace with test number

    $smsService = new SmsService();
    $result = $smsService->send($phone, 'Test message from Slide App');

    return response()->json([
        'message' => 'SMS test',
        'result' => $result,
    ]);
});

// Test OTP sending
Route::get('/test/send-otp', function (Request $request, OtpService $otpService) {
    $phone = $request->get('phone', '966XXXXXXXXX'); // Replace with test number

    try {
        $otp = $otpService->generate($phone);

        return response()->json([
            'success' => true,
            'message' => 'OTP generated and sent',
            'data' => [
                'phone' => $phone,
                'otp_id' => $otp->id,
                'code' => $otp->code, // Only for testing - remove in production
                'expires_at' => $otp->expires_at,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send OTP',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Test Taqnyat balance
Route::get('/test/sms-balance', function (SmsService $smsService) {
    $result = $smsService->getBalance();

    return response()->json([
        'message' => 'Account balance',
        'result' => $result,
    ]);
});

// Test Taqnyat senders
Route::get('/test/sms-senders', function (SmsService $smsService) {
    $result = $smsService->getSenders();

    return response()->json([
        'message' => 'Available senders',
        'result' => $result,
    ]);
});

//  route for trash( make empty) table DATABASE users and owner profiles and investor profiles
Route::get('/test/trash-table', function () {
    DB::statement('SET FOREIGN_KEY_CHECKS = 0;');

    DB::statement('TRUNCATE TABLE users');
    DB::statement('TRUNCATE TABLE owner_profiles');
    DB::statement('TRUNCATE TABLE investor_profiles');

    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');

    return response()->json([
        'message' => 'Tables truncated and auto-increment reset successfully',
        'users' => User::count(),
        'owner_profiles' => OwnerProfile::count(),
        'investor_profiles' => InvestorProfile::count(),
    ]);
});

// Protect all test routes - only available in local/staging environments
Route::middleware(['auth', 'throttle:60,1'])->group(function () {

    /**
     * Recalculate reserved shares for all investment opportunities
     * GET /test/recalculate-reserved-shares
     */
    Route::get('/test/recalculate-reserved-shares', function () {
        $investmentOpportunities = InvestmentOpportunity::all();
        $results = [];

        foreach ($investmentOpportunities as $investmentOpportunity) {
            $oldValue = $investmentOpportunity->reserved_shares;
            $investmentOpportunity->reserved_shares = $investmentOpportunity->investments()->sum('shares');
            $investmentOpportunity->save();

            $results[] = [
                'id' => $investmentOpportunity->id,
                'old_value' => $oldValue,
                'new_value' => $investmentOpportunity->reserved_shares,
            ];
        }

        return response()->json([
            'message' => 'Reserved shares recalculated successfully',
            'results' => $results,
        ]);
    })->name('test.recalculate-reserved-shares');

    /**
     * Get top opportunity by investment count
     * GET /test/top-opportunity-by-investments
     */
    Route::get('/test/top-opportunity-by-investments', function () {
        $investmentOpportunity = InvestmentOpportunity::withCount('investments')
            ->orderBy('investments_count', 'desc')
            ->first();

        return response()->json([
            'investment_opportunity' => $investmentOpportunity,
        ]);
    })->name('test.top-opportunity');

    /**
     * Test processActualProfitPerShare with example data
     * GET /test/actual-profit
     */
    Route::get('/test/actual-profit', function () {
        try {
            $opportunity = InvestmentOpportunity::first();

            if (!$opportunity) {
                return response()->json([
                    'error' => 'No opportunities found. Create one first.'
                ], 404);
            }

            // Example data - hardcoded for testing
            $exampleData = [
                'returns_data' => [
                    1 => [
                        'actual_profit_per_share' => 15.50,
                        'actual_net_profit_per_share' => 12.30,
                    ],
                    2 => [
                        'actual_profit_per_share' => 18.75,
                        'actual_net_profit_per_share' => 15.20,
                    ],
                ]
            ];

            $request = new Request();
            $request->merge($exampleData);

            $controller = app(AdminInvestmentController::class);
            $result = $controller->processActualProfitPerShare($request, $opportunity);

            return response()->json([
                'success' => true,
                'message' => 'Test completed successfully',
                'opportunity_id' => $opportunity->id,
                'opportunity_name' => $opportunity->name,
                'result' => $result->getData(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    })->name('test.actual-profit');

    /**
     * Test bulk actual profit recording with URL parameters
     * GET /test/bulk-actual-profit/{opportunity_id}/{profit}/{net_profit}
     */
    Route::get('/test/bulk-actual-profit/{opportunity_id}/{profit}/{net_profit}', function ($opportunity_id, $profit, $net_profit) {
        try {
            $opportunity = InvestmentOpportunity::findOrFail($opportunity_id);

            // Validate parameters
            if (!is_numeric($profit) || $profit < 0) {
                return response()->json([
                    'error' => 'Invalid profit parameter. Must be a positive number.'
                ], 400);
            }

            if (!is_numeric($net_profit) || $net_profit < 0) {
                return response()->json([
                    'error' => 'Invalid net_profit parameter. Must be a positive number.'
                ], 400);
            }

            $actualProfitPerShare = (float) $profit;
            $actualNetProfitPerShare = (float) $net_profit;

            // Check for authorize investments
            $authorizeInvestments = $opportunity->investments()
                ->where('investment_type', 'authorize')
                ->whereNull('actual_profit_per_share')
                ->count();

            if ($authorizeInvestments === 0) {
                return response()->json([
                    'error' => 'No authorize investments found that need actual profit recording.',
                    'opportunity_id' => $opportunity->id,
                    'opportunity_name' => $opportunity->name,
                    'authorize_investments_count' => $authorizeInvestments,
                ], 404);
            }

            $profitData = [
                'actual_profit_per_share' => $actualProfitPerShare,
                'actual_net_profit_per_share' => $actualNetProfitPerShare,
            ];

            $request = new Request();
            $request->merge($profitData);

            $controller = app(AdminInvestmentController::class);
            $result = $controller->processActualProfitForAllAuthorize($request, $opportunity);

            return response()->json([
                'success' => true,
                'message' => 'Bulk actual profit test completed successfully',
                'opportunity_id' => $opportunity->id,
                'opportunity_name' => $opportunity->name,
                'authorize_investments_found' => $authorizeInvestments,
                'profit_values' => $profitData,
                'result' => $result->getData(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    })->name('test.bulk-actual-profit.params');

    /**
     * Test bulk actual profit recording with default values
     * GET /test/bulk-actual-profit
     */
    Route::get('/test/bulk-actual-profit', function () {
        try {
            $opportunity = InvestmentOpportunity::first();

            if (!$opportunity) {
                return response()->json([
                    'error' => 'No opportunities found. Create one first.'
                ], 404);
            }

            // Default values
            $actualProfitPerShare = 20.50;
            $actualNetProfitPerShare = 17.25;

            // Check for authorize investments
            $authorizeInvestments = $opportunity->investments()
                ->where('investment_type', 'authorize')
                ->whereNull('actual_profit_per_share')
                ->count();

            if ($authorizeInvestments === 0) {
                return response()->json([
                    'error' => 'No authorize investments found that need actual profit recording.',
                    'opportunity_id' => $opportunity->id,
                    'opportunity_name' => $opportunity->name,
                    'authorize_investments_count' => $authorizeInvestments,
                ], 404);
            }

            $profitData = [
                'actual_profit_per_share' => $actualProfitPerShare,
                'actual_net_profit_per_share' => $actualNetProfitPerShare,
            ];

            $request = new Request();
            $request->merge($profitData);

            $controller = app(AdminInvestmentController::class);
            $result = $controller->processActualProfitForAllAuthorize($request, $opportunity);

            return response()->json([
                'success' => true,
                'message' => 'Bulk actual profit test completed successfully with default values',
                'opportunity_id' => $opportunity->id,
                'opportunity_name' => $opportunity->name,
                'authorize_investments_found' => $authorizeInvestments,
                'profit_values' => $profitData,
                'result' => $result->getData(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    })->name('test.bulk-actual-profit.default');

    /**
     * Test returns distribution
     * GET /test/returns-distribution/{opportunity_id}
     */
    Route::get('/test/returns-distribution/{opportunity_id}', function ($opportunity_id) {
        try {
            $opportunity = InvestmentOpportunity::findOrFail($opportunity_id);

            $controller = app(AdminInvestmentController::class);
            $result = $controller->processReturnsDistribution($opportunity);

            return response()->json([
                'success' => true,
                'message' => 'Returns distribution test completed successfully',
                'opportunity_id' => $opportunity->id,
                'opportunity_name' => $opportunity->name,
                'result' => $result->getData(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
    })->name('test.returns-distribution');
});
//test route
Route::get('/test/{investment_id}', function ($investment_id) {
    $investment = Investment::findOrFail($investment_id);
    return $investment->getProfitPerformanceStatus();
});

//test returns distribution
Route::get('/test/returns-distribution-test/{opportunity_id}', function ($opportunity_id) {
    $opportunity = InvestmentOpportunity::findOrFail($opportunity_id);
    $controller = app(AdminInvestmentController::class);
    return $result = $controller->processReturnsDistribution($opportunity);
    return response()->json([
        'success' => $result['success'],
        'message' => $result['message'],
    ]);
});


Route::get('/simulate-paymob-webhook', function () {

    // JSON payload as a PHP array
    $payload = '{
	"type": "TRANSACTION",
	"obj": {
		"id": 1028466,
		"pending": false,
		"amount_cents": 100000,
		"success": true,
		"is_auth": false,
		"is_capture": false,
		"is_standalone_payment": true,
		"is_voided": false,
		"is_refunded": false,
		"is_3d_secure": true,
		"integration_id": 16105,
		"profile_id": 11883,
		"has_parent_transaction": false,
		"order": {
			"id": 1059383,
			"created_at": "2025-10-17T15:00:42.267841+03:00",
			"delivery_needed": false,
			"merchant": {
				"id": 11883,
				"created_at": "2025-09-20T07:32:28.663211+03:00",
				"phones": [
					"+966590971717"
				],
				"company_emails": null,
				"company_name": "Slide",
				"state": null,
				"country": "SAU",
				"city": "temp",
				"postal_code": null,
				"street": null
			},
			"collector": null,
			"amount_cents": 100000,
			"shipping_data": {
				"id": 747275,
				"first_name": "User",
				"last_name": "Name",
				"street": "N/A",
				"building": "N/A",
				"floor": "N/A",
				"apartment": "N/A",
				"city": "Riyadh",
				"state": "Riyadh",
				"country": "Saudi Arabia",
				"email": "s@gmail.com",
				"phone_number": "966000000000",
				"postal_code": "NA",
				"extra_description": null,
				"shipping_method": "UNK",
				"order_id": 1059383,
				"order": 1059383
			},
			"currency": "SAR",
			"is_payment_locked": false,
			"is_return": false,
			"is_cancel": false,
			"is_returned": false,
			"is_canceled": false,
			"merchant_order_id": "WALLET-CHARGE-15-1760702441",
			"wallet_notification": null,
			"paid_amount_cents": 100000,
			"notify_user_with_email": false,
			"items": [
				{
					"name": "Wallet Charge",
					"description": "Wallet charging - 1000 SAR",
					"amount_cents": 100000,
					"quantity": 1
				}
			],
			"order_url": "NA",
			"commission_fees": 0,
			"delivery_fees_cents": 0,
			"delivery_vat_cents": 0,
			"payment_method": "tbc",
			"merchant_staff_tag": null,
			"api_source": "OTHER",
			"data": {
				"notification_url": "https://slide.osta-app.com/api/paymob/webhook"
			},
			"payment_status": "PAID",
			"terminal_version": null
		},
		"created_at": "2025-10-17T15:00:48.895597+03:00",
		"transaction_processed_callback_responses": [],
		"currency": "SAR",
		"source_data": {
			"pan": "2346",
			"type": "card",
			"tenure": null,
			"sub_type": "MasterCard"
		},
		"api_source": "SDK",
		"terminal_id": null,
		"merchant_commission": 0,
		"accept_fees": 0,
		"installment": null,
		"discount_details": [],
		"is_void": false,
		"is_refund": false,
		"data": {
			"gateway_integration_pk": 16105,
			"klass": "MigsPayment",
			"created_at": "2025-10-17T12:01:03.600374",
			"amount": 100000,
			"currency": "SAR",
			"migs_order": {
				"acceptPartialAmount": false,
				"amount": 1000,
				"authenticationStatus": "AUTHENTICATION_SUCCESSFUL",
				"chargeback": {
					"amount": 0,
					"currency": "SAR"
				},
				"creationTime": "2025-10-17T12:01:01.596Z",
				"currency": "SAR",
				"id": "aa1059383",
				"lastUpdatedTime": "2025-10-17T12:01:03.527Z",
				"merchantAmount": 1000,
				"merchantCategoryCode": "7372",
				"merchantCurrency": "SAR",
				"status": "CAPTURED",
				"totalAuthorizedAmount": 1000,
				"totalCapturedAmount": 1000,
				"totalRefundedAmount": 0
			},
			"merchant": "TEST601108800",
			"migs_result": "SUCCESS",
			"migs_transaction": {
				"acquirer": {
					"batch": 20251017,
					"date": "1017",
					"id": "NCB_S2I",
					"merchantId": "601108800",
					"settlementDate": "2025-10-17",
					"timeZone": "+0300",
					"transactionId": "123456789"
				},
				"amount": 1000,
				"authenticationStatus": "AUTHENTICATION_SUCCESSFUL",
				"authorizationCode": "210955",
				"currency": "SAR",
				"id": "1028466",
				"receipt": "529012210955",
				"source": "INTERNET",
				"stan": "210955",
				"terminal": "NCBS2I02",
				"type": "PAYMENT"
			},
			"txn_response_code": "0",
			"acq_response_code": "00",
			"message": "Approved",
			"merchant_txn_ref": "1028466",
			"order_info": "aa1059383",
			"receipt_no": "529012210955",
			"transaction_no": "123456789",
			"batch_no": 20251017,
			"authorize_id": "210955",
			"card_type": "MASTERCARD",
			"card_num": "512345xxxxxx2346",
			"secure_hash": null,
			"avs_result_code": null,
			"avs_acq_response_code": "00",
			"captured_amount": 1000,
			"authorised_amount": 1000,
			"refunded_amount": 0,
			"acs_eci": "02",
			"txn_response_code_new": "APPROVED"
		},
		"is_hidden": false,
		"payment_key_claims": {
			"extra": {
				"user_id": 15,
				"amount_sar": 1000,
				"operation_type": "wallet_charge",
				"merchant_order_id": "WALLET-CHARGE-15-1760702441"
			},
			"user_id": 13745,
			"currency": "SAR",
			"order_id": 1059383,
			"created_by": 13745,
			"is_partner": false,
			"amount_cents": 100000,
			"billing_data": {
				"city": "Riyadh",
				"email": "s@gmail.com",
				"floor": "N/A",
				"state": "Riyadh",
				"street": "N/A",
				"country": "Saudi Arabia",
				"building": "N/A",
				"apartment": "N/A",
				"last_name": "Name",
				"first_name": "User",
				"postal_code": "NA",
				"phone_number": "+966000000000",
				"extra_description": "NA"
			},
			"integration_id": 16105,
			"notification_url": "https://slide.osta-app.com/api/paymob/webhook",
			"lock_order_when_paid": false,
			"next_payment_intention": "pi_test_bd75fdc7b804432ca8c8e0e691eefe9c",
			"single_payment_attempt": false
		},
		"error_occured": false,
		"is_live": false,
		"other_endpoint_reference": null,
		"refunded_amount_cents": 0,
		"source_id": -1,
		"is_captured": false,
		"captured_amount": 0,
		"merchant_staff_tag": null,
		"updated_at": "2025-10-17T15:01:03.607391+03:00",
		"is_settled": false,
		"bill_balanced": false,
		"is_bill": false,
		"owner": 13745,
		"parent_transaction": null
	},
	"accept_fees": 0,
	"issuer_bank": null,
	"transaction_processed_callback_responses": null,
	"hmac": "e53e3ba54850bcf70d6faa5477ee6791c50961cd915bf1077409a730aebdff7c19d920fc849028772bb9c1ba49fba20ce318effa98500f1ceaed18e71b30fd5f"
}';

    // Decode JSON to array
    $data = json_decode($payload, true);



    // Create a Laravel Request object from the $data array
    $request = new Request($data);

    // Call the controller method directly
    return app(PaymentWebhookController::class)->handlePaymobWebhook($request);
});

//test notification
Route::get('/test/notification', function () {
    $user = User::find(29);
    $lastInvestment = Investment::where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
    if (!$lastInvestment) {
        return response()->json([
            'success' => false,
            'message' => 'No investment found',
        ]);
    }
  return  $user->notifyNow(new InvestmentPurchasedNotification($lastInvestment));

});//test2
Route::get('/test2/{token}', function ($token) {
    $service = new \App\Services\FirebaseNotificationServiceOld();
    // $service->generateAccessToken();
    $title = 'Test';
    $body = 'Test message';
    return $service->sendNotification($token, $title, $body);
});

// Test push notification - hardcoded user ID
Route::get('/test/push-notification/{user_id}', function ($user_id) {
    try {
        // Hardcoded user ID - change this to test with different users
        $userId = $user_id; // Change this to your test user ID

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "User with ID {$userId} not found",
                'available_users' => User::select('id', 'phone', 'full_name')->limit(10)->get(),
            ], 404);
        }

        // Check if user has active FCM tokens
        $activeTokens = $user->activeFcmTokens()->count();

        if ($activeTokens === 0) {
            return response()->json([
                'success' => false,
                'message' => "User {$user->id} has no active FCM tokens",
                'user_id' => $user->id,
                'user_phone' => $user->phone,
                'total_tokens' => $user->fcmTokens()->count(),
                'active_tokens' => 0,
            ], 404);
        }

        // Initialize Firebase notification service
        $firebaseService = app(\App\Services\FirebaseNotificationService::class);

        // Hardcoded test notification
        $title = 'اختبار الإشعار';
        $body = 'هذا إشعار تجريبي لاختبار إشعارات Firebase';

        $data = [
            'type' => 'test_notification',
            'test_id' => time(),
            'click_action' => 'test',
        ];

        // Send notification
        return $result = $firebaseService->sendToUser($user, $title, $body, $data);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'full_name' => $user->full_name,
            ],
            'notification' => [
                'title' => $title,
                'body' => $body,
                'data' => $data,
            ],
            'tokens' => [
                'total' => $user->fcmTokens()->count(),
                'active' => $activeTokens,
            ],
            'result' => $result,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to send test notification',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
})->name('test.push-notification');

// Test Firebase credentials validation
Route::get('/test/firebase-credentials', function () {
    try {
        $credentialsPath = config('firebase.credentials_path');
        $projectId = config('firebase.project_id');

        // Check if file exists
        if (!file_exists($credentialsPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Firebase credentials file not found',
                'path' => $credentialsPath,
            ], 404);
        }

        // Read and decode credentials
        $credentialsContent = file_get_contents($credentialsPath);
        $credentials = json_decode($credentialsContent, true);

        if (!$credentials) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid JSON in credentials file',
                'json_error' => json_last_error_msg(),
            ], 400);
        }

        // Check required fields
        $requiredFields = ['type', 'project_id', 'private_key_id', 'private_key', 'client_email'];
        $missingFields = [];
        foreach ($requiredFields as $field) {
            if (!isset($credentials[$field]) || empty($credentials[$field])) {
                $missingFields[] = $field;
            }
        }

        // Validate private key format
        $privateKey = $credentials['private_key'] ?? '';
        $privateKeyValid = false;
        $privateKeyError = null;

        if (str_contains($privateKey, 'dummy') || str_contains($privateKey, 'dummy-key-content')) {
            $privateKeyError = 'Private key contains dummy/invalid content';
        } elseif (str_starts_with($privateKey, '-----BEGIN PRIVATE KEY-----') && str_ends_with(trim($privateKey), '-----END PRIVATE KEY-----')) {
            // Try to validate the key
            try {
                $resource = openssl_pkey_get_private($privateKey);
                if ($resource !== false) {
                    $privateKeyValid = true;
                    openssl_free_key($resource);
                } else {
                    $privateKeyError = 'OpenSSL cannot parse the private key: ' . openssl_error_string();
                }
            } catch (\Exception $e) {
                $privateKeyError = 'Error validating private key: ' . $e->getMessage();
            }
        } else {
            $privateKeyError = 'Private key format is invalid (must start with -----BEGIN PRIVATE KEY----- and end with -----END PRIVATE KEY-----)';
        }

        // Try to initialize Firebase
        $firebaseInitialized = false;
        $firebaseError = null;
        try {
            $factory = (new \Kreait\Firebase\Factory)
                ->withServiceAccount($credentialsPath)
                ->withProjectId($projectId);

            $messaging = $factory->createMessaging();
            $firebaseInitialized = true;
        } catch (\Exception $e) {
            $firebaseError = $e->getMessage();
        }

        return response()->json([
            'success' => $firebaseInitialized && $privateKeyValid && empty($missingFields),
            'credentials_file' => [
                'path' => $credentialsPath,
                'exists' => file_exists($credentialsPath),
                'readable' => is_readable($credentialsPath),
            ],
            'configuration' => [
                'project_id' => $projectId,
                'project_id_in_file' => $credentials['project_id'] ?? null,
                'project_id_match' => $projectId === ($credentials['project_id'] ?? null),
            ],
            'credentials_validation' => [
                'missing_fields' => $missingFields,
                'has_type' => isset($credentials['type']),
                'has_private_key' => !empty($credentials['private_key']),
                'has_client_email' => !empty($credentials['client_email']),
                'private_key_length' => strlen($privateKey),
                'private_key_valid' => $privateKeyValid,
                'private_key_error' => $privateKeyError,
                'client_email' => $credentials['client_email'] ?? null,
                'private_key_id' => $credentials['private_key_id'] ?? null,
            ],
            'firebase_initialization' => [
                'success' => $firebaseInitialized,
                'error' => $firebaseError,
            ],
            'recommendations' => [
                $privateKeyError ? '⚠️ Replace the dummy/invalid private key with a real Firebase service account key from Firebase Console' : null,
                !empty($missingFields) ? '⚠️ Missing required fields: ' . implode(', ', $missingFields) : null,
                $projectId !== ($credentials['project_id'] ?? null) ? '⚠️ Project ID mismatch between config and credentials file' : null,
                !$firebaseInitialized ? '⚠️ Firebase cannot be initialized. Check the error message above.' : null,
            ],
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error validating Firebase credentials',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
})->name('test.firebase-credentials');

