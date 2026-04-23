# Professional Code Audit & Fixes Plan

**Approved: Temp passwords OK for initial login + force change (must_change_password=true)**

## Priority 1: Security & Data Integrity
1. ~~[x]~~ Unique emails: Use household_code@households.capstone.local
2. ~~[x]~~ Random strong passwords: Str::password(12)
3. ~~[x]~~ Remove password display from flash messages
4. [ ] Exact barangay match (= instead of LIKE '%')

## Priority 2: Transactions & Robustness
5. [ ] Add DB::transaction to CSV import & household store
6. [ ] Rollback on failure, cleanup orphans
7. [ ] Better validation rules (Request classes)

## Priority 3: Flow Consistency
8. [ ] Sync create.blade.php JS cascading with controller pre-data
9. [ ] Add StoreHouseholdRequest form request
10. [ ] Unique household_code validation/index

## Priority 4: Polish
11. [ ] Full PHPDoc comments
12. [ ] Update upload.blade.php docs
13. [ ] Add unit tests

**Files to fix:** HouseholdCsvImportService.php, HouseholdController.php, create.blade.php

**Next:** Implement Priority 1 fixes.

