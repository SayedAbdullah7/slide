<?php

/**
 * Test script to verify wallet balance consistency across all APIs
 * سكريبت اختبار للتحقق من اتساق رصيد المحفظة عبر جميع APIs
 * 
 * Usage: php test_wallet_balance.php "على"
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\InvestorProfile;
use App\Services\WalletService;
use App\Services\StatisticsService;
use App\Services\InvestmentOpportunityService;

// Get investor name from command line argument or use default
$investorName = $argv[1] ?? 'على';

echo "========================================\n";
echo "اختبار اتساق رصيد المحفظة\n";
echo "Testing Wallet Balance Consistency\n";
echo "========================================\n\n";

// Find investor by name
echo "البحث عن المستثمر: $investorName\n";
echo "Searching for investor: $investorName\n\n";

$investor = InvestorProfile::where('full_name', 'like', "%$investorName%")->first();

if (!$investor) {
    echo "❌ لم يتم العثور على مستثمر باسم '$investorName'\n";
    echo "❌ Investor not found with name '$investorName'\n\n";
    
    // List all investors
    echo "المستثمرون المتاحون:\n";
    echo "Available investors:\n";
    $allInvestors = InvestorProfile::whereNotNull('full_name')->limit(10)->get(['id', 'full_name', 'user_id']);
    foreach ($allInvestors as $inv) {
        echo "  - ID: {$inv->id}, Name: {$inv->full_name}, User ID: {$inv->user_id}\n";
    }
    exit(1);
}

$user = $investor->user;
echo "✅ تم العثور على المستثمر:\n";
echo "✅ Investor found:\n";
echo "   - Investor ID: {$investor->id}\n";
echo "   - Name: {$investor->full_name}\n";
echo "   - User ID: {$user->id}\n";
echo "   - Email: {$user->email}\n";
echo "   - Phone: {$user->phone}\n\n";

// Initialize services
$walletService = app(WalletService::class);
$statisticsService = app(StatisticsService::class);
$investmentOpportunityService = app(InvestmentOpportunityService::class);

echo "========================================\n";
echo "التحقق من الرصيد في جميع APIs\n";
echo "Checking Balance in All APIs\n";
echo "========================================\n\n";

// Method 1: Direct from InvestorProfile
$balance1 = $investor->getWalletBalance();
echo "1. InvestorProfile::getWalletBalance()\n";
echo "   Balance: " . number_format($balance1, 2) . " SAR\n";
echo "   Formatted: " . number_format($balance1, 0) . " ريال\n\n";

// Method 2: WalletService::getWalletBalance()
$balance2 = $walletService->getWalletBalance($investor);
echo "2. WalletService::getWalletBalance(\$investor)\n";
echo "   Balance: " . number_format($balance2, 2) . " SAR\n";
echo "   Formatted: " . number_format($balance2, 0) . " ريال\n\n";

// Method 3: StatisticsService (simulates /api/investor/statistics)
$statisticsData = $statisticsService->getStatisticsData($investor, 'month');
$balance3 = $statisticsData['total_balance']['amount'];
echo "3. StatisticsService (simulates /api/investor/statistics)\n";
echo "   Balance: " . number_format($balance3, 2) . " SAR\n";
echo "   Formatted: {$statisticsData['total_balance']['formatted_amount']}\n\n";

// Method 4: InvestmentOpportunityService (simulates /api/investor/home?type=wallet)
$walletData = $investmentOpportunityService->getHomeData(['wallet'], [], $user->id);
$balance4 = (float) str_replace(',', '', $walletData['wallet']['balance'] ?? '0.00');
echo "4. InvestmentOpportunityService (simulates /api/investor/home?type=wallet)\n";
echo "   Balance: " . number_format($balance4, 2) . " SAR\n";
echo "   Formatted: {$walletData['wallet']['formatted_balance']}\n\n";

// Method 5: Direct wallet balance property
$balance5 = $investor->balance ?? 0.0;
echo "5. Direct \$investor->balance property\n";
echo "   Balance: " . number_format($balance5, 2) . " SAR\n";
echo "   Formatted: " . number_format($balance5, 0) . " ريال\n\n";

// Compare all balances
echo "========================================\n";
echo "نتائج المقارنة\n";
echo "Comparison Results\n";
echo "========================================\n\n";

$balances = [
    'InvestorProfile::getWalletBalance()' => $balance1,
    'WalletService::getWalletBalance()' => $balance2,
    'StatisticsService' => $balance3,
    'InvestmentOpportunityService' => $balance4,
    'Direct balance property' => $balance5,
];

$allMatch = true;
$firstBalance = null;

foreach ($balances as $method => $balance) {
    if ($firstBalance === null) {
        $firstBalance = $balance;
    }
    
    $match = abs($balance - $firstBalance) < 0.01; // Allow 0.01 difference for floating point
    $status = $match ? '✅' : '❌';
    
    echo "$status $method: " . number_format($balance, 2) . " SAR";
    if (!$match) {
        echo " (DIFFERENCE: " . number_format($balance - $firstBalance, 2) . ")";
        $allMatch = false;
    }
    echo "\n";
}

echo "\n";

if ($allMatch) {
    echo "✅ ✅ ✅ جميع الأرصدة متطابقة!\n";
    echo "✅ ✅ ✅ All balances match!\n";
    echo "\nالرصيد الموحد: " . number_format($firstBalance, 2) . " SAR\n";
    echo "Unified Balance: " . number_format($firstBalance, 2) . " SAR\n";
} else {
    echo "❌ ❌ ❌ هناك اختلافات في الأرصدة!\n";
    echo "❌ ❌ ❌ Balance inconsistencies found!\n";
    echo "\n⚠️  يجب مراجعة الكود للتأكد من استخدام WalletService::getWalletBalance() في جميع الأماكن\n";
    echo "⚠️  Please review code to ensure WalletService::getWalletBalance() is used everywhere\n";
}

echo "\n========================================\n";
echo "معلومات إضافية\n";
echo "Additional Information\n";
echo "========================================\n\n";

// Check if wallet exists
$hasWallet = $investor->hasWallet();
echo "Has Wallet: " . ($hasWallet ? 'Yes ✅' : 'No ❌') . "\n";

if ($hasWallet) {
    // Get wallet transactions count
    $transactionsCount = $investor->transactions()->count();
    echo "Transactions Count: $transactionsCount\n";
    
    // Get recent transactions
    $recentTransactions = $investor->transactions()
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get(['id', 'type', 'amount', 'created_at']);
    
    if ($recentTransactions->count() > 0) {
        echo "\nRecent Transactions:\n";
        foreach ($recentTransactions as $transaction) {
            $amount = $transaction->amount / 100; // Convert from cents
            $type = $transaction->type === 'deposit' ? 'Deposit' : 'Withdraw';
            echo "  - $type: " . number_format($amount, 2) . " SAR on {$transaction->created_at}\n";
        }
    }
}

echo "\n";









