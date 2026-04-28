<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Permit;
use App\Models\PermitHolder;
use App\Models\Vehicle;
use Illuminate\Support\Str;
use Carbon\Carbon;

$h = PermitHolder::first();
$v = Vehicle::first();
if (!$h || !$v) {
    echo "Missing test data
";
    exit(1);
}

echo "holder={$h->id} vehicle={$v->id}
";
$p = Permit::create([
    'permit_holder_id' => $h->id,
    'vehicle_id' => $v->id,
    'type' => 'Navetta',
    'valid_from' => Carbon::now()->subDays(1),
    'valid_to' => Carbon::now()->addDays(1),
    'status' => 'active',
    'qr_token' => (string) Str::uuid(),
]);
echo json_encode($p->toArray()) . "
";
