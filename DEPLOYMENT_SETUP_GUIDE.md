# SafeTrack Features - Deployment & Setup Guide

## Quick Start - Feature Activation

All features are implemented and ready to use. Follow these steps to fully activate them:

---

## Step 1: Install Required Packages

For data export functionality (Excel & PDF):

```bash
cd c:\CapsoneProject\Capstone
composer require maatwebsite/excel barryvdh/laravel-pdf
```

---

## Step 2: Run Database Migrations

Create the required database tables:

```bash
php artisan migrate
```

### If migrations don't include audit_logs & notifications, create them manually:

```bash
php artisan make:migration create_audit_logs_table
php artisan make:migration create_notifications_table
```

Then copy the migration code below:

#### Migration: `create_audit_logs_table.php`
```php
public function up(): void
{
    Schema::create('audit_logs', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('user_id')->nullable();
        $table->string('action', 50);
        $table->string('model', 255);
        $table->string('model_id', 255)->nullable();
        $table->json('changes')->nullable();
        $table->timestamps();
        
        $table->foreign('user_id')
            ->references('user_id')
            ->on('users')
            ->onDelete('set null');
        
        $table->index(['user_id', 'created_at']);
    });
}

public function down(): void
{
    Schema::dropIfExists('audit_logs');
}
```

#### Migration: `create_notifications_table.php`
```php
public function up(): void
{
    Schema::create('notifications', function (Blueprint $table) {
        $table->bigIncrements('id');
        $table->string('user_id');
        $table->string('title', 255);
        $table->text('message');
        $table->string('notification_channel', 50);
        $table->string('severity_level', 50);
        $table->string('notification_status', 50)->default('pending');
        $table->timestamp('sent_at')->nullable();
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
        
        $table->foreign('user_id')
            ->references('user_id')
            ->on('users')
            ->onDelete('cascade');
        
        $table->index(['user_id', 'notification_status']);
        $table->index(['notification_channel']);
    });
}

public function down(): void
{
    Schema::dropIfExists('notifications');
}
```

---

## Step 3: Publish Config Files

For Excel export functionality:

```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config
```

For PDF functionality:

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

---

## Step 4: Clear Cache

```bash
php artisan cache:clear
php artisan config:cache
php artisan route:cache
```

---

## Step 5: Verify Installation

Test the new features:

### 1. Access Admin Dashboard
```
http://localhost:8000/admin/dashboard
```

### 2. Test Features
- **Accounts:** http://localhost:8000/admin/accounts
- **Vulnerable Groups:** http://localhost:8000/admin/vulnerable-groups
- **Device Tokens:** http://localhost:8000/admin/device-tokens
- **Advanced Search:** http://localhost:8000/admin/search
- **CSV Import:** http://localhost:8000/admin/csv-import
- **Audit Logs:** http://localhost:8000/admin/audit-logs
- **Notifications:** http://localhost:8000/admin/notifications

---

## Feature Access Control

### Admin-Only Routes
All admin features require authentication with `admin` middleware:
```
Routes: /admin/*
Required: Authenticated user with appropriate role
```

### Role-Based Access (can be customized)
- **Captain:** Full access to all admin features
- **Encoder:** Limited access (view/create only)
- **Household:** No admin access

---

## File Storage Configuration

For CSV imports and exports:

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'private',
    ],
    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
    ],
]
```

Ensure storage is linked:
```bash
php artisan storage:link
```

---

## Testing the Features

### 1. Create an Account
```
Navigate to: /admin/accounts/create
- Fill in: Name, Email, Username, Password, Role
- Click: Create Account
```

### 2. Create a Notification
```
Navigate to: /admin/notifications/create
- Select: Recipient (User/Role/All)
- Enter: Title, Message
- Select: Channel (Email/SMS/Push/In-App)
- Choose: Severity Level
- Click: Send
```

### 3. Track Device Tokens
```
Navigate to: /admin/device-tokens
- View: List of registered devices
- Click: Device name for details
- See: Battery level, signal strength, last login
```

### 4. Advanced Search
```
Navigate to: /admin/search
- Enter: Search query (household code/member name)
- Select: Filters (Barangay, Gender, etc.)
- View: Results list
```

### 5. Export Data
```
Navigate to: /admin/households or /admin/residents
- Click: Export to Excel or Export to PDF button
- File downloads automatically
```

---

## Environment Variables

Add to `.env` if needed:

```env
# Mail Configuration (for email notifications)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@safetrack.local

# PDF Export
PDF_PUBLIC_PATH=/storage
PDF_PRIVATE_PATH=/pdf
```

---

## Troubleshooting

### Issue: Routes not accessible
**Solution:** Clear route cache
```bash
php artisan route:cache --force
```

### Issue: Blade templates not found
**Solution:** Verify view paths and clear cache
```bash
php artisan view:cache --force
```

### Issue: Database errors on migration
**Solution:** Check database connection in `.env` and ensure migrations table exists
```bash
php artisan migrate:fresh  # Warning: This clears all data!
```

### Issue: CSS/JS not loading
**Solution:** Ensure public folder is set up correctly
```bash
php artisan storage:link
php artisan serve --host=localhost --port=8000
```

### Issue: Export files not downloading
**Solution:** Check storage permissions
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

---

## API Endpoints (if API routes added)

### Accounts
```
GET    /api/admin/accounts
POST   /api/admin/accounts
GET    /api/admin/accounts/{user_id}
PUT    /api/admin/accounts/{user_id}
DELETE /api/admin/accounts/{user_id}
```

### Notifications
```
GET    /api/admin/notifications
POST   /api/admin/notifications
GET    /api/admin/notifications/{id}
POST   /api/admin/notifications/{id}/retry
DELETE /api/admin/notifications/{id}
```

---

## Performance Tips

1. **Enable Query Caching:**
   ```php
   // config/database.php
   'redis' => [
       'client' => 'phpredis',
       'connection' => 'cache',
   ]
   ```

2. **Optimize Database Indexes:**
   ```sql
   CREATE INDEX idx_user_id ON users(user_id);
   CREATE INDEX idx_audit_logs ON audit_logs(user_id, created_at);
   CREATE INDEX idx_notifications ON notifications(user_id, notification_status);
   ```

3. **Pagination:**
   - Accounts: 15 items/page
   - Search Results: 20 items/page
   - Device Tokens: 20 items/page
   - Audit Logs: 50 items/page

---

## Maintenance Tasks

### Daily
- Monitor failed notifications
- Check device token status
- Review audit logs

### Weekly
- Archive old audit logs (>6 months)
- Clean up failed import records
- Review user account activity

### Monthly
- Backup database
- Export usage reports
- Review and update vulnerable groups

---

## Support

For issues or questions:
1. Check `FEATURES_IMPLEMENTATION_COMPLETE.md` for detailed documentation
2. Review controller code for business logic
3. Check blade templates for UI structure
4. Consult `IMPLEMENTATION_SUMMARY.md` for system overview

---

## Version History

- **v1.0** (Current) - Initial feature implementation
  - Account Management
  - Vulnerable Groups
  - Device Tracking
  - Advanced Search
  - CSV Import Dashboard
  - Audit Logs
  - Notifications
  - Data Export

---

**Setup Complete!** Your SafeTrack admin dashboard is now fully operational with all features activated.
