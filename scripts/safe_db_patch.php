<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "========================================================\n";
echo "STARTING SAFE DATABASE PATCH ON THE ACTIVE DATABASE\n";
echo "========================================================\n\n";

try {
    // 1. Roles table: Add 'name' column if missing
    if (Schema::hasTable('roles')) {
        if (!Schema::hasColumn('roles', 'name')) {
            echo "[+] Adding column 'name' to 'roles' table...\n";
            Schema::table('roles', function (Blueprint $table) {
                $table->string('name', 255)->nullable()->after('role_name');
            });
        }
        
        // Backfill name from role_name
        echo "[~] Backfilling 'name' column in 'roles' table...\n";
        DB::statement("UPDATE roles SET name = role_name WHERE name IS NULL OR name = ''");
        
        // Rename evac_admin to moderator and evac_personnel to personel if they exist
        echo "[~] Renaming evac roles to moderator and personel in database...\n";
        DB::statement("UPDATE roles SET role_key = 'moderator', role_name = 'Moderator', name = 'Moderator' WHERE role_key = 'evac_admin'");
        DB::statement("UPDATE roles SET role_key = 'personel', role_name = 'personel', name = 'personel' WHERE role_key = 'evac_personnel' OR role_key = 'evac_personel'");

        // Add name mapping to Captain/Moderator/personel/Household if needed
        DB::statement("UPDATE roles SET name = 'Captain' WHERE role_key = 'super_admin' OR role_key = 'admin'");
        DB::statement("UPDATE roles SET name = 'Moderator' WHERE role_key = 'moderator'");
        DB::statement("UPDATE roles SET name = 'personel' WHERE role_key = 'personel' OR role_key = 'rescuer'");
        DB::statement("UPDATE roles SET name = 'Household' WHERE role_key = 'household_resident'");
    }

    // 2. Users table: Add name, email_verified_at, must_change_password, temp_password, remember_token
    if (Schema::hasTable('users')) {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'name')) {
                echo "[+] Adding column 'name' to 'users' table...\n";
                $table->string('name', 255)->nullable()->after('last_name');
            }
            if (!Schema::hasColumn('users', 'email_verified_at')) {
                echo "[+] Adding column 'email_verified_at' to 'users' table...\n";
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                echo "[+] Adding column 'must_change_password' to 'users' table...\n";
                $table->boolean('must_change_password')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('users', 'temp_password')) {
                echo "[+] Adding column 'temp_password' to 'users' table...\n";
                $table->string('temp_password', 255)->nullable()->after('must_change_password');
            }
            if (!Schema::hasColumn('users', 'remember_token')) {
                echo "[+] Adding column 'remember_token' to 'users' table...\n";
                $table->rememberToken()->after('temp_password');
            }
        });

        // Backfill name from first_name and last_name
        echo "[~] Backfilling 'name' column in 'users' table...\n";
        DB::statement("UPDATE users SET name = CONCAT(IFNULL(first_name,''), ' ', IFNULL(last_name,'')) WHERE name IS NULL OR name = ''");
    }

    // 3. Households table: Add household_number, email, member_count, created_by
    if (Schema::hasTable('households')) {
        Schema::table('households', function (Blueprint $table) {
            if (!Schema::hasColumn('households', 'household_number')) {
                echo "[+] Adding column 'household_number' to 'households' table...\n";
                $table->string('household_number', 255)->nullable()->after('household_code');
            }
            if (!Schema::hasColumn('households', 'email')) {
                echo "[+] Adding column 'email' to 'households' table...\n";
                $table->string('email', 255)->nullable()->after('household_name');
            }
            if (!Schema::hasColumn('households', 'member_count')) {
                echo "[+] Adding column 'member_count' to 'households' table...\n";
                $table->unsignedInteger('member_count')->default(0)->after('email');
            }
            if (!Schema::hasColumn('households', 'created_by')) {
                echo "[+] Adding column 'created_by' to 'households' table...\n";
                $table->string('created_by', 255)->nullable()->after('member_count');
            }
        });
        
        // Backfill member_count by counting active household_members
        echo "[~] Backfilling 'member_count' column in 'households' table...\n";
        DB::statement("
            UPDATE households h
            SET h.member_count = (
                SELECT COUNT(*) FROM household_members m WHERE m.household_id = h.household_id
            )
        ");
    }

    // 4. Household_members table (mapped to Member model): Add deleted_at, is_pwd, is_senior, is_pregnant, name, sex, gender, age, relation, civil_status, special_needs, created_at, updated_at
    if (Schema::hasTable('household_members')) {
        Schema::table('household_members', function (Blueprint $table) {
            if (!Schema::hasColumn('household_members', 'deleted_at')) {
                echo "[+] Adding column 'deleted_at' to 'household_members' table...\n";
                $table->timestamp('deleted_at')->nullable();
            }
            if (!Schema::hasColumn('household_members', 'is_pwd')) {
                echo "[+] Adding column 'is_pwd' to 'household_members' table...\n";
                $table->boolean('is_pwd')->default(false);
            }
            if (!Schema::hasColumn('household_members', 'is_senior')) {
                echo "[+] Adding column 'is_senior' to 'household_members' table...\n";
                $table->boolean('is_senior')->default(false);
            }
            if (!Schema::hasColumn('household_members', 'is_pregnant')) {
                echo "[+] Adding column 'is_pregnant' to 'household_members' table...\n";
                $table->boolean('is_pregnant')->default(false);
            }
            if (!Schema::hasColumn('household_members', 'name')) {
                echo "[+] Adding column 'name' to 'household_members' table...\n";
                $table->string('name', 255)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'sex')) {
                echo "[+] Adding column 'sex' to 'household_members' table...\n";
                $table->string('sex', 1)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'gender')) {
                echo "[+] Adding column 'gender' to 'household_members' table...\n";
                $table->string('gender', 20)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'age')) {
                echo "[+] Adding column 'age' to 'household_members' table...\n";
                $table->unsignedInteger('age')->nullable();
            }
            if (!Schema::hasColumn('household_members', 'relation')) {
                echo "[+] Adding column 'relation' to 'household_members' table...\n";
                $table->string('relation', 50)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'civil_status')) {
                echo "[+] Adding column 'civil_status' to 'household_members' table...\n";
                $table->string('civil_status', 50)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'education_level')) {
                echo "[+] Adding column 'education_level' to 'household_members' table...\n";
                $table->string('education_level', 100)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'special_needs')) {
                echo "[+] Adding column 'special_needs' to 'household_members' table...\n";
                $table->string('special_needs', 50)->nullable();
            }
            if (!Schema::hasColumn('household_members', 'created_at')) {
                echo "[+] Adding column 'created_at' to 'household_members' table...\n";
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('household_members', 'updated_at')) {
                echo "[+] Adding column 'updated_at' to 'household_members' table...\n";
                $table->timestamp('updated_at')->nullable();
            }
        });

        echo "[~] Backfilling 'name' in 'household_members'...\n";
        DB::statement("UPDATE household_members SET name = CONCAT(IFNULL(first_name,''), ' ', IF(middle_name IS NOT NULL AND middle_name != '', CONCAT(middle_name, ' '), ''), IFNULL(last_name,'')) WHERE name IS NULL OR name = ''");

        echo "[~] Backfilling 'sex' and 'gender' in 'household_members'...\n";
        DB::statement("UPDATE household_members SET sex = 'M', gender = 'Male' WHERE (gender_id = 1 OR sex IS NULL) AND gender_id = 1");
        DB::statement("UPDATE household_members SET sex = 'F', gender = 'Female' WHERE (gender_id = 2 OR sex IS NULL) AND gender_id = 2");

        echo "[~] Backfilling 'age' (based on birth_date) in 'household_members'...\n";
        DB::statement("UPDATE household_members SET age = TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) WHERE age IS NULL");

        echo "[~] Backfilling 'civil_status' in 'household_members'...\n";
        DB::statement("UPDATE household_members SET civil_status = 'Single' WHERE civil_status_id = 1 AND civil_status IS NULL");
        DB::statement("UPDATE household_members SET civil_status = 'Married' WHERE civil_status_id = 2 AND civil_status IS NULL");
        DB::statement("UPDATE household_members SET civil_status = 'Widowed' WHERE civil_status_id = 3 AND civil_status IS NULL");
        DB::statement("UPDATE household_members SET civil_status = 'Divorced' WHERE civil_status_id = 4 AND civil_status IS NULL");

        echo "[~] Backfilling 'education_level' in 'household_members'...\n";
        DB::statement("
            UPDATE household_members m
            SET m.education_level = (
                SELECT el.education_level_label 
                FROM education_levels el 
                WHERE el.education_level_id = m.education_level_id
            )
            WHERE m.education_level IS NULL AND m.education_level_id IS NOT NULL
        ");

        echo "[~] Backfilling relational vulnerabilities in 'household_members'...\n";
        // PWD backfill
        DB::statement("
            UPDATE household_members m
            SET m.is_pwd = 1
            WHERE EXISTS (
                SELECT 1 FROM member_vulnerable_groups mvg
                JOIN vulnerable_groups vg ON mvg.vulnerable_group_id = vg.vulnerable_group_id
                WHERE mvg.member_id = m.member_id AND vg.vulnerable_group_key = 'pwd'
            )
        ");
        // Senior backfill
        DB::statement("
            UPDATE household_members m
            SET m.is_senior = 1
            WHERE EXISTS (
                SELECT 1 FROM member_vulnerable_groups mvg
                JOIN vulnerable_groups vg ON mvg.vulnerable_group_id = vg.vulnerable_group_id
                WHERE mvg.member_id = m.member_id AND vg.vulnerable_group_key = 'senior'
            )
        ");
        // Pregnant backfill
        DB::statement("
            UPDATE household_members m
            SET m.is_pregnant = 1
            WHERE EXISTS (
                SELECT 1 FROM member_vulnerable_groups mvg
                JOIN vulnerable_groups vg ON mvg.vulnerable_group_id = vg.vulnerable_group_id
                WHERE mvg.member_id = m.member_id AND vg.vulnerable_group_key = 'pregnant'
            )
        ");
    }

    // 5. Addresses table: Add street, purok_sitio, house_number, zip_code, full_address, barangay_name
    if (Schema::hasTable('addresses')) {
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'street')) {
                echo "[+] Adding column 'street' to 'addresses' table...\n";
                $table->string('street', 255)->nullable()->after('street_address');
            }
            if (!Schema::hasColumn('addresses', 'purok_sitio')) {
                echo "[+] Adding column 'purok_sitio' to 'addresses' table...\n";
                $table->string('purok_sitio', 255)->nullable()->after('sitio_id');
            }
            if (!Schema::hasColumn('addresses', 'house_number')) {
                echo "[+] Adding column 'house_number' to 'addresses' table...\n";
                $table->string('house_number', 255)->nullable()->after('purok_sitio');
            }
            if (!Schema::hasColumn('addresses', 'zip_code')) {
                echo "[+] Adding column 'zip_code' to 'addresses' table...\n";
                $table->string('zip_code', 50)->nullable()->after('zipcode_id');
            }
            if (!Schema::hasColumn('addresses', 'full_address')) {
                echo "[+] Adding column 'full_address' to 'addresses' table...\n";
                $table->string('full_address', 500)->nullable()->after('zip_code');
            }
            if (!Schema::hasColumn('addresses', 'barangay_name')) {
                echo "[+] Adding column 'barangay_name' to 'addresses' table...\n";
                $table->string('barangay_name', 255)->nullable()->after('barangay_id');
            }
        });

        echo "[~] Backfilling 'barangay_name' in 'addresses'...\n";
        DB::statement("
            UPDATE addresses a
            SET a.barangay_name = (
                SELECT b.barangay_name FROM barangays b WHERE b.barangay_id = a.barangay_id
            )
            WHERE a.barangay_name IS NULL AND a.barangay_id IS NOT NULL
        ");
        
        echo "[~] Backfilling 'purok_sitio' in 'addresses'...\n";
        DB::statement("
            UPDATE addresses a
            SET a.purok_sitio = (
                SELECT s.sitio_name FROM sitios s WHERE s.sitio_id = a.sitio_id
            )
            WHERE a.purok_sitio IS NULL AND a.sitio_id IS NOT NULL
        ");
    }

    // 6. Import_logs table: Add row_number
    if (Schema::hasTable('import_logs')) {
        Schema::table('import_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('import_logs', 'row_number')) {
                echo "[+] Adding column 'row_number' to 'import_logs' table...\n";
                $table->unsignedInteger('row_number')->nullable()->after('row_num');
            }
        });

        echo "[~] Backfilling 'row_number' in 'import_logs'...\n";
        DB::statement("UPDATE import_logs SET `row_number` = row_num WHERE `row_number` IS NULL OR `row_number` = 0");
    }

    // 7. Analytics table: Add barangay_id, purok_sitio, record_period, total_households, total_population, total_males, total_females, total_pwd, total_seniors, total_children, total_adults, total_pregnant, total_evacuees, created_at, updated_at
    if (Schema::hasTable('analytics')) {
        Schema::table('analytics', function (Blueprint $table) {
            if (!Schema::hasColumn('analytics', 'barangay_id')) {
                echo "[+] Adding column 'barangay_id' to 'analytics' table...\n";
                $table->unsignedInteger('barangay_id')->nullable()->after('evacuation_center_id');
            }
            if (!Schema::hasColumn('analytics', 'purok_sitio')) {
                echo "[+] Adding column 'purok_sitio' to 'analytics' table...\n";
                $table->string('purok_sitio', 150)->nullable()->after('barangay_id');
            }
            if (!Schema::hasColumn('analytics', 'record_period')) {
                echo "[+] Adding column 'record_period' to 'analytics' table...\n";
                $table->date('record_period')->nullable()->after('purok_sitio');
            }
            if (!Schema::hasColumn('analytics', 'total_households')) {
                echo "[+] Adding column 'total_households' to 'analytics' table...\n";
                $table->unsignedInteger('total_households')->default(0)->after('record_period');
            }
            if (!Schema::hasColumn('analytics', 'total_males')) {
                echo "[+] Adding column 'total_males' to 'analytics' table...\n";
                $table->unsignedInteger('total_males')->default(0)->after('total_population');
            }
            if (!Schema::hasColumn('analytics', 'total_females')) {
                echo "[+] Adding column 'total_females' to 'analytics' table...\n";
                $table->unsignedInteger('total_females')->default(0)->after('total_males');
            }
            if (!Schema::hasColumn('analytics', 'total_pwd')) {
                echo "[+] Adding column 'total_pwd' to 'analytics' table...\n";
                $table->unsignedInteger('total_pwd')->default(0)->after('total_females');
            }
            if (!Schema::hasColumn('analytics', 'total_seniors')) {
                echo "[+] Adding column 'total_seniors' to 'analytics' table...\n";
                $table->unsignedInteger('total_seniors')->default(0)->after('total_pwd');
            }
            if (!Schema::hasColumn('analytics', 'total_children')) {
                echo "[+] Adding column 'total_children' to 'analytics' table...\n";
                $table->unsignedInteger('total_children')->default(0)->after('total_seniors');
            }
            if (!Schema::hasColumn('analytics', 'total_adults')) {
                echo "[+] Adding column 'total_adults' to 'analytics' table...\n";
                $table->unsignedInteger('total_adults')->default(0)->after('total_children');
            }
            if (!Schema::hasColumn('analytics', 'total_pregnant')) {
                echo "[+] Adding column 'total_pregnant' to 'analytics' table...\n";
                $table->unsignedInteger('total_pregnant')->default(0)->after('total_adults');
            }
            if (!Schema::hasColumn('analytics', 'total_evacuees')) {
                echo "[+] Adding column 'total_evacuees' to 'analytics' table...\n";
                $table->unsignedInteger('total_evacuees')->default(0)->after('total_pregnant');
            }
            if (!Schema::hasColumn('analytics', 'created_at')) {
                echo "[+] Adding column 'created_at' to 'analytics' table...\n";
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('analytics', 'updated_at')) {
                echo "[+] Adding column 'updated_at' to 'analytics' table...\n";
                $table->timestamp('updated_at')->nullable();
            }
        });

        echo "[~] Backfilling 'analytics' table columns from count columns...\n";
        DB::statement("UPDATE analytics SET total_households = total_household WHERE total_households = 0 AND total_household > 0");
        DB::statement("UPDATE analytics SET total_males = male_count WHERE total_males = 0 AND male_count > 0");
        DB::statement("UPDATE analytics SET total_females = female_count WHERE total_females = 0 AND female_count > 0");
        DB::statement("UPDATE analytics SET total_pwd = pwd_count WHERE total_pwd = 0 AND pwd_count > 0");
        DB::statement("UPDATE analytics SET total_seniors = elderly_count WHERE total_seniors = 0 AND elderly_count > 0");
        DB::statement("UPDATE analytics SET total_children = children_count WHERE total_children = 0 AND children_count > 0");
        DB::statement("UPDATE analytics SET total_adults = adult_count WHERE total_adults = 0 AND adult_count > 0");
        DB::statement("UPDATE analytics SET total_pregnant = pregnant_count WHERE total_pregnant = 0 AND pregnant_count > 0");
        DB::statement("UPDATE analytics SET record_period = CAST(recorded_at AS DATE) WHERE record_period IS NULL AND recorded_at IS NOT NULL");
        DB::statement("UPDATE analytics SET created_at = recorded_at WHERE created_at IS NULL AND recorded_at IS NOT NULL");
    }

    echo "\n========================================================\n";
    echo "SUCCESS: DATABASE PATCH COMPLETED SUCCESSFULLY!\n";
    echo "========================================================\n";

} catch (\Exception $e) {
    echo "\n[!] ERROR OCCURRED: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    exit(1);
}
