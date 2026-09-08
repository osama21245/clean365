<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$zone = \Modules\ZoneManagement\Entities\Zone::first();
var_dump(get_class($zone->coordinates));
echo "\n";
print_r(json_decode($zone->coordinates->toJson()));
echo "\n";
