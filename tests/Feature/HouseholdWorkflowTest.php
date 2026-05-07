<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\City;
use App\Models\Province;
use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Services\HouseholdService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_household_create_and_update_keeps_members_and_credentials(): void
    {
        $captainRole = Role::create(['name' => 'Captain']);
        Role::create(['name' => 'Household']);

        $captain = User::create([
            'name' => 'Captain Test',
            'email' => 'captain@example.test',
            'password' => 'password',
            'role_id' => $captainRole->id,
            'must_change_password' => false,
        ]);

        $region = Region::create(['name' => 'Region Test']);
        $province = Province::create(['region_id' => $region->id, 'name' => 'Province Test']);
        $city = City::create(['province_id' => $province->id, 'name' => 'City Test']);
        $barangay = Barangay::create(['city_id' => $city->id, 'name' => 'Barangay Test']);

        $service = app(HouseholdService::class);
        $household = $service->create([
            'household_name' => 'Dela Cruz Household',
            'barangay_id' => $barangay->id,
            'street' => 'Main Street',
            'purok_sitio' => 'Purok 1',
            'head_first_name' => 'Juan',
            'head_last_name' => 'Dela Cruz',
            'head_birth_date' => '1980-01-01',
            'head_sex' => 'M',
            'head_is_pwd' => true,
            'members' => [
                [
                    'first_name' => 'Maria',
                    'last_name' => 'Dela Cruz',
                    'birth_date' => '2010-05-10',
                    'sex' => 'F',
                    'relation' => 'Daughter',
                ],
            ],
        ], $captain->id);

        $this->assertMatchesRegularExpression('/^HH\d{6}$/', $household->id);
        $this->assertSame($household->id, $household->household_code);
        $this->assertSame(2, $household->members()->count());
        $this->assertTrue($household->members()->where('relation', 'Head')->first()->is_pwd);
        $this->assertNotEmpty($household->user->temp_password);

        $service->update($household->fresh(['address', 'members']), [
            'household_name' => 'Updated Dela Cruz Household',
            'barangay_id' => $barangay->id,
            'street' => 'Updated Street',
        ]);

        $this->assertSame(2, $household->fresh()->members()->count());
        $this->assertSame('Updated Dela Cruz Household', $household->fresh()->household_name);
    }
}
