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

class AdminBladeSmokeTest extends TestCase
{
    use RefreshDatabase;

    private User $captain;
    private Household $household;
    private Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $captainRole = Role::create(['name' => 'Captain']);
        Role::create(['name' => 'Encoder']);
        Role::create(['name' => 'Household']);

        $this->captain = User::create([
            'name' => 'Captain Test',
            'email' => 'captain@example.test',
            'username' => 'captain',
            'password' => Hash::make('password'),
            'role_id' => $captainRole->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $region = Region::create(['name' => 'Region Test']);
        $province = Province::create(['region_id' => $region->id, 'name' => 'Province Test']);
        $city = City::create(['province_id' => $province->id, 'name' => 'City Test']);
        $barangay = Barangay::create(['city_id' => $city->id, 'name' => 'Barangay Test']);
        $address = Address::create([
            'region_id' => $region->id,
            'province_id' => $province->id,
            'city_id' => $city->id,
            'barangay_id' => $barangay->id,
            'purok_sitio' => 'Purok 1',
            'street_address' => 'Main Street',
        ]);

        $this->household = Household::create([
            'household_code' => 'HH-TEST-001',
            'household_name' => 'Test Household',
            'contact_number' => '09170000000',
            'email' => 'household@example.test',
            'address_id' => $address->id,
            'created_by' => $this->captain->id,
        ]);

        $this->member = Member::create([
            'household_id' => $this->household->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'name' => 'Juan Dela Cruz',
            'birth_date' => '1980-01-01',
            'age' => 46,
            'sex' => 'M',
            'gender' => 'Male',
            'relation' => 'Head',
            'civil_status' => 'Married',
            'is_pwd' => false,
            'is_pregnant' => false,
            'is_graduate' => false,
        ]);
    }

    public function test_guest_auth_pages_render(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Login');
    }

    public function test_admin_get_pages_render_without_exceptions(): void
    {
        $routes = [
            route('admin.dashboard'),
            route('admin.households.index'),
            route('admin.households.create'),
            route('admin.households.show', $this->household),
            route('admin.households.edit', $this->household),
            route('admin.residents.index'),
            route('admin.residents.create', $this->household),
            route('admin.residents.edit', $this->member),
            route('admin.accounts.index'),
            route('admin.accounts.create'),
            route('admin.analytics.index'),
            route('admin.reports.index'),
            route('admin.reports.evacuation'),
            route('admin.reports.rescue'),
            route('admin.reports.logistics'),
            route('admin.tokens.index'),
            route('admin.search.form'),
            route('admin.search.search', ['q' => 'Juan']),
            route('csv.upload'),
            route('password.change'),
        ];

        foreach ($routes as $uri) {
            $this->actingAs($this->captain)
                ->get($uri)
                ->assertOk();
        }
    }

    public function test_login_logout_and_password_change_flow(): void
    {
        $this->post(route('login'), [
            'email' => $this->captain->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->actingAs($this->captain)
            ->post(route('password.update'), [
                'current_password' => 'password',
                'password' => 'NewPassword123',
                'password_confirmation' => 'NewPassword123',
            ])
            ->assertRedirect(route('dashboard'));

        $this->actingAs($this->captain)
            ->post(route('logout'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_household_and_resident_form_actions_work(): void
    {
        $this->actingAs($this->captain)
            ->post(route('admin.households.store'), [
                'household_code' => 'HH-FORM-001',
                'household_name' => 'Form Test Household',
                'contact_number' => '09171111111',
                'email' => 'form-household@example.test',
                'emergency_contact' => '09172222222',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $created = Household::where('household_code', 'HH-FORM-001')->firstOrFail();

        $this->actingAs($this->captain)
            ->put(route('admin.households.update', $created), [
                'household_name' => 'Updated Form Test Household',
                'contact_number' => '09173333333',
                'email' => 'updated-form-household@example.test',
                'emergency_contact' => '09174444444',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.households.show', $created));

        $this->actingAs($this->captain)
            ->post(route('admin.residents.store', $created), [
                'first_name' => 'Maria',
                'middle_name' => '',
                'last_name' => 'Santos',
                'birth_date' => '1995-06-15',
                'sex' => 'F',
                'gender' => 'Female',
                'relation' => 'Spouse',
                'civil_status' => 'Married',
                'education_level' => 'College',
                'occupation' => 'Teacher',
                'special_needs' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.households.show', $created));

        $resident = Member::where('last_name', 'Santos')->firstOrFail();

        $this->actingAs($this->captain)
            ->put(route('admin.residents.update', $resident), [
                'first_name' => 'Maria',
                'middle_name' => 'L',
                'last_name' => 'Santos',
                'birth_date' => '1995-06-15',
                'sex' => 'F',
                'gender' => 'Female',
                'relation' => 'Spouse',
                'civil_status' => 'Married',
                'education_level' => 'College',
                'occupation' => 'Nurse',
                'special_needs' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.households.show', $created));
    }

    public function test_admin_account_form_actions_work(): void
    {
        $encoderRole = Role::where('name', 'Encoder')->firstOrFail();

        $this->actingAs($this->captain)
            ->post(route('admin.accounts.store'), [
                'name' => 'Encoder User',
                'username' => 'encoder-user',
                'email' => 'encoder-user@example.test',
                'contact_number' => '09175555555',
                'role_id' => $encoderRole->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.accounts.index'));

        $user = User::where('email', 'encoder-user@example.test')->firstOrFail();

        $this->actingAs($this->captain)
            ->put(route('admin.accounts.update', $user), [
                'name' => 'Updated Encoder User',
                'username' => 'encoder-user',
                'email' => 'encoder-user@example.test',
                'contact_number' => '09176666666',
                'role_id' => $encoderRole->id,
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.accounts.index'));
    }

    public function test_household_dashboard_renders_for_household_user(): void
    {
        $householdRole = Role::where('name', 'Household')->first() ?? Role::create(['name' => 'Household']);
        
        $householdUser = User::create([
            'name' => 'Household User',
            'email' => 'household@example.test',
            'username' => 'household',
            'password' => Hash::make('password'),
            'role_id' => $householdRole->id,
            'household_id' => $this->household->id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($householdUser)
            ->get(route('household.dashboard'))
            ->assertOk()
            ->assertSee($this->household->household_code);
    }
}
