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
                    <span>{{ $plandoffice->name }}</span>
                </h2>
            </div>
            
            <!-- Search inputs form -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex-grow: 1; justify-content: flex-end; max-width: 600px; font-family: 'Inter', sans-serif; margin-right: 30px;">
                <div style="position: relative;">
                    <input type="text" id="search-so-to" placeholder="Số tờ..." style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; border-radius: 6px; padding: 6px 12px; font-size: 12px; width: 90px; outline: none; transition: all 0.2s;" />
                </div>
                <div style="position: relative;">
                    <input type="text" id="search-so-thua" placeholder="Số thửa..." style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: #ffffff; border-radius: 6px; padding: 6px 12px; font-size: 12px; width: 90px; outline: none; transition: all 0.2s;" />
                </div>
                <button type="button" id="btn-search-parcel" style="background: #22c55e; border: none; color: #ffffff; border-radius: 6px; padding: 6px 14px; font-size: 12px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s; outline: none;">
                    <i class="fa fa-search"></i>
                    <span>Tìm kiếm</span>
                </button>
            </div>
            
            <button type="button" class="close" data-dismiss="modal" title="Đóng" style="color: #ffffff; opacity: 0.8; transition: all 0.2s; background: none; border: none; margin: 0; padding: 0; line-height: 1; position: absolute; right: 24px; top: 50%; transform: translateY(-50%); cursor: pointer; display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%;">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="modal-body" style="padding: 0; position: relative; background-color: #f1f5f9; overflow: hidden;">
            <!-- Map Container -->
            <div id="planning-map-canvas" style="width: 100%; height: calc(90vh - 130px); min-height: 500px; z-index: 1;"></div>
            
            <!-- Bottom Sheet Panel -->
            <div id="parcel-bottom-sheet" style="position: absolute; bottom: 0; left: 0; right: 0; background: #ffffff; z-index: 999; border-top-left-radius: 20px; border-top-right-radius: 20px; box-shadow: 0 -10px 25px -5px rgba(0,0,0,0.1), 0 -8px 10px -6px rgba(0,0,0,0.1); transform: translateY(100%); transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); font-family: 'Inter', sans-serif;">
                <!-- Drag handle or header click to hide -->
                <div id="bottom-sheet-drag-handle" style="height: 24px; display: flex; align-items: center; justify-content: center; cursor: pointer;">
                    <div style="width: 40px; height: 4px; background: #cbd5e1; border-radius: 2px;"></div>
                </div>
                <!-- Content Area -->
                <div id="bottom-sheet-content" style="padding: 0 24px 24px 24px; max-height: 300px; overflow-y: auto;">
                    <!-- Content will be injected dynamically -->
                </div>
            </div>
            
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
                var DEFAULT_SO_TO = '{{ $default_so_to ?? "" }}';
                var kmlUrls = @json($kml_urls ?? []);
                if (kmlUrls.length === 0 && "{{ $plandoffice->kml_file ?? '' }}") {
                    var rawKml = "{{ $plandoffice->kml_file ?? '' }}";
                    var splitKmls = rawKml.split('||');
                    for (var i = 0; i < splitKmls.length; i++) {
                        var k = splitKmls[i].trim();
                        if (k) {
                            kmlUrls.push("{{ asset('storage') }}/" + k);
                        }
                    }
                }

                function getPointName(feature) {
                    if (!feature || !feature.properties) return '';

                    return String(
                        feature.properties.name ||
                        feature.properties.Name ||
                        ''
                    ).trim();
                }

                function getPointDesc(feature) {
                    if (!feature || !feature.properties) return '';

                    return String(
                        feature.properties.description ||
                        feature.properties.Description ||
                        ''
                    ).trim();
                }

                function getPointStyle(feature) {
                    if (!feature || !feature.properties) return '';

                    return String(
                        feature.properties.styleUrl ||
                        feature.properties.styleurl ||
                        feature.properties.style ||
                        feature.properties.Style ||
                        feature.properties.styleHash ||
                        ''
                    ).trim();
                }

                function isIntegerText(val) {
                    return /^\d+$/.test(String(val || '').trim());
                }

                function isDecimalText(val) {
                    return /^\d+([,.]\d+)?$/.test(String(val || '').trim());
                }

                function isLandCode(val) {
                    var clean = String(val || '').trim().toUpperCase();

                    var landCodes = [
                        'ODT', 'CLN', 'BCS', 'CQP', 'DCK', 'DGT', 'DNL', 'DTL', 'TIN', 'ONT',
                        'HNK', 'LNK', 'BHK', 'TSC', 'DVH', 'DKN', 'DHT', 'DXH', 'DGD', 'DKT',
                        'DDT', 'DSH', 'DKV', 'CANH', 'RPT', 'RSN', 'MNC', 'TONG', 'LUC', 'SKC',
                        'TMD', 'NTS', 'SON', 'PNK', 'DRA', 'NTD', 'DTS', 'DTT', 'DCH'
                    ];

                    if (landCodes.indexOf(clean) !== -1) {
                        return true;
                    }

                    if (clean.indexOf('+') !== -1) {
                        var parts = clean.split('+');

                        for (var i = 0; i < parts.length; i++) {
                            if (landCodes.indexOf(parts[i].trim()) === -1) {
                                return false;
                            }
                        }

                        return true;
                    }

                    return false;
                }

                function classifyPointFeature(feature) {
                    var name = getPointName(feature);
                    var desc = getPointDesc(feature);
                    var style = getPointStyle(feature);

                    if (!name) return null;

                    // TEST.kml
                    if (desc === 'Nhan Thua') {
                        if (style === '#InPointMap_Point_005' && isIntegerText(name)) {
                            return 'so_thua';
                        }

                        if (style === '#InPointMap_Point_004' && isDecimalText(name)) {
                            return 'dien_tich';
                        }

                        if (style === '#InPointMap_Point_000' || isLandCode(name)) {
                            return 'loai_dat';
                        }

                        return null;
                    }

                    // dctest.kml
                    if (style === '#InPointMap_Point_007' && desc === '1' && isIntegerText(name)) {
                        return 'so_to';
                    }

                    if (style === '#InPointMap_Point_002' && desc === '13' && isIntegerText(name)) {
                        return 'so_thua';
                    }

                    if (style === '#InPointMap_Point_003' && desc === '13' && isDecimalText(name)) {
                        return 'dien_tich';
                    }

                    if (style === '#InPointMap_Point_001' && isLandCode(name)) {
                        return 'loai_dat';
                    }

                    if (style === '#InPointMap_Point_004' && desc === '4') {
                        return 'ten_chu';
                    }

                    if (isLandCode(name)) {
                        return 'loai_dat';
                    }

                    if (isIntegerText(name)) {
                        return 'so_thua';
                    }

                    return null;
                }

                function findNearestPointByType(centerLatLng, type, maxMeters) {
                    var nearest = null;
                    var minDist = Infinity;

                    if (!centerLatLng || !allPointFeatures || allPointFeatures.length === 0) {
                        return null;
                    }

                    allPointFeatures.forEach(function(f) {
                        if (!f.geometry || f.geometry.type !== 'Point') return;

                        if (classifyPointFeature(f) !== type) return;

                        var coords = f.geometry.coordinates;
                        var ptLatLng = L.latLng(coords[1], coords[0]);
                        var dist = centerLatLng.distanceTo(ptLatLng);

                        if (dist < minDist && dist <= maxMeters) {
                            minDist = dist;
                            nearest = {
                                feature: f,
                                latlng: ptLatLng,
                                distance: dist,
                                name: getPointName(f)
                            };
                        }
                    });

                    return nearest;
                }

                function extractNumberFromText(text, keyWords) {
                    if (!text) return null;
                    var cleanText = text.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ');
                    for (var i = 0; i < keyWords.length; i++) {
                        var kw = keyWords[i];
                        var regex = new RegExp(kw + '\\s*(?:đất|số|bản\\s*đồ)?\\s*[:\\-\\s\\.]+\\s*(\\d+)', 'i');
                        var match = cleanText.match(regex);
                        if (match) {
                            return match[1];
                        }
                        
                        var regexDirect = new RegExp(kw + '\\s+(\\d+)', 'i');
                        var matchDirect = cleanText.match(regexDirect);
                        if (matchDirect) {
                            return matchDirect[1];
                        }
                    }
                    return null;
                }

                function getSoThua(feature, centerLatLng) {
                    if (feature && feature.geometry && feature.geometry.type === 'Point') {
                        if (classifyPointFeature(feature) === 'so_thua') {
                            return getPointName(feature);
                        }
                    }

                    var nearest = findNearestPointByType(centerLatLng, 'so_thua', 80);
                    if (nearest) {
                        return nearest.name;
                    }

                    if (feature && feature.properties) {
                        var props = feature.properties;
                        var possibleKeys = ['so_thua', 'so_thua_dat', 'Số thửa', 'Số thửa đất', 'SỐ_THỬA', 'SOTHUA', 'thua', 'Thửa'];
                        for (var i = 0; i < possibleKeys.length; i++) {
                            var k = possibleKeys[i];
                            if (props[k] !== undefined && props[k] !== null && String(props[k]).trim() !== '') {
                                return String(props[k]).trim();
                            }
                        }

                        var desc = props.description || props.Description || '';
                        var extracted = extractNumberFromText(desc, ['số thửa', 'số thửa đất', 'thửa đất', 'thửa số', 'thửa', 'thua']);
                        if (extracted) {
                            return extracted;
                        }

                        var name = getPointName(feature);
                        if (name) {
                            if (isIntegerText(name)) {
                                return name;
                            }
                            var extractedName = extractNumberFromText(name, ['số thửa', 'số thửa đất', 'thửa đất', 'thửa số', 'thửa', 'thua']);
                            if (extractedName) {
                                return extractedName;
                            }
                        }
                    }

                    return null;
                }

                function getSoTo(feature, centerLatLng) {
                    var inputSoTo = $('#search-so-to').val().trim();

                    if (inputSoTo) {
                        return inputSoTo;
                    }

                    if (feature && feature.geometry && feature.geometry.type === 'Point') {
                        if (classifyPointFeature(feature) === 'so_to') {
                            return getPointName(feature);
                        }
                    }

                    var nearest = findNearestPointByType(centerLatLng, 'so_to', 80);

                    if (nearest) {
                        return nearest.name;
                    }

                    if (feature && feature.properties) {
                        var props = feature.properties;
                        var possibleKeys = ['so_to', 'Số tờ', 'SỐ_TỜ', 'SOTO', 'to', 'Tờ'];
                        for (var i = 0; i < possibleKeys.length; i++) {
                            var k = possibleKeys[i];
                            if (props[k] !== undefined && props[k] !== null && String(props[k]).trim() !== '') {
                                return String(props[k]).trim();
                            }
                        }

                        var desc = props.description || props.Description || '';
                        var extracted = extractNumberFromText(desc, ['số tờ', 'tờ bản đồ', 'số tờ bản đồ', 'số bản đồ', 'tờ', 'bản đồ số', 'bản đồ', 'to']);
                        if (extracted) {
                            return extracted;
                        }

                        var name = getPointName(feature);
                        if (name) {
                            var extractedName = extractNumberFromText(name, ['số tờ', 'tờ bản đồ', 'số tờ bản đồ', 'số bản đồ', 'tờ', 'bản đồ số', 'bản đồ', 'to']);
                            if (extractedName) {
                                return extractedName;
                            }
                        }
                    }

                    return DEFAULT_SO_TO || '';
                }

                function showBottomSheetLoading() {
                    var html = '<div style="text-align: center; padding: 30px;">' +
                               '<i class="fa fa-spinner fa-spin text-success" style="font-size: 32px; margin-bottom: 12px;"></i>' +
                               '<div style="font-size: 13px; color: #64748b; font-weight: 500;">Đang tải thông tin chi tiết...</div>' +
                               '</div>';
                    $('#bottom-sheet-content').html(html);
                    $('#parcel-bottom-sheet').css('transform', 'translateY(0)');
                }

                function hideBottomSheet() {
                    $('#parcel-bottom-sheet').css('transform', 'translateY(100%)');
                }

                $('#bottom-sheet-drag-handle').on('click', hideBottomSheet);
                $(document).on('click', '.bottom-sheet-close-btn', hideBottomSheet);

                function getRawBottomSheetHtml(props, title) {
                    var desc = props.description || props.Description || '';
                    var html = '<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;">' +
                               '<div style="display: flex; align-items: center; gap: 8px;">' +
                               '<div style="background: #fef3c7; color: #d97706; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">' +
                               '<i class="fa fa-info-circle"></i>' +
                               '</div>' +
                               '<div>' +
                               '<h5 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">Lô đất: ' + title + '</h5>' +
                               '<p style="margin: 0; font-size: 11px; color: #64748b;">Thuộc tính bản vẽ (KML)</p>' +
                               '</div>' +
                               '</div>' +
                               '<button type="button" class="bottom-sheet-close-btn" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer; padding: 4px;">&times;</button>' +
                               '</div>';
                    
                    if (desc) {
                        html += '<div style="font-size: 12px; color: #475569; margin-bottom: 12px; padding: 10px; background: #f8fafc; border-radius: 6px; border-left: 3px solid #cbd5e1; max-height: 100px; overflow-y: auto;">' + desc + '</div>';
                    }
                    
                    var skipKeys = ['name', 'Name', 'description', 'Description', 'styleUrl', 'styleHash', 'stroke', 'stroke-width', 'stroke-opacity', 'fill', 'fill-opacity'];
                    var extraProps = [];
                    for (var key in props) {
                        if (props.hasOwnProperty(key) && skipKeys.indexOf(key) === -1 && props[key]) {
                            extraProps.push('<div style="background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #f1f5f9;">' +
                                            '<div style="font-size: 10px; color: #64748b; font-weight: 500; margin-bottom: 2px;">' + key + '</div>' +
                                            '<div style="font-size: 12px; color: #0f172a; font-weight: 600;">' + props[key] + '</div>' +
                                            '</div>');
                        }
                    }
                    if (extraProps.length > 0) {
                        html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">' + extraProps.join('') + '</div>';
                    } else if (!desc) {
                        html += '<div style="text-align: center; color: #94a3b8; font-size: 12px; padding: 10px;">Không có thông tin thuộc tính nào.</div>';
                    }
                    
                    return html;
                }

                function loadParcelInfo(so_thua, rawHtml, so_to) {
                    if (!so_thua) {
                        $('#bottom-sheet-content').html(rawHtml);
                        return;
                    }
                    $.ajax({
                        url: '{{ url("/admin/plandoffices/get-parcel-info") }}',
                        type: 'GET',
                        data: { 
                            so_to: so_to || '',
                            so_thua: so_thua,
                            plandoffice_id: "{{ $plandoffice->id ?? '' }}"
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res && res.result && res.data) {
                                var data = res.data;
                                var html = '<div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; margin-bottom: 16px;">' +
                                           '<div style="display: flex; align-items: center; gap: 8px;">' +
                                           '<div style="background: #e6f4ea; color: #137333; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px;">' +
                                           '<i class="fa fa-map-o"></i>' +
                                           '</div>' +
                                           '<div>' +
                                           '<h5 style="margin: 0; font-size: 14px; font-weight: 700; color: #0f172a;">Thông tin thửa đất số ' + (data.so_thua || so_thua) + '</h5>' +
                                           '<p style="margin: 0; font-size: 11px; color: #64748b;">Dữ liệu địa chính chính thức</p>' +
                                           '</div>' +
                                           '</div>' +
                                           '<button type="button" class="bottom-sheet-close-btn" style="background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer; padding: 4px;">&times;</button>' +
                                           '</div>';
                                
                                var gridItems = [];
                                if (data.so_to) {
                                    gridItems.push({ label: 'Số tờ bản đồ', val: data.so_to, icon: 'fa-clone' });
                                }
                                if (data.so_thua) {
                                    gridItems.push({ label: 'Số thửa đất', val: data.so_thua, icon: 'fa-tag' });
                                }
                                if (data.dien_tich !== null && data.dien_tich !== undefined && data.dien_tich !== '') {
                                    var areaVal = parseFloat(data.dien_tich).toLocaleString('vi-VN') + ' m²';
                                    gridItems.push({ label: 'Diện tích', val: areaVal, icon: 'fa-expand' });
                                }
                                if (data.loai_dat) {
                                    gridItems.push({ label: 'Loại đất', val: data.loai_dat, icon: 'fa-bookmark-o' });
                                }
                                if (data.cong_trinh) {
                                    gridItems.push({ label: 'Công trình xây dựng', val: data.cong_trinh, icon: 'fa-building-o' });
                                }
                                if (data.ten_chu) {
                                    gridItems.push({ label: 'Chủ sở hữu', val: data.ten_chu, icon: 'fa-user-o' });
                                }
                                
                                if (gridItems.length > 0) {
                                    html += '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">';
                                    gridItems.forEach(function(item) {
                                        html += '<div style="background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 12px;">' +
                                                '<div style="background: #ffffff; color: #475569; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 1px solid #e2e8f0; flex-shrink: 0;">' +
                                                '<i class="fa ' + item.icon + '"></i>' +
                                                '</div>' +
                                                '<div style="overflow: hidden;">' +
                                                '<div style="font-size: 10px; color: #64748b; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px;">' + item.label + '</div>' +
                                                '<div style="font-size: 13px; color: #0f172a; font-weight: 700; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">' + item.val + '</div>' +
                                                '</div>' +
                                                '</div>';
                                    });
                                    html += '</div>';
                                } else {
                                    html += '<div style="text-align: center; color: #94a3b8; font-size: 12px; padding: 10px;">Không có thông tin chi tiết nào.</div>';
                                }
                                
                                $('#bottom-sheet-content').html(html);
                            } else {
                                $('#bottom-sheet-content').html(rawHtml);
                            }
                        },
                        error: function() {
                            $('#bottom-sheet-content').html(rawHtml);
                        }
                    });
                }

                function showParcelDetailsForFeature(feature, centerLatLng) {
                    var props = feature.properties || {};
                    var so_thua = getSoThua(feature, centerLatLng);
                    var so_to = getSoTo(feature, centerLatLng);
                    var title = so_thua ? ('Thửa ' + so_thua) : (props.name || props.Name || 'Vùng không tên');
                    var rawHtml = getRawBottomSheetHtml(props, title);

                    console.log('CLICK PARCEL:', {
                        so_to: so_to,
                        so_thua: so_thua,
                        feature_name: props.name || props.Name || '',
                        center: centerLatLng
                    });

                    showBottomSheetLoading();
                    loadParcelInfo(so_thua, rawHtml, so_to);
                }


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
                                // Update nearest
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
                        
                        // 4. Hiển thị thông tin chi tiết lô đất ở Bottom Sheet
                        var props = nearest.feature.properties || {};
                        var so_thua = getSoThua(nearest.feature, nearest.latlng);
                        var so_to = getSoTo(nearest.feature, nearest.latlng);
                        var title = so_thua ? ('Thửa ' + so_thua) : (props.name || props.Name || 'Lô đất');
                        var rawHtml = getRawBottomSheetHtml(props, title);

                        showBottomSheetLoading();
                        loadParcelInfo(so_thua, rawHtml, so_to);

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

                        hideBottomSheet();
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
                            showParcelDetailsForFeature(feature, center);
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
                            color: isBoundary ? '#ef4444' : '#0284c7', // Viền đỏ cho ranh giới dự án lớn, viền xanh cho ô đất nhỏ
                            weight: isBoundary ? 2 : 1.5,
                            opacity: isBoundary ? 0.9 : 0.8,
                            fillColor: isBoundary ? 'transparent' : '#0284c7',
                            fillOpacity: isBoundary ? 0 : 0.05,
                            fill: !isBoundary,
                            interactive: !isBoundary
                        };
                    },
                    onEachFeature: function(feature, layer) {
                        if (feature.isBoundary) return;

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
                            
                            var center = null;
                            if (layer.getBounds) {
                                center = layer.getBounds().getCenter();
                            }
                            showParcelDetailsForFeature(feature, center);
                        });

                    },
                    filter: function(feature) {
                        // Loại bỏ Point/MultiPoint để tránh lag
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
                            
                            // Nếu LineString khép kín, chuyển thành Polygon
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

                // Fetch map data from database instead of parsing KML files
                $.ajax({
                    url: '{{ url("/admin/plandoffices/get-map-data/" . $plandoffice->id) }}',
                    type: 'GET',
                    dataType: 'JSON',
                    success: function(res) {
                        if (!res.result || !res.data || res.data.length === 0) {
                            $('#map-loader i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-circle').css('color', '#ef4444');
                            $('#map-loader span').text('Chưa trích xuất tờ thửa hoặc không có dữ liệu bản đồ.').css('color', '#ef4444');
                            return;
                        }

                        var parcels = res.data;
                        var geojsonFeatures = [];
                        
                        parcels.forEach(function(p) {
                            if (!p.coords) return;
                            
                            var coordsArray;
                            try {
                                coordsArray = JSON.parse(p.coords);
                            } catch (e) {
                                return;
                            }
                            
                            if (!coordsArray || coordsArray.length === 0) return;

                            // Form coordinate points to GeoJSON [lng, lat] format
                            var geojsonCoords = coordsArray.map(function(pt) {
                                return [pt[1], pt[0]]; // [lng, lat]
                            });

                            // Close polygon loop if not closed
                            if (geojsonCoords.length > 0) {
                                var first = geojsonCoords[0];
                                var last = geojsonCoords[geojsonCoords.length - 1];
                                if (first[0] !== last[0] || first[1] !== last[1]) {
                                    geojsonCoords.push([first[0], first[1]]);
                                }
                            }

                            var feature = {
                                type: 'Feature',
                                geometry: {
                                    type: 'Polygon',
                                    coordinates: [geojsonCoords]
                                },
                                properties: {
                                    id: p.id,
                                    so_to: p.so_to,
                                    so_thua: p.so_thua,
                                    dien_tich: p.dien_tich,
                                    loai_dat: p.loai_dat,
                                    cong_trinh: p.cong_trinh,
                                    ten_chu: p.ten_chu,
                                    name: 'Thửa ' + p.so_thua
                                }
                            };
                            geojsonFeatures.push(feature);

                            if (p.lat && p.lng) {
                                allPointFeatures.push({
                                    type: 'Feature',
                                    geometry: {
                                        type: 'Point',
                                        coordinates: [parseFloat(p.lng), parseFloat(p.lat)]
                                    },
                                    properties: {
                                        so_to: p.so_to,
                                        so_thua: p.so_thua,
                                        name: p.so_thua,
                                        description: 'Nhan Thua',
                                        styleUrl: '#InPointMap_Point_005'
                                    }
                                });
                            }
                        });

                        // Calculate relative areas to flag large blocks
                        var getPolygonArea = function(ring) {
                            var latlngs = ring.map(function(coord) {
                                return L.latLng(coord[1], coord[0]);
                            });
                            return getGeodesicArea(latlngs);
                        };

                        geojsonFeatures.forEach(function(feature) {
                            var outerRing = feature.geometry.coordinates[0];
                            var area = getPolygonArea(outerRing);
                            if (area > 3000) {
                                feature.isBoundary = true;
                            }
                        });

                        // Sort: Large boundaries drawn at bottom, small ones on top
                        geojsonFeatures.sort(function(a, b) {
                            var areaA = getPolygonArea(a.geometry.coordinates[0]);
                            var areaB = getPolygonArea(b.geometry.coordinates[0]);
                            return areaB - areaA;
                        });

                        customLayer.addData(geojsonFeatures);
                        customLayer.addTo(map);

                        // Fit to bounds
                        if (customLayer.getBounds().isValid()) {
                            map.fitBounds(customLayer.getBounds(), {
                                padding: [40, 40]
                            });

                            // Hide loader
                            $('#map-loader').css('opacity', 0);
                            setTimeout(function() {
                                $('#map-loader').hide();
                            }, 300);
                        } else {
                            $('#map-loader i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-circle').css('color', '#ef4444');
                            $('#map-loader span').text('Bản đồ không chứa dữ liệu hình học hoặc tệp KML rỗng.').css('color', '#ef4444');
                        }
                    },
                    error: function() {
                        $('#map-loader i').removeClass('fa-spinner fa-spin').addClass('fa-exclamation-circle').css('color', '#ef4444');
                        $('#map-loader span').text('Lỗi khi tải dữ liệu bản đồ từ máy chủ.').css('color', '#ef4444');
                    }
                });

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

                // Search parcel logic
                function searchAndZoomToParcel() {
                    if (!allPointFeatures || allPointFeatures.length === 0) {
                        alert('Bản đồ đang tải dữ liệu, vui lòng đợi trong giây lát...');
                        return;
                    }

                    var inputSoTo = $('#search-so-to').val().trim();
                    var inputSoThua = $('#search-so-thua').val().trim();

                    if (!inputSoThua) {
                        alert('Vui lòng nhập Số thửa để tìm kiếm.');
                        return;
                    }

                    var foundFeature = null;
                    var foundLatLng = null;

                    allPointFeatures.forEach(function(f) {
                        if (foundFeature) return;
                        if (!f.geometry || f.geometry.type !== 'Point') return;

                        var name = getPointName(f);
                        var type = classifyPointFeature(f);

                        if (type === 'so_thua' && name === inputSoThua) {
                            var coords = f.geometry.coordinates;
                            foundFeature = f;
                            foundLatLng = L.latLng(coords[1], coords[0]);
                        }
                    });

                    if (foundLatLng) {
                        map.setView(foundLatLng, 20);

                        var foundLayer = null;

                        customLayer.eachLayer(function(layer) {
                            if (foundLayer) return;

                            if (layer.getBounds && layer.getBounds().contains(foundLatLng)) {
                                foundLayer = layer;
                            }
                        });

                        var soToSend = inputSoTo || getSoTo(foundFeature, foundLatLng) || DEFAULT_SO_TO || '';
                        var rawHtml = getRawBottomSheetHtml(foundFeature.properties || {}, 'Thửa ' + inputSoThua);

                        if (foundLayer) {
                            selectPlot(foundLayer, foundLayer.feature);
                        } else {
                            handleMapClick({ latlng: foundLatLng });
                        }

                        showBottomSheetLoading();
                        loadParcelInfo(inputSoThua, rawHtml, soToSend);
                    } else {
                        alert('Không tìm thấy thửa đất số ' + inputSoThua + (inputSoTo ? ' thuộc tờ số ' + inputSoTo : '') + ' trên bản đồ.');
                    }
                }

                $('#btn-search-parcel').on('click', searchAndZoomToParcel);
                $('#search-so-to, #search-so-thua').on('keypress', function(e) {
                    if (e.which === 13) {
                        searchAndZoomToParcel();
                    }
                });

                $('#search-so-to, #search-so-thua').on('focus', function() {
                    $(this).css({
                        'border-color': '#22c55e',
                        'background': 'rgba(255, 255, 255, 0.2)'
                    });
                }).on('blur', function() {
                    $(this).css({
                        'border-color': 'rgba(255, 255, 255, 0.2)',
                        'background': 'rgba(255, 255, 255, 0.1)'
                    });
                });

            } catch (e) {
                console.error('Map Init Exception:', e);
                $('#map-loader span').text('Đã xảy ra lỗi khởi tạo bản đồ.').css('color', '#ef4444');
            }
        }
    })();
</script>
