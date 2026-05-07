# Sample CSV Import Guide

This document describes the CSV format for importing households and members.

## File: sample_household_import.csv

A working sample CSV file is provided at `sample_household_import.csv`.

## CSV Column Reference

### Household Information (Columns 0-9)

| Column | Field Name | Required | Description | Example |
|-------|------------|----------|-------------|---------|
| 0 | head_first_name | Yes | Household head first name | Juan |
| 1 | head_middle_name | No | Household head middle name | Dela |
| 2 | head_last_name | Yes | Household head last name | Cruz |
| 3 | household_name | No | Full household name | Juan Dela Cruz |
| 4 | email | No | Household email | example@email.com |
| 5 | street | No | Street address | 123 Main Street |
| 6 | purok | No | Purok/Sitio name | Purok 1 |
| 7 | barangay | Yes | Barangay name | Barangay 1 |
| 8 | contact_number | No | Contact number | 09123456789 |
| 9 | emergency_contact | No | Emergency contact | 09987654321 |

### Member Information (Columns 10-20)

| Column | Field Name | Required | Description | Example |
|-------|------------|----------|-------------|---------|
| 10 | member_first_name | No* | Member first name | Juan |
| 11 | member_middle_name | No | Member middle name | Dela |
| 12 | member_last_name | No* | Member last name | Cruz |
| 13 | member_birth_date | No* | Birth date (MM/DD/YYYY or YYYY-MM-DD) | 01/15/1990 |
| 14 | member_sex | No | Sex (M/F) | M |
| 15 | member_relation | No | Relation to head | Head |
| 16 | member_civil_status | No | Civil status | Married |
| 17 | member_education_level | No | Education level | College |
| 18 | member_occupation | No | Occupation | Farmer |
| 19 | member_is_pwd | No | Person with Disability (Y/N) | N |
| 20 | member_is_pregnant | No | Pregnant (Y/N) | N |

*If member information is provided, at least first_name, last_name, and birth_date are required.

## Important Notes

1. **Barangay must exist**: The barangay must already exist in the database. Run the LocationSeeder first to populate barangays.

2. **One row = One household + One member**: Each CSV row creates a household and optionally one member. For households with multiple members, create multiple rows with the same household head information.

3. **Auto-generated data**:
   - Household code: Auto-generated (e.g., HH-A1B2C3D4)
   - User account: Created for household head with temp password
   - Login email: Uses household email or auto-generated (HH-XXXX@capstone.local)

4. **Date formats supported**:
   - MM/DD/YYYY (e.g., 01/15/1990)
   - YYYY-MM-DD (e.g., 1990-01-15)

## Example Usage

### Single Member Household
```csv
head_first_name,head_middle_name,head_last_name,household_name,email,street,purok,barangay,contact_number,emergency_contact,member_first_name,member_middle_name,member_last_name,member_birth_date,member_sex,member_relation,member_civil_status,member_education_level,member_occupation,member_is_pwd,member_is_pregnant
Juan,Dela,Cruz,Juan Dela Cruz,juan@email.com,123 Main St,Purok 1,Barangay 1,09123456789,09987654321,Juan,Dela,Cruz,01/15/1990,M,Head,Married,College,Farmer,N,N
```

### Multi-Member Household (two rows with same head)
```csv
head_first_name,head_middle_name,head_last_name,household_name,email,street,purok,barangay,contact_number,emergency_contact,member_first_name,member_middle_name,member_last_name,member_birth_date,member_sex,member_relation,member_civil_status,member_education_level,member_occupation,member_is_pwd,member_is_pregnant
Juan,Dela,Cruz,Juan Dela Cruz,juan@email.com,123 Main St,Purok 1,Barangay 1,09123456789,09987654321,Juan,Dela,Cruz,01/15/1990,M,Head,Married,College,Farmer,N,N
Juan,Dela,Cruz,Juan Dela Cruz,juan@email.com,123 Main St,Purok 1,Barangay 1,09123456789,09987654321,Maria,Dela,Cruz,03/20/1995,F,Wife,Married,College,Teacher,N,Y
```

## Prerequisites

Before importing CSV, ensure:

1. **Roles are seeded**: Run `php artisan db:seed --class=RoleSeeder`
2. **Locations are seeded**: Run `php artisan db:seed --class=LocationSeeder`
3. **Migrations are run**: Run `php artisan migrate`

## Testing the Import

```bash
# Test import with sample CSV
php artisan tinker
App\Models\User::first()->id  # Get admin user ID
```

Then use the CSV upload feature in the web interface or API to upload `sample_household_import.csv`.
