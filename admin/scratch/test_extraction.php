<?php
// Set up Laravel bootstrap
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PlandofficeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;

// Enable query log
DB::enableQueryLog();

// Create a mock user that satisfies the has_permission check and implements Authenticatable
$mockUser = new class implements AuthenticatableContract {
    use Authenticatable;
    public $admin = true;
    public function getKey() { return 1; }
    public function hasPermissionUser() { return true; }
    public function hasPermissionParent() { return true; }
};

// Log in the mock user under 'admin' guard
Auth::guard('admin')->setUser($mockUser);

echo "Began verification script..." . PHP_EOL;

// Get baseline count of parcels
$beforeCount = DB::table('tbl_plandoffice_parcels')->count();
echo "Baseline parcel count: " . $beforeCount . PHP_EOL;

// Check existing parcels
$sampleBefore = DB::table('tbl_plandoffice_parcels')
    ->whereNotNull('so_to')
    ->whereNotNull('so_thua')
    ->first();

if ($sampleBefore) {
    echo "Sample parcel before: To " . $sampleBefore->so_to . ", Thua " . $sampleBefore->so_thua . ", Ten Chu: " . ($sampleBefore->ten_chu ?? 'NULL') . PHP_EOL;
    
    // Simulate a manual edit by changing the ten_chu (owner name) to a unique test value
    DB::table('tbl_plandoffice_parcels')
        ->where('id', $sampleBefore->id)
        ->update(['ten_chu' => 'Test Owner Manual Edit']);
    
    echo "Simulated manual edit for parcel ID " . $sampleBefore->id . " by updating ten_chu to 'Test Owner Manual Edit'" . PHP_EOL;
}

// Instantiate the controller and invoke extractParcels
$request = new Request(['id' => 1]);
$controller = new PlandofficeController($request);
$response = $controller->extractParcels();

$responseContent = json_decode($response->getContent(), true);
echo "Controller response: " . json_encode($responseContent, JSON_UNESCAPED_UNICODE) . PHP_EOL;

// Get after count of parcels
$afterCount = DB::table('tbl_plandoffice_parcels')->count();
echo "After extraction parcel count: " . $afterCount . PHP_EOL;

// Verify that the manually edited parcel's custom owner is preserved
if ($sampleBefore) {
    $sampleAfter = DB::table('tbl_plandoffice_parcels')->where('id', $sampleBefore->id)->first();
    echo "Sample parcel after extraction: To " . $sampleAfter->so_to . ", Thua " . $sampleAfter->so_thua . ", Ten Chu: " . ($sampleAfter->ten_chu ?? 'NULL') . PHP_EOL;
    
    if ($sampleAfter->ten_chu === 'Test Owner Manual Edit') {
        echo "SUCCESS: Manual edit was preserved!" . PHP_EOL;
    } else {
        echo "FAILURE: Manual edit was overwritten or deleted!" . PHP_EOL;
    }
    
    // Restore the original owner name to keep database clean
    DB::table('tbl_plandoffice_parcels')
        ->where('id', $sampleBefore->id)
        ->update(['ten_chu' => $sampleBefore->ten_chu]);
    echo "Restored original owner name for parcel ID " . $sampleBefore->id . PHP_EOL;
}

if ($beforeCount === $afterCount && $responseContent['result'] === true) {
    echo "SUCCESS: Extraction ran successfully and did not duplicate existing parcels." . PHP_EOL;
} else {
    echo "INFO: Parcels count changed from " . $beforeCount . " to " . $afterCount . " (new parcels might have been added if KML had new entries)." . PHP_EOL;
}
