<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

foreach (App\Models\User::all() as $u) {
    echo $u->email . ' -> ' . ($u->role?->name ?? 'NULL') . "\n";
}
