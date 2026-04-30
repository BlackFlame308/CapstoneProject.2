# System Audit & Fix TODO

## Status: COMPLETED ✅

## Issues Fixed:

### 1. AUTHORIZATION GAPS [CRITICAL] - FIXED ✅
- [x] Web routes now have role-based access control 
- [x] API HouseholdController has proper authorization middleware
- [x] Captain has full access (Captain only can DELETE)
- [x] Encoder can create/view but not delete
- [x] Households have limited access

### 2. BUGS & CODE ISSUES - FIXED ✅
- [x] Removed unused method `generateUniqueEmail()` in API HouseholdController
- [x] Fixed Comment/Logic mismatch in API AuthController (now checks isCaptain())
- [x] Fixed StoreHouseholdRequest authorize() to use `canManageHouseholds()`
- [x] Fixed UpdateHouseholdRequest authorize() to use `canManageHouseholds()`
- [x] Fixed HouseholdController - changed `household_id` to `household_code` in search
- [x] Fixed LocationController - changed int to string for UUID parameters
- [x] Fixed Household model casts - address_id and created_by should be string (UUID)

### 3. MIGRATION CLEANUP - VERIFIED ✅
- [x] No stale migration files exist in migrations folder

---

## Summary of Changes Made:

### 1. routes/web.php
- Added `role:Captain|Encoder` middleware to household CRUD routes
- Added `role:Captain` middleware to CSV upload and account management
- Added role-aware access to location dropdown routes

### 2. routes/api.php  
- Added `role:Captain|Encoder` middleware to protected routes
- DELETE routes restricted to Captain only
- Modified routes to manually handle authorization

### 3. app/Http/Controllers/API/HouseholdController.php
- Removed unused `generateUniqueEmail()` method

### 4. app/Http/Requests/StoreHouseholdRequest.php
- Fixed authorize() to use `canManageHouseholds()` instead of `can('manage_households')`

### 5. app/Http/Requests/UpdateHouseholdRequest.php
- Fixed authorize() to use `canManageHouseholds()` instead of `can('manage_households')`

### 6. app/Http/Controllers/API/AuthController.php
- Fixed comment and logic to check `isCaptain()` instead of `isSuperAdmin()`

### 7. app/Http/Controllers/HouseholdController.php
- Fixed search query: changed `household_id` to `household_code`

### 8. app/Http/Controllers/LocationController.php
- Fixed parameter types: changed `int` to `string` for UUID parameters

### 9. app/Models/Household.php
- Fixed casts: changed `integer` to `string` for UUID fields (address_id, created_by)

---

## Authorization Matrix:

| Feature | Captain | Encoder | Household |
|---------|---------|---------|-----------|
| View Dashboard | ✓ | ✓ | ✓ |
| View All Households | ✓ | ✓ | Own only |
| Create Household | ✓ | ✓ | ✗ |
| Edit Household | ✓ | ✓ | ✗ |
| Delete Household | ✓ | ✗ | ✗ |
| CSV Upload | ✓ | ✗ | ✗ |
| Manage Accounts | ✓ | ✗ | ✗ |
| Location Dropdowns | ✓ | ✓ | ✓ |

---

## Code Quality Issues Fixed:

1. **Type Mismatch in HouseholdController** - Search was using `household_id` instead of `household_code`
2. **Type Mismatch in LocationController** - Parameters were `int` but should be `string` for UUIDs
3. **Type Mismatch in Household Model** - Casts were `integer` but should be `string` for UUIDs
4. **Wrong Authorization Method** - Using `can('manage_households')` instead of `canManageHouseholds()`
