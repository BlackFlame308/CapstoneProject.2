<?php
require __DIR__ . '/vendor/autoload.php';

$r = new ReflectionMethod('Illuminate\Database\Grammar', 'wrapTable');
echo "Parameters count: " . $r->getNumberOfParameters() . PHP_EOL;
foreach ($r->getParameters() as $p) {
    echo "  Parameter: " . $p->getName() . PHP_EOL;
    if ($p->hasType()) {
        echo "    Type: " . $p->getType()->getName() . PHP_EOL;
    }
}
