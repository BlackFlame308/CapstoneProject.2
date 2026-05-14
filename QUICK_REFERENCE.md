# SafeTrack Admin Dashboard - Quick Reference Card

## 🚀 Quick Start Commands

```bash
# Install dependencies
composer require maatwebsite/excel barryvdh/laravel-pdf

# Run migrations
php artisan migrate

# Clear cache
php artisan cache:clear
php artisan route:cache

# Start server
php artisan serve --host=localhost --port=8000
```

---

## 🔗 Feature URLs

| Feature | URL | Actions |
|---------|-----|---------|
| **Accounts** | `/admin/accounts` | Create, Edit, Delete users |
| **Vulnerable Groups** | `/admin/vulnerable-groups` | Manage group categories |
| **Device Tracking** | `/admin/device-tokens` | Monitor device health |
| **Search** | `/admin/search` | Cross-table search |
| **CSV Import** | `/admin/csv-import` | View import history |
| **Audit Logs** | `/admin/audit-logs` | Track changes |
| **Export** | `/admin/export/*` | Excel/PDF export |
| **Notifications** | `/admin/notifications` | Send alerts |

---

## 📝 Common Code Patterns

### Controller Pattern
```php
<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\YourModel;
use Illuminate\Http\Request;

class YourAdminController extends Controller
{
    public function index(Request $request)
    {
        $items = YourModel::paginate(20);
        return view('admin.your-feature.index', ['items' => $items]);
    }

    public function create()
    {
        return view('admin.your-feature.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        YourModel::create($validated);
        return redirect()->route('admin.your-feature.index')
            ->with('success', 'Created successfully');
    }
}
```

### View Pattern (Blade)
```blade
@extends('layouts.admin')

@section('page_title', 'Feature Name')
@section('page_icon')
    <i class="fas fa-icon"></i>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <!-- Your content -->
    </div>
</div>
@endsection
```

### Route Pattern
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/feature', [FeatureController::class, 'index'])->name('feature.index');
    Route::get('/feature/create', [FeatureController::class, 'create'])->name('feature.create');
    Route::post('/feature', [FeatureController::class, 'store'])->name('feature.store');
    Route::get('/feature/{model}/edit', [FeatureController::class, 'edit'])->name('feature.edit');
    Route::put('/feature/{model}', [FeatureController::class, 'update'])->name('feature.update');
    Route::delete('/feature/{model}', [FeatureController::class, 'destroy'])->name('feature.destroy');
});
```

---

## 🎨 Blade Component Templates

### Stat Card
```blade
<div class="stat-card">
    <div class="stat-icon">📊</div>
    <div class="stat-value">{{ $count }}</div>
    <div class="stat-label">{{ $label }}</div>
</div>
```

### Table with Actions
```blade
<table class="table">
    <thead>
        <tr style="background-color: #f8f9fa;">
            <th style="font-weight: 600; padding: 15px;">Column</th>
            <th style="font-weight: 600; padding: 15px; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td style="padding: 15px;">{{ $item->name }}</td>
            <td style="padding: 15px; text-align: center;">
                <a href="{{ route('admin.feature.edit', $item) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('admin.feature.destroy', $item) }}" method="POST" style="display: inline;">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
```

### Form Input
```blade
<div class="mb-3">
    <label class="form-label" style="font-weight: 600;">
        Field Name <span style="color: #dc3545;">*</span>
    </label>
    <input type="text" name="field_name" class="form-control @error('field_name') is-invalid @enderror"
           value="{{ old('field_name') }}">
    @error('field_name')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
```

### Badge Status
```blade
@php
    $color = match($status) {
        'active' => '#d4edda',
        'inactive' => '#f8d7da',
        default => '#e2e3e5'
    };
    $textColor = match($status) {
        'active' => '#155724',
        'inactive' => '#721c24',
        default => '#383d41'
    };
@endphp
<span class="badge" style="background-color: {{ $color }}; color: {{ $textColor }};">
    {{ ucfirst($status) }}
</span>
```

---

## 🔍 Database Query Helpers

### Eager Load Relationships
```php
$items = Item::with('relation1', 'relation2')->paginate(20);
```

### Filter Query
```php
$query = Item::query();

if ($request->get('status')) {
    $query->where('status', $request->get('status'));
}

if ($request->get('date')) {
    $query->whereDate('created_at', $request->get('date'));
}

$items = $query->latest()->paginate(20);
```

### Create with Relationships
```php
$item = Item::create($validated);
$item->relations()->attach($relationIds);
```

### Soft Deletes
```php
// Define in Model
use SoftDeletes;
protected $dates = ['deleted_at'];

// Query
Item::withTrashed()->find($id);
Item::onlyTrashed()->get();
```

---

## 🧪 Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=AccountAdminControllerTest

# Generate coverage
php artisan test --coverage

# Refresh database and seed
php artisan migrate:fresh --seed
```

---

## 🐛 Debug Tips

### dd() - Dump and Die
```php
dd($variable);  // Output and stop execution
```

### Log Messages
```php
Log::info('Info message', $data);
Log::warning('Warning message');
Log::error('Error message', ['context' => $data]);
```

### SQL Debugging
```php
DB::enableQueryLog();
// ... your queries ...
dd(DB::getQueryLog());
```

### Artisan Debugging
```bash
php artisan tinker          # Interactive shell
php artisan route:list      # Show all routes
php artisan config:show     # Show configuration
```

---

## 📚 File Structure Reference

```
app/
├── Http/Controllers/Admin/
│   ├── [Feature]AdminController.php
│   └── ...
├── Models/
│   ├── [Model].php
│   └── ...
└── ...

resources/
├── views/admin/
│   ├── [feature]/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── show.blade.php
│   ├── layouts/admin.blade.php
│   └── ...
├── css/
│   └── admin.css
└── ...

routes/
├── web.php
└── api.php

database/
├── migrations/
└── seeders/
```

---

## 🔐 Common Validation Rules

```php
$validated = $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|confirmed',
    'age' => 'required|integer|between:1,150',
    'status' => 'required|in:active,inactive',
    'date' => 'required|date|before:today',
    'file' => 'required|file|mimes:pdf,xlsx|max:2048',
]);
```

---

## 💾 Common Model Relationships

```php
// One to Many
class Parent extends Model {
    public function children() {
        return $this->hasMany(Child::class);
    }
}

// Many to Many
class Student extends Model {
    public function courses() {
        return $this->belongsToMany(Course::class);
    }
}

// Belongs To
class Child extends Model {
    public function parent() {
        return $this->belongsTo(Parent::class);
    }
}
```

---

## 🔗 Useful Links

- **Laravel Docs:** https://laravel.com/docs
- **Bootstrap Docs:** https://getbootstrap.com/docs
- **Font Awesome:** https://fontawesome.com/icons
- **Blade Syntax:** https://laravel.com/docs/blade
- **Eloquent ORM:** https://laravel.com/docs/eloquent

---

## ⚡ Performance Tips

1. **Use Pagination**
   ```php
   $items = Item::paginate(20);  // Not: Item::all()
   ```

2. **Eager Load**
   ```php
   $items = Item::with('relation')->get();  // Not: N+1 queries
   ```

3. **Index Database Columns**
   ```sql
   CREATE INDEX idx_user_id ON items(user_id);
   ```

4. **Cache Queries**
   ```php
   $items = Cache::remember('items', 3600, function() {
       return Item::all();
   });
   ```

---

## 📋 Deployment Checklist

- [ ] Install all composer packages
- [ ] Run database migrations
- [ ] Set proper file permissions
- [ ] Clear all caches
- [ ] Run tests
- [ ] Test all features
- [ ] Check error logs
- [ ] Verify email/SMS sending
- [ ] Monitor performance

---

## 🎯 Troubleshooting Quick Fixes

| Issue | Solution |
|-------|----------|
| Routes not found | `php artisan route:cache --force` |
| Views not rendering | `php artisan view:cache --force` |
| Database errors | `php artisan migrate` |
| Permission denied | `chmod -R 775 storage/` |
| CSS not loading | `php artisan storage:link` |
| Slow queries | Add database indexes |
| Memory limit | Increase `memory_limit` in php.ini |

---

**Quick Reference Card - Always Handy!**
