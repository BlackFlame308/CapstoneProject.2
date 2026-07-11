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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SafeTrackIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private User $captain;
    private User $encoder;
    private User $householdUser;
    private Role $captainRole;
    private Role $encoderRole;
    private Role $householdRole;
    private Barangay $barangay;
    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create roles
        $this->captainRole   = Role::create(['name' => 'Captain', 'role_key' => 'admin']);
        $this->encoderRole   = Role::create(['name' => 'Encoder', 'role_key' => 'encoder']);
        $this->householdRole = Role::create(['name' => 'Household', 'role_key' => 'household_resident']);

        // 2. Create users
        $this->captain = User::create([
            'name'                 => 'Captain Test',
            'email'                => 'captain@example.test',
            'username'             => 'captain',
            'password'             => Hash::make('password123'),
            'role_id'              => $this->captainRole->role_id,
            'must_change_password' => false,
            'is_active'            => true,
        ]);

        $this->encoder = User::create([
            'name'                 => 'Encoder Test',
            'email'                => 'encoder@example.test',
            'username'             => 'encoder',
            'password'             => Hash::make('password123'),
            'role_id'              => $this->encoderRole->role_id,
            'must_change_password' => false,
            'is_active'            => true,
        ]);

        // 3. Create locations
        $region   = Region::create(['name' => 'Region Test']);
        $province = Province::create(['region_id' => $region->region_id, 'name' => 'Province Test']);
        $city     = City::create(['province_id' => $province->province_id, 'name' => 'City Test']);
        $this->barangay = Barangay::create(['city_id' => $city->city_id, 'name' => 'Barangay Test']);

        // 4. Create household
        $address = Address::create([
            'barangay_id' => $this->barangay->barangay_id,
            'purok_sitio' => 'Purok 1',
        ]);

        $this->household = Household::create([
            'household_code' => 'HH-INTEG-001',
            'household_name' => 'Integ Test Household',
            'address_id'     => $address->address_id,
            'created_by'     => $this->captain->user_id,
        ]);

        $this->householdUser = User::create([
            'name'                 => 'Household User',
            'email'                => 'household@example.test',
            'username'             => 'household',
            'password'             => Hash::make('password123'),
            'role_id'              => $this->householdRole->role_id,
            'household_id'         => $this->household->household_id,
            'must_change_password' => false,
            'is_active'            => true,
        ]);
    }

    /**
     * Test system features access control based on user roles.
     */
    public function test_roles_and_permissions_access_control(): void
    {
        // Captain can view admin dashboard
        $this->actingAs($this->captain)
             ->get(route('admin.dashboard'))
             ->assertOk();

        // Encoder can view admin dashboard
        $this->actingAs($this->encoder)
             ->get(route('admin.dashboard'))
             ->assertOk();

        // Household user gets redirected from admin dashboard
        $this->actingAs($this->householdUser)
             ->get(route('admin.dashboard'))
             ->assertRedirect(route('login'));

        // Captain has delete access
        $this->assertTrue($this->captain->canDeleteHouseholds());

        // Encoder cannot delete households
        $this->assertFalse($this->encoder->canDeleteHouseholds());

        // Household user cannot delete households
        $this->assertFalse($this->householdUser->canDeleteHouseholds());
    }

    /**
     * Test web-based login and registration flows.
     */
    public function test_web_authentication_and_registration(): void
    {
        // 1. Web Login Flow
        $response = $this->post(route('login'), [
            'email'    => 'captain@example.test',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->captain);

        // 2. Admin account registration flow (Captain registers a new Moderator)
        $moderatorRole = Role::create(['name' => 'Moderator', 'role_key' => 'moderator']);

        $this->actingAs($this->captain)
             ->post(route('admin.accounts.store'), [
                 'name'                  => 'Moderator User',
                 'username'              => 'moderator-user',
                 'email'                 => 'moderator@example.test',
                 'contact_number'        => '09171234567',
                 'role_id'               => $moderatorRole->role_id,
                 'password'              => 'password123',
                 'password_confirmation' => 'password123',
             ])
             ->assertSessionHasNoErrors()
             ->assertRedirect(route('admin.accounts.index'));

        $this->assertTrue(User::where('email', 'moderator@example.test')->exists());
    }

    /**
     * Test connection to other subsystems (evacuation, rescue, logistics) by
     * dynamically creating schemas to simulate multi-system database.
     */
    public function test_subsystem_connections_and_reports(): void
    {
        // 1. Create mock tables in SQLite test DB for reports
        Schema::create('evacuation_records', function ($table) {
            $table->increments('record_id');
            $table->string('status', 50);
            $table->unsignedInteger('event_id')->nullable();
            $table->unsignedInteger('center_id')->nullable();
            $table->string('household_id', 255);
            $table->timestamps();
        });

        Schema::create('disaster_events', function ($table) {
            $table->increments('event_id');
            $table->string('name', 255);
        });

        Schema::create('evacuation_centers', function ($table) {
            $table->increments('evacuation_center_id');
            $table->string('name', 255);
        });

        Schema::create('responder_assignments', function ($table) {
            $table->increments('assignment_id');
            $table->string('status', 50);
            $table->dateTime('assigned_at');
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('responder_id')->nullable();
            $table->unsignedInteger('team_id')->nullable();
        });

        Schema::create('responders', function ($table) {
            $table->increments('responder_id');
            $table->string('full_name', 255);
        });

        // Add team_type missing column in rescue_teams schema for test
        Schema::table('rescue_teams', function ($table) {
            $table->string('team_type', 100)->nullable();
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

        // 2. Seed mock data
        DB::table('disaster_events')->insert(['event_id' => 10, 'name' => 'Super Typhoon']);
        DB::table('evacuation_centers')->insert(['evacuation_center_id' => 20, 'name' => 'Gymnasuim Center']);
        DB::table('evacuation_records')->insert([
            'status'       => 'active',
            'event_id'     => 10,
            'center_id'    => 20,
            'household_id' => $this->household->household_id,
            'created_at'   => now(),
        ]);

        DB::table('responders')->insert(['responder_id' => 30, 'full_name' => 'John Rescuer']);
        DB::table('rescue_teams')->insert(['team_id' => 40, 'team_name' => 'Alpha Team', 'team_type' => 'medical']);
        DB::table('responder_assignments')->insert([
            'status'       => 'pending',
            'assigned_at'  => now(),
            'responder_id' => 30,
            'team_id'      => 40,
        ]);

        DB::table('urgency_levels')->insert(['urgency_id' => 50, 'urgency_key' => 'high', 'urgency_label' => 'High']);
        DB::table('resource_request_status')->insert(['status_id' => 60, 'status_key' => 'pending', 'status_label' => 'Pending']);
        DB::table('resource_requests')->insert([
            'resource_type'        => 'Water Bottles',
            'quantity'             => 100,
            'evacuation_center_id' => 20,
            'urgency_id'             => 50,
            'status_id'            => 60,
            'created_at'           => now(),
        ]);

        // 3. Make requests to Reports API endpoints with valid secret handshake
        $secretKey = config('app.api_handshake_key') ?: 'safetrack_handshake_secret';
        $token     = $this->captain->createToken('api-token')->plainTextToken;

        // Evacuation Report
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->withHeader('X-Handshake-Key', $secretKey)
                         ->getJson('/api/reports/evacuation');
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.0.status', 'active')
                 ->assertJsonPath('data.0.event_name', 'Super Typhoon')
                 ->assertJsonPath('data.0.center_name', 'Gymnasuim Center')
                 ->assertJsonPath('data.0.household.name', 'Integ Test Household');

        // Rescue Report
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->withHeader('X-Handshake-Key', $secretKey)
                         ->getJson('/api/reports/rescue');
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.0.status', 'pending')
                 ->assertJsonPath('data.0.responder_name', 'John Rescuer')
                 ->assertJsonPath('data.0.team.name', 'Alpha Team')
                 ->assertJsonPath('data.0.team.type', 'medical');

        // Logistics Report
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->withHeader('X-Handshake-Key', $secretKey)
                         ->getJson('/api/reports/logistics');
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.0.item_type', 'Water Bottles')
                 ->assertJsonPath('data.0.quantity', 100)
                 ->assertJsonPath('data.0.center_name', 'Gymnasuim Center')
                 ->assertJsonPath('data.0.urgency', 'High')
                 ->assertJsonPath('data.0.status.label', 'Pending');
    }

    /**
     * Test that manual household creation and CSV imports matching in Barangay
     * and Purok/Sitio resolve to the same location, differing only by street address.
     */
    public function test_csv_and_manual_add_same_area(): void
    {
        // 1. Manually add household via API
        $manualAddress = Address::create([
            'barangay_id'   => $this->barangay->barangay_id,
            'purok_sitio'   => 'Sitio Alpha',
            'street'        => 'Rizal Street',
        ]);

        $manualHousehold = Household::create([
            'household_code' => 'HH-MANUAL-999',
            'household_name' => 'Manual Dela Cruz Household',
            'address_id'     => $manualAddress->address_id,
            'created_by'     => $this->captain->user_id,
        ]);

        // 2. Import CSV household with same Barangay and Purok/Sitio but different street
        $parser = new \App\Services\Csv\CsvRowParser('test_source', $this->captain->user_id);
        $currentHousehold = null;

        $parser->processRow(
            ['Maria', 'Santos', '1990-05-15', 'F', 'HH-CSV-999', 'Bonifacio Street', 'Sitio Alpha', 'Barangay Test'],
            ['first_name', 'last_name', 'birth_date', 'sex', 'household_code', 'street', 'purok', 'barangay'],
            2,
            $currentHousehold
        );

        $csvHousehold = Household::where('household_code', 'HH-CSV-999')->firstOrFail();

        // 3. Verify addresses resolve to same area (same barangay, same normalized purok_sitio)
        $this->assertSame($manualHousehold->address->barangay_id, $csvHousehold->address->barangay_id);
        $this->assertSame($manualHousehold->address->purok_sitio, $csvHousehold->address->purok_sitio);

        // 4. Verify they differ by street address
        $this->assertSame('Rizal Street', $manualHousehold->address->street);
        $this->assertSame('Bonifacio Street', $csvHousehold->address->street);
    }

    /**
     * Test all admin areas and access permissions under the Encoder role.
     */
    public function test_encoder_role_functionality_and_pages(): void
    {
        // Log in as Encoder
        $this->actingAs($this->encoder);

        // 1. Verify get requests for core admin panels work
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.households.index'))->assertOk();
        $this->get(route('admin.residents.index'))->assertOk();
        $this->get(route('admin.accounts.index'))->assertOk();
        $this->get(route('admin.search.form'))->assertOk();

        // 2. Verify creation page routes are accessible
        $this->get(route('admin.households.create'))->assertOk();
        $this->get(route('admin.residents.create', $this->household))->assertOk();
        $this->get(route('admin.accounts.create'))->assertOk();

        // 3. Verify delete/destroy operations are rejected for Encoder
        // Try deleting a household
        $this->delete(route('admin.households.destroy', $this->household))
             ->assertStatus(403);

        // Try deleting a user account
        $this->delete(route('admin.accounts.destroy', $this->householdUser))
             ->assertStatus(403);
    }
}

