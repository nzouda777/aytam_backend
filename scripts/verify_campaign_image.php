<?php
require __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Campaign;

$campaign = new Campaign();
$campaign->image = 'campaigns/test.jpg';

echo "Image Path: " . $campaign->image . "\n";
echo "Image URL: " . $campaign->image_url . "\n";

$json = json_encode($campaign);
echo "JSON: " . $json . "\n";

if (strpos($json, 'image_url') !== false) {
    echo "SUCCESS: image_url is present in JSON.\n";
} else {
    echo "FAILURE: image_url is missing from JSON.\n";
}
