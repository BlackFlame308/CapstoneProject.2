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
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SafeTrackApiTest extends TestCase
{
    use RefreshDatabase;

    private User $captain;
    private User $encoder;
    private Role $captainRole;
    private Role $encoderRole;
    private Role $householdRole;
    private Barangay $barangay;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Roles
        $this->captainRole   = Role::create(['name' => 'Captain', 'role_key' => 'admin']);
        $this->encoderRole   = Role::create(['name' => 'Encoder', 'role_key' => 'encoder']);
        $this->householdRole = Role::create(['name' => 'Household', 'role_key' => 'household_resident']);

        // 2. Create Users
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

        // 3. Create Locations
        $region   = Region::create(['name' => 'Region Test']);
        $province = Province::create(['region_id' => $region->region_id, 'name' => 'Province Test']);
        $city     = City::create(['province_id' => $province->province_id, 'name' => 'City Test']);
        $this->barangay = Barangay::create(['city_id' => $city->city_id, 'name' => 'Barangay Test']);
    }

    /**
     * Test Auth Flow: login, change password, register, and logout.
     */
    public function test_auth_and_user_registration_api_workflow(): void
    {
        // 1. Failed Login
        $response = $this->postJson('/api/login', [
            'email'    => 'captain@example.test',
            'password' => 'wrong_password',
        ]);
        $response->assertStatus(401)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', 'Invalid credentials');

        // 2. Successful Login
        $response = $this->postJson('/api/login', [
            'email'    => 'captain@example.test',
            'password' => 'password123',
        ]);
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonStructure(['data' => ['user', 'token']]);

        $token = $response->json('data.token');

        // 3. Register user (only Captain can register)
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/register', [
                             'name'                  => 'New Encoder',
                             'email'                 => 'newencoder@example.test',
                             'password'              => 'password123',
                             'password_confirmation' => 'password123',
                             'role_id'               => $this->encoderRole->role_id,
                         ]);
        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.name', 'New Encoder');

        // 4. Change Password
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/change-password', [
                             'current_password'      => 'password123',
                             'new_password'          => 'newsecurepassword',
                             'new_password_confirmation' => 'newsecurepassword',
                         ]);
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('message', 'Password changed successfully');

        // 5. Logout
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/logout');
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');
    }

    /**
     * Test Household and Member CRUD API operations.
     */
    public function test_household_and_member_crud_endpoints(): void
    {
        $loginResponse = $this->postJson('/api/login', [
            'email'    => 'captain@example.test',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        // 1. Create a Household with Head and Members
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/households', [
                             'household_name'    => 'Dela Cruz Household',
                             'email'             => 'delacruz@example.test',
                             'street'            => 'Rizal St.',
                             'purok_sitio'       => 'Purok Uno',
                             'house_number'      => '12A',
                             'zip_code'          => '6000',
                             'barangay_id'       => $this->barangay->barangay_id,
                             'contact_number'    => '09171112222',
                             'emergency_contact' => '09173334444',
                             'head_first_name'   => 'Juan',
                             'head_middle_name'  => 'M',
                             'head_last_name'    => 'Dela Cruz',
                             'members' => [
                                 [
                                     'first_name'   => 'Maria',
                                     'middle_name'  => 'M',
                                     'last_name'    => 'Dela Cruz',
                                     'birth_date'   => '2015-08-20',
                                     'sex'          => 'F',
                                     'relation'     => 'Child',
                                     'civil_status' => 'Single',
                                     'is_pwd'       => false,
                                 ]
                             ]
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.household_name', 'Dela Cruz Household');

        $householdId = $response->json('data.household_code');

        // 2. Index Households
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson('/api/households');
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonStructure(['data' => ['data']]);

        // 3. Show Household
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->getJson("/api/households/{$householdId}");
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.household_name', 'Dela Cruz Household');

        // 4. Update Household Info
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->putJson("/api/households/{$householdId}", [
                             'household_name' => 'Dela Cruz Clan',
                             'purok_sitio'    => 'Purok Dos',
                         ]);
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.household_name', 'Dela Cruz Clan');

        // 5. Add a Member via API
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->postJson('/api/members', [
                             'household_id' => $householdId,
                             'first_name'   => 'Pedro',
                             'last_name'    => 'Dela Cruz',
                             'birth_date'   => '1990-12-05',
                             'sex'          => 'M',
                             'civil_status' => 'Single',
                             'is_pwd'       => true,
                         ]);
        $response->assertStatus(201)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.name', 'Pedro Dela Cruz');

        $memberId = $response->json('data.member_id');

        // 6. Update Member via API
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->putJson("/api/members/{$memberId}", [
                             'first_name' => 'Pedro Antonio',
                         ]);
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('data.name', 'Pedro Antonio Dela Cruz');

        // 7. Delete Member
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->deleteJson("/api/members/{$memberId}");
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // 8. Delete Household
        $response = $this->withHeader('Authorization', "Bearer {$token}")
                         ->deleteJson("/api/households/{$householdId}");
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');
    }

    /**
     * Test Report Endpoints and secure handshake verification.
     */
    public function test_reports_api_handshake_protection(): void
    {
        $endpoints = [
            '/api/reports/evacuation',
            '/api/reports/rescue',
            '/api/reports/logistics',
        ];

        // 1. Guest request is rejected by auth:sanctum middleware
        foreach ($endpoints as $url) {
            $response = $this->getJson($url);
            $response->assertStatus(401)
                     ->assertJsonPath('message', 'Unauthenticated.');
        }

        // Log in to get a token for authenticated tests
        $loginResponse = $this->postJson('/api/login', [
            'email'    => 'captain@example.test',
            'password' => 'password123',
        ]);
        $token = $loginResponse->json('data.token');

        // 2. Authenticated request without X-Handshake-Key header is rejected by ReportController
        foreach ($endpoints as $url) {
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                             ->getJson($url);
            $response->assertStatus(401)
                     ->assertJsonPath('status', 'error')
                     ->assertJsonPath('message', 'Handshake verification failed: Invalid or missing X-Handshake-Key header.');
        }

        // 3. Authenticated request with correct X-Handshake-Key header is approved
        $secretKey = config('app.api_handshake_key') ?: 'safetrack_handshake_secret';
        foreach ($endpoints as $url) {
            $response = $this->withHeader('Authorization', "Bearer {$token}")
                             ->withHeader('X-Handshake-Key', $secretKey)
                             ->getJson($url);
            $response->assertStatus(200)
                     ->assertJsonPath('status', 'success')
                     ->assertJsonStructure(['data']);
        }
    }
}
