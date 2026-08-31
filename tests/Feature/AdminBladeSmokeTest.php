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

        $captainRole = Role::create(['name' => 'Captain', 'role_key' => 'admin']);
        Role::create(['name' => 'Moderator', 'role_key' => 'moderator']);
        Role::create(['name' => 'personel', 'role_key' => 'personel']);
        Role::create(['name' => 'Household', 'role_key' => 'household_resident']);

        $this->captain = User::create([
            'name' => 'Captain Test',
            'email' => 'captain@example.test',
            'username' => 'captain',
            'password' => Hash::make('password'),
            'role_id' => $captainRole->role_id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $region = Region::create(['name' => 'Region Test']);
        $province = Province::create(['region_id' => $region->region_id, 'name' => 'Province Test']);
        $city = City::create(['province_id' => $province->province_id, 'name' => 'City Test']);
        $barangay = Barangay::create(['city_id' => $city->city_id, 'name' => 'Barangay Test']);
        $address = Address::create([
            'barangay_id' => $barangay->barangay_id,
            'purok_sitio' => 'Purok 1',
            'street' => 'Main Street',
        ]);

        $this->household = Household::create([
            'household_code' => 'HH-TEST-001',
            'household_name' => 'Test Household',
            'contact_number' => '09170000000',
            'email' => 'household@example.test',
            'address_id' => $address->address_id,
            'created_by' => $this->captain->user_id,
        ]);

        $this->member = Member::create([
            'household_id' => $this->household->household_id,
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
        $this->assertSame('Spouse', $resident->relation);

        $this->actingAs($this->captain)
            ->post(route('admin.residents.store', $created), [
                'first_name' => 'Anna',
                'middle_name' => '',
                'last_name' => 'Reyes',
                'birth_date' => '2000-01-10',
                'sex' => 'F',
                'gender' => 'Female',
                'relation' => 'Sibling',
                'civil_status' => 'Single',
                'education_level' => 'College',
                'occupation' => 'Nurse',
                'special_needs' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.households.show', $created));

        $sibling = Member::where('last_name', 'Reyes')->firstOrFail();
        $this->assertSame('Sibling', $sibling->relation);

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

    public function test_admin_add_member_uses_standardized_household_member_data(): void
    {
        $this->actingAs($this->captain)
            ->post(route('admin.residents.store', $this->household), [
                'first_name' => 'Maria',
                'middle_name' => 'L',
                'last_name' => 'Santos',
                'birth_date' => '1995-06-15',
                'sex' => 'F',
                'relation' => 'Spouse',
                'civil_status' => 'Married',
                'education_level' => 'College',
                'occupation' => 'Teacher',
                'is_pwd' => false,
                'is_pregnant' => false,
                'special_needs' => '',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.households.show', $this->household));

        $resident = Member::where('last_name', 'Santos')->firstOrFail();

        $this->assertSame($this->household->household_id, $resident->household_id);
        $this->assertSame('Maria L Santos', $resident->name);
        $this->assertSame('Female', $resident->gender);
        $this->assertSame(2, $this->household->fresh()->members()->count());
    }

    public function test_admin_account_form_actions_work(): void
    {
        $encoderRole = Role::where('name', 'personel')->firstOrFail();

        $this->actingAs($this->captain)
            ->post(route('admin.accounts.store'), [
                'name' => 'Encoder User',
                'username' => 'encoder-user',
                'email' => 'encoder-user@example.test',
                'contact_number' => '09175555555',
                'role_id' => $encoderRole->role_id,
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
                'role_id' => $encoderRole->role_id,
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
            'role_id' => $householdRole->role_id,
            'household_id' => $this->household->household_id,
            'must_change_password' => false,
            'is_active' => true,
        ]);

        $this->actingAs($householdUser)
            ->get(route('household.dashboard'))
            ->assertOk()
            ->assertSee($this->household->household_code);
    }

    public function test_manual_member_relation_handles_typo_sibing_and_custom_text(): void
    {
        $this->actingAs($this->captain)
            ->post(route('admin.residents.store', $this->household), [
                'first_name'   => 'James',
                'middle_name'  => 'M',
                'last_name'    => 'Santos',
                'birth_date'   => '1998-04-12',
                'sex'          => 'M',
                'relation'     => 'sibing',
                'civil_status' => 'Single',
            ])
            ->assertSessionHasNoErrors();

        $member = Member::where('first_name', 'James')->where('last_name', 'Santos')->firstOrFail();
        $this->assertSame('Sibling', $member->relation);
        $this->assertEquals(5, $member->relationship_id);

        $this->actingAs($this->captain)
            ->get(route('admin.households.show', $this->household))
            ->assertOk()
            ->assertSee('Sibling');
    }

    public function test_male_resident_cannot_be_marked_as_pregnant(): void
    {
        $this->actingAs($this->captain)
            ->post(route('admin.residents.store', $this->household), [
                'first_name'   => 'Robert',
                'last_name'    => 'Santos',
                'birth_date'   => '1995-03-10',
                'sex'          => 'M',
                'relation'     => 'Sibling',
                'civil_status' => 'Single',
                'is_pregnant'  => true,
            ])
            ->assertSessionHasErrors(['is_pregnant']);
    }

    public function test_resident_list_is_ordered_alphabetically_by_surname(): void
    {
        Member::create([
            'household_id' => $this->household->household_id,
            'first_name'   => 'Zoe',
            'last_name'    => 'Abad',
            'birth_date'   => '1992-01-01',
            'sex'          => 'F',
            'relation'     => 'Child',
            'civil_status' => 'Single',
        ]);

        Member::create([
            'household_id' => $this->household->household_id,
            'first_name'   => 'Adam',
            'last_name'    => 'Zeta',
            'birth_date'   => '1993-01-01',
            'sex'          => 'M',
            'relation'     => 'Child',
            'civil_status' => 'Single',
        ]);

        $response = $this->actingAs($this->captain)
            ->get(route('admin.residents.index', ['household_id' => $this->household->household_id]))
            ->assertOk();

        $residents = $response->viewData('residents');
        $surnames = $residents->pluck('last_name')->toArray();
        $sortedSurnames = $surnames;
        sort($sortedSurnames);

        $this->assertSame($sortedSurnames, $surnames);
    }

    public function test_grouped_residents_are_ordered_alphabetically_by_household_name(): void
    {
        $hhZeta = Household::create([
            'household_code' => 'HH-ZETA',
            'household_name' => 'Zeta Family',
        ]);
        Member::create([
            'household_id' => $hhZeta->household_id,
            'first_name'   => 'John',
            'last_name'    => 'Zeta',
            'birth_date'   => '1990-01-01',
            'sex'          => 'M',
            'relation'     => 'Head',
            'civil_status' => 'Single',
        ]);

        $hhAlpha = Household::create([
            'household_code' => 'HH-ALPHA',
            'household_name' => 'Alpha Family',
        ]);
        Member::create([
            'household_id' => $hhAlpha->household_id,
            'first_name'   => 'Alice',
            'last_name'    => 'Alpha',
            'birth_date'   => '1991-01-01',
            'sex'          => 'F',
            'relation'     => 'Head',
            'civil_status' => 'Single',
        ]);

        $response = $this->actingAs($this->captain)
            ->get(route('admin.residents.index'))
            ->assertOk();

        $grouped = $response->viewData('groupedResidents');
        $householdNames = $grouped->map(fn($members) => $members->first()->household->household_name)->values()->toArray();
        $sortedNames = $householdNames;
        sort($sortedNames);

        $this->assertSame($sortedNames, $householdNames);
    }
}
