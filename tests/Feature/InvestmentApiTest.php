<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\InvestmentOpportunity;
use App\Models\InvestorProfile;
use App\Models\Investment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class InvestmentApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $investorProfile;
    protected $opportunity;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user and investor profile
        $this->user = User::factory()->create();
        $this->investorProfile = InvestorProfile::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Create test investment opportunity
        $this->opportunity = InvestmentOpportunity::factory()->create([
            'status' => 'open',
            'share_price' => 1000.00,
            'available_shares' => 10,
            'min_investment' => 1,
            'max_investment' => 5,
            'shipping_fee_per_share' => 50.00,
            'expected_profit' => 100.00,
            'expected_net_profit' => 80.00,
        ]);
    }

    /** @test */
    public function it_can_create_an_investment_successfully()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 2,
            'type' => 'myself'
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'result' => [
                        'id',
                        'shares',
                        'total_investment',
                        'total_payment_required',
                        'investment_type',
                        'status'
                    ]
                ]);

        // Verify investment was created in database
        $this->assertDatabaseHas('investments', [
            'user_id' => $this->user->id,
            'opportunity_id' => $this->opportunity->id,
            'shares' => 2,
            'investment_type' => 'myself',
            'status' => 'active'
        ]);

        // Verify total investment calculation
        $investment = Investment::where('user_id', $this->user->id)->first();
        $this->assertEquals(2000.00, $investment->total_investment); // 2 shares * 1000
        $this->assertEquals(2100.00, $investment->total_payment_required); // 2000 + (2 * 50 shipping)
    }

    /** @test */
    public function it_validates_required_fields()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'investment_opportunity_id',
                    'shares',
                    'type'
                ]);
    }

    /** @test */
    public function it_validates_investment_opportunity_exists()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => 999999,
            'shares' => 2,
            'type' => 'myself'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['investment_opportunity_id']);
    }

    /** @test */
    public function it_validates_shares_is_positive_integer()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 0,
            'type' => 'myself'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['shares']);
    }

    /** @test */
    public function it_validates_investment_type()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 2,
            'type' => 'invalid_type'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['type']);
    }

    /** @test */
    public function it_prevents_investing_in_own_opportunity()
    {
        // Create opportunity owned by the same user
        $ownerProfile = \App\Models\OwnerProfile::factory()->create([
            'user_id' => $this->user->id
        ]);

        $ownOpportunity = InvestmentOpportunity::factory()->create([
            'owner_profile_id' => $ownerProfile->id,
            'status' => 'open'
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $ownOpportunity->id,
            'shares' => 2,
            'type' => 'myself'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false
                ]);
    }

    /** @test */
    public function it_handles_insufficient_shares()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 100, // More than available
            'type' => 'myself'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false
                ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 2,
            'type' => 'myself'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_handles_authorize_investment_type()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 2,
            'type' => 'authorize'
        ]);

        $response->assertStatus(201);

        // Verify investment was created with correct type
        $investment = Investment::where('user_id', $this->user->id)->first();
        $this->assertEquals('authorize', $investment->investment_type);
        $this->assertEquals(2000.00, $investment->total_payment_required); // No shipping fee for authorize
    }

    /** @test */
    public function it_updates_existing_investment_when_adding_more_shares()
    {
        Sanctum::actingAs($this->user);

        // Create initial investment
        $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 2,
            'type' => 'myself'
        ]);

        // Add more shares to the same opportunity
        $response = $this->postJson('/api/investor/invest', [
            'investment_opportunity_id' => $this->opportunity->id,
            'shares' => 1,
            'type' => 'myself'
        ]);

        $response->assertStatus(201);

        // Verify only one investment record exists with updated shares
        $this->assertCount(1, Investment::where('user_id', $this->user->id)->get());

        $investment = Investment::where('user_id', $this->user->id)->first();
        $this->assertEquals(3, $investment->shares);
        $this->assertEquals(3000.00, $investment->total_investment);
        $this->assertEquals(3150.00, $investment->total_payment_required); // 3000 + (3 * 50)
    }
}
