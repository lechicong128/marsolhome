<?php
require './vendor/autoload.php';
$app = require './bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kmlPath = storage_path('app/public/plannings/1780623786_6a2229aa1e2a1.kml');
$xml = simplexml_load_file($kmlPath);
$xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

$placemarks = $xml->xpath('//kml:Placemark');

$px = 107.12212484964;
$py = 20.93967172143;

echo "Target Point: $px, $py\n\n";

function distanceToPointMeters($px, $py, $x1, $y1) {
    $latRad = deg2rad($py);
    $kY = 111132.92;
    $kX = 111132.92 * cos($latRad);
    return sqrt((($px - $x1) * $kX)**2 + (($py - $y1) * $kY)**2);
}

// Find points within 15 meters
echo "--- Points within 15m ---\n";
foreach ($placemarks as $pm) {
    if (isset($pm->Point)) {
        $coords = explode(',', trim((string)$pm->Point->coordinates));
        if (count($coords) >= 2) {
            $x = (double)$coords[0];
            $y = (double)$coords[1];
            $dist = distanceToPointMeters($px, $py, $x, $y);
            if ($dist <= 15) {
                echo "Point: Name: {$pm->name}, Desc: {$pm->description}, coords: $x, $y, dist: {$dist}m\n";
            }
        }
    }
}

// Find lines within 15 meters
echo "\n--- LineStrings within 15m ---\n";
foreach ($placemarks as $pm) {
    if (isset($pm->LineString)) {
        $coordsStr = trim((string)$pm->LineString->coordinates);
        $coordsParts = preg_split('/\s+/', $coordsStr);
        $coords = [];
        foreach ($coordsParts as $part) {
            $pt = explode(',', $part);
            if (count($pt) >= 2) {
                $coords[] = [(double)$pt[0], (double)$pt[1]];
            }
        }
        
        // Check minimum distance to line
        $minDist = INF;
        foreach ($coords as $pt) {
            $d = distanceToPointMeters($px, $py, $pt[0], $pt[1]);
            if ($d < $minDist) $minDist = $d;
        }
        
        if ($minDist <= 15) {
            echo "Line: Name: {$pm->name}, Desc: {$pm->description}, coords: " . trim((string)$pm->LineString->coordinates) . " (min dist to vertex: {$minDist}m)\n";
        }
    }
}
