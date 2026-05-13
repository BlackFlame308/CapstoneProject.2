# SafeTrack Admin Dashboard System

A complete Laravel MVC-based Admin Dashboard for the SafeTrack Barangay Management System.

## 📋 Overview

The SafeTrack Admin Dashboard provides barangay administrators and data encoders with a modern, functional interface to manage households, residents, user accounts, analytics, and reports.

### Key Features

✅ **Dashboard** - Real-time statistics and overview
✅ **Household Management** - Full CRUD with location hierarchy  
✅ **Resident Management** - Add/edit/delete household members  
✅ **Account Management** - Create and manage user accounts  
✅ **Analytics** - Demographics, population distribution, vulnerability rankings  
✅ **Reports** - Placeholder for subsystem integration (evacuation, rescue, logistics)  
✅ **RBAC** - Role-based access control (Barangay Head, Encoder)  
✅ **Bootstrap 5 UI** - Clean, modern, responsive design  
✅ **Blade Templating** - Pure Laravel MVC (no Inertia/React)  

---

## 🚀 Getting Started

### 1. Access the Admin Dashboard

Navigate to: `http://your-app.local/admin`

**Note:** You must be authenticated and have appropriate role (head or encoder)

### 2. User Roles

#### **Barangay Head (role='head')**
- Full access to all admin features
- Can create, read, update, DELETE households
- Can create, read, update, DELETE residents
- Can create, read, update, DELETE accounts
- Can view all analytics and reports

#### **Data Encoder (role='encoder')**
- Can create, read, UPDATE households
- **CANNOT delete households** (delete button hidden)
- Can create, read, UPDATE residents
- **CANNOT delete residents** (delete button hidden)
- Can view all analytics and reports
- Limited account access (cannot create/delete)

#### **Household User (role='household')**
- Limited access to own household information
- Cannot access admin dashboard features

---

## 📂 Project Structure

### Controllers (`app/Http/Controllers/Admin/`)

```
Admin/
├── AdminDashboardController.php      # Dashboard stats and overview
├── HouseholdAdminController.php      # Household CRUD operations
├── ResidentAdminController.php       # Member/Resident management
├── AccountAdminController.php        # User account management
├── AnalyticsAdminController.php      # Analytics and statistics
└── ReportAdminController.php         # Reports from subsystems
```

### Views (`resources/views/`)

```
views/
├── layouts/
│   └── admin.blade.php               # Main admin layout with sidebar
├── admin/
│   ├── dashboard.blade.php           # Dashboard page
│   ├── households/
│   │   ├── index.blade.php           # List households
│   │   ├── create.blade.php          # Create household form
│   │   ├── show.blade.php            # View household details
│   │   └── edit.blade.php            # Edit household form
│   ├── residents/
│   │   ├── create.blade.php          # Add resident form
│   │   └── edit.blade.php            # Edit resident form
│   ├── accounts/
│   │   ├── index.blade.php           # List accounts
│   │   ├── create.blade.php          # Create account form
│   │   └── edit.blade.php            # Edit account form
│   ├── analytics/
│   │   └── index.blade.php           # Analytics dashboard
│   └── reports/
│       ├── index.blade.php           # Reports overview
│       ├── evacuation.blade.php      # Evacuation reports
│       ├── rescue.blade.php          # Rescue reports
│       └── logistics.blade.php       # Logistics reports
```

### Routes (`routes/web.php`)

All admin routes are prefixed with `/admin` and protected by `admin` middleware:

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('households', HouseholdAdminController::class);
    Route::resource('residents', ResidentAdminController::class);
    Route::resource('accounts', AccountAdminController::class);
    Route::get('analytics', [AnalyticsAdminController::class, 'index'])->name('analytics.index');
    Route::get('reports', [ReportAdminController::class, 'index'])->name('reports.index');
    // ... more routes
});
```

---

## 🎯 Features Details

### 1. Dashboard (`/admin`)

**Statistics Displayed:**
- Total Households
- Total Population
- Children (< 18 years)
- Seniors (60+ years)
- PWD (Persons with Disabilities)
- Sitio Rankings (most populated areas)
- Recent households added
- Latest reports placeholder

**Quick Actions:**
- Add Household
- Create Account
- View All Households
- View Analytics

### 2. Household Management (`/admin/households`)

**List View:**
- Paginated list with search
- Filter by household code/name, sitio, barangay
- View details, edit, delete (if authorized)
- Display member count per household

**Create/Edit:**
- Household code (unique identifier)
- Household name
- Location hierarchy (Region → Province → City → Barangay → Sitio)
- Contact information (phone, email, emergency contact)
- Dynamic location dropdowns (AJAX cascading selects)

**View Details:**
- Full household information
- Location details
- All members with demographics
- Add new member button
- Edit/Delete resident options

### 3. Resident Management

**Add/Edit Member:**
- Personal information (name, birth date, sex)
- Family relationship (head, spouse, child, parent, etc.)
- Civil status
- Education level
- Occupation
- Special status (PWD, pregnant)
- Special needs/notes
- Auto-calculated age from birth date

### 4. Account Management (`/admin/accounts`)

**List View:**
- All user accounts with pagination
- Search by name, email, username
- Filter by role
- Display active/inactive status
- Edit/delete buttons

**Create Account:**
- Full name, username, email, contact
- Password assignment or temp password generation
- Role assignment (head, encoder, household)
- Household assignment (optional)
- Account activation status

**Update Account:**
- Edit all user information
- Change password (optional)
- Update role and household assignment
- Activate/deactivate account

### 5. Analytics Dashboard (`/admin/analytics`)

**Key Metrics:**
- Total households and population
- Children, seniors, PWD, pregnant count

**Demographics Breakdown:**
- Age distribution (0-5, 6-12, 13-17, 18-35, 36-59, 60+)
- Gender distribution
- Civil status breakdown
- Education level distribution
- Sitio/Purok population distribution with visual progress bars

### 6. Reports (`/admin/reports`)

**Current State:** Placeholder/Empty state UI

**Report Types:**
1. **Evacuation Reports** - Disaster evacuation incidents
2. **Rescue Reports** - Emergency rescue operations
3. **Logistics Reports** - Aid and supply distribution

Each report view has:
- Filter controls (status, date range, type)
- Empty state with integration checklist
- Placeholder message: "Reports will appear here once subsystems are connected via API"

---

## 🔐 RBAC Implementation

### Middleware

**AdminMiddleware** (`app/Http/Middleware/AdminMiddleware.php`)
- Checks if user is authenticated
- Verifies user has appropriate role (head or encoder)
- Redirects to dashboard if unauthorized

### Policy Checks

**Delete Operations:**
```blade
@if(auth()->user()->role && in_array(auth()->user()->role->name, ['head', 'Captain']))
    <!-- Show delete button -->
@endif
```

**Model-Level:**
```php
// In routes/web.php
Route::delete('/households/{household}', ...)
    ->middleware('can:delete,household')
    ->name('households.destroy');
```

---

## 📝 Database Models Used

### Household Model
- `id` (UUID)
- `household_code` (unique)
- `household_name`
- `address_id` (foreign key to Address)
- `contact_number`
- `email`
- `emergency_contact`
- `created_by` (user ID)

**Relationships:**
- `hasMany(Member)` - Household members/residents
- `belongsTo(Address)` - Location information
- `belongsTo(User)` - Created by user

### Member Model
- `id` (UUID)
- `household_id` (foreign key)
- `name` / `first_name`, `middle_name`, `last_name`
- `birth_date`
- `age` (auto-calculated)
- `sex` (M/F)
- `relation` (Head, Spouse, Child, etc.)
- `civil_status`
- `education_level`
- `occupation`
- `is_pwd` (boolean)
- `is_pregnant` (boolean)
- `special_needs`

**Relationships:**
- `belongsTo(Household)` - Parent household

### User Model
- `id` (UUID)
- `name`, `username`, `email`
- `password` (hashed)
- `role_id` (foreign key to Role)
- `household_id` (optional, for household-level users)
- `is_active`
- `must_change_password`
- `temp_password`

**Relationships:**
- `belongsTo(Role)` - User role (head, encoder, household)
- `belongsTo(Household)` - Assigned household

### Address Model
- `id` (UUID)
- `region_id`, `province_id`, `city_id`, `barangay_id`
- `purok_sitio`
- `street_address`

**Relationships:**
- Hierarchical location structure

---

## 🎨 UI/UX Design

### Layout
- **Sidebar Navigation** (fixed, 280px)
  - SafeTrack branding
  - Main navigation menu
  - Active page highlight
  - Logout option

- **Top Navigation Bar** (sticky)
  - Page title and icon
  - User profile dropdown
  - Current role display

- **Content Area**
  - Responsive grid layout
  - Bootstrap 5 components
  - Card-based design

### Color Scheme
- **Primary**: Purple gradient (#667eea → #764ba2)
- **Secondary**: Grays (#f8f9fa, #333, #999)
- **Accent**: Success (#d4edda), Danger (#f8d7da), Warning (#fff3cd)

### Components
- **Stat Cards** - Large metric display with icons
- **Tables** - Paginated, sortable, responsive
- **Forms** - Validated with error messages
- **Buttons** - Primary, secondary, danger, info variants
- **Alerts** - Success, error, info messages (auto-dismiss)
- **Badges** - Status indicators

---

## 🔗 API Integration Points

### TODO FOR STUDENTS

The following sections need API integration:

#### 1. **Reports from Subsystems**

**Evacuation Subsystem API**
```
GET /api/reports/evacuation?status={status}&date_from={date}&date_to={date}

Response:
{
  "data": [
    {
      "id": "...",
      "disaster_type": "flood",
      "households_affected": 50,
      "persons_evacuated": 250,
      "evacuation_sites": [...],
      "status": "ongoing",
      "created_at": "..."
    }
  ]
}
```

**Token Handshake:**
- Use Bearer token authentication
- Store API token in `.env`
- Implement token refresh mechanism

**Integration Location:** `ReportAdminController::evacuation()`

#### 2. **Rescue Subsystem API**

```
GET /api/reports/rescue?status={status}&incident_type={type}&date_from={date}&date_to={date}

Response:
{
  "data": [
    {
      "id": "...",
      "incident_type": "fire",
      "persons_rescued": 5,
      "location": "Purok 1",
      "status": "completed",
      "created_at": "..."
    }
  ]
}
```

**Integration Location:** `ReportAdminController::rescue()`

#### 3. **Logistics Subsystem API**

```
GET /api/reports/logistics?status={status}&item_type={type}&date_from={date}&date_to={date}

Response:
{
  "data": [
    {
      "id": "...",
      "item_type": "food",
      "quantity": 100,
      "unit": "bags",
      "status": "distributed",
      "distribution_date": "...",
      "created_at": "..."
    }
  ]
}
```

**Integration Location:** `ReportAdminController::logistics()`

#### 4. **CSV Import**

**Location:** `HouseholdAdminController::uploadCsv()`

- Use `HouseholdCsvImportService` for bulk imports
- Implement file validation
- Add duplicate detection
- Store import logs with timestamp

---

## 🛠 Installation & Setup

### 1. Prerequisites
- Laravel 11+
- PHP 8.1+
- MySQL/PostgreSQL
- Composer

### 2. Database Setup

Run migrations:
```bash
php artisan migrate
```

Seed roles:
```bash
php artisan db:seed RoleSeeder  # If exists
```

### 3. Create First User

```bash
php artisan tinker

// In tinker:
$user = \App\Models\User::create([
    'name' => 'Admin',
    'username' => 'admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role_id' => \App\Models\Role::where('name', 'head')->first()->id,
    'is_active' => true,
]);
```

### 4. Access Dashboard

1. Login: `http://your-app.local/login`
2. Go to: `http://your-app.local/admin`

---

## 📊 Sample Data

### Create Sample Household

1. Go to `/admin/households/create`
2. Fill in:
   - Code: `HH-001`
   - Name: `Santos Family`
   - Select location (Region → Province → City → Barangay → Sitio)
   - Enter contact details
3. Click "Create Household"

### Add Member

1. View household details
2. Click "Add Member"
3. Fill in member details
4. Submit

### Create Account

1. Go to `/admin/accounts/create`
2. Fill in user info
3. Select role (head/encoder/household)
4. If household role, assign to a household
5. Submit

---

## 🐛 Troubleshooting

### Routes Not Found

**Issue:** Getting 404 on `/admin` routes

**Solution:**
- Verify routes are registered in `routes/web.php`
- Run `php artisan route:list | grep admin`
- Check middleware is registered in `bootstrap/app.php`

### Unauthorized Access

**Issue:** Redirected to dashboard when accessing admin

**Solution:**
- Check user role in database: `SELECT * FROM users WHERE id = X`
- Verify role name matches: 'head', 'encoder', 'household'
- Check `AdminMiddleware` is applied to routes

### Location Dropdown Not Populating

**Issue:** Cascade selects not loading

**Solution:**
- Check API route exists: `GET /locations/provinces/{regionId}`
- Verify LocationController is implemented
- Check browser console for AJAX errors
- Verify region/province/city/barangay IDs exist

---

## 📚 Code Comments for Students

All controller methods include detailed comments explaining:

1. **What the method does**
2. **Where API integration will be added**
3. **Where token handshake will be added**
4. **Where real data will replace dummy data**
5. **Database queries used**
6. **RBAC logic applied**

Example:
```php
/**
 * TODO FOR STUDENT:
 * - Implement CSV parsing
 * - Validate CSV format
 * - Use HouseholdCsvImportService for bulk import
 * - Handle duplicate detection
 * - Store import logs
 */
public function uploadCsv(Request $request, Household $household)
{
    return back()->with('info', 'CSV upload feature is being integrated...');
}
```

---

## 🎓 Learning Objectives

By implementing this dashboard, students will learn:

✅ Laravel MVC architecture
✅ Blade templating
✅ Authentication & Authorization (RBAC)
✅ Form handling & validation
✅ Database relationships & queries
✅ Middleware usage
✅ RESTful routing
✅ Session management
✅ Bootstrap CSS framework
✅ AJAX for dynamic dropdowns
✅ API integration concepts
✅ Error handling & logging
✅ Database migrations
✅ Model factories & seeders

---

## 📞 Support & Next Steps

### For API Integration:
1. Document subsystem API endpoints
2. Create API client class
3. Implement error handling
4. Add caching layer
5. Test with mock data first

### For Real Data:
1. Replace dummy data with API calls
2. Implement pagination
3. Add filtering/sorting
4. Real-time updates (optional)

### For Production:
1. Add comprehensive error logging
2. Implement rate limiting
3. Add security headers
4. Enable query caching
5. Set up monitoring

---

## 📄 License

This SafeTrack Admin Dashboard is part of the Capstone Project for [Your Institution].

---

**Last Updated:** May 2026  
**Version:** 1.0  
**Status:** Functional & Ready for API Integration
