<?php
require './vendor/autoload.php';
$app = require './bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kmlPath = storage_path('app/public/plannings/1780623786_6a2229aa1e2a1.kml');
$xml = simplexml_load_file($kmlPath);
$xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

$placemarks = $xml->xpath('//kml:Placemark');

$points = [];
$lines = [];
foreach ($placemarks as $pm) {
    if (isset($pm->Point)) {
        $points[] = $pm;
    } elseif (isset($pm->LineString)) {
        $lines[] = $pm;
    }
}

echo "=== POINTS (First 10) ===\n";
for ($i = 0; $i < min(10, count($points)); $i++) {
    $pm = $points[$i];
    echo "PM Name: " . $pm->name . "\n";
    echo "JSON: " . json_encode($pm) . "\n";
    // Check elements
    foreach ($pm->children() as $key => $val) {
        if ($key !== 'Point') {
            echo "  $key: " . substr(trim((string)$val), 0, 100) . "\n";
        }
    }
    if (isset($pm->ExtendedData)) {
        foreach ($pm->ExtendedData->Data as $data) {
            echo "    Data Name=" . $data['name'] . " Value=" . $data->value . "\n";
        }
        foreach ($pm->ExtendedData->SchemaData->SimpleData as $sdata) {
            echo "    SchemaData Name=" . $sdata['name'] . " Value=" . $sdata . "\n";
        }
    }
    echo "\n";
}

echo "=== LINES (First 10) ===\n";
for ($i = 0; $i < min(10, count($lines)); $i++) {
    $pm = $lines[$i];
    echo "PM Name: " . $pm->name . "\n";
    echo "JSON: " . json_encode($pm) . "\n";
    foreach ($pm->children() as $key => $val) {
        if ($key !== 'LineString') {
            echo "  $key: " . substr(trim((string)$val), 0, 100) . "\n";
        }
    }
    if (isset($pm->ExtendedData)) {
        foreach ($pm->ExtendedData->Data as $data) {
            echo "    Data Name=" . $data['name'] . " Value=" . $data->value . "\n";
        }
        foreach ($pm->ExtendedData->SchemaData->SimpleData as $sdata) {
            echo "    SchemaData Name=" . $sdata['name'] . " Value=" . $sdata . "\n";
        }
    }
    echo "\n";
}
