<?php
require './vendor/autoload.php';
$app = require './bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kmlPath = storage_path('app/public/plannings/1780623786_6a2229aa1e2a1.kml');
$xml = simplexml_load_file($kmlPath);
$xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

$placemarks = $xml->xpath('//kml:Placemark');

// Find a point
$testPoint = null;
foreach ($placemarks as $pm) {
    if (isset($pm->Point) && (string)$pm->name === 'g') {
        $coords = explode(',', trim((string)$pm->Point->coordinates));
        if (count($coords) >= 2) {
            $testPoint = [
                'lng' => (double)$coords[0],
                'lat' => (double)$coords[1]
            ];
            break;
        }
    }
}

if (!$testPoint) {
    die("Test point not found\n");
}

echo "Test Point: Lat: {$testPoint['lat']}, Lng: {$testPoint['lng']}\n";

// Function to compute distance from point to segment in meters
function distanceToSegmentMeters($px, $py, $x1, $y1, $x2, $y2) {
    // Project to meters around test point
    $latRad = deg2rad($py);
    $kY = 111132.92;
    $kX = 111132.92 * cos($latRad);
    
    $ptX = $px * $kX;
    $ptY = $py * $kY;
    
    $p1X = $x1 * $kX;
    $p1Y = $y1 * $kY;
    
    $p2X = $x2 * $kX;
    $p2Y = $y2 * $kY;
    
    // Vector arithmetic to find closest point on segment
    $dx = $p2X - $p1X;
    $dy = $p2Y - $p1Y;
    
    if ($dx == 0 && $dy == 0) {
        return sqrt(($ptX - $p1X)**2 + ($ptY - $p1Y)**2);
    }
    
    $t = (($ptX - $p1X) * $dx + ($ptY - $p1Y) * $dy) / ($dx * $dx + $dy * $dy);
    $t = max(0, min(1, $t));
    
    $closestX = $p1X + $t * $dx;
    $closestY = $p1Y + $t * $dy;
    
    return sqrt(($ptX - $closestX)**2 + ($ptY - $closestY)**2);
}

function getCoordinatesAtY($x1, $y1, $x2, $y2, $y) {
    if (abs($y1 - $y2) < 1e-9) return ($x1 + $x2) / 2;
    return $x1 + ($y - $y1) * ($x2 - $x1) / ($y2 - $y1);
}

function getCoordinatesAtX($x1, $y1, $x2, $y2, $x) {
    if (abs($x1 - $x2) < 1e-9) return ($y1 + $y2) / 2;
    return $y1 + ($x - $x1) * ($y2 - $y1) / ($x2 - $x1);
}

$px = $testPoint['lng'];
$py = $testPoint['lat'];

$leftX = -INF;
$rightX = INF;
$bottomY = -INF;
$topY = INF;

$tolerance = 0.000002; // ~0.2 meters tolerance

$matchedLines = [];

foreach ($placemarks as $pm) {
    if (!isset($pm->LineString)) continue;
    
    $coordsStr = trim((string)$pm->LineString->coordinates);
    $coordsParts = preg_split('/\s+/', $coordsStr);
    
    $coords = [];
    foreach ($coordsParts as $part) {
        $pt = explode(',', $part);
        if (count($pt) >= 2) {
            $coords[] = [(double)$pt[0], (double)$pt[1]];
        }
    }
    
    // Process each segment
    for ($i = 0; $i < count($coords) - 1; $i++) {
        $p1 = $coords[$i];
        $p2 = $coords[$i+1];
        
        $dist = distanceToSegmentMeters($px, $py, $p1[0], $p1[1], $p2[0], $p2[1]);
        if ($dist > 25) continue; // within 25 meters
        
        $matchedLines[] = [
            'name' => (string)$pm->name,
            'p1' => $p1,
            'p2' => $p2,
            'dist' => $dist
        ];
        
        $lat1 = $p1[1]; $lng1 = $p1[0];
        $lat2 = $p2[1]; $lng2 = $p2[0];
        
        $dLat = abs($lat1 - $lat2);
        $dLng = abs($lng1 - $lng2);
        
        if ($dLat > $dLng) {
            // Vertical-ish line
            // Check if it spans the latitude py (with tolerance)
            if (min($lat1, $lat2) - $tolerance <= $py && max($lat1, $lat2) + $tolerance >= $py) {
                $xAtY = getCoordinatesAtY($lng1, $lat1, $lng2, $lat2, $py);
                if ($xAtY < $px) {
                    if ($xAtY > $leftX) {
                        $leftX = $xAtY;
                    }
                } else {
                    if ($xAtY < $rightX) {
                        $rightX = $xAtY;
                    }
                }
            }
        } else {
            // Horizontal-ish line
            // Check if it spans the longitude px (with tolerance)
            if (min($lng1, $lng2) - $tolerance <= $px && max($lng1, $lng2) + $tolerance >= $px) {
                $yAtX = getCoordinatesAtX($lng1, $lat1, $lng2, $lat2, $px);
                if ($yAtX < $py) {
                    if ($yAtX > $bottomY) {
                        $bottomY = $yAtX;
                    }
                } else {
                    if ($yAtX < $topY) {
                        $topY = $yAtX;
                    }
                }
            }
        }
    }
}

echo "Matched line segments count: " . count($matchedLines) . "\n";
foreach ($matchedLines as $ml) {
    echo "Line: {$ml['name']}, dist: {$ml['dist']}m, P1: [{$ml['p1'][0]}, {$ml['p1'][1]}], P2: [{$ml['p2'][0]}, {$ml['p2'][1]}]\n";
}

echo "\nResulting Box:\n";
echo "Left Longitude: " . ($leftX == -INF ? "Not Found" : $leftX) . "\n";
echo "Right Longitude: " . ($rightX == INF ? "Not Found" : $rightX) . "\n";
echo "Bottom Latitude: " . ($bottomY == -INF ? "Not Found" : $bottomY) . "\n";
echo "Top Latitude: " . ($topY == INF ? "Not Found" : $topY) . "\n";

if ($leftX != -INF && $rightX != INF && $bottomY != -INF && $topY != INF) {
    // calculate width & height in meters
    // approximate distance
    $latRad = deg2rad($py);
    $kY = 111132.92;
    $kX = 111132.92 * cos($latRad);
    
    $width = abs($rightX - $leftX) * $kX;
    $height = abs($topY - $bottomY) * $kY;
    
    echo "Width: {$width}m, Height: {$height}m\n";
}
