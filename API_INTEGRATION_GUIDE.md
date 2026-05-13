# SafeTrack Admin Dashboard - Setup & API Integration Guide

## 🚀 Quick Setup (5 Minutes)

### Step 1: Verify Database Models
Ensure these models exist:
- ✅ `App\Models\User`
- ✅ `App\Models\Role`
- ✅ `App\Models\Household`
- ✅ `App\Models\Member`
- ✅ `App\Models\Address`
- ✅ `App\Models\Region`, `Province`, `City`, `Barangay`

### Step 2: Run Migrations
```bash
php artisan migrate
```

### Step 3: Seed Roles (if needed)
```bash
# Create roles if not already in database
php artisan tinker

Role::create(['name' => 'head']);
Role::create(['name' => 'encoder']);
Role::create(['name' => 'household']);
```

### Step 4: Create Admin Account
```bash
php artisan tinker

$role = Role::where('name', 'head')->first();
User::create([
    'name' => 'Barangay Admin',
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password123'),
    'role_id' => $role->id,
    'is_active' => true,
]);
```

### Step 5: Start App & Test
```bash
php artisan serve
```
Navigate to: `http://localhost:8000/admin`

---

## 🔌 API Integration Guide

### Phase 1: Evacuation Reports API

#### 1.1 Create API Service Class

**File:** `app/Services/EvacuationApiService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

/**
 * EvacuationApiService
 * 
 * Handles API communication with the Evacuation Subsystem
 * 
 * FEATURES:
 * - Fetch evacuation reports
 * - Authentication via Bearer token
 * - Response caching (5 min)
 * - Error handling & logging
 * - Retry logic
 */
class EvacuationApiService
{
    private $baseUrl;
    private $apiToken;
    private $timeout = 10;
    private $cacheTime = 300; // 5 minutes

    public function __construct()
    {
        $this->baseUrl = env('EVACUATION_API_URL', 'http://evacuation-api.local');
        $this->apiToken = env('EVACUATION_API_TOKEN', 'your-token-here');
    }

    /**
     * Fetch evacuation reports with filters
     * 
     * @param array $filters ['status' => 'ongoing', 'date_from' => '2026-05-01', ...]
     * @return Collection
     */
    public function getReports(array $filters = []): Collection
    {
        $cacheKey = 'evacuation_reports_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, $this->cacheTime, function () use ($filters) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->timeout($this->timeout)
                    ->get("{$this->baseUrl}/api/reports", $filters);

                if ($response->failed()) {
                    \Log::error('Evacuation API Error', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                    return collect([]);
                }

                return collect($response->json('data', []));
            } catch (\Exception $e) {
                \Log::error('Evacuation API Exception: ' . $e->getMessage());
                return collect([]);
            }
        });
    }

    /**
     * Get single evacuation report
     */
    public function getReport($id)
    {
        try {
            $response = Http::withToken($this->apiToken)
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/api/reports/{$id}");

            return $response->json('data');
        } catch (\Exception $e) {
            \Log::error('Evacuation API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::flush();
    }
}
```

#### 1.2 Update Controller

**File:** `app/Http/Controllers/Admin/ReportAdminController.php`

```php
<?php

// Add to top of file
use App\Services\EvacuationApiService;

public function __construct(
    private EvacuationApiService $evacuationService
) {}

/**
 * Updated evacuation method with API integration
 */
public function evacuation(Request $request)
{
    $filters = $request->only(['status', 'date_from', 'date_to']);
    
    // Fetch from API
    $reports = $this->evacuationService->getReports($filters);
    
    return view('admin.reports.evacuation', [
        'reports' => $reports,
        'filters' => $filters,
    ]);
}
```

#### 1.3 Update .env

```env
EVACUATION_API_URL=http://evacuation-api.local
EVACUATION_API_TOKEN=your-bearer-token-here
```

#### 1.4 Update View

**File:** `resources/views/admin/reports/evacuation.blade.php`

Replace the empty state with:

```blade
<!-- Replace the empty state div with this -->
@if($reports->count() > 0)
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="font-weight: 600; color: #333; padding: 15px;">Disaster Type</th>
                    <th style="font-weight: 600; color: #333; padding: 15px;">Households Affected</th>
                    <th style="font-weight: 600; color: #333; padding: 15px;">Persons Evacuated</th>
                    <th style="font-weight: 600; color: #333; padding: 15px;">Status</th>
                    <th style="font-weight: 600; color: #333; padding: 15px;">Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reports as $report)
                    <tr style="border-bottom: 1px solid #f1f1f1;">
                        <td style="padding: 15px;">{{ $report['disaster_type'] ?? 'N/A' }}</td>
                        <td style="padding: 15px;">{{ $report['households_affected'] ?? 0 }}</td>
                        <td style="padding: 15px;">{{ $report['persons_evacuated'] ?? 0 }}</td>
                        <td style="padding: 15px;">
                            <span class="badge" style="background-color: @if($report['status'] == 'ongoing') #ffc107 @else #d4edda @endif;">
                                {{ ucfirst($report['status'] ?? 'unknown') }}
                            </span>
                        </td>
                        <td style="padding: 15px;">{{ $report['created_at'] ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <!-- Keep existing empty state -->
@endif
```

#### 1.5 Test Integration

```bash
# In tinker
$service = new \App\Services\EvacuationApiService();
$reports = $service->getReports();
dd($reports);
```

---

### Phase 2: Rescue Reports API

Follow the same pattern as Evacuation:

**File:** `app/Services/RescueApiService.php`

```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class RescueApiService
{
    private $baseUrl;
    private $apiToken;

    public function __construct()
    {
        $this->baseUrl = env('RESCUE_API_URL', 'http://rescue-api.local');
        $this->apiToken = env('RESCUE_API_TOKEN', 'your-token');
    }

    public function getReports(array $filters = []): Collection
    {
        $cacheKey = 'rescue_reports_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/api/reports", $filters);

                return $response->failed() ? collect([]) : collect($response->json('data', []));
            } catch (\Exception $e) {
                \Log::error('Rescue API Error: ' . $e->getMessage());
                return collect([]);
            }
        });
    }
}
```

**Update Controller:**
```php
public function rescue(Request $request)
{
    $filters = $request->only(['status', 'incident_type', 'date_from', 'date_to']);
    $reports = app(RescueApiService::class)->getReports($filters);
    
    return view('admin.reports.rescue', [
        'reports' => $reports,
        'filters' => $filters,
    ]);
}
```

**Update .env:**
```env
RESCUE_API_URL=http://rescue-api.local
RESCUE_API_TOKEN=your-token
```

---

### Phase 3: Logistics Reports API

Similar pattern:

**File:** `app/Services/LogisticsApiService.php`

```php
<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LogisticsApiService
{
    private $baseUrl;
    private $apiToken;

    public function __construct()
    {
        $this->baseUrl = env('LOGISTICS_API_URL', 'http://logistics-api.local');
        $this->apiToken = env('LOGISTICS_API_TOKEN', 'your-token');
    }

    public function getReports(array $filters = []): Collection
    {
        $cacheKey = 'logistics_reports_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, 300, function () use ($filters) {
            try {
                $response = Http::withToken($this->apiToken)
                    ->timeout(10)
                    ->get("{$this->baseUrl}/api/reports", $filters);

                return $response->failed() ? collect([]) : collect($response->json('data', []));
            } catch (\Exception $e) {
                \Log::error('Logistics API Error: ' . $e->getMessage());
                return collect([]);
            }
        });
    }
}
```

**Update Controller:**
```php
public function logistics(Request $request)
{
    $filters = $request->only(['status', 'item_type', 'date_from', 'date_to']);
    $reports = app(LogisticsApiService::class)->getReports($filters);
    
    return view('admin.reports.logistics', [
        'reports' => $reports,
        'filters' => $filters,
    ]);
}
```

---

## 🔐 Authentication Token Refresh

If tokens expire, implement refresh logic:

**File:** `app/Services/ApiTokenRefreshService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ApiTokenRefreshService
{
    public function refreshEvacuationToken()
    {
        try {
            $response = Http::post(env('EVACUATION_API_URL') . '/api/auth/refresh', [
                'client_id' => env('EVACUATION_CLIENT_ID'),
                'client_secret' => env('EVACUATION_CLIENT_SECRET'),
            ]);

            if ($response->successful()) {
                $newToken = $response->json('token');
                Cache::put('evacuation_api_token', $newToken, now()->addHours(1));
                return $newToken;
            }
        } catch (\Exception $e) {
            \Log::error('Token Refresh Error: ' . $e->getMessage());
        }

        return null;
    }
}
```

---

## 📊 CSV Import Implementation

**File:** `app/Services/HouseholdCsvImportService.php`

```php
<?php

namespace App\Services;

use App\Models\Household;
use App\Models\Address;
use Illuminate\Support\Str;
use League\Csv\Reader;

/**
 * HouseholdCsvImportService
 * 
 * Handles bulk import of households from CSV files
 * 
 * CSV Format:
 * household_code,household_name,purok_sitio,contact_number,email,emergency_contact
 */
class HouseholdCsvImportService
{
    public function import(string $filePath, string $userId): array
    {
        $results = [
            'imported' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        try {
            $csv = Reader::createFromPath($filePath, 'r');
            $csv->setHeaderOffset(0);

            foreach ($csv as $row) {
                try {
                    $household = Household::create([
                        'household_code' => $row['household_code'],
                        'household_name' => $row['household_name'] ?? $row['household_code'],
                        'contact_number' => $row['contact_number'] ?? null,
                        'email' => $row['email'] ?? null,
                        'emergency_contact' => $row['emergency_contact'] ?? null,
                        'created_by' => $userId,
                    ]);

                    $results['imported']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = "Row: {$row['household_code']} - {$e->getMessage()}";
                }
            }
        } catch (\Exception $e) {
            $results['errors'][] = "CSV Import Error: {$e->getMessage()}";
        }

        return $results;
    }
}
```

**Use in Controller:**
```php
public function uploadCsv(Request $request, Household $household)
{
    $request->validate(['file' => 'required|file|mimes:csv,txt']);

    $service = new HouseholdCsvImportService();
    $results = $service->import($request->file('file')->path(), auth()->id());

    return back()->with('success', 
        "Imported: {$results['imported']}, Failed: {$results['failed']}"
    );
}
```

---

## 🧪 Testing Checklist

### Manual Testing

- [ ] Login as barangay head
- [ ] Access `/admin` dashboard
- [ ] Create household with cascading location dropdowns
- [ ] Add members to household
- [ ] Edit household and member
- [ ] Create user account
- [ ] View analytics
- [ ] Test reports (if API connected)
- [ ] Logout and login as encoder
- [ ] Verify encoder cannot delete households/members
- [ ] Test search filters
- [ ] Test pagination

### Automated Testing (Optional)

```bash
php artisan make:test Admin/HouseholdControllerTest
php artisan make:test Admin/ResidentControllerTest
php artisan make:test Admin/AccountControllerTest
```

---

## 🔍 Debugging Tips

### Enable Query Logging
```php
// In AppServiceProvider.php
public function boot()
{
    if (env('APP_DEBUG')) {
        DB::listen(function ($query) {
            \Log::debug($query->sql, $query->bindings);
        });
    }
}
```

### Check API Response
```bash
php artisan tinker

$response = Http::withToken('your-token')->get('http://api-url/endpoint');
dd($response->json());
```

### View Cache
```bash
php artisan cache:clear
Cache::flush();
```

---

## 📈 Performance Optimization

### Database Indexes
```php
// In migration
Schema::table('households', function (Blueprint $table) {
    $table->index('household_code');
    $table->index('address_id');
    $table->index('created_by');
});

Schema::table('members', function (Blueprint $table) {
    $table->index('household_id');
    $table->index('birth_date');
    $table->index('is_pwd');
});
```

### Query Optimization
```php
// Always use eager loading
Household::with(['address.barangay', 'members'])->paginate(15);

// Use select to limit columns
Household::select('id', 'household_code', 'address_id')->get();
```

### Caching
```php
// Cache location data (rarely changes)
Region::cache()->remember('regions', now()->addDay(), function() {
    return Region::all();
});
```

---

## 📞 Support Resources

- **Laravel Docs:** https://laravel.com/docs/11
- **Bootstrap Docs:** https://getbootstrap.com/docs/5.0
- **HTTP Client Docs:** https://laravel.com/docs/11/http-client
- **CSV Reader:** https://csv.thephpleague.com/

---

**Good Luck with API Integration! 🚀**
