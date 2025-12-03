<?php

namespace Tests\Feature;

use App\Models\Bank;
use App\Models\InvestorProfile;
use App\Models\User;
use App\Services\WalletService;
use App\Support\CurrentProfile;
use Database\Seeders\BankSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class WithdrawalApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => BankSeeder::class]);
    }

    public function test_full_withdrawal_flow(): void
    {
        // 1) Create user + investor profile and wallet
        $user = User::create([
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '0500000000',
            'password' => bcrypt('password'),
            'is_active' => 1,
            'is_registered' => 1,
            'active_profile_type' => User::PROFILE_INVESTOR,
        ]);

        $investor = InvestorProfile::create([
            'user_id' => $user->id,
        ]);

        // Set current profile context
        $cp = app(CurrentProfile::class);
        $cp->type = 'investor';
        $cp->model = $investor;
        $cp->user = $user;

        // Deposit funds
        $walletService = app(WalletService::class);
        $walletService->depositToWallet($investor, 20000.00, ['description' => 'Test deposit']);

        // Authenticate via Sanctum
        $this->actingAs($user);

        // 2) Get available balance
        $this->getJson('/api/withdrawal/available-balance')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['result' => ['available_balance', 'formatted_balance']]);

        // 3) Get banks from DB
        $banksResp = $this->getJson('/api/withdrawal/banks')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->json('result.banks');

        $this->assertNotEmpty($banksResp, 'Seeded banks should be available');
        $bankId = $banksResp[0]['id'];

        // 4) Save a new bank account
        $iban = 'SA0380000000608010167519';
        $saveResp = $this->postJson('/api/withdrawal/bank-accounts', [
            'bank_id' => $bankId,
            'account_holder_name' => 'Test Investor',
            'iban' => $iban,
            'save_for_future' => true,
            'set_as_default' => true,
        ])->assertOk()->json('result.bank_account');

        $this->assertNotEmpty($saveResp['id']);

        // 5) List saved bank accounts
        $this->getJson('/api/withdrawal/bank-accounts')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['result' => ['bank_accounts' => [[
                'id', 'bank_id', 'bank_name', 'masked_account_number', 'is_default'
            ]]]]);

        // 6) Create withdrawal request using saved account
        $this->postJson('/api/withdrawal/request', [
            'amount' => 5000.00,
            'bank_account_id' => $saveResp['id'],
        ])->assertOk()
          ->assertJsonPath('success', true)
          ->assertJsonStructure(['result' => ['withdrawal_request' => [
              'id', 'reference_number', 'amount', 'status', 'bank_details'
          ]]]);

        // 7) Get withdrawal history
        $this->getJson('/api/withdrawal/history')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['result' => ['withdrawal_requests']]);
    }
}
