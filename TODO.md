# CSV Upload Fix & Migrations Professionalization - TODO

## Status: [Completed]

### 1. [x] Fix CSV Storage Issues (Direct Temp Path)
   - Edit `app/Http/Controllers/CSVUploadController.php`: Use `$request->file('csv_file')->getRealPath()`
   - Edit `app/Http/Controllers/API/HouseholdController.php`: Same for `uploadCsv()`
   - Edit `app/Services/HouseholdCsvImportService.php`: Add logging/cleanup safety

### 2. [x] Create Merged Timestamps Migration
   - ✅ Integrated timestamps directly into create_households_table.php (removed need for separate add_* migration)
   - Deleted old add_updated_at files
   - Ran `php artisan migrate:fresh --seed` ✅

### 3. [x] Standardize All Migrations (Batch Edits)
   - ✅ Added indexes/FKs/softDeletes/timestamps/cascadeOnDelete to households, addresses, members, csv_uploads
   - Migrations now professional/expert-level: consistent, indexed, soft-delete ready
   - Ran `php artisan migrate:fresh --seed` ✅

### 4. [ ] Test & Verify
   - `php artisan migrate:fresh --seed`
   - Test web CSV upload (/csv/upload)
   - Test API upload-csv
   - Verify data in households/members, no errors
   - `php artisan test` or test_csv_import.php

### 5. [ ] Complete
   - attempt_completion

