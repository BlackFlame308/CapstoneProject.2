<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Barangay;
use App\Models\City;
use App\Models\Household;
use App\Models\Member;
use App\Models\Province;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubsystemReportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $captain;
    private Role $captainRole;
    private Household $household;
    private string $handshakeSecret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->captainRole = Role::create(['name' => 'Captain', 'role_key' => 'admin']);

        $this->captain = User::create([
            'name'                 => 'Captain Test',
            'email'                => 'captain_report@example.test',
            'username'             => 'captain_report',
            'password'             => Hash::make('password123'),
            'role_id'              => $this->captainRole->role_id,
            'must_change_password' => false,
            'is_active'            => true,
        ]);

        $region   = Region::create(['name' => 'Region X']);
        $province = Province::create(['region_id' => $region->region_id, 'name' => 'Province X']);
        $city     = City::create(['province_id' => $province->province_id, 'name' => 'City X']);
        $barangay = Barangay::create(['city_id' => $city->city_id, 'name' => 'Barangay X']);

        $address = Address::create([
            'barangay_id' => $barangay->barangay_id,
            'purok_sitio' => 'Sitio Mabuhay',
        ]);

        $this->household = Household::create([
            'household_code' => 'HH-REPORT-001',
            'household_name' => 'Report Test Household',
            'address_id'     => $address->address_id,
            'created_by'     => $this->captain->user_id,
        ]);

        $this->handshakeSecret = config('app.api_handshake_key') ?: 'safetrack_handshake_secret';
    }

    public function test_pregnant_count_and_sitio_ranking_calculation(): void
    {
        // Add pregnant member
        Member::create([
            'household_id' => $this->household->household_id,
            'first_name'   => 'Juana',
            'last_name'    => 'Dela Cruz',
            'birth_date'   => '1995-04-10',
            'sex'          => 'Female',
            'is_pregnant'  => true,
            'is_pwd'       => false,
        ]);

        // Add PWD member
        Member::create([
            'household_id' => $this->household->household_id,
            'first_name'   => 'Pedro',
            'last_name'    => 'Dela Cruz',
            'birth_date'   => '1988-08-20',
            'sex'          => 'Male',
            'is_pregnant'  => false,
            'is_pwd'       => true,
        ]);

        $dashboardService = app(DashboardService::class);
        $stats = $dashboardService->getStats();

        $this->assertArrayHasKey('totalPregnant', $stats);
        $this->assertEquals(1, $stats['totalPregnant']);
        $this->assertEquals(1, $stats['totalPWD']);

        $sitioRankings = $dashboardService->getSitioVulnerabilityRanking();
        $this->assertNotEmpty($sitioRankings);

        $sitio = $sitioRankings[0];
        $this->assertEquals('Sitio Mabuhay', $sitio['sitio']);
        $this->assertEquals(1, $sitio['pregnant_count']);
        $this->assertEquals(1, $sitio['pwd_count']);
        // Vulnerability Score: (PWD 2.0) + (Pregnant 1.5) = 3.5
        $this->assertEquals(3.5, $sitio['vulnerability_score']);
    }

    public function test_subsystem_report_ingestion_and_retrieval(): void
    {
        // 1. Create mock tables in SQLite
        Schema::create('evacuation_records', function ($table) {
            $table->increments('record_id');
            $table->string('status', 50);
            $table->unsignedInteger('event_id')->nullable();
            $table->unsignedInteger('center_id')->nullable();
            $table->string('household_id', 255);
            $table->unsignedInteger('evacuated_count')->default(1);
            $table->timestamps();
        });

        Schema::create('responder_assignments', function ($table) {
            $table->increments('assignment_id');
            $table->string('status', 50);
            $table->dateTime('assigned_at');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('responder_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
        });

        Schema::create('resource_requests', function ($table) {
            $table->increments('request_id');
            $table->string('resource_type', 255);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('evacuation_center_id')->nullable();
            $table->unsignedInteger('urgency_id')->nullable();
            $table->unsignedInteger('status_id')->nullable();
            $table->timestamps();
        });

        $token = $this->captain->createToken('test-token')->plainTextToken;

        // 2. Test ingestion WITHOUT handshake header (should fail 401)
        $this->withHeader('Authorization', "Bearer {$token}")
             ->postJson('/api/reports/evacuation', [
                 'household_id' => $this->household->household_id,
                 'status'       => 'active',
             ])
             ->assertStatus(401);

        // 3. Test ingestion WITH handshake header (Evacuation)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->withHeader('X-Handshake-Key', $this->handshakeSecret)
                         ->postJson('/api/reports/evacuation', [
                             'household_id'    => $this->household->household_id,
                             'evacuated_count' => 4,
                             'status'          => 'active',
                         ]);
        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // 4. Test ingestion WITH handshake header (Rescue)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->withHeader('X-Handshake-Key', $this->handshakeSecret)
                         ->postJson('/api/reports/rescue', [
                             'status'      => 'assigned',
                             'assigned_at' => now()->toDateTimeString(),
                         ]);
        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // 5. Test ingestion WITH handshake header (Logistics)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->withHeader('X-Handshake-Key', $this->handshakeSecret)
                         ->postJson('/api/reports/logistics', [
                             'resource_type' => 'Emergency Food Packs',
                             'quantity'      => 50,
                         ]);
        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success');

        // 6. Test GET retrieval to verify stored reports
        $resEvac = $this->withHeader('Authorization', "Bearer {$token}")
             ->withHeader('X-Handshake-Key', $this->handshakeSecret)
             ->getJson('/api/reports/evacuation');
        $resEvac->assertStatus(200)
             ->assertJsonPath('data.0.status', 'active');

        $this->withHeader('Authorization', "Bearer {$token}")
             ->withHeader('X-Handshake-Key', $this->handshakeSecret)
             ->getJson('/api/reports/logistics')
             ->assertStatus(200)
             ->assertJsonPath('data.0.item_type', 'Emergency Food Packs')
             ->assertJsonPath('data.0.quantity', 50);
    }
}
