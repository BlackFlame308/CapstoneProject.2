<?php
$files = glob(__DIR__ . '/app/Models/*.php');
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'HasUuids') === false) {
        // Handle User Model which extends Authenticatable
        if (strpos($content, 'extends Authenticatable') !== false) {
             $content = str_replace(
                'use Illuminate\Foundation\Auth\User as Authenticatable;', 
                "use Illuminate\Foundation\Auth\User as Authenticatable;\nuse Illuminate\Database\Eloquent\Concerns\HasUuids;", 
                $content
            );
        } else {
            $content = str_replace(
                'use Illuminate\Database\Eloquent\Model;', 
                "use Illuminate\Database\Eloquent\Model;\nuse Illuminate\Database\Eloquent\Concerns\HasUuids;", 
                $content
            );
        }
        
        $content = preg_replace(
            '/class\s+[a-zA-Z0-9_]+\s+extends\s+[a-zA-Z0-9_]+(\s+implements\s+[a-zA-Z0-9_,\s]+)?\s*\{/',
            "$0\n    use HasUuids;\n",
            $content
        );
        file_put_contents($file, $content);
    }
}
echo "Models updated successfully.\n";
