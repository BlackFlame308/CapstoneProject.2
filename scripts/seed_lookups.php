<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

class SeedLookups {
    public function run() {
        echo "Seeding lookup tables...\n";

        // 1. Genders
        $genders = [
            ['gender_id' => 1, 'gender_key' => 'male', 'gender_label' => 'Male'],
            ['gender_id' => 2, 'gender_key' => 'female', 'gender_label' => 'Female']
        ];
        foreach ($genders as $g) {
            DB::table('genders')->insertOrIgnore($g);
        }
        echo "Genders table verified/seeded.\n";

        // 2. Civil Statuses
        $statuses = [
            ['status_id' => 1, 'status_key' => 'single', 'status_label' => 'Single'],
            ['status_id' => 2, 'status_key' => 'married', 'status_label' => 'Married'],
            ['status_id' => 3, 'status_key' => 'widowed', 'status_label' => 'Widowed'],
            ['status_id' => 4, 'status_key' => 'separated', 'status_label' => 'Separated'],
            ['status_id' => 5, 'status_key' => 'divorced', 'status_label' => 'Divorced'],
            ['status_id' => 6, 'status_key' => 'annulled', 'status_label' => 'Annulled']
        ];
        foreach ($statuses as $s) {
            DB::table('civil_statuses')->insertOrIgnore($s);
        }
        echo "Civil Statuses table verified/seeded.\n";

        // 3. Relationships
        $relationships = [
            ['relationship_id' => 1, 'relationship_key' => 'head_of_household', 'relationship_label' => 'Head of Household'],
            ['relationship_id' => 2, 'relationship_key' => 'spouse', 'relationship_label' => 'Spouse'],
            ['relationship_id' => 3, 'relationship_key' => 'child', 'relationship_label' => 'Child'],
            ['relationship_id' => 4, 'relationship_key' => 'parent', 'relationship_label' => 'Parent'],
            ['relationship_id' => 5, 'relationship_key' => 'sibling', 'relationship_label' => 'Sibling'],
            ['relationship_id' => 6, 'relationship_key' => 'other_relative', 'relationship_label' => 'Other Relative']
        ];
        foreach ($relationships as $r) {
            DB::table('relationships')->insertOrIgnore($r);
        }
        echo "Relationships table verified/seeded.\n";

        // 4. Education Levels
        $educationLevels = [
            ['education_level_id' => 1, 'education_level_key' => 'elementary', 'education_level_label' => 'Elementary'],
            ['education_level_id' => 2, 'education_level_key' => 'high_school', 'education_level_label' => 'High School'],
            ['education_level_id' => 3, 'education_level_key' => 'college', 'education_level_label' => 'College'],
            ['education_level_id' => 4, 'education_level_key' => 'post_graduate', 'education_level_label' => 'Post Graduate']
        ];
        foreach ($educationLevels as $el) {
            DB::table('education_levels')->insertOrIgnore($el);
        }
        echo "Education Levels table verified/seeded.\n";

        echo "All lookup seeding completed successfully!\n";
    }
}

(new SeedLookups())->run();
