<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$b = App\Models\Barangay::where('name', 'like', '%Mambaling%')->first();
echo "Barangay Mambaling: " . json_encode($b) . "\n";
