<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class KmlProcessorService
{
    /**
     * Process KML/KMZ file and generate JSON cache.
     *
     * @param string $kmlFilePath Relative path in public disk (e.g. 'plannings/filename.kmz')
     * @return array|null The cached JSON data, or null on failure.
     */
    public static function process(string $kmlFilePath): ?array
    {
        if (empty($kmlFilePath)) {
            return null;
        }

        if (!Storage::disk('public')->exists($kmlFilePath)) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($kmlFilePath);
        $extension = strtolower(pathinfo($kmlFilePath, PATHINFO_EXTENSION));
        $baseName = pathinfo($kmlFilePath, PATHINFO_FILENAME);

        $jsonRelativePath = "kmz-cache/{$baseName}.json";

        // Check if cache already exists
        if (Storage::disk('public')->exists($jsonRelativePath)) {
            $cachedData = json_decode(Storage::disk('public')->get($jsonRelativePath), true);
            if (is_array($cachedData)) {
                return $cachedData;
            }
        }

        // Initialize cache directory
        Storage::disk('public')->makeDirectory("kmz-cache/{$baseName}");

        $overlays = [];
        $type = 'kml';

        if ($extension === 'kmz') {
            $type = 'kmz';
            if (!class_exists('\ZipArchive')) {
                return null;
            }

            $zip = new \ZipArchive();
            if ($zip->open($fullPath) === true) {
                // 1. Find the KML file in the ZIP
                $kmlFileName = null;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'kml') {
                        $kmlFileName = $name;
                        break;
                    }
                }

                if (!$kmlFileName) {
                    $zip->close();
                    return null;
                }

                $kmlContent = $zip->getFromName($kmlFileName);
                $extractedOverlays = self::parseKmlGroundOverlays($kmlContent);

                // 2. Extract images securely
                foreach ($extractedOverlays as $overlay) {
                    $imgHref = $overlay['href'];
                    $zipImgPath = str_replace('\\', '/', $imgHref);

                    // Find corresponding file in ZIP
                    $foundZipName = null;
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $entryName = $zip->getNameIndex($i);
                        $normalizedEntryName = str_replace('\\', '/', $entryName);
                        if (strtolower($normalizedEntryName) === strtolower($zipImgPath) ||
                            strtolower(basename($normalizedEntryName)) === strtolower(basename($zipImgPath))) {
                            $foundZipName = $entryName;
                            break;
                        }
                    }

                    if ($foundZipName !== null) {
                        $imgData = $zip->getFromName($foundZipName);
                        // Prevent Zip Slip/path traversal: replace directory separators with underscores
                        $destFileName = str_replace(['/', '\\'], '_', $foundZipName);
                        $destRelativePath = "kmz-cache/{$baseName}/{$destFileName}";
                        
                        Storage::disk('public')->put($destRelativePath, $imgData);

                        $overlays[] = [
                            'image' => asset('storage/' . $destRelativePath),
                            'bounds' => $overlay['bounds']
                        ];
                    } else {
                        // If it's a URL, keep it
                        if (filter_var($imgHref, FILTER_VALIDATE_URL)) {
                            $overlays[] = [
                                'image' => $imgHref,
                                'bounds' => $overlay['bounds']
                            ];
                        }
                    }
                }
                $zip->close();
            } else {
                return null;
            }
        } elseif ($extension === 'kml') {
            $kmlContent = Storage::disk('public')->get($kmlFilePath);
            $extractedOverlays = self::parseKmlGroundOverlays($kmlContent);

            foreach ($extractedOverlays as $overlay) {
                $overlays[] = [
                    'image' => $overlay['href'],
                    'bounds' => $overlay['bounds']
                ];
            }
        }

        if (!empty($overlays)) {
            $resultData = [
                'type' => $type,
                'overlays' => $overlays
            ];

            Storage::disk('public')->put($jsonRelativePath, json_encode($resultData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return $resultData;
        }

        return null;
    }

    /**
     * Parse KML string for GroundOverlay elements.
     *
     * @param string $kmlContent
     * @return array
     */
    private static function parseKmlGroundOverlays(string $kmlContent): array
    {
        $overlays = [];

        // Parse <GroundOverlay> ... </GroundOverlay> using regex
        preg_match_all('/<GroundOverlay\b[^>]*>(.*?)<\/GroundOverlay>/is', $kmlContent, $matches);

        foreach ($matches[1] as $overlayXml) {
            // Extract <href>
            $href = '';
            if (preg_match('/<href\b[^>]*>(.*?)<\/href>/is', $overlayXml, $hrefMatch)) {
                $href = trim($hrefMatch[1]);
                // Remove CDATA tags if present
                $href = preg_replace('/^<!\[CDATA\[(.*?)\]\]>$/is', '$1', $href);
            }

            // Extract coordinates
            $north = 0.0;
            $south = 0.0;
            $east = 0.0;
            $west = 0.0;

            if (preg_match('/<north\b[^>]*>(.*?)<\/north>/is', $overlayXml, $m)) $north = (double)trim($m[1]);
            if (preg_match('/<south\b[^>]*>(.*?)<\/south>/is', $overlayXml, $m)) $south = (double)trim($m[1]);
            if (preg_match('/<east\b[^>]*>(.*?)<\/east>/is', $overlayXml, $m)) $east = (double)trim($m[1]);
            if (preg_match('/<west\b[^>]*>(.*?)<\/west>/is', $overlayXml, $m)) $west = (double)trim($m[1]);

            if ($href && ($north !== 0.0 || $south !== 0.0 || $east !== 0.0 || $west !== 0.0)) {
                $overlays[] = [
                    'href' => $href,
                    'bounds' => [
                        [$south, $west], // SouthWest corner
                        [$north, $east]  // NorthEast corner
                    ]
                ];
            }
        }

        return $overlays;
    }
}
