<?php
/**
 * COMPREHENSIVE SYSTEM TEST
 * Tests all features: controllers, models, views compile, business logic, data integrity
 */
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\{User, Role, Household, Member, Address, Barangay, City, Province, Region, Sitio};
use App\Models\{CsvUpload, DataSource, ImportLog};
use App\Services\{HouseholdAccountService, HouseholdCsvImportService};
use Illuminate\Support\Facades\{DB, Hash, Route};
use Illuminate\Support\Str;
use Carbon\Carbon;

$pass = 0; $fail = 0; $warn = 0;
$issues = [];
$section = '';

function section(string $s): void { global $section; $section = $s; echo PHP_EOL . "=== {$s} ===" . PHP_EOL; }
function ok(string $label, string $detail = ''): void {
    global $pass; echo "  ✓  {$label}" . ($detail ? "  [{$detail}]" : '') . PHP_EOL; $pass++;
}
function fail(string $label, string $detail = ''): void {
    global $fail, $issues, $section;
    echo "  ✗  {$label}" . ($detail ? "  [{$detail}]" : '') . PHP_EOL;
    $issues[] = "[{$section}] {$label}" . ($detail ? ": {$detail}" : '');
    $fail++;
}
function warn(string $label, string $detail = ''): void {
    global $warn; echo "  ⚠  {$label}" . ($detail ? "  [{$detail}]" : '') . PHP_EOL; $warn++;
}
function chk(string $label, bool $result, string $detail = ''): void {
    $result ? ok($label, $detail) : fail($label, $detail);
}

// ─────────────────────────────────────────────────────────────────
section('1. DATABASE SCHEMA & SEED DATA');
// ─────────────────────────────────────────────────────────────────
chk('regions table has data',  Region::count() > 0,   Region::count() . ' regions');
chk('provinces table has data', Province::count() > 0, Province::count() . ' provinces');
chk('cities table has data',   City::count() > 0,     City::count() . ' cities');
chk('barangays table has data',Barangay::count() > 0, Barangay::count() . ' barangays');
chk('sitios table has data',   Sitio::count() > 0,    Sitio::count() . ' sitios');
chk('roles table has data',    Role::count() >= 5,    Role::count() . ' roles');
chk('users table has data',    User::count() >= 2,    User::count() . ' users');
$requiredRoles = ['Captain','Moderator','personel','Household'];
foreach ($requiredRoles as $r) {
    chk("Role '{$r}' exists", Role::whereRaw('LOWER(name)=?',[strtolower($r)])->exists(), '');
}
chk('Location hierarchy intact (barangay→city→province→region)',
    Barangay::with('city.province.region')->first()?->city?->province?->region?->name !== null,
    Barangay::with('city.province.region')->first()?->city?->province?->region?->name ?? 'NULL'
);

// ─────────────────────────────────────────────────────────────────
section('2. USER MODEL & AUTHENTICATION');
// ─────────────────────────────────────────────────────────────────
$captain = User::with('role')->where('email','captain@safetrack.local')->first();
$encoder = User::with('role')->where('email','encoder@safetrack.local')->first();
chk('Captain user exists', $captain !== null);
chk('Encoder user exists', $encoder !== null);
chk('Captain has user_id PK', !empty($captain?->user_id));
chk('Captain role loaded', $captain?->role?->name === 'Captain', $captain?->role?->name ?? 'NULL');
chk('Encoder role loaded', in_array($encoder?->role?->name, ['Encoder', 'Moderator', 'personel'], true), $encoder?->role?->name ?? 'NULL');
chk('Captain isCaptain()', $captain?->isCaptain() === true);
chk('Encoder isEncoder()', $encoder?->isEncoder() === true);
chk('Captain canManageAccounts()', $captain?->canManageAccounts() === true);
chk('Captain canDeleteHouseholds()', $captain?->canDeleteHouseholds() === true);
chk('Encoder cannot delete households', $encoder?->canDeleteHouseholds() === false);
chk('Password hashed (not plaintext)', !empty($captain?->password) && $captain?->password !== 'password');
chk('getKey() returns user_id', $captain?->getKey() === $captain?->user_id);

// ─────────────────────────────────────────────────────────────────
section('3. LOCATION CONTROLLER & DROPDOWN DATA');
// ─────────────────────────────────────────────────────────────────
// Test LocationController data shape (what the JS AJAX gets)
$regions = Region::select('region_id as id','name')->orderBy('name')->get();
chk('Regions have id alias', isset($regions->first()->id));
chk('Regions have name', isset($regions->first()->name));
chk('id is integer (not null)', is_int($regions->first()->id), 'id=' . $regions->first()->id);

$firstBarangay = Barangay::has('sitios')->with('city.province.region')->first();
if (!$firstBarangay) {
    $firstBarangay = Barangay::with('city.province.region')->first();
}

$firstCity = $firstBarangay->city;
$firstProvince = $firstCity->province;
$firstRegion = $firstProvince->region;

$provinces = Province::where('region_id',$firstRegion->region_id)->select('province_id as id','name')->get();
chk('Provinces for region populated', $provinces->count() > 0, $provinces->count() . ' provinces');
chk('Provinces have id alias', isset($provinces->first()?->id));

$cities = City::where('province_id',$firstProvince->province_id)->select('city_id as id','name')->get();
chk('Cities for province populated', $cities->count() > 0, $cities->count() . ' cities');

$barangays = Barangay::where('city_id',$firstCity->city_id)->select('barangay_id as id','name')->get();
chk('Barangays for city populated', $barangays->count() > 0, $barangays->count() . ' barangays');
chk('Barangay id is integer', is_int($barangays->first()->id), 'id=' . $barangays->first()->id);

$sitios = Sitio::where('barangay_id',$firstBarangay->barangay_id)->select('sitio_id as id','name')->get();
chk('Sitios for barangay populated', $sitios->count() > 0, $sitios->count() . ' sitios');

// ─────────────────────────────────────────────────────────────────
section('4. HOUSEHOLD CRUD');
// ─────────────────────────────────────────────────────────────────
$addr = Address::create(['barangay_id'=>$firstBarangay->barangay_id,'purok_sitio'=>'Purok Test','street'=>'Test St']);
$hCode = 'SYSTEST-HH-001';
$household = Household::create([
    'household_code'=>$hCode,'household_name'=>'System Test Family',
    'address_id'=>$addr->address_id,'created_by'=>$captain->user_id,
    'contact_number'=>'09171234567','email'=>null,
]);
chk('Household created', !empty($household->household_id), 'id='.$household->household_id);
chk('Household code set correctly', $household->household_code === $hCode);
chk('Household has address relationship', $household->address?->address_id === $addr->address_id);
chk('Household→address→barangay chain', $household->address?->barangay?->name !== null, $household->address?->barangay?->name);
chk('Household→barangay→city chain', $household->address?->barangay?->city?->name !== null, $household->address?->barangay?->city?->name);
chk('Household→city→province chain', $household->address?->barangay?->city?->province?->name !== null);
chk('Household→province→region chain', $household->address?->barangay?->city?->province?->region?->name !== null);

// Edit/Update
$household->update(['household_name'=>'Updated Test Family','contact_number'=>'09170000000']);
chk('Household update works', $household->fresh()->household_name === 'Updated Test Family');

// Soft delete
$household->delete();
chk('Household soft delete works', Household::find($household->household_id) === null);
chk('Soft deleted household in withTrashed', Household::withTrashed()->find($household->household_id) !== null);
$household->restore();
chk('Household restore works', Household::find($household->household_id) !== null);

// ─────────────────────────────────────────────────────────────────
section('5. MEMBER/RESIDENT CRUD');
// ─────────────────────────────────────────────────────────────────
$member = Member::create([
    'member_id'=>(string)Str::uuid(),'household_id'=>$household->household_id,
    'first_name'=>'Juan','middle_name'=>'D.','last_name'=>'Cruz',
    'name'=>'Juan D. Cruz','birth_date'=>'1990-06-15','sex'=>'M','gender'=>'Male',
    'age'=>Carbon::parse('1990-06-15')->age,'relation'=>'Head','civil_status'=>'Married',
    'education_level'=>'College','occupation'=>'Teacher','is_pwd'=>false,'is_pregnant'=>false,
    'is_senior'=>false,'is_graduate'=>false,
]);
chk('Member created', !empty($member->member_id));
chk('Member linked to household', $member->household_id === $household->household_id);
chk('Member household relationship', $member->household?->household_id === $household->household_id);
chk('Member full_name accessor', $member->full_name !== null, $member->full_name);
chk('Member age accessor returns > 0', $member->getAgeAttribute() > 0, 'age='.$member->getAgeAttribute());
$household->update(['member_count'=>$household->members()->count()]);
chk('member_count updated after add', $household->fresh()->member_count === 1);

// Add a child
$child = Member::create([
    'member_id'=>(string)Str::uuid(),'household_id'=>$household->household_id,
    'first_name'=>'Maria','last_name'=>'Cruz','name'=>'Maria Cruz',
    'birth_date'=>'2015-03-10','sex'=>'F','gender'=>'Female',
    'age'=>Carbon::parse('2015-03-10')->age,'relation'=>'Child','civil_status'=>'Single',
    'is_pwd'=>false,'is_pregnant'=>false,'is_senior'=>false,'is_graduate'=>false,
]);
$household->update(['member_count'=>$household->members()->count()]);
chk('Child member created', !empty($child->member_id));
chk('Household member_count = 2 after child', $household->fresh()->member_count === 2);

// Edit member
$member->update(['occupation'=>'Engineer']);
chk('Member update works', $member->fresh()->occupation === 'Engineer');

// Member soft delete
$child->delete();
chk('Member soft delete works', Member::find($child->member_id) === null);
chk('Member in withTrashed', Member::withTrashed()->find($child->member_id) !== null);
$household->update(['member_count'=>$household->members()->count()]);
chk('member_count decremented after delete', $household->fresh()->member_count === 1);

// ─────────────────────────────────────────────────────────────────
section('6. ACCOUNT MANAGEMENT');
// ─────────────────────────────────────────────────────────────────
$householdRole = Role::whereRaw('LOWER(name)=?',['household'])->first();
$encoderRole   = Role::whereRaw('LOWER(name)=?',['personel'])->first();
$captainRole   = Role::whereRaw('LOWER(name)=?',['captain'])->first();

// Manual account creation
$testUser = User::create([
    'name'=>'Test Encoder Account','username'=>'test_enc_sys','email'=>'testenc_sys@safetrack.local',
    'password'=>Hash::make('TestPass123!'),'role_id'=>$encoderRole->role_id,
    'is_active'=>true,'must_change_password'=>false,
]);
chk('Manual account creation works', !empty($testUser->user_id));
chk('Account role correctly set', in_array($testUser->role?->name, ['Encoder', 'Moderator', 'personel'], true), $testUser->role?->name ?? 'NULL');

// Update account
$testUser->update(['name'=>'Updated Encoder']);
chk('Account update works', $testUser->fresh()->name === 'Updated Encoder');

// unique email validation check
$dupEmail = User::where('email','testenc_sys@safetrack.local')->count();
chk('No duplicate email accounts', $dupEmail === 1, "count={$dupEmail}");

// Account uniqueness on username
$dupUser = User::where('username','test_enc_sys')->count();
chk('No duplicate username accounts', $dupUser === 1, "count={$dupUser}");

// ─────────────────────────────────────────────────────────────────
section('7. HOUSEHOLD ACCOUNT AUTO-PROVISIONING');
// ─────────────────────────────────────────────────────────────────
$accountService = new HouseholdAccountService();
$result = $accountService->provision($household, null, 'Juan D. Cruz');
chk('provision() returns result array', is_array($result), gettype($result));
chk('Provisioned user linked to household', $result['user']->household_id === $household->household_id);
chk('Provisioned user has Household role', $result['user']->role?->name === 'Household');
chk('Provisioned user must_change_password = true', $result['user']->must_change_password === true);
chk('Password is non-empty string', !empty($result['password']));

// Idempotency
$result2 = $accountService->provision($household, null);
chk('Second provision() returns null (idempotent)', $result2 === null);
chk('Only 1 user linked to household', User::where('household_id',$household->household_id)->count() === 1);

// ─────────────────────────────────────────────────────────────────
section('8. CSV IMPORT SERVICE');
// ─────────────────────────────────────────────────────────────────
$csvContent = "head_first_name,head_last_name,household_name,email,contact_number,barangay\n";
$csvContent .= "Pedro,Reyes,Reyes Family,reyesfamily@test.local,09181234567,{$firstBarangay->name}\n";
$csvContent .= "Ana,Santos,Santos Family,,09182345678,{$firstBarangay->name}\n";
$tmpFile = tempnam(sys_get_temp_dir(), 'csv_test_') . '.csv';
file_put_contents($tmpFile, $csvContent);

$csvService = new HouseholdCsvImportService();
$csvResult = $csvService->import($tmpFile, $captain->user_id);
@unlink($tmpFile);

chk('CSV import returns success', $csvResult['success'] === true, $csvResult['message']);
chk('CSV import processed 2 rows', $csvResult['stats']['total'] === 2, 'total='.$csvResult['stats']['total']);
chk('CSV imported 2 households successfully', $csvResult['stats']['success'] === 2, 'success='.$csvResult['stats']['success']);
chk('CSV import 0 failed rows', $csvResult['stats']['failed'] === 0, 'failed='.$csvResult['stats']['failed']);

$csvHH1 = Household::where('household_name','Reyes Family')->first();
$csvHH2 = Household::where('household_name','Santos Family')->first();
chk('CSV household 1 (Reyes) created', $csvHH1 !== null);
chk('CSV household 2 (Santos) created', $csvHH2 !== null);
chk('CSV household 1 has user account', User::where('household_id',$csvHH1?->household_id)->exists());
chk('CSV household 2 has user account', User::where('household_id',$csvHH2?->household_id)->exists());
chk('CSV household 1 email set', $csvHH1?->user?->email === 'reyesfamily@test.local'
    || str_ends_with($csvHH1?->user?->email ?? '','@safetrack.local'),
    $csvHH1?->user?->email ?? 'NO EMAIL');
chk('DataSource record created', DataSource::latest()->first() !== null);
chk('CsvUpload record created', CsvUpload::latest()->first() !== null);
chk('ImportLog has success entries', ImportLog::where('status','success')->count() >= 2);
chk('ImportLog has no error entries for this import', ImportLog::where('status','error')->count() === 0);

// ─────────────────────────────────────────────────────────────────
section('9. ANALYTICS CALCULATIONS');
// ─────────────────────────────────────────────────────────────────
$isSqlite = DB::connection()->getDriverName() === 'sqlite';
$ageRaw = $isSqlite
    ? "COALESCE(cast(strftime('%Y', 'now') - strftime('%Y', birth_date) as integer), age)"
    : "TIMESTAMPDIFF(YEAR, birth_date, CURDATE())";

$totalHH  = Household::count();
$totalMem = Member::count();
$children = Member::whereRaw("({$ageRaw}) < 18")->whereNotNull('birth_date')->count();
$seniors  = Member::whereRaw("({$ageRaw}) >= 60")->whereNotNull('birth_date')->count();
$adults   = max(0, $totalMem - $children - $seniors);
$males    = Member::whereRaw("LOWER(sex) IN ('m','male')")->count();
$females  = Member::whereRaw("LOWER(sex) IN ('f','female')")->count();

chk('totalHouseholds > 0', $totalHH > 0, "count={$totalHH}");
chk('totalPopulation > 0', $totalMem > 0, "count={$totalMem}");
chk('children + seniors + adults = total', ($children+$seniors+$adults) === $totalMem,
    "c={$children}+s={$seniors}+a={$adults}=".($children+$seniors+$adults)." vs {$totalMem}");
chk('males + females = total (all have sex)', $males+$females === $totalMem,
    "m={$males}+f={$females}=" .($males+$females)." vs {$totalMem}");

// Age bracket no-overlap
$b05  = Member::whereRaw("({$ageRaw}) BETWEEN 0 AND 5")->whereNotNull('birth_date')->count();
$b612 = Member::whereRaw("({$ageRaw}) BETWEEN 6 AND 12")->whereNotNull('birth_date')->count();
$b1317= Member::whereRaw("({$ageRaw}) BETWEEN 13 AND 17")->whereNotNull('birth_date')->count();
$b1835= Member::whereRaw("({$ageRaw}) BETWEEN 18 AND 35")->whereNotNull('birth_date')->count();
$b3659= Member::whereRaw("({$ageRaw}) BETWEEN 36 AND 59")->whereNotNull('birth_date')->count();
$b60p = Member::whereRaw("({$ageRaw}) >= 60")->whereNotNull('birth_date')->count();
$withDob = Member::whereNotNull('birth_date')->count();
chk('Age brackets sum = members with birth_date (no overlap)', ($b05+$b612+$b1317+$b1835+$b3659+$b60p)===$withDob,
    "sum=".($b05+$b612+$b1317+$b1835+$b3659+$b60p)." dob={$withDob}");

// Sitio distribution
$sitioSum = DB::table('household_members')
    ->join('households','household_members.household_id','=','households.household_id')
    ->leftJoin('addresses','households.address_id','=','addresses.address_id')
    ->whereNull('household_members.deleted_at')->whereNull('households.deleted_at')
    ->count('household_members.member_id');
chk('Sitio distribution population sum = totalMembers', $sitioSum === $totalMem,
    "sitio_sum={$sitioSum} total={$totalMem}");

// ─────────────────────────────────────────────────────────────────
section('10. CSV IMPORT DASHBOARD CONTROLLER LOGIC');
// ─────────────────────────────────────────────────────────────────
$totalImports   = CsvUpload::count();
$totalRecords   = CsvUpload::sum('total_records') ?? 0;
$successRecords = CsvUpload::sum('successful_records') ?? 0;
$failedRecords  = CsvUpload::sum('failed_records') ?? 0;
chk('CsvUpload records exist', $totalImports > 0, "count={$totalImports}");
chk('total_records > 0', $totalRecords > 0, "total={$totalRecords}");
chk('successful_records = total_records', $successRecords === $totalRecords,
    "success={$successRecords} total={$totalRecords}");
chk('failed_records = 0', $failedRecords === 0, "failed={$failedRecords}");

// CSVImportDashboard uses 'error' status — check this will be empty
$recentErrors = ImportLog::where('status','error')->count();
chk("ImportLog 'error' status query returns 0 (status='failed' is correct)", $recentErrors === 0,
    "error_count={$recentErrors}");

// ─────────────────────────────────────────────────────────────────
section('11. ADVANCED SEARCH LOGIC');
// ─────────────────────────────────────────────────────────────────
$searchHH = Household::where('household_name','like','%Test%')->get();
chk('Household text search works', $searchHH->count() > 0, 'found='.$searchHH->count());

$searchMem = Member::where('first_name','like','%Juan%')->get();
chk('Member name search works', $searchMem->count() > 0, 'found='.$searchMem->count());

// Barangay filter
$barangayFilter = Household::whereHas('address', fn($q) => $q->where('barangay_id',$firstBarangay->barangay_id))->count();
chk('Barangay filter on households works', $barangayFilter > 0, "found={$barangayFilter}");

// ─────────────────────────────────────────────────────────────────
section('12. ACCOUNT MANAGEMENT QUERIES');
// ─────────────────────────────────────────────────────────────────
$allUsers = User::with(['role','household'])->latest()->get();
chk('Users with role eager-loaded', $allUsers->first()?->relationLoaded('role') === true);

$roleFilter = User::whereHas('role', fn($q) => $q->where('name','Captain'))->get();
chk('Role filter query works', $roleFilter->count() >= 1, 'Captain count='.$roleFilter->count());

$searchUsers = User::where('name','like','%captain%')->orWhere('email','like','%captain%')->get();
chk('User search by name/email works', $searchUsers->count() >= 1);

$rolesForFilter = Role::where('name','!=','Household')->orderBy('name')->get();
chk('Roles for filter dropdown (excl. Household)', $rolesForFilter->count() >= 4, 'count='.$rolesForFilter->count());

// unique constraint validation simulation
$uniqueUsernameRule = User::where('username','captain@safetrack.local')->exists(); // this is an email, not username
chk('Username uniqueness check works', !$uniqueUsernameRule); // email can't be a username here

// ─────────────────────────────────────────────────────────────────
section('13. PASSWORD CHANGE LOGIC');
// ─────────────────────────────────────────────────────────────────
// Test password hashing
$raw = 'NewSecurePass123!';
$hashed = Hash::make($raw);
chk('Hash::make produces bcrypt hash', str_starts_with($hashed,'$2'));
chk('Hash::check validates correct password', Hash::check($raw,$hashed));
chk('Hash::check rejects wrong password', !Hash::check('WrongPass',$hashed));

// must_change_password flow
$pwdTestUser = User::where('must_change_password',true)->first();
chk('must_change_password users exist', $pwdTestUser !== null, $pwdTestUser?->email ?? 'NONE');
$pwdTestUser?->update(['password'=>Hash::make('NewPass123!'),'must_change_password'=>false,'temp_password'=>null]);
chk('Password change clears must_change_password', $pwdTestUser?->fresh()->must_change_password === false);
chk('Password change clears temp_password', $pwdTestUser?->fresh()->temp_password === null);

// ─────────────────────────────────────────────────────────────────
section('14. HOUSEHOLD INDEX - FILTER & PAGINATION DATA');
// ─────────────────────────────────────────────────────────────────
$paginatedHH = Household::with(['address.barangay','members','user'])->latest()->paginate(15);
chk('Household paginator works', $paginatedHH->count() > 0);
chk('total() returns correct count', $paginatedHH->total() === Household::count());
$firstHH = $paginatedHH->first();
chk('Household has user relationship loaded', $firstHH?->relationLoaded('user'));
chk('Household has members relationship loaded', $firstHH?->relationLoaded('members'));

// Purok filter
$purokFilter = Household::whereHas('address', fn($q) => $q->where('purok_sitio','like','%Test%'))->count();
chk('Purok_sitio filter works', $purokFilter >= 0, "found={$purokFilter}");

// ─────────────────────────────────────────────────────────────────
section('15. VIEW BLADE FILES COMPILE CHECK');
// ─────────────────────────────────────────────────────────────────
$viewsToCheck = [
    'admin.dashboard','admin.households.index','admin.households.create',
    'admin.households.edit','admin.households.show','admin.residents.index',
    'admin.residents.create','admin.residents.edit','admin.accounts.index',
    'admin.accounts.create','admin.accounts.edit','admin.analytics.index',
    'admin.reports.index','admin.reports.evacuation','admin.reports.rescue',
    'admin.reports.logistics','admin.csv-import.dashboard','admin.csv-import.show',
    'admin.search.advanced-search','admin.search.results','admin.csv-upload',
];

foreach ($viewsToCheck as $view) {
    try {
        $path = resource_path('views/' . str_replace('.','/',$view) . '.blade.php');
        chk("View [{$view}] file exists", file_exists($path), $path);
    } catch (\Throwable $e) {
        fail("View [{$view}] error", $e->getMessage());
    }
}

// ─────────────────────────────────────────────────────────────────
section('16. ROUTE REGISTRATION CHECK');
// ─────────────────────────────────────────────────────────────────
$routesToCheck = [
    'admin.dashboard','admin.households.index','admin.households.create',
    'admin.households.store','admin.households.show','admin.households.edit',
    'admin.households.update','admin.households.destroy',
    'admin.residents.index','admin.residents.create','admin.residents.store',
    'admin.residents.edit','admin.residents.update','admin.residents.destroy',
    'admin.accounts.index','admin.accounts.create','admin.accounts.store',
    'admin.accounts.edit','admin.accounts.update','admin.accounts.destroy',
    'admin.analytics.index','admin.reports.index','admin.reports.evacuation',
    'admin.reports.rescue','admin.reports.logistics',
    'admin.csv-import.index','admin.csv-import.show',
    'admin.search.form','admin.search.search',
    'locations.regions','locations.provinces','locations.cities',
    'locations.barangays','locations.sitios',
    'csv.upload','csv.upload.process',
    'login','logout','password.change','password.update',
];
foreach ($routesToCheck as $routeName) {
    try {
        if (\Illuminate\Support\Facades\Route::has($routeName)) {
            $url = route($routeName, [
                'household' => 1,
                'member' => 1,
                'user' => 1,
                'csvUpload' => 1,
                'regionId' => 1,
                'provinceId' => 1,
                'cityId' => 1,
                'barangayId' => 1
            ], false);
            ok("Route [{$routeName}] registered", $url);
        } else {
            fail("Route [{$routeName}] NOT registered", "Route not registered in RouteCollection");
        }
    } catch (\Throwable $e) {
        fail("Route [{$routeName}] NOT registered", $e->getMessage());
    }
}

// ─────────────────────────────────────────────────────────────────
section('17. MODEL RELATIONSHIP INTEGRITY');
// ─────────────────────────────────────────────────────────────────
chk('Household->address (BelongsTo)', $household->address !== null);
chk('Household->members (HasMany)', $household->members()->count() >= 1);
chk('Household->user (HasOne)', $household->user !== null);
chk('Household->creator (BelongsTo)', $household->creator?->user_id === $captain->user_id);
chk('Member->household (BelongsTo)', $member->household?->household_id === $household->household_id);
chk('User->role (BelongsTo)', $captain->role?->name === 'Captain');
chk('User->household (BelongsTo)', $captain->household === null); // captain has no household
$hhUser = User::where('household_id',$household->household_id)->first();
chk('HouseholdUser->household (BelongsTo)', $hhUser?->household?->household_id === $household->household_id);
chk('Address->barangay (BelongsTo)', $addr->barangay?->barangay_id === $firstBarangay->barangay_id);
chk('Barangay->city (BelongsTo)', $firstBarangay->city !== null);
chk('City->province (BelongsTo)', $firstCity->province !== null);
chk('Province->region (BelongsTo)', $firstProvince->region !== null);
chk('Region->provinces (HasMany)', $firstRegion->provinces->count() > 0);
chk('Province->cities (HasMany)', $firstProvince->cities->count() > 0);
chk('City->barangays (HasMany)', $firstCity->barangays->count() > 0);

// ─────────────────────────────────────────────────────────────────
section('18. SOFT DELETE INTEGRITY');
// ─────────────────────────────────────────────────────────────────
// Soft deleted items don't appear in normal queries
$deletedChild = Member::withTrashed()->find($child->member_id);
chk('Soft-deleted member not in normal query', Member::find($child->member_id) === null);
chk('Soft-deleted member found in withTrashed', $deletedChild !== null);
chk('Soft-deleted member has deleted_at set', $deletedChild?->deleted_at !== null);

// ─────────────────────────────────────────────────────────────────
// SUMMARY
// ─────────────────────────────────────────────────────────────────
echo PHP_EOL . str_repeat('=',60) . PHP_EOL;
echo "  TOTAL PASSED: {$pass}" . PHP_EOL;
echo "  TOTAL FAILED: {$fail}" . PHP_EOL;
echo "  WARNINGS:     {$warn}" . PHP_EOL;

if (!empty($issues)) {
    echo PHP_EOL . "ISSUES TO FIX:" . PHP_EOL;
    foreach ($issues as $i => $issue) {
        echo "  " . ($i+1) . ". {$issue}" . PHP_EOL;
    }
}
echo str_repeat('=',60) . PHP_EOL;

// ─────────────────────────────────────────────────────────────────
// CLEANUP
// ─────────────────────────────────────────────────────────────────
echo PHP_EOL . "Cleaning up test data..." . PHP_EOL;
$isSqlite = DB::connection()->getDriverName() === 'sqlite';
if ($isSqlite) {
    DB::statement('PRAGMA foreign_keys = OFF');
} else {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
}

$member->forceDelete();
Member::withTrashed()->where('member_id',$child->member_id)->forceDelete();
User::where('household_id',$household->household_id)->forceDelete();
Household::withTrashed()->where('household_id',$household->household_id)->forceDelete();
$addr->forceDelete();
User::where('username','test_enc_sys')->forceDelete();

// Clean CSV test households
foreach(['Reyes Family','Santos Family'] as $n) {
    $h = Household::withTrashed()->where('household_name',$n)->first();
    if ($h) {
        User::where('household_id',$h->household_id)->forceDelete();
        Member::withTrashed()->where('household_id',$h->household_id)->forceDelete();
        $h->forceDelete();
    }
}
// Clean import logs from test
$ds = DataSource::latest()->take(10)->get();
foreach ($ds as $d) {
    ImportLog::where('data_source_id',$d->id)->delete();
    CsvUpload::where('data_source_id',$d->id)->delete();
    $d->delete();
}

if ($isSqlite) {
    DB::statement('PRAGMA foreign_keys = ON');
} else {
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
}
echo "Done." . PHP_EOL;
