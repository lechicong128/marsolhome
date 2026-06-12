<style>
    .modal .modal-dialog .modal-content {
        padding: unset !important; 
    }
    
    .modal-overlay {
        top: 0 !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
    
    /* Buttons systems */
    .mod-btn {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
        height: 42px !important;
        padding: 0 18px !important;
        border-radius: 12px !important;
        font-size: 13px !important;
        font-weight: 700 !important;
        text-decoration: none !important;
        transition: all .18s ease;
        cursor: pointer;
    }
    
    .mod-btn-secondary {
        border: 1px solid #e2e8f0 !important;
        color: #475569 !important;
        background: #ffffff !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
    }
    
    .mod-btn-secondary:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }
</style>

<div class="modal-overlay">
    <div class="modal-content" style="border-radius: 16px; border: none; overflow: hidden; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); max-width: 95% !important; width: 95% !important; margin: 20px auto !important;">
        <!-- Modal Header -->
        <div class="modal-header" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #ffffff; border-bottom: none; padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; position: relative;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h2 class="modal-title" style="font-weight: 700; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 10px; margin: 0; font-size: 16px; color: #ffffff;">
                    <i class="fa fa-map-marker text-success" style="font-size: 20px;"></i>
                    <span>{{ $planning->name }}</span>
                </h2>
            </div>
            
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-right: 30px;">
                <span style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; padding: 4px 10px; font-size: 12px; font-family: 'Inter', sans-serif; font-weight: 500; color: #cbd5e1; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa fa-map-o" style="color: #94a3b8;"></i> {{ $planning->province ? $planning->province->name : 'Chưa xác định' }}
                </span>
                <span style="background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); border-radius: 6px; padding: 4px 10px; font-size: 12px; font-family: 'Inter', sans-serif; font-weight: 700; color: #34d399; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="fa fa-arrows-alt" style="font-size: 11px;"></i> {{ number_format($planning->area, 2, ',', '.') }} m²
                </span>
                <a href="{{ asset('storage/' . $planning->kml_file) }}" target="_blank" style="background: #22c55e; border: none; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-family: 'Inter', sans-serif; font-weight: 600; color: #ffffff; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; transition: all 0.2s;">
                    <i class="fa fa-download"></i> KML
                </a>
            </div>

            <button type="button" class="close" data-dismiss="modal" title="Đóng" style="color: #ffffff; opacity: 0.8; transition: all 0.2s; background: none; border: none; margin: 0; padding: 0; line-height: 1; position: absolute; right: 24px; top: 50%; transform: translateY(-50%); cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%;">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="modal-body" style="padding: 0; position: relative; background-color: #f1f5f9;">
            <!-- Map Container -->
            <div id="planning-map-canvas" style="width: 100%; height: calc(90vh - 130px); min-height: 500px; z-index: 1;"></div>
            
            <!-- Loading Indicator -->
            <div id="map-loader" style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(4px); z-index: 9999; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #ffffff; transition: opacity 0.3s ease;">
                <i class="fa fa-spinner fa-spin" style="font-size: 40px; color: #34d399; margin-bottom: 12px;"></i>
                <span style="font-family: 'Inter', sans-serif; font-weight: 500; font-size: 14px; letter-spacing: 0.5px;">Đang tải và xử lý bản đồ quy hoạch...</span>
            </div>
            

        </div>
        
        <!-- Modal Footer -->
        <div class="modal-footer" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 12px 24px; margin: 0; display: flex; justify-content: flex-end;">
            <button type="button" class="mod-btn mod-btn-secondary" data-dismiss="modal" style="height: 38px !important; border-radius: 8px !important; padding: 0 16px !important;">Hủy bỏ</button>
        </div>
    </div>
</div>

<script>
    (function() {
        // Enforce Inter font link
        if (!$('link[href*="Inter"]').length) {
            $('<link>', {
                rel: 'stylesheet',
                href: 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap'
            }).appendTo('head');
        }

        // Dynamically load Leaflet CSS if not already on page
        if (!$('link[href*="leaflet.css"]').length) {
            $('<link>', {
                rel: 'stylesheet',
                type: 'text/css',
                href: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                integrity: 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=',
                crossorigin: ''
            }).appendTo('head');
        }

        // Helper to load JS files as Promises
        function loadScript(url) {
            return new Promise(function(resolve, reject) {
                var script = document.createElement('script');
                script.type = 'text/javascript';
                script.src = url;
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }

        // Load Leaflet and Leaflet-Omnivore sequentially
        var leafletPromise = (typeof L !== 'undefined') ? Promise.resolve() : loadScript('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');

        leafletPromise.then(function() {
            var omnivorePromise = (typeof omnivore !== 'undefined') ? Promise.resolve() : loadScript('https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.3.1/leaflet-omnivore.min.js');
            return omnivorePromise;
        }).then(function() {
            initPlanningMap();
        }).catch(function(err) {
            console.error('Lỗi khi tải thư viện bản đồ:', err);
            $('#map-loader i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-triangle').css('color', '#ef4444');
            $('#map-loader span').text('Không thể tải thư viện bản đồ! Vui lòng tải lại trang.').css('color', '#ef4444');
        });

        function initPlanningMap() {
            try {
                // Initialize map with Google Satellite Hybrid by default
                var satelliteLayer = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                    maxZoom: 22,
                    attribution: '&copy; Google Maps'
                });
                
                var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                });

                var map = L.map('planning-map-canvas', {
                    zoomControl: true,
                    layers: [satelliteLayer] // Vệ tinh làm mặc định
                }).setView([10.762622, 106.660172], 12);
                
                // Cho phép chuyển đổi bản đồ nền ở góc phải
                var baseMaps = {
                    "Bản đồ Vệ tinh": satelliteLayer,
                    "Bản đồ Đường bộ": osmLayer
                };
                L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);
                                var activeHighlightLayer = null;
                var activeHighlightMarker = null;
                var activeLabels = [];
                var allPointFeatures = [];
                var cacheJsonUrl = {!! json_encode($cacheJsonUrl) !!};
                var kmlUrl = "{{ asset('storage/' . $planning->kml_file) }}";

                function getOuterRing(layer) {
                    var latlngs = layer.getLatLngs();
                    while (Array.isArray(latlngs) && latlngs.length > 0 && Array.isArray(latlngs[0])) {
                        latlngs = latlngs[0];
                    }
                    return latlngs;
                }

                function getGeodesicArea(latLngs) {
                    var area = 0;
                    var R = 6378137; // Bán kính Trái Đất (mét)
                    var n = latLngs.length;
                    if (n < 3) return 0;
                    
                    for (var i = 0; i < n; i++) {
                        var p1 = latLngs[i];
                        var p2 = latLngs[(i + 1) % n];
                        var lambda1 = p1.lng * Math.PI / 180;
                        var lambda2 = p2.lng * Math.PI / 180;
                        var phi1 = p1.lat * Math.PI / 180;
                        var phi2 = p2.lat * Math.PI / 180;
                        area += (lambda2 - lambda1) * (2 + Math.sin(phi1) + Math.sin(phi2));
                    }
                    area = Math.abs(area * R * R / 2);
                    return area;
                }
                
                function getCoordinatesAtY(coords, y) {
                    var p1 = coords[0], p2 = coords[1];
                    if (Math.abs(p1[1] - p2[1]) < 1e-7) return (p1[0] + p2[0]) / 2;
                    return p1[0] + (y - p1[1]) * (p2[0] - p1[0]) / (p2[1] - p1[1]);
                }

                function getCoordinatesAtX(coords, x) {
                    var p1 = coords[0], p2 = coords[1];
                    if (Math.abs(p1[0] - p2[0]) < 1e-7) return (p1[1] + p2[1]) / 2;
                    return p1[1] + (x - p1[0]) * (p2[1] - p1[1]) / (p2[0] - p1[0]);
                }

                function distanceToSegmentMeters(latlng, p1, p2) {
                    var latRad = latlng.lat * Math.PI / 180;
                    var kY = 111132.92;
                    var kX = 111132.92 * Math.cos(latRad);
                    
                    var ptX = latlng.lng * kX;
                    var ptY = latlng.lat * kY;
                    
                    var p1X = p1.lng * kX;
                    var p1Y = p1.lat * kY;
                    
                    var p2X = p2.lng * kX;
                    var p2Y = p2.lat * kY;
                    
                    var dx = p2X - p1X;
                    var dy = p2Y - p1Y;
                    
                    if (dx === 0 && dy === 0) {
                        return Math.sqrt((ptX - p1X) * (ptX - p1X) + (ptY - p1Y) * (ptY - p1Y));
                    }
                    
                    var t = ((ptX - p1X) * dx + (ptY - p1Y) * dy) / (dx * dx + dy * dy);
                    t = Math.max(0, Math.min(1, t));
                    
                    var closestX = p1X + t * dx;
                    var closestY = p1Y + t * dy;
                    
                    return Math.sqrt((ptX - closestX) * (ptX - closestX) + (ptY - closestY) * (ptY - closestY));
                }

                function extractSegments(geometry) {
                    var segments = [];
                    if (!geometry) return segments;
                    var type = geometry.type;
                    var coords = geometry.coordinates;
                    
                    if (type === 'LineString') {
                        for (var i = 0; i < coords.length - 1; i++) {
                            segments.push({
                                p1: L.latLng(coords[i][1], coords[i][0]),
                                p2: L.latLng(coords[i+1][1], coords[i+1][0])
                            });
                        }
                    } else if (type === 'MultiLineString') {
                        coords.forEach(function(line) {
                            for (var i = 0; i < line.length - 1; i++) {
                                segments.push({
                                    p1: L.latLng(line[i][1], line[i][0]),
                                    p2: L.latLng(line[i+1][1], line[i+1][0])
                                });
                            }
                        });
                    } else if (type === 'Polygon') {
                        coords.forEach(function(ring) {
                            for (var i = 0; i < ring.length - 1; i++) {
                                segments.push({
                                    p1: L.latLng(ring[i][1], ring[i][0]),
                                    p2: L.latLng(ring[i+1][1], ring[i+1][0])
                                });
                            }
                            if (ring.length > 0) {
                                segments.push({
                                    p1: L.latLng(ring[ring.length - 1][1], ring[ring.length - 1][0]),
                                    p2: L.latLng(ring[0][1], ring[0][0])
                                });
                            }
                        });
                    } else if (type === 'MultiPolygon') {
                        coords.forEach(function(poly) {
                            poly.forEach(function(ring) {
                                for (var i = 0; i < ring.length - 1; i++) {
                                    segments.push({
                                        p1: L.latLng(ring[i][1], ring[i][0]),
                                        p2: L.latLng(ring[i+1][1], ring[i+1][0])
                                      });
                                }
                                if (ring.length > 0) {
                                    segments.push({
                                        p1: L.latLng(ring[ring.length - 1][1], ring[ring.length - 1][0]),
                                        p2: L.latLng(ring[0][1], ring[0][0])
                                    });
                                }
                            });
                        });
                    }
                    return segments;
                }

                // Dựng đa giác (Polygon) giả lập cho ô đất từ các đường bao (LineString) xung quanh điểm click
                function constructCellPolygon(clickLatLng, pointLatLng) {
                    var px = pointLatLng.lng;
                    var py = pointLatLng.lat;
                    
                    var leftX = -Infinity, rightX = Infinity;
                    var bottomY = -Infinity, topY = Infinity;
                    
                    var tolerance = 0.000002; // Dung sai ~0.2m
                    var searchDistanceMeters = 30; // Khoảng cách tối đa từ tâm lô đất đến đường ranh giới
                    
                    // Quét toàn bộ layer đường vẽ để tìm 4 cạnh bao quanh điểm tâm
                    customLayer.eachLayer(function(layer) {
                        var f = layer.feature;
                        if (!f || !f.geometry) return;
                        if (f.isBoundary) return; // Bỏ qua đường ranh giới dự án lớn
                        
                        var segments = extractSegments(f.geometry);
                        segments.forEach(function(seg) {
                            var p1 = seg.p1;
                            var p2 = seg.p2;
                            
                            // Tính khoảng cách trắc địa từ tâm ô đất đến đoạn thẳng
                            var dist = distanceToSegmentMeters(pointLatLng, p1, p2);
                            if (dist > searchDistanceMeters) return;
                            
                            var lat1 = p1.lat, lng1 = p1.lng;
                            var lat2 = p2.lat, lng2 = p2.lng;
                            
                            var dLat = Math.abs(lat1 - lat2);
                            var dLng = Math.abs(lng1 - lng2);
                            
                            if (dLat > dLng) {
                                // Đường dọc (đứng) - Tìm biên trái/phải
                                if (Math.min(lat1, lat2) - tolerance <= py && Math.max(lat1, lat2) + tolerance >= py) {
                                    var xAtY = getCoordinatesAtY([[lng1, lat1], [lng2, lat2]], py);
                                    if (xAtY < px) {
                                        if (xAtY > leftX) leftX = xAtY;
                                    } else {
                                        if (xAtY < rightX) rightX = xAtY;
                                    }
                                }
                            } else {
                                // Đường ngang (nằm) - Tìm biên trên/dưới
                                if (Math.min(lng1, lng2) - tolerance <= px && Math.max(lng1, lng2) + tolerance >= px) {
                                    var yAtX = getCoordinatesAtX([[lng1, lat1], [lng2, lat2]], px);
                                    if (yAtX < py) {
                                        if (yAtX > bottomY) bottomY = yAtX;
                                    } else {
                                        if (yAtX < topY) topY = yAtX;
                                    }
                                }
                            }
                        });
                    });
                    
                    // Nếu tìm thấy đủ 4 phía bao quanh hợp lý, dựng đa giác
                    if (leftX !== -Infinity && rightX !== Infinity && bottomY !== -Infinity && topY !== Infinity) {
                        var width = L.latLng(py, leftX).distanceTo(L.latLng(py, rightX));
                        var height = L.latLng(bottomY, px).distanceTo(L.latLng(topY, px));
                        
                        // Ngưỡng kích thước của một lô đất thông thường (chiều rộng < 60m, chiều sâu < 120m)
                        if (width < 60 && height < 120) {
                            return L.polygon([
                                [bottomY, leftX],
                                [bottomY, rightX],
                                [topY, rightX],
                                [topY, leftX]
                            ], {
                                color: '#ef4444',       // Viền đỏ rực rỡ giống hình mẫu
                                weight: 2.5,
                                fillColor: '#ef4444',
                                fillOpacity: 0.25,      // Đổ nền đỏ nhẹ
                                interactive: false      // Không nhận click nữa để tránh nhiễu
                            });
                        }
                    }
                    return null;
                }

                // Tìm điểm Point đại diện lô đất gần tọa độ click nhất
                function findNearestPoint(latlng) {
                    var nearest = null;
                    var minDist = Infinity;
                    
                    allPointFeatures.forEach(function(f) {
                        if (f.geometry && f.geometry.type === 'Point') {
                            var coords = f.geometry.coordinates;
                            var ptLatLng = L.latLng(coords[1], coords[0]);
                            var dist = latlng.distanceTo(ptLatLng);
                            if (dist < minDist) {
                                minDist = dist;
                                nearest = {
                                    feature: f,
                                    latlng: ptLatLng,
                                    distance: dist
                                };
                            }
                        }
                    });
                    return nearest;
                }

                function handleMapClick(e) {
                    var clickedLatLng = e.latlng;
                    var nearest = findNearestPoint(clickedLatLng);
                    
                    // Nếu click gần điểm tâm lô đất (trong vòng 35m)
                    if (nearest && nearest.distance < 35) {
                        // 1. Reset các highlight và nhãn kích thước cũ
                        if (activeHighlightMarker) {
                            map.removeLayer(activeHighlightMarker);
                        }
                        if (activeHighlightLayer) {
                            customLayer.resetStyle(activeHighlightLayer);
                            activeHighlightLayer = null;
                        }
                        activeLabels.forEach(function(marker) {
                            map.removeLayer(marker);
                        });
                        activeLabels = [];
                        
                        // 2. Tạo Gold Marker tại tâm lô đất được chọn
                        var centerIcon = L.divIcon({
                            className: 'center-gold-marker',
                            html: '<div style="display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; background: #eab308; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 3px 6px rgba(0,0,0,0.4); color: #ffffff; font-size: 14px;"><i class="fa fa-home"></i></div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        });
                        
                        activeHighlightMarker = L.marker(nearest.latlng, { icon: centerIcon }).addTo(map);
                        
                        // 3. Tạo và vẽ vùng đa giác ô đất từ các đường thẳng bao quanh
                        var cellPoly = constructCellPolygon(clickedLatLng, nearest.latlng);
                        if (cellPoly) {
                            cellPoly.addTo(map);
                            activeLabels.push(cellPoly);
                            
                            // Tự động đo đạc và hiển thị chiều dài 4 cạnh của ô đất nhỏ vừa dựng lên
                            var outerRing = getOuterRing(cellPoly);
                            var n = outerRing.length;
                            if (n >= 3) {
                                for (var i = 0; i < n; i++) {
                                    var p1 = outerRing[i];
                                    var p2 = outerRing[(i + 1) % n];
                                    var dist = p1.distanceTo(p2);
                                    if (dist < 0.5) continue;
                                    var distText = dist.toFixed(1) + ' m';
                                    var midLat = (p1.lat + p2.lat) / 2;
                                    var midLng = (p1.lng + p2.lng) / 2;
                                    var labelIcon = L.divIcon({
                                        className: 'edge-length-label',
                                        html: '<div style="color: #ffffff; text-shadow: 0 0 2px #000000, 0 0 4px #000000; font-size: 10px; font-weight: 700; white-space: nowrap; text-align: center; background: rgba(15, 23, 42, 0.75); padding: 1px 4px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.25);">' + distText + '</div>',
                                        iconSize: [60, 20],
                                        iconAnchor: [30, 10]
                                    });
                                    var labelMarker = L.marker([midLat, midLng], { icon: labelIcon, interactive: false }).addTo(map);
                                    activeLabels.push(labelMarker);
                                }
                            }
                        }
                        
                        // 4. Hiển thị popup thông tin chi tiết lô đất
                        var props = nearest.feature.properties || {};
                        var title = props.name || props.Name || 'Lô đất';
                        var desc = props.description || props.Description || '';
                        
                        var popupHtml = '<div style="font-family: \'Inter\', sans-serif; min-width: 200px; max-width: 320px;">';
                        popupHtml += '<div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 2px solid #eab308;">' + title + '</div>';
                        if (desc) {
                            popupHtml += '<div style="font-size: 12px; color: #475569; margin-bottom: 8px; max-height: 150px; overflow-y: auto;">' + desc + '</div>';
                        }
                        
                        var skipKeys = ['name', 'Name', 'description', 'Description', 'styleUrl', 'styleHash'];
                        var extraProps = [];
                        for (var key in props) {
                            if (props.hasOwnProperty(key) && skipKeys.indexOf(key) === -1 && props[key]) {
                                extraProps.push('<div style="display: flex; justify-content: space-between; gap: 8px; padding: 2px 0;"><span style="color: #94a3b8; font-size: 11px;">' + key + ':</span><span style="color: #1e293b; font-weight: 500; font-size: 11px; text-align: right;">' + props[key] + '</span></div>');
                            }
                        }
                        if (extraProps.length > 0) {
                            popupHtml += '<div style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 4px;">' + extraProps.join('') + '</div>';
                        }
                        popupHtml += '</div>';
                        
                        activeHighlightMarker.bindPopup(popupHtml, { maxWidth: 350 }).openPopup();
                        
                        // 4. Nếu lô đất này có đường vẽ hoặc polygon tương ứng, tìm và highlight nó lên
                        // (Ở đây ta highlight tất cả các cạnh bao quanh nếu có polygon bao chứa điểm này)
                    } else {
                        // Click vào khoảng trống -> Hủy chọn
                        if (activeHighlightMarker) {
                            map.removeLayer(activeHighlightMarker);
                            activeHighlightMarker = null;
                        }
                        if (activeHighlightLayer) {
                            customLayer.resetStyle(activeHighlightLayer);
                            activeHighlightLayer = null;
                        }
                        activeLabels.forEach(function(marker) {
                            map.removeLayer(marker);
                        });
                        activeLabels = [];
                    }
                }

                function selectPlot(layer, feature) {
                    // Reset style trước đó
                    if (activeHighlightLayer && activeHighlightLayer !== layer) {
                        customLayer.resetStyle(activeHighlightLayer);
                    }
                    if (activeHighlightMarker) {
                        map.removeLayer(activeHighlightMarker);
                        activeHighlightMarker = null;
                    }
                    activeLabels.forEach(function(marker) {
                        map.removeLayer(marker);
                    });
                    activeLabels = [];

                    activeHighlightLayer = layer;
                    layer.setStyle({
                        color: '#ef4444',
                        weight: 3,
                        fillColor: '#ef4444',
                        fillOpacity: 0.15
                    });
                    
                    if (layer.bringToFront) {
                        layer.bringToFront();
                    }

                    var outerRing = getOuterRing(layer);
                    var n = outerRing.length;
                    if (n >= 3) {
                        for (var i = 0; i < n; i++) {
                            var p1 = outerRing[i];
                            var p2 = outerRing[(i + 1) % n];
                            
                            var dist = p1.distanceTo(p2);
                            if (dist < 0.5) continue;
                            
                            var distText = dist.toFixed(1) + ' m';
                            var midLat = (p1.lat + p2.lat) / 2;
                            var midLng = (p1.lng + p2.lng) / 2;
                            
                            var labelIcon = L.divIcon({
                                className: 'edge-length-label',
                                html: '<div style="color: #ffffff; text-shadow: 0 0 2px #000000, 0 0 4px #000000; font-size: 10px; font-weight: 700; white-space: nowrap; text-align: center; background: rgba(15, 23, 42, 0.75); padding: 1px 4px; border-radius: 3px; border: 1px solid rgba(255,255,255,0.25);">' + distText + '</div>',
                                iconSize: [60, 20],
                                iconAnchor: [30, 10]
                            });
                            
                            var labelMarker = L.marker([midLat, midLng], { icon: labelIcon, interactive: false }).addTo(map);
                            activeLabels.push(labelMarker);
                        }
                        
                        var bounds = layer.getBounds();
                        var center = bounds.getCenter();
                        
                        var centerIcon = L.divIcon({
                            className: 'center-gold-marker',
                            html: '<div style="display: flex; align-items: center; justify-content: center; width: 30px; height: 30px; background: #eab308; border: 2.5px solid #ffffff; border-radius: 50%; box-shadow: 0 3px 6px rgba(0,0,0,0.4); color: #ffffff; font-size: 14px;"><i class="fa fa-home"></i></div>',
                            iconSize: [30, 30],
                            iconAnchor: [15, 15]
                        });
                        var centerMarker = L.marker(center, { icon: centerIcon }).addTo(map);
                        
                        centerMarker.on('click', function(e) {
                            L.DomEvent.stopPropagation(e);
                            layer.openPopup(center);
                        });
                        activeLabels.push(centerMarker);
                        
                        var area = getGeodesicArea(outerRing);
                        var areaText = Math.round(area).toLocaleString('vi-VN') + ' m²';
                        
                        var areaIcon = L.divIcon({
                            className: 'center-area-label',
                            html: '<div style="background: rgba(15, 23, 42, 0.85); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); border-radius: 4px; padding: 2.5px 6px; font-size: 11px; font-weight: 700; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.3); text-align: center;">' + areaText + '</div>',
                            iconSize: [100, 22],
                            iconAnchor: [50, -16]
                        });
                        var areaMarker = L.marker(center, { icon: areaIcon, interactive: false }).addTo(map);
                        activeLabels.push(areaMarker);
                    }
                }

                // Gán sự kiện click toàn bản đồ
                map.on('click', handleMapClick);

                // Style custom layer cho KML
                var customLayer = L.geoJson(null, {
                    style: function(feature) {
                        var isBoundary = feature.isBoundary || false;
                        return {
                            color: isBoundary ? '#ef4444' : '#0284c7', // Viền đỏ cho ranh giới dự án lớn như hình mẫu, viền xanh cho ô đất nhỏ
                            weight: isBoundary ? 2 : 1.5,
                            opacity: isBoundary ? 0.9 : 0.8,
                            fillColor: isBoundary ? 'transparent' : '#0284c7',
                            fillOpacity: isBoundary ? 0 : 0.05,      // Trong suốt ở ô ranh giới để không cản click
                            fill: !isBoundary,                      // Không tô nền cho ranh giới lớn
                            interactive: !isBoundary                // Ranh giới lớn không nhận click (click xuyên qua được)
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        if (feature.isBoundary) return; // Không gán sự kiện click/hover cho ranh giới lớn để click xuyên qua được

                        // Hover nhẹ khi di chuột qua
                        if (layer.setStyle) {
                            layer.on('mouseover', function() {
                                if (activeHighlightLayer !== layer) {
                                    layer.setStyle({
                                        color: '#38bdf8',
                                        weight: 2.5,
                                        fillOpacity: 0.15
                                    });
                                }
                            });
                            layer.on('mouseout', function() {
                                if (activeHighlightLayer !== layer) {
                                    customLayer.resetStyle(layer);
                                }
                            });
                        }

                        // Khi click vào vùng
                        layer.on('click', function(e) {
                            L.DomEvent.stopPropagation(e);
                            selectPlot(layer, feature);
                        });

                        // Build popup nội dung chi tiết
                        var props = feature.properties || {};
                        var title = props.name || props.Name || 'Vùng không tên';
                        var desc = props.description || props.Description || '';

                        var popupHtml = '<div style="font-family: \'Inter\', sans-serif; min-width: 200px; max-width: 320px;">';
                        popupHtml += '<div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 6px; padding-bottom: 6px; border-bottom: 2px solid #22c55e;">' + title + '</div>';
                        
                        if (desc) {
                            popupHtml += '<div style="font-size: 12px; color: #475569; margin-bottom: 8px; max-height: 150px; overflow-y: auto;">' + desc + '</div>';
                        }

                        var skipKeys = ['name', 'Name', 'description', 'Description', 'styleUrl', 'styleHash', 'stroke', 'stroke-width', 'stroke-opacity', 'fill', 'fill-opacity'];
                        var extraProps = [];
                        for (var key in props) {
                            if (props.hasOwnProperty(key) && skipKeys.indexOf(key) === -1 && props[key]) {
                                extraProps.push('<div style="display: flex; justify-content: space-between; gap: 8px; padding: 2px 0;"><span style="color: #94a3b8; font-size: 11px;">' + key + ':</span><span style="color: #1e293b; font-weight: 500; font-size: 11px; text-align: right;">' + props[key] + '</span></div>');
                            }
                        }
                        if (extraProps.length > 0) {
                            popupHtml += '<div style="border-top: 1px solid #e2e8f0; padding-top: 6px; margin-top: 4px;">' + extraProps.join('') + '</div>';
                        }

                        popupHtml += '</div>';
                        layer.bindPopup(popupHtml, { maxWidth: 350 });
                    },
                    filter: function(feature) {
                        // Loại bỏ Point/MultiPoint (icon map) để tránh lag
                        if (feature && feature.geometry) {
                            var type = feature.geometry.type;
                            if (type === 'Point' || type === 'MultiPoint') {
                                return false;
                            }
                        }
                        return sanitizeFeature(feature);
                    },
                    pointToLayer: function(feature, latlng) {
                        return null;
                    }
                });

                function sanitizeFeature(feature) {
                    if (!feature || !feature.geometry) return false;
                    var type = feature.geometry.type;
                    var coords = feature.geometry.coordinates;

                    try {
                        if (type === 'Point') {
                            return isValidCoord(coords);
                        } else if (type === 'LineString') {
                            if (!Array.isArray(coords)) return false;
                            var validCoords = coords.filter(isValidCoord);
                            
                            // Nếu LineString khép kín (điểm đầu trùng điểm cuối), chuyển thành Polygon để click được bên trong
                            if (validCoords.length >= 4) {
                                var first = validCoords[0];
                                var last = validCoords[validCoords.length - 1];
                                if (Math.abs(first[0] - last[0]) < 1e-7 && Math.abs(first[1] - last[1]) < 1e-7) {
                                    feature.geometry.type = 'Polygon';
                                    feature.geometry.coordinates = [validCoords];
                                } else {
                                    feature.geometry.coordinates = validCoords;
                                }
                            } else {
                                feature.geometry.coordinates = validCoords;
                            }
                            return feature.geometry.coordinates.length > 0;
                        } else if (type === 'MultiLineString') {
                            if (!Array.isArray(coords)) return false;
                            
                            // Tương tự, nếu MultiLineString có các đường khép kín, chuyển thành MultiPolygon/Polygon
                            var polygonRings = [];
                            var remainingLines = [];
                            
                            coords.forEach(function(line) {
                                if (Array.isArray(line)) {
                                    var validLine = line.filter(isValidCoord);
                                    if (validLine.length >= 4) {
                                        var first = validLine[0];
                                        var last = validLine[validLine.length - 1];
                                        if (Math.abs(first[0] - last[0]) < 1e-7 && Math.abs(first[1] - last[1]) < 1e-7) {
                                            polygonRings.push(validLine);
                                            return;
                                        }
                                    }
                                    if (validLine.length > 0) {
                                        remainingLines.push(validLine);
                                    }
                                }
                            });

                            if (polygonRings.length > 0) {
                                // Nếu tất cả đều là khép kín, biến thành MultiPolygon hoặc Polygon
                                feature.geometry.type = polygonRings.length === 1 ? 'Polygon' : 'MultiPolygon';
                                feature.geometry.coordinates = polygonRings.length === 1 ? [polygonRings[0]] : polygonRings.map(function(ring) { return [ring]; });
                            } else {
                                feature.geometry.coordinates = remainingLines;
                            }
                            return feature.geometry.coordinates.length > 0;
                        } else if (type === 'MultiPoint') {
                            if (!Array.isArray(coords)) return false;
                            feature.geometry.coordinates = coords.filter(isValidCoord);
                            return feature.geometry.coordinates.length > 0;
                        } else if (type === 'Polygon') {
                            if (!Array.isArray(coords)) return false;
                            feature.geometry.coordinates = coords.map(function(ring) {
                                return Array.isArray(ring) ? ring.filter(isValidCoord) : [];
                            }).filter(function(ring) {
                                return ring.length >= 3;
                            });
                            return feature.geometry.coordinates.length > 0;
                        } else if (type === 'MultiPolygon') {
                            if (!Array.isArray(coords)) return false;
                            feature.geometry.coordinates = coords.map(function(polygon) {
                                return Array.isArray(polygon) ? polygon.map(function(ring) {
                                    return Array.isArray(ring) ? ring.filter(isValidCoord) : [];
                                }).filter(function(ring) {
                                    return ring.length >= 3;
                                }) : [];
                            }).filter(function(polygon) {
                                return polygon.length > 0;
                            });
                            return feature.geometry.coordinates.length > 0;
                        } else if (type === 'GeometryCollection') {
                            if (!feature.geometry.geometries || !Array.isArray(feature.geometry.geometries)) return false;
                            feature.geometry.geometries = feature.geometry.geometries.filter(function(geom) {
                                var dummyFeature = { type: 'Feature', geometry: geom };
                                return sanitizeFeature(dummyFeature);
                            });
                            return feature.geometry.geometries.length > 0;
                        }
                    } catch (e) {
                        console.error('Error sanitizing feature:', e);
                        return false;
                    }
                    return true;
                }

                function isValidCoord(coord) {
                    return Array.isArray(coord) && 
                           coord.length >= 2 && 
                           typeof coord[0] === 'number' && !isNaN(coord[0]) && 
                           typeof coord[1] === 'number' && !isNaN(coord[1]);
                }

                // Try cache first if available
                if (cacheJsonUrl) {
                    fetch(cacheJsonUrl)
                        .then(function(response) {
                            if (!response.ok) {
                                throw new Error('Không thể tải file cache JSON');
                            }
                            return response.json();
                        })
                        .then(function(cacheData) {
                            if (cacheData && cacheData.overlays && cacheData.overlays.length > 0) {
                                var overlayBounds = [];
                                cacheData.overlays.forEach(function(overlay) {
                                    var bounds = L.latLngBounds(overlay.bounds);
                                    var imgOverlay = L.imageOverlay(overlay.image, bounds, {
                                        opacity: 0.85,
                                        interactive: true
                                    }).addTo(map);

                                    imgOverlay.on('click', function(e) {
                                        L.DomEvent.stopPropagation(e);
                                        var popup = L.popup()
                                            .setLatLng(e.latlng)
                                            .setContent('<div style="font-family: \'Inter\', sans-serif; font-size: 12px; font-weight: 600;">Ảnh bản đồ quy hoạch (GroundOverlay)</div>')
                                            .openOn(map);
                                    });

                                    overlayBounds.push(bounds);
                                });

                                if (overlayBounds.length > 0) {
                                    var groupBounds = L.latLngBounds(overlayBounds[0]);
                                    for (var i = 1; i < overlayBounds.length; i++) {
                                        groupBounds.extend(overlayBounds[i]);
                                    }
                                    map.fitBounds(groupBounds, {
                                        padding: [40, 40]
                                    });
                                }

                                $('#map-loader').css('opacity', 0);
                                setTimeout(function() {
                                    $('#map-loader').hide();
                                }, 300);
                            } else {
                                loadKmlVectorFallback();
                            }
                        })
                        .catch(function(err) {
                            console.warn('Lỗi khi tải cache JSON, chuyển sang tải vector KML:', err);
                            loadKmlVectorFallback();
                        });
                } else {
                    loadKmlVectorFallback();
                }

                function loadKmlVectorFallback() {
                    fetch(kmlUrl)
                        .then(function(response) {
                            if (!response.ok) {
                                throw new Error('Không thể tải file KML từ server');
                            }
                            return response.text();
                        })
                        .then(function(kmlText) {
                            // 1. Clean up coordinate whitespace to prevent parser bugs
                            var cleanedKml = kmlText.replace(/<coordinates>([\s\S]*?)<\/coordinates>/g, function(match, coords) {
                                return '<coordinates>' + coords.trim().replace(/\s+/g, ' ') + '</coordinates>';
                            });

                            // 2. Parse XML/KML DOM
                            var parser = new DOMParser();
                            var kmlDoc = parser.parseFromString(cleanedKml, 'text/xml');
                            
                            var parserError = kmlDoc.querySelector('parsererror');
                            if (parserError) {
                                throw new Error('File KML không đúng định dạng XML');
                            }

                            // 3. Convert KML DOM to GeoJSON using an interceptor
                            var geojsonData = null;
                            var interceptor = {
                                addData: function(data) {
                                    geojsonData = data;
                                }
                            };
                            omnivore.kml.parse(kmlDoc, null, interceptor);

                            if (geojsonData && geojsonData.features) {
                                // 3a. Tách riêng các điểm Point (tâm lô đất) để lưu trữ truy vấn click, tránh vẽ đè gây lag
                                allPointFeatures = geojsonData.features.filter(function(feature) {
                                    return feature.geometry && (feature.geometry.type === 'Point' || feature.geometry.type === 'MultiPoint');
                                });

                                var lineAndPolygonFeatures = geojsonData.features.filter(function(feature) {
                                    return feature.geometry && (feature.geometry.type !== 'Point' && feature.geometry.type !== 'MultiPoint');
                                });

                                // 3b. Sanitize và chuẩn hóa kiểu hình học của đường và vùng trước khi vẽ
                                lineAndPolygonFeatures = lineAndPolygonFeatures.filter(function(feature) {
                                    return sanitizeFeature(feature);
                                });

                                // Hàm tính diện tích thực tế trắc địa (mét vuông)
                                var getPolygonArea = function(ring) {
                                    if (!ring || ring.length < 3) return 0;
                                    var area = 0;
                                    var R = 6378137; // Bán kính Trái Đất (mét)
                                    for (var i = 0; i < ring.length; i++) {
                                        var p1 = ring[i];
                                        var p2 = ring[(i + 1) % ring.length];
                                        var lambda1 = p1[0] * Math.PI / 180;
                                        var lambda2 = p2[0] * Math.PI / 180;
                                        var phi1 = p1[1] * Math.PI / 180;
                                        var phi2 = p2[1] * Math.PI / 180;
                                        area += (lambda2 - lambda1) * (2 + Math.sin(phi1) + Math.sin(phi2));
                                    }
                                    area = Math.abs(area * R * R / 2);
                                    return area;
                                };

                                var getGeomArea = function(geometry) {
                                    if (!geometry) return 0;
                                    var type = geometry.type;
                                    var coords = geometry.coordinates;
                                    
                                    if (type === 'Polygon') {
                                        return getPolygonArea(coords[0]);
                                    } else if (type === 'MultiPolygon') {
                                        var area = 0;
                                        for (var i = 0; i < coords.length; i++) {
                                            if (coords[i] && coords[i][0]) {
                                                area += getPolygonArea(coords[i][0]);
                                            }
                                        }
                                        return area;
                                    } else if (type === 'LineString') {
                                        // Bounding box area approximation
                                        var minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
                                        for (var i = 0; i < coords.length; i++) {
                                            var pt = coords[i];
                                            if (pt[0] < minX) minX = pt[0];
                                            if (pt[0] > maxX) maxX = pt[0];
                                            if (pt[1] < minY) minY = pt[1];
                                            if (pt[1] > maxY) maxY = pt[1];
                                        }
                                        return getPolygonArea([[minX, minY], [maxX, minY], [maxX, maxY], [minX, maxY], [minX, minY]]);
                                    } else if (type === 'MultiLineString') {
                                        var area = 0;
                                        for (var i = 0; i < coords.length; i++) {
                                            var minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
                                            var line = coords[i];
                                            for (var j = 0; j < line.length; j++) {
                                                var pt = line[j];
                                                if (pt[0] < minX) minX = pt[0];
                                                if (pt[0] > maxX) maxX = pt[0];
                                                if (pt[1] < minY) minY = pt[1];
                                                if (pt[1] > maxY) maxY = pt[1];
                                            }
                                            area += getPolygonArea([[minX, minY], [maxX, minY], [maxX, maxY], [minX, maxY], [minX, minY]]);
                                        }
                                        return area;
                                    } else if (type === 'GeometryCollection') {
                                        var area = 0;
                                        if (geometry.geometries) {
                                            for (var i = 0; i < geometry.geometries.length; i++) {
                                                area += getGeomArea(geometry.geometries[i]);
                                            }
                                        }
                                        return area;
                                    }
                                    return 0;
                                };

                                var getApproxArea = function(feature) {
                                    if (!feature || !feature.geometry) return 0;
                                    return getGeomArea(feature.geometry);
                                };

                                // Đánh dấu các vùng ranh giới / block lớn (diện tích > 3000 m2)
                                lineAndPolygonFeatures.forEach(function(feature) {
                                    var area = getApproxArea(feature);
                                    if (area > 3000) {
                                        feature.isBoundary = true;
                                    }
                                });

                                // Sắp xếp: Vùng lớn vẽ dưới (dưới cùng), vùng nhỏ vẽ sau (trên cùng)
                                lineAndPolygonFeatures.sort(function(a, b) {
                                    return getApproxArea(b) - getApproxArea(a);
                                });

                                // Vẽ các đường vẽ và vùng nhỏ lên bản đồ
                                customLayer.addData(lineAndPolygonFeatures);
                            }
                            customLayer.addTo(map);

                            // Fit to bounds
                            if (customLayer.getBounds().isValid()) {
                                map.fitBounds(customLayer.getBounds(), {
                                    padding: [40, 40]
                                });

                                // Hide the loader gracefully
                                $('#map-loader').css('opacity', 0);
                                setTimeout(function() {
                                    $('#map-loader').hide();
                                }, 300);
                            } else {
                                $('#map-loader i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-circle').css('color', '#ef4444');
                                $('#map-loader span').text('Bản đồ không chứa dữ liệu hình học hoặc tệp KML rỗng.').css('color', '#ef4444');
                            }
                        })
                        .catch(function(err) {
                            console.error('KML Load & Parse Error:', err);
                            $('#map-loader i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-circle').css('color', '#ef4444');
                            $('#map-loader span').text('Không thể hiển thị bản đồ: tệp KML bị lỗi hoặc trống.').css('color', '#ef4444');
                        });
                }

                // Invalidate size once the modal transition finishes
                $('#dtModal').one('shown.bs.modal', function() {
                    map.invalidateSize();
                    if (customLayer.getBounds().isValid()) {
                        map.fitBounds(customLayer.getBounds(), {
                            padding: [40, 40]
                        });
                    }
                });

                // Invalidate size on manual resize fallback
                setTimeout(function() {
                    map.invalidateSize();
                }, 400);

            } catch (e) {
                console.error('Map Init Exception:', e);
                $('#map-loader span').text('Đã xảy ra lỗi khởi tạo bản đồ.').css('color', '#ef4444');
            }
        }
    })();
</script>
