@extends('admin.layouts.index')
@section('page_title', lang('manage_home'))
@section('content')
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#005ae0',
                            600: '#004ec4',
                            700: '#003ea1',
                            800: '#1e40af',
                            900: '#1e3a8a',
                            950: '#172554',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome Icons -->
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <style>
        .pac-container {
            z-index: 9999 !important;
        }
        /* Custom scrollbar */
        body {
            font-size: 14px !important;
        }
        /* Tăng kích thước các class text nhỏ của Tailwind cho dễ đọc */
        .text-xs {
            font-size: 13px !important;   /* ~12.8px thay vì 12px */
            line-height: 1.5 !important;
        }
        .text-sm {
            font-size: 13px !important;   /* ~14.4px thay vì 14px */
            line-height: 1.6 !important;
        }
        label, input, select, textarea, button {
            font-size: 14px !important;
        }
        select option {
            font-size: 14px !important;
        }
        /* Tăng nhẹ padding input/select để dễ click hơn */
        input[type="text"],
        input[type="number"],
        input[type="tel"],
        select,
        textarea {
            font-size: 14px !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important; /* rounded-xl */
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="tel"]:focus,
        select:focus,
        textarea:focus {
            border-color: #005ae0 !important;
            outline: none !important;
            box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.1) !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #005ae0 !important;
            box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.1) !important;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        /* Animation transitions */
        .app-view {
            transition: all 0.25s ease-in-out;
        }
        .form-nav-tab.active {
            border-color: #005ae0;
            background-color: #eff6ff;
            color: #003ea1;
        }
        /* Bootstrap layout overrides for form sections */
        #property-form > div {
            margin-bottom: 20px;
        }
        #form-step-1, #form-step-2, #form-step-3 {
            margin-bottom: 20px;
        }
        .col-md-2 .bg-white {
            position: sticky;
            top: 10px;
        }
        
        /* Premium Select2 Styling overrides */
        .select2-container--default .select2-selection--single {
            background-color: #ffffff !important; /* bg-white */
            border: 1px solid #e2e8f0 !important; /* border-slate-200 */
            border-radius: 0.75rem !important; /* rounded-xl */
            height: 48px !important;
            display: flex !important;
            align-items: center !important;
            padding-left: 8px !important;
            padding-right: 8px !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: none !important;
        }
        .select2-container--default .select2-selection--single:hover {
            border-color: #cbd5e1 !important;
            background-color: #ffffff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important; /* text-slate-800 */
            font-size: 13.5px !important;
            font-weight: 600 !important;
            padding-left: 4px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            font-weight: 500 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 46px !important;
            right: 12px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #64748b transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #005ae0 !important; /* border-brand-500 */
            box-shadow: 0 0 0 4px rgba(0, 90, 224, 0.1) !important;
            background-color: #ffffff !important;
        }
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
            z-index: 999999999 !important;
            overflow: hidden !important;
            background-color: #ffffff !important;
            padding: 4px !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #005ae0 !important;
            color: #ffffff !important;
            border-radius: 0.5rem !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 12px !important;
            font-size: 13.5px !important;
            font-weight: 500 !important;
            color: #334155 !important;
            border-radius: 0.5rem !important;
            margin-bottom: 2px !important;
            transition: all 0.15s ease !important;
        }
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #eff6ff !important;
            color: #005ae0 !important;
            font-weight: 600 !important;
        }
        .select2-search--dropdown {
            padding: 6px !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 6px 10px !important;
            outline: none !important;
            font-size: 13px !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #005ae0 !important;
        }
        /* Custom utilities */
        .truncate-two-lines {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .peer:checked ~ .peer-checked\:after\:border-white::after {
            margin-top: -1.9px;
        }
        .peer ~ div::after {
            margin-top: -1.9px;
        }
           /* AI Modal Styles */
        .ai-tone-option {
            border: 1.5px solid #cbd5e1 !important;
            border-radius: 18px !important;
            padding: 22px 20px !important;
            cursor: pointer;
            transition: all 0.2s ease;
            background-color: #ffffff !important;
        }
        .ai-tone-option:hover {
            border-color: #94a3b8 !important;
            background-color: #ffffff !important;
        }
        .ai-tone-option.active {
            border-color: #0f172a !important;
            background-color: #ffffff !important;
        }
        .ai-radio-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            background-color: #ffffff;
        }
        .ai-tone-option.active .ai-radio-circle {
            border-color: #0f172a !important;
            background-color: #ffffff !important;
        }
        .ai-radio-circle-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #0f172a;
            transform: scale(0);
            transition: transform 0.2s ease;
        }
        .ai-tone-option.active .ai-radio-circle-dot {
            transform: scale(1);
        }
    </style>

    <!-- MAIN VIEW WORKSPACE -->
    <div class="flex justify-center">
      <div class="w-full md:w-10/12 lg:w-8/12 mx-auto bg-gray-100 p-4">
        <div class="card-box">

        <!-- 2. VIEW: CREATE PROPERTY -->
        <div id="view-create" class="app-view">
            <!-- Dynamic Page Title Header Section -->
            <div class="mb-6 bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between hide">
                <h2 class="text-lg font-black uppercase text-slate-800 tracking-wide flex items-center gap-2">
                    <i class="fa fa-building-o text-brand-500"></i>
                    @if(isset($home))
                        Cập nhật thông tin bất động sản: <span class="text-brand-600 font-extrabold">{{ $home->code ?? 'BĐS-'.$home->id }}</span>
                    @else
                        Tạo tin đăng bất động sản mới
                    @endif
                </h2>
                <a href="admin/manage_home/list" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
                    <i class="fa fa-long-arrow-left"></i> Quay lại danh sách
                </a>
            </div>

            <!-- 2-COLUMN LAYOUT: Left Navigation + Right Form Content -->
            <div class="row">
                
                <!-- LEFT COLUMN: Sticky Vertical Navigation Tabs (Bám dính tuyệt đối khi cuộn) -->
                <div class="col-md-3" style="position:sticky;top:75px;z-index:30;">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-col gap-2 w-full">
                        <div class="text-xl uppercase font-bold text-slate-400 tracking-wider mb-3 border-b border-slate-100 pb-3">Danh mục các bước</div>
                        
                        <!-- Tab Step 1 -->
                        <button type="button" data-scroll-target="#form-step-1" class="form-nav-tab mb-2 w-full text-left px-4 py-3.5 rounded-xl text-sm font-bold transition-all border-l-4 border-brand-500 bg-brand-50 text-brand-700 flex items-center gap-3 focus:outline-none">
                            <span class="w-6 h-6 bg-brand-500 text-white rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">1</span> Thông tin chung
                        </button>

                        <!-- Tab Step 2 -->
                        <button type="button" data-scroll-target="#form-step-2" class="form-nav-tab mb-2 w-full text-left px-4 py-3.5 rounded-xl text-sm font-bold transition-all border-l-4 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center gap-3 focus:outline-none">
                            <span class="w-6 h-6 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">2</span> Hình ảnh & Video
                        </button>

                        <!-- Tab Step 3 -->
                        <button type="button" data-scroll-target="#form-step-3" class="form-nav-tab w-full text-left px-4 py-3.5 rounded-xl text-sm font-bold transition-all border-l-4 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center gap-3 focus:outline-none">
                            <span class="w-6 h-6 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">3</span> Khác
                        </button>
                    </div>
                </div><!-- end col-md-2 -->

                <!-- RIGHT COLUMN: The main property form sections -->
                <div class="col-md-9">
                <form id="property-form" class="" onsubmit="return false;">
                    <input type="hidden" id="edit-property-id" value="{{ $id ?? '' }}">

                    <!-- BƯỚC 1: THÔNG TIN CHI TIẾT BẤT ĐỘNG SẢN & NỘI THẤT -->
                    <div id="form-step-1" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-7 scroll-mt-24">
                        <h3 class="text-xl md:text-xl font-black uppercase tracking-wider text-brand-600 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <span class="w-7 h-7 bg-brand-500 text-white rounded-full inline-flex items-center justify-center text-sm font-black">1</span>
                            Thông tin chi tiết tài sản & Nội thất
                        </h3>

                        <!-- Loại giao dịch -->
                        <div>
                            <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Nhu Cầu <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-4 max-w-lg">
                                @foreach($typeHome as $key => $value)
                                    <label class="flex items-center justify-center gap-2 py-4 px-5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-brand-500 has-[:checked]:border-brand-500 has-[:checked]:text-white font-bold text-sm">
                                        <input type="radio" name="type" value="{{ $value['id'] }}"  {{ (isset($home) && $home->type == $value['id']) ? 'checked' : ($value['id'] == 1 ? 'checked' : '') }}  class="sr-only"> {{$value['name']}}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Vị trí Địa Lý chi tiết -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3 cursor-pointer" id="address-header-toggle">
                                <h4 class="font-bold text-slate-800 text-sm uppercase tracking-wide flex items-center gap-2">
                                    <i class="fa fa-map-marker text-brand-500"></i> Địa chỉ <span class="text-rose-500">*</span>
                                </h4>
                                <i class="fa fa-chevron-up text-slate-400 transition-transform duration-200" id="address-chevron"></i>
                            </div>

                            <!-- Address Content Block -->
                            <div id="address-card-body" class="space-y-4 transition-all duration-300">
                                <!-- Placeholder when no address selected -->
                                <div id="address-placeholder" class="py-8 border-2 border-dashed border-slate-200 hover:border-brand-500 rounded-xl bg-slate-50/50 hover:bg-brand-50/10 text-center cursor-pointer transition-all flex flex-col items-center justify-center hidden">
                                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-brand-500 text-lg mb-2 border border-slate-100">
                                        <i class="fa fa-map"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Nhấp để thiết lập địa chỉ tài sản</p>
                                    <p class="text-xs text-slate-400 mt-1">Chọn vị trí bản đồ, Tỉnh/Thành, Quận/Huyện, Phường/Xã...</p>
                                </div>

                                <!-- Real Address Details (displayed when address exists) -->
                                <div id="address-details" class="flex justify-between items-start gap-4">
                                    <div class="space-y-3.5 flex-1">
                                        <!-- Địa chỉ -->
                                        <div class="space-y-1">
                                            <div class="flex items-center gap-2 text-slate-800 font-bold">
                                                <i class="fa fa-map-marker text-rose-500 text-sm"></i>
                                                <span class="text-xs uppercase tracking-wider text-slate-400">Địa chỉ</span>
                                            </div>
                                            <p id="display-new-address" class="text-sm font-bold text-slate-800 pl-5">
                                                {{ (isset($home) && $home->address) ? $home->address . ($initial_ward_name ? ', ' . $initial_ward_name : '') . ($initial_province_name ? ', ' . $initial_province_name : '') : 'Đang cập nhật...' }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Edit Button -->
                                    <button type="button" id="btn-edit-address" class="w-11 h-11 border border-slate-200 rounded-full flex items-center justify-center hover:bg-slate-50 hover:border-slate-300 transition-all text-slate-600 shadow-sm active:scale-95 focus:outline-none flex-shrink-0">
                                        <i class="fa fa-pencil text-sm"></i>
                                    </button>
                                </div>

                                <!-- Main Form Map -->
                                <div id="main-leaflet-map-container" class="space-y-2">
                                    <div id="main-leaflet-map" class="w-full h-48 rounded-xl border border-slate-200 bg-slate-50 z-10"></div>
                                </div>
                            </div>

                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" id="hidden-province-id" name="province_id" value="{{ $home->province_id ?? '' }}">
                            <input type="hidden" id="hidden-district-id" name="district_id" value="{{ $selected_district_id ?? '' }}">
                            <input type="hidden" id="hidden-ward-id" name="ward_id" value="{{ $home->ward_id ?? '' }}">
                            <input type="hidden" id="hidden-is-new-address" name="is_new_address" value="{{ $home->is_new_address ?? 1 }}">
                            <input type="hidden" id="hidden-address" name="address" value="{{ $home->address ?? '' }}">
                            <input type="hidden" id="hidden-latitude" name="latitude" value="{{ $home->latitude ?? '' }}">
                            <input type="hidden" id="hidden-longitude" name="longitude" value="{{ $home->longitude ?? '' }}">
                            <!-- Dummy input for HTML5 validation -->
                            <input type="text" id="address-validation-dummy" required value="{{ (isset($home) && $home->address) ? $home->address : '' }}" style="position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;">
                        </div>

                       
                                     <!-- Chi tiết nội thất -->
                        <div class="bg-slate-50/70 p-6 rounded-2xl border border-slate-200/60 space-y-5">
                        
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tiện ích xung quanh</label>
                                <div class="flex flex-wrap gap-3">
                                    @foreach($interior_amenities as $amenity)
                                        @php
                                            $checked = false;
                                            if (isset($home)) {
                                                $checked = $home->interior_amenities->contains('id', $amenity->id);
                                            }
                                        @endphp
                                        <label class="cursor-pointer">
                                            <input type="checkbox" name="interior_amenities[]" value="{{ $amenity->id }}" {{ $checked ? 'checked' : '' }} class="sr-only peer">
                                            <div class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-full text-slate-700 hover:border-brand-400 hover:bg-brand-50/30 transition-all peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700 peer-checked:ring-2 peer-checked:ring-brand-500 shadow-sm">
                                                @if(!empty($amenity->icon))
                                                    <img src="{{ asset('storage/' . $amenity->icon) }}" class="w-4 h-4 object-contain" alt="{{ $amenity->name }}">
                                                @endif
                                                <span class="text-sm font-semibold">{{ $amenity->name }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- THÔNG TIN CHI TIẾT TÀI SẢN & TIỆN ÍCH ĐỘNG -->
                        <div class="bg-brand-50/40 p-6 rounded-2xl border border-brand-100/70 space-y-5" style="margin-bottom: 24px;">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5 border-b border-brand-100 pb-3">
                                <i class="fa fa-info-circle text-brand-500"></i> Thông tin chi tiết bất động sản
                            </h4>
                            <!-- Loại nhà ở & Pháp lý & Trạng thái -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Loại Bất Động Sản <span class="text-red-500">*</span></label>
                                    <select name="property_type" required class="select2 w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                        <option value="">-- Chọn Loại BĐS --</option>
                                        @foreach($property_types as $pt)
                                            <option value="{{ $pt->id }}" 
                                                    {{ (isset($home) && $home->property_type == $pt->id) ? 'selected' : '' }}>
                                                {{ $pt->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                             <!-- Giá & Diện tích -->
                             <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                 <div>
                                      <label id="price-label-text" class="block text-sm font-bold text-slate-600 uppercase mb-3">Mức Giá (VNĐ){{ (isset($home) && $home->type == 2) ? '/ Tháng' : '' }} <span class="text-red-500">*</span></label>
                                      <input type="hidden" id="form-price-input-hidden" name="price" value="{{ $home->price ?? '' }}">
                                      <input type="text" id="form-price-input" value="{{ isset($home) && $home->price ? number_format($home->price, 0, ',', '.') : '' }}" required placeholder="{{ (isset($home) && $home->type == 2) ? 'Ví dụ: 12.000.000/ Tháng' : 'Ví dụ: 3.000.000.000' }}" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold text-red-600">
                                 </div>
                                 <div>
                                     <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Diện Tích (m²) <span class="text-red-500">*</span></label>
                                     <input type="number" name="area" value="{{ $home->area ?? '' }}" required placeholder="Ví dụ: 100" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold">
                                 </div>
                             </div>
                             <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 wrap-price_m2 ">
                                 <div>
                                     <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Giá/m² (VNĐ) </label>
                                     <input type="text" readonly id="form-price-m2-input" name="price_m2" value="{{ isset($home) && $home->price_m2 ? formatMoney($home->price_m2) : '' }}" placeholder="Ví dụ: 50.000.000" class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-xl text-sm focus:outline-none font-extrabold text-brand-600 cursor-not-allowed" style="background-color: #f1f5f9 !important;">
                                 </div>
                                    <div>
                                     <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Khả năng vay</label>
                                     <input type="text" name="loanability" value="{{ isset($home) && $home->loanability ? ($home->loanability) : '' }}" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold text-brand-600">
                                 </div>
                             </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 wrap-plot_land {{ !empty($home) && $home->type == 1 ? '' : (empty($home) ? '' : 'hide') }}">
                                  <div>
                                     <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Thửa đất số <span class="plot-land-asterisk text-red-500 {{ (isset($home) && $home->type == 2) ? 'hidden' : '' }}">*</span></label>
                                     <input type="number" name="plot_land" value="{{ $home->plot_land ?? '' }}" {{ (!isset($home) || $home->type == 1) ? 'required' : '' }} placeholder="Ví dụ: 100" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold">
                                 </div>
                                 <div>
                                     <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tờ bản đồ <span class="number-sheets-asterisk text-red-500 {{ (isset($home) && $home->type == 2) ? 'hidden' : '' }}">*</span></label>
                                     <input type="number" name="number_sheets" value="{{ $home->number_sheets ?? '' }}" {{ (!isset($home) || $home->type == 1) ? 'required' : '' }} placeholder="Ví dụ: 100" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold">
                                 </div>
                             </div>
                            <div class="grid grid-cols-1 sm:grid-cols-1 gap-6 wrap-currently_rent {{ !empty($home) && $home->type == 1 ? '' : (empty($home) ? '' : 'hide') }}">
                                 <div>
                                     <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Đơn giá đang cho thuê / Tháng (VNĐ)</label>
                                     <input type="text" id="form-currently-rent-input" onkeyup="formatNumBerKeyChange(this)" name="currently_rent" value="{{ isset($home) && $home->currently_rent ? formatMoney($home->currently_rent) : '' }}" placeholder="Ví dụ: 50.000.000" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold text-brand-600">
                                 </div>
                             </div>

                             <!-- Tiện ích động (Chi tiết bất động sản) -->
                             <div id="dynamic-utilities-wrapper" style="display: none; border-top: 1px solid #e2e8f0; padding-top: 20px;" class="space-y-5">
                                 <h5 class="text-sm font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                                     <i class="fa fa-list-alt text-brand-500"></i> Các thông số bổ sung
                                 </h5>
                                 <div id="dynamic-utilities-container" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                     <!-- JS will render utility inputs here -->
                                 </div>
                             </div>
                         </div>
            
                         <!-- TIÊU ĐỀ & MÔ TẢ TIN ĐĂNG -->
                         <div class="bg-amber-50/40 p-6 rounded-2xl border border-amber-100/80 space-y-5" style="margin-bottom: 24px;">
                             <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide flex items-center justify-between border-b border-amber-200 pb-3">
                                <span class="flex items-center gap-1.5">
                                     <i class="fa fa-pencil-square-o text-amber-600"></i> Tiêu đề & Nội dung tin đăng
                                 </span>
                                  <button type="button" id="btn-generate-ai-desc" class="bg-white hover:bg-slate-50 rounded-full font-bold shadow-sm transition-all flex items-center gap-2 normal-case cursor-pointer border" style="font-size: 15px !important; padding: 7px 18px !important; border: 1px solid #cbd5e1 !important; outline: none !important;">
                                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-purple-600 animate-pulse">
                                         <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/>
                                         <path d="m5 3 1 2.5L8.5 6 6 7 5 9.5 4 7 1.5 6 4 5Z"/>
                                         <path d="m19 17 1 2.5 2.5.5-2.5 1-1 2.5-1-2.5-2.5-1 2.5-1Z"/>
                                     </svg>
                                     <span class="bg-gradient-to-r from-purple-600 via-pink-500 to-blue-600 bg-clip-text text-transparent font-bold">Tạo với AI</span>
                                 </button>
                             </h4>
                             <div>
                                 <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tiêu Đề Tin Đăng <span class="text-red-500">*</span></label>
                                 <input type="text" id="form-title-input" name="title" value="{{ $home->title ?? '' }}" required placeholder="Ví dụ: Bán căn hộ 1PN, 1WC tại The Queen Villas, 3 tỷ VND, 100m2..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-semibold">
                             </div>
                             <div>
                                 <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Nội dung video <span class="text-red-500">*</span></label>
                                 <textarea name="detail" required rows="3" placeholder="Nội dung ngắn video" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 leading-relaxed">{{ $home->detail ?? '' }}</textarea>
                             </div>
                             <div>
                                 <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Mô Tả Chi Tiết Nội Dung <span class="text-red-500">*</span></label>
                                @php
    $descriptionValue = old('description', $home->description ?? '');

    // Nếu dữ liệu là text có xuống dòng thật (không chứa tag HTML), đổi sang <br> để editor hiểu
    if ($descriptionValue === strip_tags($descriptionValue)) {
        $descriptionValue = nl2br(e($descriptionValue));
    }
@endphp
                                 <textarea name="description" required rows="13" placeholder="Mô tả chi tiết về:
• Loại hình bất động sản
• Vị trí
• Diện tích, tiện ích
• Tình trạng nội thất
 ...
(VD: Khu nhà có vị trí thuận lợi, gần công viên, trường học...)" class="editor w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 leading-relaxed">{!! $descriptionValue !!}</textarea>
                             </div>
                         </div>
                    </div>

                    <!-- BƯỚC 2: HÌNH ẢNH & VIDEO (Tối thiểu 3 ảnh + Video Đại diện 9:16) -->
                    <div id="form-step-2" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-7 scroll-mt-24">
                        <h3 class="text-xl md:text-xl font-black uppercase tracking-wider text-brand-600 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <span class="w-7 h-7 bg-brand-500 text-white rounded-full inline-flex items-center justify-center text-sm font-black">2</span>
                            Tải lên hình ảnh & video bất động sản
                        </h3>

                        <!-- Checklists status indicator row -->
                        <div class="flex flex-col sm:flex-row gap-3 sm:items-center text-sm">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
                                <div id="status-img-count" class="w-4 h-4 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-xs font-black">
                                    <i class="fa fa-times"></i>
                                </div>
                                <span class="text-slate-600 font-bold">Đăng tối thiểu 3 ảnh (Hiện tại: <span id="photo-current-count">0</span>)</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
                                <div id="status-video-state" class="w-4 h-4 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-xs font-black">
                                    <i class="fa fa-exclamation"></i>
                                </div>
                                <span class="text-slate-500 font-medium">Khuyến khích tải video đại diện (Dọc 9:16)</span>
                            </div>
                        </div>

                        <!-- 2-COLUMN MEDIA WORKSPACE: Left 9:16 Video Upload Panel + Right Standard Photo Area -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            
                            <!-- LEFT COLUMN: 9:16 Aspect Ratio Video Block (Col span 4) -->
                            <div class="lg:col-span-4 flex flex-col items-center">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2 text-center block">Video đại diện (9:16)</span>
                                
                                <!-- The vertical viewport card container -->
                                <div class="w-full max-w-[200px] aspect-[9/16] relative bg-slate-950 rounded-2xl overflow-hidden border-2 border-slate-200 shadow-lg group flex items-center justify-center">
                                
                                    <!-- Real video element -->
                                    <video id="form-video-element" class="hidden absolute inset-0 w-full h-full object-cover z-10" loop muted playsinline></video>

                                    <!-- Preview Mockup Overlay Viewfinder -->
                                    <div id="video-upload-preview" class="hidden absolute inset-0 z-20 flex flex-col justify-between p-3 bg-black/30 pointer-events-none">
                                        <div class="flex items-center justify-between text-xs text-white/90">
                                            <div class="flex items-center gap-1">
                                                <span class="w-2 h-2 rounded-full bg-red-600 animate-pulse"></span>
                                                <span>REC</span>
                                            </div>
                                            <div class="font-mono text-right">00:15 / 9:16</div>
                                        </div>
                                        <!-- Viewfinder Crosshairs inside 9:16 format -->
                                        <div class="absolute inset-x-8 top-[40%] bottom-[40%] border-l border-r border-white/20 flex items-center justify-center">
                                            <div class="w-4 h-[1px] bg-white/20"></div>
                                        </div>
                                        <div class="text-xs text-white/80 bg-black/40 p-1.5 rounded backdrop-blur-[2px]">
                                            <p class="font-bold truncate"><i class="fa fa-play-circle-o mr-1"></i>Xem thử video dọc thành công</p>
                                        </div>
                                    </div>

                                    <!-- Simulated click placeholder upload panel -->
                                    <div id="video-upload-placeholder" class="z-10 p-4 text-center flex flex-col items-center justify-center space-y-3 cursor-pointer">
                                        <div class="w-12 h-12 bg-slate-900 text-brand-500 rounded-full flex items-center justify-center border border-slate-800 shadow">
                                            <i class="fa fa-video-camera text-base"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-300">Nhấp để tải lên</p>
                                            <p class="text-xs text-slate-500 mt-1 leading-normal">Tỉ lệ dọc chuẩn 9:16 (MP4 / MOV)</p>
                                        </div>
                                    </div>

                                    <!-- Delete overlay button on top right -->
                                    <button type="button" id="btn-delete-video" class="hidden absolute top-2 right-2 w-7 h-7 bg-red-600 hover:bg-red-700 active:scale-95 text-white rounded-full flex items-center justify-center z-30 shadow shadow-black/30 transition-all focus:outline-none">
                                        <i class="fa fa-trash-o text-sm"></i>
                                    </button>
                                </div>

                                <!-- Hidden native video file input -->
                                <input type="file" id="real-video-input" accept="video/mp4, video/quicktime" class="hidden">
                            </div>

                            <!-- RIGHT COLUMN: Photo Grid & Photo upload actions (Col span 8) -->
                            <div class="lg:col-span-8 space-y-4">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-wider block">Hình ảnh thực tế tài sản (Tối thiểu 3 ảnh)</span>
                                
                                <!-- Drag and drop photo trigger area -->
                                <div id="trigger-upload-area" class="p-8 border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl bg-slate-50/50 hover:bg-brand-50/10 text-center cursor-pointer transition-all flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-brand-500 text-lg mb-3 border border-slate-100">
                                        <i class="fa fa-cloud-upload"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Bấm vào đây để tải lên hình ảnh (<span id="count-media-txt">0</span>/24)</p>
                                    <p class="text-xs text-slate-400 mt-1">Định dạng hỗ trợ: PNG, JPG, JPEG, GIF. Đăng ít nhất 3 ảnh sắc nét.</p>
                                </div>

                                <!-- Media loading dynamic spinner -->
                                <div id="form-upload-loader" class="hidden p-4 bg-brand-50 border border-brand-100 rounded-2xl text-sm text-brand-800 flex items-center justify-center gap-2">
                                    <i class="fa fa-circle-o-notch animate-spin text-brand-500"></i> Đang tải dữ liệu hình ảnh lên hệ thống...
                                </div>

                                <!-- Uploaded Previews grid -->
                                <div id="form-media-list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    <!-- Filled dynamically via Javascript -->
                                </div>

                                <!-- Hidden native images input -->
                                <input type="file" id="real-images-input" accept="image/*" multiple class="hidden">
                            </div>
                        </div>

                             <!-- HỒ SƠ PHÁP LÝ: SỔ ĐỎ/HỒNG & GIẤY TỜ KHÁC (Chỉ hiện khi là Mua bán) -->
                        <div id="legal-documents-upload-wrapper" class="space-y-6 border-t border-slate-100 pt-6 hidden">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5 pb-2">
                                <i class="fa fa-file-text-o text-brand-500"></i> Hồ sơ pháp lý bất động sản (Bắt buộc)
                            </h4>
                            <div id="legal-uploads-grid" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 items-start">
                                <!-- CỘT 1: Giấy tờ sổ đỏ, hồng -->
                                <div class="space-y-4 md:contents">
                                    <div class="md:col-start-1 md:row-start-1">
                                        <span class="text-sm font-bold text-slate-600 uppercase tracking-wide block">Giấy tờ sổ đỏ, sổ hồng</span>
                                        
                                        <div class="flex items-center gap-7 mt-3.5">
                                            <label class="flex items-center gap-2.5 cursor-pointer text-base font-bold text-slate-700 mb-0">
                                                <input type="radio" name="legal_status" value="1" {{ (!isset($home) || empty($home->legal_status) || $home->legal_status == 1) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 border-slate-300 focus:ring-brand-500">
                                                <span>Có sổ</span>
                                            </label>
                                            <label class="flex items-center gap-2.5 cursor-pointer text-base font-bold text-slate-700 mb-0">
                                                <input type="radio" name="legal_status" value="2" {{ (isset($home) && $home->legal_status == 2) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 border-slate-300 focus:ring-brand-500">
                                                <span>Không sổ</span>
                                            </label>
                                            <label class="flex items-center gap-2.5 cursor-pointer text-base font-bold text-slate-700 mb-0">
                                                <input type="radio" name="legal_status" value="3" {{ (isset($home) && $home->legal_status == 3) ? 'checked' : '' }} class="w-5 h-5 text-brand-600 border-slate-300 focus:ring-brand-500">
                                                <span>Chưa rõ</span>
                                            </label>
                                        </div>
                                        <div id="legal-warning-banner" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-800 font-semibold flex items-center gap-2 mt-2">
                                            <i class="fa fa-exclamation-triangle text-amber-500"></i>
                                            <span>Lượt khách sẽ xem nhiều nếu bài đăng có thông tin sổ</span>
                                        </div>
                                    </div>
                                    
                                    <div class="md:col-start-1 md:row-start-2 mt-4 md:mt-0 md:self-stretch">
                                        <!-- Drag and drop trigger area -->
                                        <div id="trigger-red-book-upload" class="h-full p-6 border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl bg-slate-50/50 hover:bg-brand-50/10 text-center cursor-pointer transition-all flex flex-col items-center justify-center">
                                            <div class="w-10 h-10 bg-white rounded-2xl shadow-sm flex items-center justify-center text-brand-500 text-base mb-2 border border-slate-100">
                                                <i class="fa fa-cloud-upload"></i>
                                            </div>
                                            <p class="text-xs font-bold text-slate-800">Tải lên ảnh/file sổ đỏ, sổ hồng (<span id="count-red-book-txt">0</span>)</p>
                                            <p class="text-[11px] text-slate-400 mt-1">Định dạng hỗ trợ: PNG, JPG, JPEG, PDF. Tải tối thiểu 1 ảnh/file.</p>
                                        </div>
                                    </div>

                                    <div class="md:col-start-1 md:row-start-3 mt-4 md:mt-0">
                                        <!-- Previews grid -->
                                        <div id="red-book-media-list" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <!-- Filled dynamically via Javascript -->
                                        </div>

                                        <!-- Hidden native input -->
                                        <input type="file" id="real-red-book-input" accept="image/*, application/pdf" multiple class="hidden">
                                    </div>
                                </div>

                                <!-- CỘT 2: Giấy tờ khác -->
                                <div class="space-y-4 md:contents">
                                    <div class="md:col-start-2 md:row-start-1">
                                        <span class="text-sm font-bold text-slate-600 uppercase tracking-wide block">Giấy tờ khác</span>
                                    </div>
                                    
                                    <div class="md:col-start-2 md:row-start-2 mt-4 md:mt-0 md:self-stretch">
                                        <!-- Drag and drop trigger area -->
                                        <div id="trigger-other-doc-upload" class="h-full p-6 border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl bg-slate-50/50 hover:bg-brand-50/10 text-center cursor-pointer transition-all flex flex-col items-center justify-center">
                                            <div class="w-10 h-10 bg-white rounded-2xl shadow-sm flex items-center justify-center text-brand-500 text-base mb-2 border border-slate-100">
                                                <i class="fa fa-cloud-upload"></i>
                                            </div>
                                            <p class="text-xs font-bold text-slate-800">Tải lên giấy tờ khác liên quan (<span id="count-other-doc-txt">0</span>)</p>
                                            <p class="text-[11px] text-slate-400 mt-1">Định dạng hỗ trợ: PNG, JPG, JPEG, PDF. Tải tối thiểu 1 ảnh/file.</p>
                                        </div>
                                    </div>

                                    <div class="md:col-start-2 md:row-start-3 mt-4 md:mt-0">
                                        <!-- Previews grid -->
                                        <div id="other-doc-media-list" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <!-- Filled dynamically via Javascript -->
                                        </div>

                                        <!-- Hidden native input -->
                                        <input type="file" id="real-other-doc-input" accept="image/*, application/pdf" multiple class="hidden">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- BƯỚC 3: ĐỊA CHỈ LIÊN HỆ ĐĂNG TIN -->
                    <div id="form-step-3" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-7 scroll-mt-24">
                        <h3 class="text-xl md:text-xl font-black uppercase tracking-wider text-brand-600 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <span class="w-7 h-7 bg-brand-500 text-white rounded-full inline-flex items-center justify-center text-sm font-black">3</span>
                            Thông tin khác
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                                <input type="tel" name="contact_phone" value="{{ $home->contact_phone ?? '' }}" required placeholder="Ví dụ: 090xxxxxxx" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tên người liên hệ <span class="text-red-500">*</span></label>
                                <input type="text" name="contact_name" value="{{ $home->contact_name ?? '' }}" required placeholder="Họ và tên người đại diện" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Email liên hệ</label>
                                <input type="text" name="email_phone" value="{{ $home->email_phone ?? '' }}" placeholder="Ví dụ: email@gmail.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Mức hoa hồng (%) <span class="text-red-500">*</span></label>
                                <input type="text" name="commission_rate"  value="{{ $home->commission_rate ?? '' }}" required placeholder="" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500 utility-number-input">
                            </div>
                        </div>
                        <h3 class="text-xl md:text-xl font-black uppercase tracking-wider text-brand-600 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <span class="w-7 h-7 bg-brand-500 text-white rounded-full inline-flex items-center justify-center text-sm font-black">4</span>
                            Thời gian đăng tin
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tin nổi bật</label>
                                <div class="flex items-center gap-3 mt-2">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_featured" value="1" {{ (isset($home) && $home->is_featured) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="relative w-14 h-8 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-brand-500"></div>
                                    </label>
                                    <span class="text-sm font-semibold text-slate-600">Kích hoạt tin nổi bật</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Ngày bắt đầu <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" value="{{ isset($home) && $home->start_date ? $home->start_date : date('Y-m-d') }}" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Ngày kết thúc <span class="text-red-500">*</span></label>
                                <input type="date" name="end_date" value="{{ $home->end_date ?? '' }}" required class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                        </div>
                        <h3 class="text-xl md:text-xl font-black uppercase tracking-wider text-brand-600 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <span class="w-7 h-7 bg-brand-500 text-white rounded-full inline-flex items-center justify-center text-sm font-black">5</span>
                            Người đăng
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Vai trò <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center justify-center gap-1 py-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-brand-500 has-[:checked]:border-brand-500 has-[:checked]:text-white text-sm font-bold">
                                        <input type="radio" name="contact_role" value="1" {{ (!isset($home) || $home->contact_role == '1') ? 'checked' : '' }} class="sr-only"> Nhân viên Sale
                                    </label>
                                    <label class="flex items-center justify-center gap-1 py-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-brand-500 has-[:checked]:border-brand-500 has-[:checked]:text-white text-sm font-bold">
                                        <input type="radio" name="contact_role" value="2" {{ (isset($home) && $home->contact_role == '2') ? 'checked' : '' }} class="sr-only"> Admin
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Chọn người đăng</label>
                                <select name="customer_id" id="select-customer" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    @if(!empty($selected_client))
                                        <option value="{{ $selected_client['id'] }}" selected>{{ $selected_client['fullname'] }} - {{ $selected_client['phone'] }}</option>
                                    @else
                                        <option value="">-- Chọn người đăng --</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                       
                    </div>
                        <!-- Form submission action row -->
                        <div class="sticky bottom-4 bg-white/95 backdrop-blur-sm border border-slate-200 p-4 rounded-3xl flex items-center justify-center gap-4 z-40 shadow-[0_-8px_30px_rgb(0,0,0,0.08)] mt-6">
                            <a href="admin/manage_home/list" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-sm rounded-xl transition-all flex items-center gap-2">
                                Hủy bỏ
                            </a>
                            <button type="button" id="btn-save-property" class="px-10 py-3.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-black text-sm rounded-xl shadow-md shadow-brand-500/20 transition-all flex items-center gap-2">
                                <i class="fa fa-save"></i> LƯU LẠI
                            </button>
                        </div>
                    </form>
                </div><!-- end col-md-10 -->
            </div><!-- end row -->
        </div><!-- end view-create -->

        </div><!-- end card-box -->
      </div><!-- end col-sm-12 -->
    </div><!-- end row -->

     <!-- AI MODAL SYSTEM -->
    <div id="ai-modal-wrapper" class="fixed inset-0 bg-slate-950/60 z-[2020] flex items-center justify-center p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-6xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-100 transform scale-95 opacity-0 transition-all duration-300 overflow-hidden">
            <!-- Header -->
            <div class="bg-slate-900 px-6 py-4 flex justify-between items-center text-white">
                <h4 class="font-extrabold text-base flex items-center gap-2 text-white" style="color: white !important; margin: 0;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="text-purple-400">
                        <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"/>
                        <path d="m5 3 1 2.5L8.5 6 6 7 5 9.5 4 7 1.5 6 4 5Z"/>
                        <path d="m19 17 1 2.5 2.5.5-2.5 1-1 2.5-1-2.5-2.5-1 2.5-1Z"/>
                    </svg>
                    Tạo với AI
                </h4>
                <button type="button" id="ai-modal-close" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 rounded-full transition-all focus:outline-none" style="background: none; border: none;">
                    <i class="fa fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 space-y-5 overflow-y-auto flex-1 scrollbar-thin">
                <div class="space-y-1">
                    <p class="text-sm font-semibold text-slate-700 mb-0" style="font-size: 14px !important; margin-bottom: 0;">Chọn một giọng điệu bên dưới, sau đó hệ thống sẽ tự động tạo nội dung Tiêu đề và Mô tả tương ứng</p>
                </div>
                
                <!-- Options -->
                <div class="space-y-4">
                    <!-- Option 1: Lịch sự -->
                    <div class="ai-tone-option flex flex-col shadow-sm select-none" data-tone="polite" style="padding: 20px !important;">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-xs font-bold border border-emerald-100" style="font-size: 12px !important;">
                                    <i class="fa fa-briefcase"></i> Lịch sự
                                </span>
                            </div>
                            <div class="ai-radio-circle">
                                <input type="radio" name="ai_tone" value="polite" class="sr-only">
                                <div class="ai-radio-circle-dot"></div>
                            </div>
                        </div>
                        
                        <!-- Content Preview Area -->
                        <div class="ai-preview-container hidden mt-4 pt-4 border-t border-slate-100 flex flex-col">
                            <!-- Loading state -->
                            <div class="ai-loading-state hidden py-6 flex flex-col items-center justify-center gap-2">
                                <i class="fa fa-spinner fa-spin text-2xl text-purple-600"></i>
                                <span class="text-xs text-slate-500 font-semibold">Đang viết nội dung bằng AI...</span>
                            </div>
                            
                            <!-- Generated Result -->
                            <div class="ai-result-content">
                                <h5 class="font-bold text-slate-900 mb-3 ai-preview-title leading-snug" style="font-size: 16px !important; font-weight: 700 !important; line-height: 1.5 !important;"></h5>
                                <div class="text-slate-700 leading-relaxed whitespace-pre-wrap ai-preview-description" style="font-size: 14px !important; line-height: 1.6 !important; margin-bottom: 8px;"></div>
                                
                                <div class="text-left">
                                    <button type="button" class="text-xs font-bold text-slate-500 hover:text-slate-700 underline ai-preview-collapse border-none bg-transparent cursor-pointer p-0" style="border: none !important; background: transparent !important; text-decoration: underline !important; font-size: 13px !important;">Thu gọn</button>
                                </div>
                            </div>
                            
                            <!-- Rewrite button -->
                            <button type="button" class="w-full mt-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-full transition-all flex items-center justify-center gap-2 ai-rewrite-btn" style="border: 1px solid #cbd5e1 !important; border-radius: 9999px !important; height: 46px; font-size: 14px !important; color: #1e293b !important; cursor: pointer;">
                                <i class="fa fa-refresh text-purple-600" style="color: #8b5cf6 !important; font-weight: bold;"></i> Viết lại
                            </button>
                        </div>
                    </div>

                    <!-- Option 2: Trẻ trung -->
                    <div class="ai-tone-option flex flex-col shadow-sm select-none" data-tone="youthful" style="padding: 20px !important;">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-orange-50 text-orange-600 rounded-full text-xs font-bold border border-orange-100" style="font-size: 12px !important;">
                                    <i class="fa fa-smile-o"></i> Trẻ trung
                                </span>
                            </div>
                            <div class="ai-radio-circle">
                                <input type="radio" name="ai_tone" value="youthful" class="sr-only">
                                <div class="ai-radio-circle-dot"></div>
                            </div>
                        </div>
                        
                        <!-- Content Preview Area -->
                        <div class="ai-preview-container hidden mt-4 pt-4 border-t border-slate-100 flex flex-col">
                            <!-- Loading state -->
                            <div class="ai-loading-state hidden py-6 flex flex-col items-center justify-center gap-2">
                                <i class="fa fa-spinner fa-spin text-2xl text-purple-600"></i>
                                <span class="text-xs text-slate-500 font-semibold">Đang viết nội dung bằng AI...</span>
                            </div>
                            
                            <!-- Generated Result -->
                            <div class="ai-result-content">
                                <h5 class="font-bold text-slate-900 mb-3 ai-preview-title leading-snug" style="font-size: 16px !important; font-weight: 700 !important; line-height: 1.5 !important;"></h5>
                                <div class="text-slate-700 leading-relaxed whitespace-pre-wrap ai-preview-description" style="font-size: 14px !important; line-height: 1.6 !important; margin-bottom: 8px;"></div>
                                
                                <div class="text-left">
                                    <button type="button" class="text-xs font-bold text-slate-500 hover:text-slate-700 underline ai-preview-collapse border-none bg-transparent cursor-pointer p-0" style="border: none !important; background: transparent !important; text-decoration: underline !important; font-size: 13px !important;">Thu gọn</button>
                                </div>
                            </div>
                            
                            <!-- Rewrite button -->
                            <button type="button" class="w-full mt-4 py-2.5 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold text-sm rounded-full transition-all flex items-center justify-center gap-2 ai-rewrite-btn" style="border: 1px solid #cbd5e1 !important; border-radius: 9999px !important; height: 46px; font-size: 14px !important; color: #1e293b !important; cursor: pointer;">
                                <i class="fa fa-refresh text-purple-600" style="color: #8b5cf6 !important; font-weight: bold;"></i> Viết lại
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 px-6 py-4 flex justify-end items-center border-t border-slate-100" style="display: flex !important; justify-content: flex-end !important; align-items: center !important;">
                <button type="button" id="ai-use-btn" class="px-7 py-2.5 text-white font-extrabold text-sm shadow-md hover:bg-[#a10b14] active:scale-95 transition-all flex items-center gap-2" style="background-color: #c20e1a; border: none; display: none; align-items: center; border-radius: 9999px !important; cursor: pointer; height: 46px; font-size: 15px !important;">
                    Sử dụng
                </button>
            </div>
        </div>
    </div>


    <!-- CUSTOM CONFIRM MODAL SYSTEM (Xác nhận an toàn, không block ứng dụng) -->
    <div id="confirm-modal-wrapper" class="fixed inset-0 bg-slate-950/60 z-[2010] flex items-center justify-center p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl border border-slate-100 transform scale-95 opacity-0 transition-all duration-300 space-y-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-lg shadow-sm">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-slate-900 text-base">Xác nhận xóa tài sản?</h4>
                <p class="text-sm text-slate-500 mt-1 leading-relaxed">Hành động này sẽ xóa tin đăng này vĩnh viễn khỏi cơ sở dữ liệu hệ thống. Bạn không thể hoàn tác thao tác này.</p>
            </div>
            <div class="flex items-center gap-2 pt-2 justify-end">
                <button type="button" id="confirm-modal-cancel" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all">
                    Hủy bỏ
                </button>
                <button type="button" id="confirm-modal-submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-sm rounded-xl shadow shadow-rose-600/20 transition-all">
                    Đồng ý xóa
                </button>
            </div>
        </div>
    </div>

    <!-- ADDRESS CONFIRMATION MODAL -->
    <div id="address-modal-wrapper" class="fixed inset-0 bg-slate-950/60 z-[2000] flex items-center justify-center p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-7xl w-full flex flex-col max-h-[92vh] shadow-2xl border border-slate-100 transform scale-95 opacity-0 transition-all duration-300">
            <!-- Header -->
            <div class="bg-white px-6 py-5 border-b border-slate-100 flex justify-between items-center rounded-t-3xl">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-sm shadow-sm border border-brand-100">
                        <i class="fa fa-map"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-base leading-tight">Thiết lập địa chỉ tài sản</h4>
                        <p class="text-xs text-slate-400 font-medium">Xác thực tọa độ bản đồ & thông tin hành chính</p>
                    </div>
                </div>
                <button type="button" id="address-modal-close" class="w-9 h-9 flex items-center justify-center text-slate-400 hover:text-slate-800 hover:bg-slate-100 rounded-full transition-all focus:outline-none">
                    <i class="fa fa-times text-lg"></i>
                </button>
            </div>
            
            <!-- Body -->
            <div class="p-6 overflow-y-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left Column: Fields & Previews (lg:col-span-7) -->
                    <div class="lg:col-span-7 space-y-5">
                        <!-- Fields: Province, District (hidden), Ward, Detail -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 flex items-center gap-1">
                                    Tỉnh/Thành phố <span class="text-rose-500">*</span>
                                </label>
                                <select id="select-province" class="select2 w-full">
                                    <option value="">-- Chọn Tỉnh/Thành --</option>
                                </select>
                            </div>

                            <div id="district-wrapper" class="hidden">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 flex items-center gap-1">
                                    Quận/Huyện <span class="text-rose-500">*</span>
                                </label>
                                <select id="select-district" class="select2 w-full">
                                    <option value="">-- Chọn Quận/Huyện --</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 flex items-center gap-1">
                                    Phường/Xã <span class="text-rose-500">*</span>
                                </label>
                                <select id="select-ward" class="select2 w-full">
                                    <option value="">-- Chọn Phường/Xã --</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1.5 flex items-center gap-1">
                                    Địa chỉ chi tiết <span class="text-xs text-slate-400 font-normal lowercase">(số nhà, ngõ hẻm, tên đường, khu phố...)</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="modal-input-detail-address" placeholder="Ví dụ: Số 123 Đường Nguyễn Trãi, Hẻm 45..." class="w-full pl-4 pr-10 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 font-medium text-slate-800 transition-all">
                                    <i class="fa fa-pencil-square-o absolute right-3.5 top-[15px] text-slate-400"></i>
                                </div>
                            </div>
                        </div>
                                            
                        <!-- Display Address on Post Section -->
                        <div class="space-y-3.5 pt-4 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Địa chỉ hiển thị trên tin đăng <span class="text-rose-500">*</span></label>

                            <div class="p-3.5 bg-emerald-50/50 border border-emerald-200 rounded-2xl flex items-start gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0 text-xs">
                                    <i class="fa fa-map-marker"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-wider block leading-none mb-1">Địa chỉ</span>
                                    <span id="modal-display-new-address" class="text-xs font-bold text-emerald-800 block truncate-two-lines leading-normal">Đang cập nhật...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Maps Section (lg:col-span-5) -->
                    <div class="lg:col-span-5 flex flex-col space-y-4 lg:border-l lg:border-slate-100 lg:pl-6">
                        <div class="space-y-2 flex-1 flex flex-col">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Vị trí chính xác trên bản đồ</label>
                                <button type="button" id="modal-map-reset" class="px-2.5 py-1 text-xs font-bold text-brand-600 hover:text-brand-700 bg-brand-50 hover:bg-brand-100 rounded-lg transition-all focus:outline-none flex items-center gap-1 border border-brand-100">
                                    <i class="fa fa-refresh text-[10px]"></i> Đặt lại
                                </button>
                            </div>
                            <span class="text-xs text-slate-400 block leading-tight">Bạn có thể di chuyển ghim màu đỏ đến đúng vị trí của bất động sản.</span>
                            
                            <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-sm z-10 flex-1 min-h-[320px] lg:min-h-[500px] mt-2">
                                <div id="modal-leaflet-map" class="absolute inset-0 bg-slate-100 z-10"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-slate-50 rounded-b-3xl">
                <button type="button" id="address-modal-cancel" class="px-5 py-2.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-sm rounded-xl transition-all shadow-sm active:scale-95">
                    Hủy bỏ
                </button>
                <button type="button" id="address-modal-submit" class="px-7 py-2.5 bg-brand-500 hover:bg-brand-600 text-white font-extrabold text-sm rounded-xl shadow-md shadow-brand-500/10 hover:shadow-brand-500/20 transition-all active:scale-95">
                    Xác nhận vị trí
                </button>
            </div>
        </div>
    </div>

    <!-- NOTIFICATION SYSTEM TOASTS CONTAINER -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-2 pointer-events-none"></div>
@endsection

@section('script')
    @if(!empty($google_api_key))
        <script src="https://maps.googleapis.com/maps/api/js?key={{ $google_api_key }}&libraries=places"></script>
    @endif
    <script>
        // System Address data
        const provincesOld = @json($provinces_old);
        const provincesNew = @json($provinces_new);
        let homeIsNewAddress = @json(isset($home) ? $home->is_new_address : 0);
        let homeProvinceId = @json(isset($home) ? $home->province_id : null);
        let homeWardId = @json(isset($home) ? $home->ward_id : null);
        let homeDistrictId = @json($selected_district_id ?? null);
        let loadedInitialValues = false;

        const apiToken = "{{ $apiToken }}";
        const apiHeaders = {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        };
        if (apiToken) {
            apiHeaders['Authorization'] = 'Bearer ' + apiToken;
        }

        // System utilities data
        const propertyTypesUtilities = @json($property_types);
        const homeUtilities = @json(isset($home) && $home->utilities ? $home->utilities->pluck('pivot.value', 'id') : new \stdClass());

        // System Data store loaded from Backend
        let uploadedFormVideo = @json(isset($home) ? $home->video_url : '');
        let selectedVideoFile = null; // Stores new File object if selected

        // Unified media items array containing both existing and new files
        let mediaItems = [];
        
        // Initialize with existing media from tbl_home_media
        const initialMediaItems = @json(isset($home) && $home->media_items ? $home->media_items : []);
        
        initialMediaItems.forEach((item) => {
            mediaItems.push({
                type: 'existing',
                url: item.url,
                caption: item.caption || ""
            });
        });

        // Unified red book and other documents arrays containing both existing and new files
        let redBookItems = [];
        let otherDocItems = [];

        const initialRedBook = @json(isset($home) && $home->documents_red ? $home->documents_red->pluck('url')->values() : []);
        const initialOtherDocs = @json(isset($home) && $home->documents_other ? $home->documents_other->pluck('url')->values() : []);

        initialRedBook.forEach((url) => {
            redBookItems.push({
                type: 'existing',
                url: url
            });
        });

        initialOtherDocs.forEach((url) => {
            otherDocItems.push({
                type: 'existing',
                url: url
            });
        });


        // Declare globally-accessible functions first
        window.updateFormCaptionValue = function(idx, val) {
            mediaItems[idx].caption = val;
        };

        window.deleteFormPhoto = function(idx) {
            let item = mediaItems[idx];
            if (item.type === 'new' && item.url.startsWith('blob:')) {
                URL.revokeObjectURL(item.url);
            }
            mediaItems.splice(idx, 1);
            renderFormMediaThumbnails();
            checkMinPhotoCriteria();
            showToast("Đã gỡ bỏ hình ảnh!", "info");
        };

        window.deleteRedBookPhoto = function(idx) {
            let item = redBookItems[idx];
            if (item.type === 'new' && item.url.startsWith('blob:')) {
                URL.revokeObjectURL(item.url);
            }
            redBookItems.splice(idx, 1);
            renderRedBookThumbnails();
            showToast("Đã gỡ bỏ tài liệu sổ đỏ, sổ hồng!", "info");
        };

        window.deleteOtherDocPhoto = function(idx) {
            let item = otherDocItems[idx];
            if (item.type === 'new' && item.url.startsWith('blob:')) {
                URL.revokeObjectURL(item.url);
            }
            otherDocItems.splice(idx, 1);
            renderOtherDocThumbnails();
            showToast("Đã gỡ bỏ tài liệu khác!", "info");
        };

        window.removeToast = function(id) {
            $(`#${id}`).removeClass("translate-x-0 opacity-100").addClass("translate-x-12 opacity-0");
            setTimeout(() => {
                $(`#${id}`).remove();
            }, 300);
        };

        // Custom Notification Toast Generator
        function showToast(message, type = "success") {
            const container = $("#toast-container");
            const toastId = "toast-" + Math.floor(Math.random() * 1000);
            
            let bgClass = "bg-white/90 backdrop-blur-md border-slate-200 text-slate-800";
            let icon = '<i class="fa fa-info-circle text-blue-500 text-sm"></i>';
            
            if (type === "success") {
                bgClass = "bg-emerald-50/90 backdrop-blur-md border-emerald-200 text-emerald-900";
                icon = '<i class="fa fa-check-circle text-emerald-500 text-sm"></i>';
            } else if (type === "warning") {
                bgClass = "bg-amber-50/90 backdrop-blur-md border-amber-200 text-amber-900";
                icon = '<i class="fa fa-circle-o-notch animate-spin text-amber-500 text-sm"></i>';
            } else if (type === "error") {
                bgClass = "bg-rose-50/90 backdrop-blur-md border-rose-200 text-rose-900";
                icon = '<i class="fa fa-exclamation-triangle text-rose-500 text-sm"></i>';
            } else if (type === "info") {
                bgClass = "bg-blue-50/90 backdrop-blur-md border-blue-200 text-blue-900";
                icon = '<i class="fa fa-info-circle text-blue-500 text-sm"></i>';
            }

            const toastHTML = `
                <div id="${toastId}" class="flex items-center gap-3 p-3.5 rounded-2xl border shadow-lg max-w-xs pointer-events-auto transition-all duration-300 transform translate-x-12 opacity-0 text-xs font-bold ${bgClass}">
                    ${icon}
                    <div class="flex-1">${message}</div>
                    <button onclick="removeToast('${toastId}')" class="text-slate-400 hover:text-slate-600 focus:outline-none"><i class="fa fa-times"></i></button>
                </div>
            `;

            container.append(toastHTML);
            setTimeout(() => {
                $(`#${toastId}`).removeClass("translate-x-12 opacity-0").addClass("translate-x-0 opacity-100");
            }, 10);

            setTimeout(() => {
                removeToast(toastId);
            }, 4000);
        }

        // Checks standard photo criteria
        function checkMinPhotoCriteria() {
            let count = mediaItems.length;
            $("#photo-current-count").text(count);
            let indicator = $("#status-img-count");
            if (count >= 3) {
                indicator.removeClass("bg-red-100 text-red-600").addClass("bg-emerald-100 text-emerald-600");
                indicator.html('<i class="fa fa-check"></i>');
            } else {
                indicator.removeClass("bg-emerald-100 text-emerald-600").addClass("bg-red-100 text-red-600");
                indicator.html('<i class="fa fa-times"></i>');
            }
        }

        // Load standard photo preview cards
        function renderFormMediaThumbnails() {
            let container = $("#form-media-list");
            container.empty();
            $("#count-media-txt").text(mediaItems.length);

            mediaItems.forEach((item, idx) => {
                let displayUrl = item.url;
                if (item.type === 'existing' && !displayUrl.startsWith('http') && !displayUrl.startsWith('/') && !displayUrl.startsWith('blob:')) {
                    displayUrl = "{{ asset('storage') }}/" + displayUrl;
                }
                container.append(`
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden p-2 flex flex-col gap-2 relative">
                        <div class="relative aspect-video rounded-lg overflow-hidden bg-slate-100">
                            <img src="${displayUrl}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/e2e8f0/1e293b?text=BĐS'">
                            <button type="button" onclick="deleteFormPhoto(${idx})" class="absolute top-1 right-1 w-5 h-5 bg-red-600 hover:bg-red-700 text-white rounded-full flex items-center justify-center text-xs transition-colors shadow shadow-black/20">
                                <i class="fa fa-times"></i>
                            </button>
                            ${idx === 0 ? '<span class="absolute bottom-1 left-1 bg-brand-500 text-white font-black px-1.5 py-0.5 rounded text-[10px] uppercase">Ảnh bìa</span>' : ''}
                        </div>
                    </div>
                `);
            });
        }

          function renderRedBookThumbnails() {
            let container = $("#red-book-media-list");
            container.empty();
            $("#count-red-book-txt").text(redBookItems.length);

            redBookItems.forEach((item, idx) => {
                let displayUrl = item.url;
                if (item.type === 'existing' && !displayUrl.startsWith('http') && !displayUrl.startsWith('/') && !displayUrl.startsWith('blob:')) {
                    displayUrl = "{{ asset('storage') }}/" + displayUrl;
                }
                
                let isPdf = displayUrl.toLowerCase().endsWith('.pdf') || displayUrl.startsWith('blob:') && item.file && item.file.type === 'application/pdf';
                let previewHtml = '';
                if (isPdf) {
                    previewHtml = `
                        <div class="w-full h-full flex flex-col items-center justify-center p-2 text-slate-500">
                            <i class="fa fa-file-pdf-o text-red-500 text-3xl mb-1"></i>
                            <span class="text-[9px] truncate max-w-full font-semibold px-1" title="${displayUrl.split('/').pop()}">${item.file ? item.file.name : displayUrl.split('/').pop()}</span>
                        </div>
                    `;
                } else {
                    previewHtml = `<img src="${displayUrl}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/e2e8f0/1e293b?text=BĐS'">`;
                }

                container.append(`
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden p-2 flex flex-col gap-2 relative">
                        <div class="relative aspect-video rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">
                            ${previewHtml}
                            <button type="button" onclick="deleteRedBookPhoto(${idx})" class="absolute top-1 right-1 w-5 h-5 bg-red-600 hover:bg-red-700 text-white rounded-full flex items-center justify-center text-xs transition-colors shadow shadow-black/20">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                `);
            });
        }

        function renderOtherDocThumbnails() {
            let container = $("#other-doc-media-list");
            container.empty();
            $("#count-other-doc-txt").text(otherDocItems.length);

            otherDocItems.forEach((item, idx) => {
                let displayUrl = item.url;
                if (item.type === 'existing' && !displayUrl.startsWith('http') && !displayUrl.startsWith('/') && !displayUrl.startsWith('blob:')) {
                    displayUrl = "{{ asset('storage') }}/" + displayUrl;
                }
                
                let isPdf = displayUrl.toLowerCase().endsWith('.pdf') || displayUrl.startsWith('blob:') && item.file && item.file.type === 'application/pdf';
                let previewHtml = '';
                if (isPdf) {
                    previewHtml = `
                        <div class="w-full h-full flex flex-col items-center justify-center p-2 text-slate-500">
                            <i class="fa fa-file-pdf-o text-red-500 text-3xl mb-1"></i>
                            <span class="text-[9px] truncate max-w-full font-semibold px-1" title="${displayUrl.split('/').pop()}">${item.file ? item.file.name : displayUrl.split('/').pop()}</span>
                        </div>
                    `;
                } else {
                    previewHtml = `<img src="${displayUrl}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/e2e8f0/1e293b?text=BĐS'">`;
                }

                container.append(`
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden p-2 flex flex-col gap-2 relative">
                        <div class="relative aspect-video rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center">
                            ${previewHtml}
                            <button type="button" onclick="deleteOtherDocPhoto(${idx})" class="absolute top-1 right-1 w-5 h-5 bg-red-600 hover:bg-red-700 text-white rounded-full flex items-center justify-center text-xs transition-colors shadow shadow-black/20">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                `);
            });
        }

        $(document).ready(function() {
            function formatNumberDecimal(value) {
                value = String(value || '');

                // Chỉ giữ số và dấu chấm thập phân
                value = value.replace(/[^0-9.]/g, '');

                // Chỉ cho phép 1 dấu chấm
                let parts = value.split('.');
                let integerPart = parts[0] || '';
                let decimalPart = parts.length > 1 ? parts.slice(1).join('') : '';

                // Format phần nguyên: 1000 => 1,000
                integerPart = integerPart.replace(/^0+(?=\d)/, '');
                integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                if (parts.length > 1) {
                    return integerPart + '.' + decimalPart;
                }

                return integerPart;
            }

            function unformatNumberDecimal(value) {
                value = String(value || '');

                // Bỏ dấu phẩy ngăn cách hàng nghìn, giữ dấu chấm lẻ
                return value.replace(/,/g, '').replace(/[^0-9.]/g, '');
            }
              // Legal warning radio toggle
            function toggleLegalWarning() {
                const selectedVal = $('input[name="legal_status"]:checked').val();
                
                if (selectedVal && selectedVal !== '1') {
                    $('#legal-warning-banner').removeClass('hidden').show();
                } else {
                    $('#legal-warning-banner').addClass('hidden').hide();
                }
            }

            $(document).on('change', 'input[name="legal_status"]', toggleLegalWarning);
            
            // Run initially
            toggleLegalWarning();
            // Setup CSRF Header for JQuery AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Helper: get type_client_search from selected contact_role radio
            function getTypeClientSearch() {
                var role = $('input[name="contact_role"]:checked').val();
                if (role === '1') return 1;
                if (role === '2') return 2;
                return '';
            }

            // Initialize Customer Select2 autocomplete
            function initCustomerSelect2() {
                $('#select-customer').select2({
                    placeholder: '-- Chọn khách hàng --',
                    allowClear: true,
                    width: '100%',
                    ajax: {
                        url: 'admin/clients/ajaxSearch',
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term,
                                type_client_search: getTypeClientSearch()
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results
                            };
                        },
                        cache: true
                    }
                });
            }
            initCustomerSelect2();

            // When contact role changes, clear selected customer and re-filter
            $('input[name="contact_role"]').on('change', function() {
                $('#select-customer').val(null).trigger('change');
            });

            // Auto fill contact fields when client is selected
            $('#select-customer').on('select2:select', function (e) {
                var data = e.params.data;
            });

            // Clear contact fields when selection is cleared
            $('#select-customer').on('select2:clear', function (e) {
            });

             function calculatePriceM2() {
                const selectedType = $('input[name="type"]:checked').val();
                if (selectedType != 1) {
                    $("#form-price-m2-input").val('');
                    return;
                }
                let price = parseFloat($("#form-price-input-hidden").val()) || 0;
                let area = parseFloat($('input[name="area"]').val()) || 0;
                if (price > 0 && area > 0) {
                    let price_m2 = Math.round(price / area);
                    $("#form-price-m2-input").val(formatMoney(price_m2));
                } else {
                    $("#form-price-m2-input").val('');
                }
            }

            // Format price input with dot separators (e.g. 1.000.000)
            $("#form-price-input").on("input", function() {
                let val = this.value.replace(/\D/g, ""); // Strip non-digits
                $("#form-price-input-hidden").val(val); // Store raw value
                this.value = val.replace(/\B(?=(\d{3})+(?!\d))/g, ","); // Format with dots
                calculatePriceM2();
            });
            $('input[name="area"]').on('input', function() {
                calculatePriceM2();
            });

            // Render existing media if any
            renderFormMediaThumbnails();
            checkMinPhotoCriteria();
            renderRedBookThumbnails();
            renderOtherDocThumbnails();

            // Set up video preview if existing
            if (uploadedFormVideo) {
                const videoEl = document.getElementById("form-video-element");
                if (videoEl) {
                    let videoUrl = uploadedFormVideo;
                    if (!videoUrl.startsWith('http') && !videoUrl.startsWith('/') && !videoUrl.startsWith('blob:')) {
                        videoUrl = "{{ asset('storage') }}/" + videoUrl;
                    }
                    videoEl.src = videoUrl;
                    videoEl.classList.remove("hidden");
                }
                $("#video-upload-preview").removeClass("hidden");
                $("#video-upload-placeholder").addClass("hidden");
                $("#status-video-state").removeClass("bg-brand-100 text-brand-600").addClass("bg-emerald-100 text-emerald-600").html('<i class="fa fa-check"></i>');
                $("#btn-delete-video").removeClass("hidden");
            }

            // Toggle Mua bán (Buying/Selling) vs Cho thuê (Renting) fields
            function togglePropertyTypeFields() {
                const selectedType = $('input[name="type"]:checked').val();
                if (selectedType == 1) { // Mua bán
                    $('#legal-document-wrapper').show();
                    $('#legal-documents-upload-wrapper').show().removeClass('hidden');
                    $('#rental-details-wrapper').addClass('hidden').hide();
                    $('#price-label-text').html('Mức Giá (VNĐ) <span class="text-red-500">*</span>');
                    $('#form-price-input').attr('placeholder', 'Ví dụ: 3.000.000.000');
                    $(".wrap-currently_rent").removeClass('hide');
                    $(".wrap-price_m2").removeClass('hide');
                    $(".wrap-plot_land").removeClass('hide');
                    $('input[name="plot_land"]').prop('required', true);
                    $('input[name="number_sheets"]').prop('required', true);
                    $('.plot-land-asterisk').removeClass('hidden');
                    $('.number-sheets-asterisk').removeClass('hidden');
                } else if (selectedType == 2) { // Cho thuê
                    $('#legal-document-wrapper').hide();
                    $('#legal-documents-upload-wrapper').hide().addClass('hidden');
                    $('#rental-details-wrapper').removeClass('hidden').show();
                    $('#price-label-text').html('Mức Giá (VNĐ)/ Tháng <span class="text-red-500">*</span>');
                    $('#form-price-input').attr('placeholder', 'Ví dụ: 12.000.000/ Tháng');
                    $(".wrap-currently_rent").addClass('hide');
                    $(".wrap-price_m2").addClass('hide');
                    $(".wrap-plot_land").addClass('hide');
                    $('input[name="plot_land"]').prop('required', false);
                    $('input[name="number_sheets"]').prop('required', false);
                    $('.plot-land-asterisk').addClass('hidden');
                    $('.number-sheets-asterisk').addClass('hidden');
                    
                    // Re-initialize select2 with width 100% to avoid layout squeeze
                    $('#rental-details-wrapper select.select2').each(function() {
                        if ($(this).data('select2')) {
                            $(this).select2('destroy');
                        }
                        $(this).select2({
                            width: '100%'
                        });
                    });
                }
                calculatePriceM2();
            }

            // Bind change event to transaction type radio inputs
            $('input[name="type"]').on('change', togglePropertyTypeFields);

            // Execute initially
            togglePropertyTypeFields();

            // Toggle fields specific to selected property type (utilities)
            function togglePropertyTypeSpecificFields() {
                const selectedVal = $('select[name="property_type"]').val();
                const transactionType = $('input[name="type"]:checked').val() || 1;
                const container = $('#dynamic-utilities-container');
                container.empty();
                $('#dynamic-utilities-wrapper').hide();

                if (!selectedVal) {
                    return;
                }

                const propType = propertyTypesUtilities.find(item => item.id == selectedVal);
                if (propType && propType.utilities && propType.utilities.length > 0) {
                    // Filter utilities by transaction type (1: sell, 2: rent, 3: both)
                    const filteredUtilities = propType.utilities.filter(utility => {
                        const utType = utility.transaction_type ?? 3;
                        return utType == 3 || utType == transactionType;
                    });

                    if (filteredUtilities.length > 0) {
                        $('#dynamic-utilities-wrapper').show();
                        filteredUtilities.forEach(utility => {
                            const savedValue = homeUtilities[utility.id] !== undefined ? homeUtilities[utility.id] : '';
                            const wrapper = $('<div></div>');
                            
                            const labelText = utility.unit ? `${utility.name} (${utility.unit})` : utility.name;
                            const label = $('<label></label>')
                                .addClass('block text-sm font-bold text-slate-600 uppercase mb-3')
                                .text(labelText);
                                
                            let input;
                            if (utility.input_type === 'select') {
                                input = $('<select></select>')
                                    .attr('name', `utilities[${utility.id}]`)
                                    .addClass('w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-semibold text-slate-800');
                                
                                // Add default empty option
                                const defaultOpt = $('<option></option>')
                                    .attr('value', '')
                                    .text(`-- Chọn ${utility.name} --`);
                                input.append(defaultOpt);

                                // Build options from relation options
                                if (utility.options && utility.options.length > 0) {
                                    utility.options.forEach(optData => {
                                        const opt = $('<option></option>')
                                            .attr('value', optData.id)
                                            .text(optData.name);
                                        if (String(optData.id) === String(savedValue)) {
                                            opt.attr('selected', 'selected');
                                        }
                                        input.append(opt);
                                    });
                                }
                            } else {
                                const isNumber = utility.input_type === 'number';
                                const formattedVal = isNumber && savedValue ? formatNumberDecimal(savedValue) : savedValue;
                                input = $('<input>')
                                    .attr('type', 'text')
                                    .attr('name', `utilities[${utility.id}]`)
                                    .val(formattedVal)
                                    .addClass('w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold');

                                if (isNumber) {
                                    input.addClass('utility-number-input');
                                    input.on('input', function() {
                                        this.value = formatNumberDecimal(this.value);
                                    });
                                }
                            }

                            wrapper.append(label).append(input);
                            container.append(wrapper);
                        });
                    }
                }
            }

            // Bind change event to property_type select input and transaction type radio inputs
            $('select[name="property_type"]').on('change', togglePropertyTypeSpecificFields);
            $('input[name="type"]').on('change', togglePropertyTypeSpecificFields);

            // Execute initially
            togglePropertyTypeSpecificFields();

            // Modal DOM elements
            const addressModalWrapper = $("#address-modal-wrapper");
            const addressModalBox = addressModalWrapper.find("> div");
            const addressModalClose = $("#address-modal-close");
            const addressModalCancel = $("#address-modal-cancel");
            const addressModalSubmit = $("#address-modal-submit");

            // Open/Trigger buttons
            const triggerPlaceholder = $("#address-placeholder");
            const triggerEditBtn = $("#btn-edit-address");

            // Leaflet Map state variables
            let mainLeafletMap = null;
            let mainLeafletMarker = null;
            let modalLeafletMap = null;
            let modalLeafletMarker = null;
            const defaultLat = 10.762622; // HCMC default
            const defaultLng = 106.660172;

            // Accordion Collapse/Expand
            $("#address-header-toggle").on("click", function() {
                const body = $("#address-card-body");
                const chevron = $("#address-chevron");
                if (body.hasClass("hidden")) {
                    body.removeClass("hidden");
                    chevron.removeClass("rotate-180");
                    if (mainLeafletMap) {
                        setTimeout(() => mainLeafletMap.invalidateSize(), 100);
                    }
                } else {
                    body.addClass("hidden");
                    chevron.addClass("rotate-180");
                }
            });

            // Open Modal Handler
            function openAddressModal() {
                addressModalWrapper.removeClass("hidden");
                setTimeout(() => {
                    addressModalBox.removeClass("scale-95 opacity-0").addClass("scale-100 opacity-100");
                }, 10);

                // Initialize Select2 in the modal properly with width 100%
                $("#select-province, #select-district, #select-ward, #modal-select-project").select2({
                    dropdownParent: addressModalWrapper,
                    width: '100%'
                });

                // Load database province/district/ward choices
                if (!loadedInitialValues) {
                    loadSavedSelections();
                    loadedInitialValues = true;
                }

                // Initialize Modal Map
                initModalMap();
                
                // Set initial preview address
                initAutocomplete();
                updateSuggestedAddress();

                // Ensure Leaflet map invalidates size after modal animation completes
                setTimeout(() => {
                    if (modalLeafletMap) {
                        modalLeafletMap.invalidateSize();
                    }
                }, 350);
            }

            triggerPlaceholder.on("click", openAddressModal);
            triggerEditBtn.on("click", openAddressModal);

            // Close Modal Function
            function closeAddressModal() {
                addressModalBox.removeClass("scale-100 opacity-100").addClass("scale-95 opacity-0");
                setTimeout(() => {
                    addressModalWrapper.addClass("hidden");
                }, 300);
            }

            addressModalClose.on("click", closeAddressModal);
            addressModalCancel.on("click", closeAddressModal);

            // Parse existing address
            function parseExistingAddress() {
                const fullAddr = $("#hidden-address").val();
                if (!fullAddr) return;

                $("#modal-input-detail-address").val(fullAddr);
            }

            // Update main form address details display from hidden fields
            function updateMainFormAddressUI() {
                const addr = $("#hidden-address").val();

                if (!addr) {
                    $("#address-placeholder").removeClass("hidden");
                    $("#address-details").addClass("hidden");
                    $("#main-leaflet-map-container").addClass("hidden");
                    $("#address-validation-dummy").val("");
                    return;
                }

                $("#address-placeholder").addClass("hidden");
                $("#address-details").removeClass("hidden");
                $("#main-leaflet-map-container").removeClass("hidden");
                $("#address-validation-dummy").val(addr);

                const provinceText = $("#select-province option:selected").val() ? $("#select-province option:selected").text().trim() : "";
                const wardText = $("#select-ward option:selected").val() ? $("#select-ward option:selected").text().trim() : "";

                if (provinceText) {
                    let newParts = [addr];
                    if (wardText && wardText.indexOf('--') === -1) newParts.push(wardText);
                    if (provinceText && provinceText.indexOf('--') === -1) newParts.push(provinceText);
                    $("#display-new-address").text(newParts.join(", "));
                }

                initMainFormMap();
            }

            // Initialize Main Form Leaflet Map (non-draggable marker)
            function initMainFormMap() {
                const lat = parseFloat($("#hidden-latitude").val());
                const lng = parseFloat($("#hidden-longitude").val());
                
                if (!lat || !lng) {
                    $("#main-leaflet-map-container").addClass("hidden");
                    return;
                }

                $("#main-leaflet-map-container").removeClass("hidden");

                if (mainLeafletMap) {
                    mainLeafletMap.setView([lat, lng], 15);
                    if (mainLeafletMarker) {
                        mainLeafletMarker.setLatLng([lat, lng]);
                    } else {
                        mainLeafletMarker = L.marker([lat, lng]).addTo(mainLeafletMap);
                    }
                    setTimeout(() => mainLeafletMap.invalidateSize(), 100);
                    return;
                }

                mainLeafletMap = L.map('main-leaflet-map').setView([lat, lng], 15);

                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }).addTo(mainLeafletMap);

                mainLeafletMarker = L.marker([lat, lng]).addTo(mainLeafletMap);
                setTimeout(() => mainLeafletMap.invalidateSize(), 100);
            }

            // Initialize Modal Leaflet Map (draggable marker)
            function initModalMap() {
                if (modalLeafletMap) {
                    setTimeout(() => {
                        modalLeafletMap.invalidateSize();
                    }, 100);
                    return;
                }

                const lat = parseFloat($("#hidden-latitude").val()) || defaultLat;
                const lng = parseFloat($("#hidden-longitude").val()) || defaultLng;

                modalLeafletMap = L.map('modal-leaflet-map').setView([lat, lng], 15);

                L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                    maxZoom: 20,
                    subdomains: ['mt0', 'mt1', 'mt2', 'mt3']
                }).addTo(modalLeafletMap);

                modalLeafletMarker = L.marker([lat, lng], { draggable: true }).addTo(modalLeafletMap);

                modalLeafletMarker.on("dragend", function(event) {
                    const position = modalLeafletMarker.getLatLng();
                    $("#hidden-latitude").val(position.lat);
                    $("#hidden-longitude").val(position.lng);
                });
            }

            // Reset map location
            $("#modal-map-reset").on("click", function() {
                const lat = parseFloat($("#hidden-latitude").val()) || defaultLat;
                const lng = parseFloat($("#hidden-longitude").val()) || defaultLng;
                if (modalLeafletMap && modalLeafletMarker) {
                    modalLeafletMap.setView([lat, lng], 15);
                    modalLeafletMarker.setLatLng([lat, lng]);
                }
            });

            function updateSuggestedAddress() {
                const provinceText = $("#select-province option:selected").val() ? $("#select-province option:selected").text().trim() : "";
                const wardText = $("#select-ward option:selected").val() ? $("#select-ward option:selected").text().trim() : "";
                const detailText = $("#modal-input-detail-address").val().trim();

                let streetAndDetail = detailText;

                let newParts = [];
                if (streetAndDetail) newParts.push(streetAndDetail);
                if (wardText && wardText.indexOf('--') === -1) newParts.push(wardText);
                if (provinceText && provinceText.indexOf('--') === -1) newParts.push(provinceText);

                const newAddressStr = newParts.join(", ") || "Chưa nhập đủ thông tin";
                $("#modal-display-new-address").text(newAddressStr);
            }

            // Run initial parsing and UI display on load
            if ($("#hidden-address").val()) {
                parseExistingAddress();
            }
            updateMainFormAddressUI();

            // Initialize Google Places Autocomplete on input detail address
            let autocompleteInstance = null;
            function initAutocomplete() {
                if (autocompleteInstance) return;
                if (typeof google !== 'undefined' && google.maps && google.maps.places) {
                    const inputElement = document.getElementById('modal-input-detail-address');
                    if (!inputElement) return;

                    autocompleteInstance = new google.maps.places.Autocomplete(inputElement, {
                        componentRestrictions: { country: 'vn' }, // Limit search to Vietnam
                        fields: ['address_components', 'geometry', 'formatted_address']
                    });

                    autocompleteInstance.addListener('place_changed', function() {
                        const place = autocompleteInstance.getPlace();
                        if (!place.geometry) {
                            return;
                        }

                        const lat = place.geometry.location.lat();
                        const lng = place.geometry.location.lng();

                        // Set bypass flag so geocodeAddress doesn't run and overwrite this lat/lng
                        bypassGeocodeOnce = true;

                        if (modalLeafletMap && modalLeafletMarker) {
                            modalLeafletMap.setView([lat, lng], 16);
                            modalLeafletMarker.setLatLng([lat, lng]);
                            $("#hidden-latitude").val(lat);
                            $("#hidden-longitude").val(lng);
                        }

                        // Extract the formatted address and update input
                        let addressVal = place.formatted_address;
                        if (addressVal) {
                            addressVal = addressVal.replace(/, Việt Nam$/, '').trim();
                            $(inputElement).val(addressVal);
                        }

                        updateSuggestedAddress();
                    });
                }
            }

            // updateAutocompleteBounds and debouncedUpdateAutocompleteBounds removed as per request to allow normal searching across Vietnam

            // Initialize Project select
            $("#modal-select-project").select2({
                dropdownParent: addressModalWrapper,
                width: '100%',
                placeholder: "Chọn dự án"
            });

            // Initial load selector values helper
            function loadSavedSelections() {
                const selectProvince = $("#select-province");
                const selectDistrict = $("#select-district");
                const selectWard = $("#select-ward");

                selectProvince.empty().append('<option value="">-- Chọn Tỉnh/Thành --</option>');
                selectDistrict.empty().append('<option value="">-- Chọn Quận/Huyện --</option>');
                selectWard.empty().append('<option value="">-- Chọn Phường/Xã --</option>');

                // Hide district wrapper since we only use the new address system
                $("#district-wrapper").addClass("hidden");

                // Populate with new provinces
                provincesNew.forEach(function(p) {
                    selectProvince.append('<option value="' + p.id + '">' + p.name + '</option>');
                });
                selectProvince.trigger('change.select2');

                if (homeProvinceId) {
                    $("#select-province").val(homeProvinceId).trigger("change.select2");
                    
                    $.ajax({
                        url: 'admin/manage_home/getWards',
                        type: 'POST',
                        data: { province_id: homeProvinceId, is_new: 1 },
                        success: function(data) {
                            selectWard.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
                            data.forEach(function(ward) {
                                const isSelected = (homeWardId && ward.id == homeWardId) ? 'selected' : '';
                                selectWard.append('<option value="' + ward.id + '" ' + isSelected + '>' + ward.name + '</option>');
                            });
                            selectWard.trigger('change.select2');
                            updateSuggestedAddress();
                        }
                    });
                }
            }

            // Province change handler
            $("#select-province").on("change", function() {
                let provinceId = $(this).val();
                let selectWard = $("#select-ward");
                
                selectWard.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
                
                if (provinceId) {
                    // Load Wards directly for this province (New Address System)
                    $.ajax({
                        url: 'admin/manage_home/getWards',
                        type: 'POST',
                        data: { province_id: provinceId, is_new: 1 },
                        success: function(data) {
                            data.forEach(function(ward) {
                                selectWard.append('<option value="' + ward.id + '">' + ward.name + '</option>');
                            });
                            selectWard.trigger('change.select2');
                            updateSuggestedAddress();
                        },
                        error: function() {
                            showToast("Lỗi khi tải danh sách Phường/Xã", "error");
                        }
                    });
                } else {
                    selectWard.trigger('change.select2');
                    updateSuggestedAddress();
                }
            });

            // Ward change handler
            $("#select-ward").on("change", function() {
                updateSuggestedAddress();
            });

            // Detail address keyup/change handler
            $("#modal-input-detail-address").on("input change", function() {
                updateSuggestedAddress();
            });

            // Confirm address selection
            addressModalSubmit.on("click", function() {
                const provinceId = $("#select-province").val();
                const wardId = $("#select-ward").val();
                const detailText = $("#modal-input-detail-address").val().trim();

                if (!provinceId) {
                    showToast("Vui lòng chọn Tỉnh/Thành", "error");
                    return;
                }
                if (!wardId) {
                    showToast("Vui lòng chọn Phường/Xã", "error");
                    return;
                }

                let streetAndDetail = detailText;
                if (!streetAndDetail) {
                    showToast("Vui lòng nhập địa chỉ chi tiết (số nhà, tên đường...)", "error");
                    return;
                }

                // Copy values to hidden inputs in the main form
                $("#hidden-province-id").val(provinceId);
                $("#hidden-district-id").val("");
                $("#hidden-ward-id").val(wardId);
                $("#hidden-is-new-address").val(1);
                $("#hidden-address").val(streetAndDetail);

                // Update state variables
                homeIsNewAddress = 1;
                homeProvinceId = provinceId;
                homeWardId = wardId;
                homeDistrictId = null;

                // Update the main form UI and pan the main map
                updateMainFormAddressUI();

                showToast("Đã cập nhật địa chỉ thành công!", "success");
                closeAddressModal();
            });

            // Media standard photo upload trigger
            $("#trigger-upload-area").on("click", function() {
                $("#real-images-input").click();
            });

            $("#real-images-input").on("change", function(e) {
                let files = e.target.files;
                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        let file = files[i];
                        let objectUrl = URL.createObjectURL(file);
                        mediaItems.push({
                            type: 'new',
                            url: objectUrl,
                            file: file,
                            caption: ''
                        });
                    }
                    renderFormMediaThumbnails();
                    checkMinPhotoCriteria();
                    $("#real-images-input").val("");
                    showToast("Đã thêm hình ảnh thành công! Đừng quên bấm lưu để cập nhật.", "success");
                }
            });

              // Red book upload trigger
            $("#trigger-red-book-upload").on("click", function() {
                $("#real-red-book-input").click();
            });

            $("#real-red-book-input").on("change", function(e) {
                let files = e.target.files;
                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        let file = files[i];
                        let objectUrl = URL.createObjectURL(file);
                        redBookItems.push({
                            type: 'new',
                            url: objectUrl,
                            file: file
                        });
                    }
                    renderRedBookThumbnails();
                    $("#real-red-book-input").val("");
                    showToast("Đã thêm sổ đỏ, hồng! Đừng quên bấm lưu để cập nhật.", "success");
                }
            });

            // Other documents upload trigger
            $("#trigger-other-doc-upload").on("click", function() {
                $("#real-other-doc-input").click();
            });

            $("#real-other-doc-input").on("change", function(e) {
                let files = e.target.files;
                if (files.length > 0) {
                    for (let i = 0; i < files.length; i++) {
                        let file = files[i];
                        let objectUrl = URL.createObjectURL(file);
                        otherDocItems.push({
                            type: 'new',
                            url: objectUrl,
                            file: file
                        });
                    }
                    renderOtherDocThumbnails();
                    $("#real-other-doc-input").val("");
                    showToast("Đã thêm tài liệu khác! Đừng quên bấm lưu để cập nhật.", "success");
                }
            });

            // Video 9:16 Upload actions (Using real local files)
            $("#video-upload-placeholder").on("click", function() {
                $("#real-video-input").click();
            });

            $("#real-video-input").on("change", function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 300 * 1024 * 1024) {
                        showToast("Kích thước video vượt giới hạn cho phép (Tối đa 300MB)!", "error");
                        return;
                    }
                    
                    selectedVideoFile = file;
                    const objectUrl = URL.createObjectURL(file);

                    const videoEl = document.getElementById("form-video-element");
                    if (videoEl) {
                        videoEl.src = objectUrl;
                        videoEl.classList.remove("hidden");
                        
                        const playPromise = videoEl.play();
                        if (playPromise !== undefined) {
                            playPromise.then(() => {
                            }).catch(err => {
                                console.warn("Autoplay prevented", err);
                            });
                        }
                    }

                    $("#video-upload-preview").removeClass("hidden");
                    $("#video-upload-placeholder").addClass("hidden");
                    $("#status-video-state").removeClass("bg-brand-100 text-brand-600").addClass("bg-emerald-100 text-emerald-600").html('<i class="fa fa-check"></i>');
                    $("#btn-delete-video").removeClass("hidden");
                    showToast("Đã chọn video thành công! Đừng quên bấm lưu để tải lên.", "success");
                }
            });

            // Handle delete video action
            $("#btn-delete-video").on("click", function(e) {
                e.stopPropagation();
                
                if (selectedVideoFile) {
                    const videoEl = document.getElementById("form-video-element");
                    if (videoEl && videoEl.src.startsWith('blob:')) {
                        URL.revokeObjectURL(videoEl.src);
                    }
                }
                
                selectedVideoFile = null;
                uploadedFormVideo = null;
                $("#real-video-input").val("");
                
                const videoEl = document.getElementById("form-video-element");
                if (videoEl) {
                    videoEl.pause();
                    videoEl.removeAttribute('src');
                    videoEl.load();
                    videoEl.classList.add("hidden");
                }

                $("#video-upload-preview").addClass("hidden");
                $("#video-upload-placeholder").removeClass("hidden");
                $("#status-video-state").removeClass("bg-emerald-100 text-emerald-600").addClass("bg-brand-100 text-brand-600").html('<i class="fa fa-exclamation"></i>');
                $("#btn-delete-video").addClass("hidden");
                showToast("Đã gỡ bỏ video đại diện!", "info");
            });

             // AI Generate Description Action
            const aiModalWrapper = $("#ai-modal-wrapper");
            const aiModalBox = aiModalWrapper.find("> div");
            
            $("#btn-generate-ai-desc").on("click", function() {
                // 1. Check required inputs for AI generation
                const provinceId = $("#hidden-province-id").val();
                const districtId = $("#hidden-district-id").val();
                const wardId = $("#hidden-ward-id").val();
                const address = $("#hidden-address").val();
                
                const propertyType = $('select[name="property_type"]').val();
                const price = $('#form-price-input-hidden').val();
                const area = $('input[name="area"]').val();
                
                let missingFields = [];
                if (!provinceId || !wardId || !address) {
                    missingFields.push("Địa chỉ đầy đủ");
                }
                if (!propertyType) {
                    missingFields.push("Loại bất động sản");
                }
                if (!price) {
                    missingFields.push("Mức giá");
                }
                if (!area) {
                    missingFields.push("Diện tích");
                }
                
                if (missingFields.length > 0) {
                    showToast("Vui lòng điền đủ thông tin sau trước khi tạo bằng AI: " + missingFields.join(", ") + "!", "warning");
                    return;
                }

                // Hide Use button initially when opening
                $("#ai-use-btn").css("display", "none");

                // Clear active states and data caches when opening the modal
                $(".ai-tone-option").removeClass("active");
                $(".ai-tone-option .ai-preview-container").addClass("hidden");
                $(".ai-tone-option input[type='radio']").prop("checked", false);
                $(".ai-tone-option").each(function() {
                    $(this).data('title', '');
                    $(this).data('detail', '');
                    $(this).data('description', '');
                });
                
                aiModalWrapper.removeClass("hidden");
                setTimeout(() => {
                    aiModalBox.removeClass("scale-95 opacity-0").addClass("scale-100 opacity-100");
                }, 10);
            });
            
            // Close AI Modal
            function closeAiModal() {
                aiModalBox.removeClass("scale-100 opacity-100").addClass("scale-95 opacity-0");
                setTimeout(() => {
                    aiModalWrapper.addClass("hidden");
                }, 300);
            }
            
            $("#ai-modal-close, #ai-modal-cancel").on("click", closeAiModal);
            
            // Offline fallback generator function
            function generateAIDescription(tone) {
                const type = $('input[name="type"]:checked').val() || '1';
                const propertyTypeName = $('select[name="property_type"] option:selected').text().trim() || 'Bất động sản';
                
                const rawPrice = $('#form-price-input-hidden').val() || '';
                let priceFormatted = 'Thỏa thuận';
                if (rawPrice) {
                    priceFormatted = new Intl.NumberFormat('vi-VN').format(rawPrice) + ' VNĐ';
                    if (type == '2') {
                        priceFormatted += ' / tháng';
                    }
                }
                
                const area = $('input[name="area"]').val() || '';
                const areaStr = area ? area + ' m²' : '';
                
                const addressParts = [];
                const wardText = $('select[name="ward_id"] option:selected').text().trim();
                const districtText = $('select[name="district_id"] option:selected').text().trim();
                const provinceText = $('select[name="province_id"] option:selected').text().trim();
                
                if (wardText && !wardText.includes('Chọn')) addressParts.push(wardText);
                if (districtText && !districtText.includes('Chọn')) addressParts.push(districtText);
                if (provinceText && !provinceText.includes('Chọn')) addressParts.push(provinceText);
                const locationStr = addressParts.join(', ') || '';
                
                const selectedAmenities = [];
                $('input[name="interior_amenities[]"]:checked').each(function() {
                    const name = $(this).closest('label').find('span.text-sm').text().trim();
                    if (name) {
                        selectedAmenities.push(name);
                    }
                });
                const amenitiesStr = selectedAmenities.join(', ');
                
                let title = '';
                let detail = '';
                let description = '';
                
                if (tone === 'polite') {
                    title = (type == '1' ? 'Bán ' : 'Cho thuê ') + propertyTypeName + " cao cấp tại " + (locationStr || 'trung tâm') + " - " + areaStr;
                    detail = "Cơ hội sở hữu " + propertyTypeName + " vị trí đắc địa, giao thông kết nối hoàn hảo.";
                    description = "Kính gửi Quý khách hàng,\n\nChúng tôi xin trân trọng giới thiệu thông tin sản phẩm " + propertyTypeName + " như sau:\n";
                    description += "- Vị trí: " + (locationStr || 'Đang cập nhật') + "\n";
                    description += "- Diện tích: " + (areaStr || 'Đang cập nhật') + "\n";
                    description += "- Giá bán: " + priceFormatted + "\n";
                    if (amenitiesStr) {
                        description += "- Tiện ích xung quanh: " + amenitiesStr + "\n";
                    }
                    description += "\nSản phẩm sở hữu hồ sơ pháp lý hoàn chỉnh, thiết kế kiến trúc hiện đại, thích hợp cho nhu cầu đầu tư lâu dài hoặc làm nơi cư trú lý tưởng. Quý khách vui lòng liên hệ trực tiếp để được tư vấn và xem nhà thực tế.\n\nTrân trọng cảm ơn!";
                } else { // youthful
                    title = "CỰC HOT! " + propertyTypeName + " siêu chất " + areaStr + " vị trí đỉnh " + (locationStr || 'trung tâm');
                    detail = "Review nhanh căn " + propertyTypeName + " cực chill cho giới trẻ, xem ngay kẻo lỡ!";
                    description = "Hello mọi người! Mình đang cần pass gấp căn " + propertyTypeName + " siêu xịn mịn đây:\n";
                    description += "• Địa chỉ: " + (locationStr || 'Trung tâm') + " - giao thông cực tiện lợi luôn.\n";
                    description += "• Diện tích: " + (areaStr || 'Rộng rãi thoáng mát') + "\n";
                    description += "• Giá yêu thương: " + priceFormatted + "\n";
                    if (amenitiesStr) {
                        description += "• Điểm cộng: " + amenitiesStr + " sát vách nhà nha.\n";
                    }
                    description += "\nCăn này thiết kế phong cách hiện đại cực kỳ trẻ trung năng động, không gian thoáng đãng ngập tràn ánh sáng. A/C nào ưng ý thì nhắn tin hoặc alo trực tiếp mình dẫn đi xem thực tế luôn nhé! Cảm ơn cả nhà!";
                }
                
                return {
                    title: title,
                    detail: detail,
                    description: description
                };
            }

            // Function to generate text using API
            function generateAITextForTone(tone, card) {
                card.find(".ai-loading-state").removeClass("hidden");
                card.find(".ai-result-content").addClass("hidden");
                card.find(".ai-rewrite-btn").addClass("hidden");
                
                // Hide use button during loading
                $("#ai-use-btn").css("display", "none");
                
                const form = document.getElementById("property-form");
                const formData = new FormData(form);
                formData.append('ai_tone', tone);
                
                $.ajax({
                    url: 'admin/manage_home/generate_ai',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: apiHeaders,
                    success: function(response) {
                        if (response.result) {
                            card.data('title', response.title || '');
                            card.data('detail', response.detail || '');
                            card.data('description', response.description || '');
                            
                            card.find(".ai-preview-title").text(response.title || '');
                            card.find(".ai-preview-description").html((response.description || '').replace(/\r?\n/g, '<br>'));
                            
                            card.find(".ai-loading-state").addClass("hidden");
                            card.find(".ai-result-content").removeClass("hidden");
                            card.find(".ai-rewrite-btn").removeClass("hidden");
                            
                            // Show use button since we have valid data
                            if (card.hasClass("active")) {
                                $("#ai-use-btn").css("display", "flex");
                            }
                        } else {
                            showToast(response.message || "Tạo nội dung AI thất bại!", "error");
                            useFallback(tone, card);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.warn("API AI generate failed, using local offline fallback generator.", error);
                        useFallback(tone, card);
                    }
                });
            }
            
            // Offline fallback helper
            function useFallback(tone, card) {
                const fallbackData = generateAIDescription(tone);
                
                card.data('title', fallbackData.title || '');
                card.data('detail', fallbackData.detail || '');
                card.data('description', fallbackData.description || '');
                
                card.find(".ai-preview-title").text(fallbackData.title || '');
                card.find(".ai-preview-description").html((fallbackData.description || '').replace(/\r?\n/g, '<br>'));
                
                card.find(".ai-loading-state").addClass("hidden");
                card.find(".ai-result-content").removeClass("hidden");
                card.find(".ai-rewrite-btn").removeClass("hidden");
                
                // Show use button since we have fallback data
                if (card.hasClass("active")) {
                    $("#ai-use-btn").css("display", "flex");
                }
            }
            
            // Handle option selection click (Clicking a card)
            $(".ai-tone-option").on("click", function(e) {
                if ($(e.target).closest('.ai-rewrite-btn, .ai-preview-collapse, a, button').length) {
                    return;
                }
                
                const card = $(this);
                const isAlreadyActive = card.hasClass("active");
                if (isAlreadyActive) {
                    return;
                }
                
                $(".ai-tone-option").removeClass("active");
                $(".ai-tone-option .ai-preview-container").addClass("hidden");
                $(".ai-tone-option input[type='radio']").prop("checked", false);
                
                // Hide use button until current selection has data
                $("#ai-use-btn").css("display", "none");
                
                card.addClass("active");
                card.find("input[type='radio']").prop("checked", true);
                card.find(".ai-preview-container").removeClass("hidden");
                
                const tone = card.data("tone");
                
                if (card.data("title")) {
                    card.find(".ai-preview-title").text(card.data("title"));
                    card.find(".ai-preview-description").html(card.data("description").replace(/\r?\n/g, '<br>'));
                    card.find(".ai-loading-state").addClass("hidden");
                    card.find(".ai-result-content").removeClass("hidden");
                    card.find(".ai-rewrite-btn").removeClass("hidden");
                    
                    // Show use button
                    $("#ai-use-btn").css("display", "flex");
                } else {
                    generateAITextForTone(tone, card);
                }
            });
            
            // Handle rewrite action click
            $(".ai-rewrite-btn").on("click", function(e) {
                e.stopPropagation();
                const card = $(this).closest(".ai-tone-option");
                const tone = card.data("tone");
                
                card.data('title', '');
                card.data('detail', '');
                card.data('description', '');
                
                // Hide use button during load
                $("#ai-use-btn").css("display", "none");
                
                generateAITextForTone(tone, card);
            });
            
            // Handle collapse action click
            $(".ai-preview-collapse").on("click", function(e) {
                e.stopPropagation();
                const card = $(this).closest(".ai-tone-option");
                card.removeClass("active");
                card.find(".ai-preview-container").addClass("hidden");
                card.find("input[type='radio']").prop("checked", false);
                
                // Hide use button when collapsed
                $("#ai-use-btn").css("display", "none");
            });
            
            // Handle use action click
            $("#ai-use-btn").on("click", function() {
                const activeCard = $(".ai-tone-option.active");
                if (!activeCard.length || !activeCard.data("title")) {
                    showToast("Vui lòng chọn một giọng điệu để tạo và sử dụng!", "warning");
                    return;
                }
                
                const title = activeCard.data("title");
                const detail = activeCard.data("detail");
                const description = activeCard.data("description");
                
                $('#form-title-input').val(title);
                $('textarea[name="detail"]').val(detail);
                $('textarea[name="description"]').val(description);
                
                const htmlDescription = description.replace(/\n/g, '<br>');
                
                if (typeof tinymce !== 'undefined') {
                    tinymce.editors.forEach(function(editor) {
                        if (editor.getElement().name === 'description') {
                            editor.setContent(htmlDescription);
                        }
                    });
                }
                
                if (typeof CKEDITOR !== 'undefined') {
                    for (let instanceName in CKEDITOR.instances) {
                        if (CKEDITOR.instances.hasOwnProperty(instanceName)) {
                            const element = CKEDITOR.instances[instanceName].element;
                            if (element && element.$.name === 'description') {
                                CKEDITOR.instances[instanceName].setData(htmlDescription);
                            }
                        }
                    }
                }
                
                showToast("Đã tự động điền tiêu đề và mô tả bằng AI thành công!", "success");
                closeAiModal();
            });


            // Save & Publish Property Action
            $("#btn-save-property").on("click", function() {
                let form = document.getElementById("property-form");
                
                // 1. Check basic form validations
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // 2. Strict constraint rule validation
                if (mediaItems.length < 3) {
                    showToast("Yêu cầu tối thiểu tải lên 3 hình ảnh chuẩn để đảm bảo phê duyệt!", "error");
                    
                    let mediaSection = document.getElementById("form-step-2");
                    if (mediaSection) {
                        mediaSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    return;
                }

                 // Check document requirements when transaction type is Mua bán (1)
                const transactionType = $('input[name="type"]:checked').val() || 1;
                if (transactionType == 1) {
                    const plotLandVal = $('input[name="plot_land"]').val();
                    const numberSheetsVal = $('input[name="number_sheets"]').val();
                    if (!plotLandVal || plotLandVal.trim() === '') {
                        showToast("Vui lòng nhập Thửa đất số!", "error");
                        $('input[name="plot_land"]').focus();
                        return;
                    }
                    if (!numberSheetsVal || numberSheetsVal.trim() === '') {
                        showToast("Vui lòng nhập Tờ bản đồ!", "error");
                        $('input[name="number_sheets"]').focus();
                        return;
                    }

                    const legal_status = $('input[name="legal_status"]:checked').val() || 1;
                    if(legal_status == 1){
                        if (redBookItems.length < 1) {
                            showToast("Vui lòng tải lên tối thiểu 1 hình ảnh hoặc tài liệu Sổ đỏ, sổ hồng!", "error");
                            let redBookSection = document.getElementById("trigger-red-book-upload");
                            if (redBookSection) {
                                redBookSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                            return;
                        }
                    }
                }

                // 3. Assemble FormData
                let id = $("#edit-property-id").val();
                let formData = new FormData(form);

                // Strip dots from dynamic numeric utilities before submitting
                $('.utility-number-input').each(function() {
                    const nameAttr = $(this).attr('name');
                    if (nameAttr) {
                        const rawVal = unformatNumberDecimal($(this).val());
                        formData.set(nameAttr, rawVal);
                    }
                });

                // Add media files and captions
                mediaItems.forEach((item, index) => {
                    if (item.type === 'existing') {
                        formData.append('existing_media[]', item.url);
                        formData.append('existing_media_captions[]', item.caption);
                    } else if (item.type === 'new') {
                        formData.append('new_media[]', item.file);
                        formData.append('new_media_captions[]', item.caption);
                    }
                });


                // Add red book files
                redBookItems.forEach((item, index) => {
                    if (item.type === 'existing') {
                        formData.append('existing_red_book[]', item.url);
                    } else if (item.type === 'new') {
                        formData.append('new_red_book[]', item.file);
                    }
                });

                // Add other document files
                otherDocItems.forEach((item, index) => {
                    if (item.type === 'existing') {
                        formData.append('existing_other_documents[]', item.url);
                    } else if (item.type === 'new') {
                        formData.append('new_other_documents[]', item.file);
                    }
                });

                // Add video
                if (selectedVideoFile) {
                    formData.append('video_file', selectedVideoFile);
                } else if (uploadedFormVideo) {
                    formData.append('video_url', uploadedFormVideo);
                } else {
                    formData.append('video_url', '');
                }
                formData.append('id', id);
                formData.append('web', 1);
                showToast("Đang lưu thông tin...", "warning");
                
                let submitUrl = 'api/home/detail';
                
                $.ajax({
                    url: submitUrl,
                    type: 'POST',
                    data: formData,
                    headers: apiHeaders,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.result) {
                            showToast(response.message || "Lưu thông tin thành công!", "success");
                            setTimeout(function() {
                                window.location.href = 'admin/manage_home/list';
                            }, 1000);
                        } else {
                            showToast(response.message || "Lưu thông tin thất bại", "error");
                        }
                    },
                    error: function(xhr) {
                        let msg = "Có lỗi xảy ra khi lưu thông tin";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        showToast(msg, "error");
                    }
                });
            });

            // Tab scroll navigation listener
            $(".form-nav-tab").on("click", function() {
                let target = $(this).data("scroll-target");
                let targetEl = $(target);
                if (targetEl.length) {
                    let scrollTarget = $('html, body');
                    let targetOffset = targetEl.offset().top - 80;
                    
                    if ($("main").css("overflow-y") === "scroll" || $("main").css("overflow-y") === "auto") {
                        scrollTarget = $("main");
                        targetOffset = targetEl.offset().top - $("main").offset().top + $("main").scrollTop() - 80;
                    }

                    scrollTarget.animate({
                        scrollTop: targetOffset
                    }, 500);

                    // Sync tab states
                    $(".form-nav-tab")
                        .removeClass("border-brand-500 bg-brand-50 text-brand-700")
                        .addClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100");
                    
                    $(this)
                        .addClass("border-brand-500 bg-brand-50 text-brand-700")
                        .removeClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100");

                    $(this).find('span')
                        .addClass("bg-brand-500 text-white")
                        .removeClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100 bg-slate-200");   

                    // Visual feedback blink on the section target
                    $(target).addClass("ring-2 ring-brand-500/30").delay(800).queue(function(next) {
                        $(this).removeClass("ring-2 ring-brand-500/30");
                        next();
                    });
                }
            });

            // SCROLLSPY MECHANISM: Highlight vertical stepper tabs automatically as user scrolls
            let scrollEl = $("main").css("overflow-y") === "scroll" || $("main").css("overflow-y") === "auto" ? $("main") : $(window);
            scrollEl.on("scroll", function() {
                let scrollTop = $(this).scrollTop();
                let steps = ["#form-step-1", "#form-step-2", "#form-step-3"];
                let activeId = null;

                // Check if user has scrolled to the bottom of the container
                let isBottom = false;
                if (scrollEl[0] === window) {
                    isBottom = (scrollTop + $(window).height() >= $(document).height() - 150);
                } else {
                    isBottom = (scrollTop + scrollEl.innerHeight() >= scrollEl[0].scrollHeight - 150);
                }

                if (isBottom) {
                    activeId = steps[steps.length - 1];
                } else {
                    steps.forEach(id => {
                        let el = $(id);
                        if (el.length) {
                            let top = 0;
                            if (scrollEl[0] === window) {
                                top = el.offset().top - 150;
                            } else {
                                top = el.offset().top - $("main").offset().top + scrollTop - 150;
                            }
                            if (scrollTop >= top) {
                                activeId = id;
                            }
                        }
                    });
                }

                if (activeId) {
                    $(".form-nav-tab")
                        .removeClass("border-brand-500 bg-brand-50 text-brand-700")
                        .addClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100");

                    $(".form-nav-tab").find('span')
                        .removeClass("bg-brand-500 text-white")
                        .addClass("bg-slate-200 text-slate-600");

                    let activeTab = $(`.form-nav-tab[data-scroll-target="${activeId}"]`);
                    activeTab
                        .addClass("border-brand-500 bg-brand-50 text-brand-700")
                        .removeClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100");

                    activeTab.find('span')
                        .addClass("bg-brand-500 text-white")
                        .removeClass("bg-slate-200 text-slate-600");
                }
            });
        });
    </script>
@endsection