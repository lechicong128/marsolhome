@extends('admin.layouts.index')
@section('page_title', lang('manage_home'))
@section('content')
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
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
                    <i class="fa-solid fa-hotel text-brand-500"></i>
                    @if(isset($home))
                        Cập nhật thông tin bất động sản: <span class="text-brand-600 font-extrabold">{{ $home->code ?? 'BĐS-'.$home->id }}</span>
                    @else
                        Tạo tin đăng bất động sản mới
                    @endif
                </h2>
                <a href="admin/manage_home/list" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left-long"></i> Quay lại danh sách
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
                            <span class="w-6 h-6 bg-slate-200 text-slate-600 rounded-full flex items-center justify-center text-xs font-black flex-shrink-0">3</span> Người liên hệ
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

                        <!-- Loại nhà ở & Pháp lý & Trạng thái -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Loại Bất Động Sản <span class="text-red-500">*</span></label>
                                <select name="property_type" required class="select2 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="">-- Chọn Loại BĐS --</option>
                                    @foreach($property_types as $pt)
                                        <option value="{{ $pt->id }}" {{ (isset($home) && $home->property_type == $pt->id) ? 'selected' : '' }}>{{ $pt->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Giấy tờ pháp lý</label>
                                <select name="legal_id" class="select2 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="">-- Chọn Giấy tờ pháp lý --</option>
                                    @foreach($legal_documents as $doc)
                                        <option value="{{ $doc->id }}" {{ (isset($home) && $home->legal_id == $doc->id) ? 'selected' : '' }}>{{ $doc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Vị trí Địa Lý chi tiết -->
                        <div class="space-y-3">
                            <label class="block text-sm font-bold text-slate-600 uppercase mb-2">Địa chỉ chi tiết của tài sản <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <select id="select-province" name="province_id" required class="select2 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="">-- Chọn Tỉnh/Thành --</option>
                                    @foreach($provinces as $p)
                                        <option value="{{ $p->province_id }}" {{ (isset($home) && $home->province_id == $p->province_id) ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <select id="select-ward" name="ward_id" required class="select2 w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="">-- Chọn Phường/Xã --</option>
                                    @foreach($wards as $w)
                                        <option value="{{ $w->wards_id }}" {{ (isset($home) && $home->ward_id == $w->wards_id) ? 'selected' : '' }}>{{ $w->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <input type="text" name="address" required value="{{ $home->address ?? '' }}" placeholder="Số nhà, Tên đường (Ví dụ: Số 123 Đường Quốc Lộ 32)" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                        </div>

                        <!-- Giá & Diện tích -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Mức Giá (VNĐ) <span class="text-red-500">*</span></label>
                                <input type="number" id="form-price-input" name="price" value="{{ $home->price ?? '' }}" required placeholder="Ví dụ: 3000000000" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold text-brand-600">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Diện Tích (m²) <span class="text-red-500">*</span></label>
                                <input type="number" name="area" value="{{ $home->area ?? '' }}" required placeholder="Ví dụ: 100" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Đường vào (m) <span class="text-red-500">*</span></label>
                                <input type="number" id="entrance" name="entrance" value="{{ $home->entrance ?? '' }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Mặt tiền (m) <span class="text-red-500">*</span></label>
                                <input type="number" name="facade" value="{{ $home->facade ?? '' }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-extrabold">
                            </div>
                        </div>

                        <!-- Số phòng và hướng -->
                        <div class="grid grid-cols-3 gap-5">
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3 text-center">Phòng ngủ</label>
                                <input type="number" name="beds" min="0" value="{{ $home->beds ?? 1 }}" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3 text-center">Phòng tắm</label>
                                <input type="number" name="baths" min="0" value="{{ $home->baths ?? 1 }}" class="w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm text-center font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3 text-center">Hướng nhà</label>
                                <select name="direction_id" class="select2 w-full px-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:ring-2 focus:ring-brand-500">
                                    <option value="">-- Hướng nhà --</option>
                                    @foreach($orientations as $ori)
                                        <option value="{{ $ori->id }}" {{ (isset($home) && $home->direction_id == $ori->id) ? 'selected' : '' }}>{{ $ori->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Chi tiết nội thất -->
                        <div class="bg-slate-50/70 p-6 rounded-2xl border border-slate-200/60 space-y-5">
                            <h4 class="text-sm font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                                <i class="fa-solid fa-couch text-brand-500"></i> Hiện trạng & Chi tiết nội thất
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 uppercase mb-2">Tình trạng bàn giao</label>
                                    <select name="interior_id" class="select2 w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm font-semibold focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                        <option value="">-- Chọn tình trạng bàn giao --</option>
                                        @foreach($interior_handovers as $interior)
                                            <option value="{{ $interior->id }}" {{ (isset($home) && $home->interior_id == $interior->id) ? 'selected' : '' }}>{{ $interior->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-600 uppercase mb-2">Ghi chú thêm về nội thất</label>
                                    <input type="text" name="interior_note" value="{{ $home->interior_note ?? '' }}" placeholder="Ví dụ: Sofa da thật, bếp từ nhập khẩu Đức..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Trang thiết bị nội thất có sẵn</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach($interior_amenities as $amenity)
                                        @php
                                            $checked = false;
                                            if (isset($home)) {
                                                $checked = $home->interior_amenities->contains('id', $amenity->id);
                                            }
                                        @endphp
                                        <label class="flex items-center gap-2.5 text-sm text-slate-700 bg-white px-3.5 py-2.5 border border-slate-200 rounded-xl cursor-pointer hover:border-brand-400 hover:bg-brand-50/50 transition-all">
                                            <input type="checkbox" name="interior_amenities[]" value="{{ $amenity->id }}" {{ $checked ? 'checked' : '' }} class="rounded text-brand-500 focus:ring-brand-500 w-4 h-4"> {{ $amenity->name }}
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Tiêu đề & Mô tả -->
                        <div>
                            <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tiêu Đề Tin Đăng <span class="text-red-500">*</span></label>
                            <input type="text" id="form-title-input" name="title" value="{{ $home->title ?? '' }}" required placeholder="Ví dụ: Bán căn hộ 1PN, 1WC tại The Queen Villas, 3 tỷ VND, 100m2..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 font-semibold">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Mô Tả Chi Tiết Nội Dung <span class="text-red-500">*</span></label>
                            <textarea name="description" required rows="5" placeholder="Mô tả chi tiết cấu trúc, nội thất, tiện ích lân cận..." class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 leading-relaxed">{{ $home->description ?? '' }}</textarea>
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
                                    <i class="fa-solid fa-xmark"></i>
                                </div>
                                <span class="text-slate-600 font-bold">Đăng tối thiểu 3 ảnh (Hiện tại: <span id="photo-current-count">0</span>)</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
                                <div id="status-video-state" class="w-4 h-4 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center text-xs font-black">
                                    <i class="fa-solid fa-exclamation"></i>
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
                                            <p class="font-bold truncate"><i class="fa-solid fa-circle-play mr-1"></i>Xem thử video dọc thành công</p>
                                        </div>
                                    </div>

                                    <!-- Simulated click placeholder upload panel -->
                                    <div id="video-upload-placeholder" class="z-10 p-4 text-center flex flex-col items-center justify-center space-y-3 cursor-pointer">
                                        <div class="w-12 h-12 bg-slate-900 text-brand-500 rounded-full flex items-center justify-center border border-slate-800 shadow">
                                            <i class="fa-solid fa-video text-base"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-300">Nhấp để tải lên</p>
                                            <p class="text-xs text-slate-500 mt-1 leading-normal">Tỉ lệ dọc chuẩn 9:16 (MP4 / MOV)</p>
                                        </div>
                                    </div>

                                    <!-- Delete overlay button on top right -->
                                    <button type="button" id="btn-delete-video" class="hidden absolute top-2 right-2 w-7 h-7 bg-red-600 hover:bg-red-700 active:scale-95 text-white rounded-full flex items-center justify-center z-30 shadow shadow-black/30 transition-all focus:outline-none">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
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
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <p class="text-sm font-bold text-slate-800">Bấm vào đây để tải lên hình ảnh (<span id="count-media-txt">0</span>/24)</p>
                                    <p class="text-xs text-slate-400 mt-1">Định dạng hỗ trợ: PNG, JPG, JPEG, GIF. Đăng ít nhất 3 ảnh sắc nét.</p>
                                </div>

                                <!-- Media loading dynamic spinner -->
                                <div id="form-upload-loader" class="hidden p-4 bg-brand-50 border border-brand-100 rounded-2xl text-sm text-brand-800 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-notch animate-spin text-brand-500"></i> Đang tải dữ liệu hình ảnh lên hệ thống...
                                </div>

                                <!-- Uploaded Previews grid -->
                                <div id="form-media-list" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    <!-- Filled dynamically via Javascript -->
                                </div>

                                <!-- Hidden native images input -->
                                <input type="file" id="real-images-input" accept="image/*" multiple class="hidden">
                            </div>
                        </div>

                    </div>

                    <!-- BƯỚC 3: ĐỊA CHỈ LIÊN HỆ ĐĂNG TIN -->
                    <div id="form-step-3" class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm space-y-7 scroll-mt-24">
                        <h3 class="text-xl md:text-xl font-black uppercase tracking-wider text-brand-600 pb-4 border-b border-slate-100 flex items-center gap-3">
                            <span class="w-7 h-7 bg-brand-500 text-white rounded-full inline-flex items-center justify-center text-sm font-black">3</span>
                            Thông tin địa chỉ liên hệ chủ tin
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Vai trò liên hệ <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center justify-center gap-1 py-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-brand-500 has-[:checked]:border-brand-500 has-[:checked]:text-white text-sm font-bold">
                                        <input type="radio" name="contact_role" value="Chính chủ" {{ (!isset($home) || $home->contact_role == 'Chính chủ') ? 'checked' : '' }} class="sr-only"> Chính chủ
                                    </label>
                                    <label class="flex items-center justify-center gap-1 py-3 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-brand-500 has-[:checked]:border-brand-500 has-[:checked]:text-white text-sm font-bold">
                                        <input type="radio" name="contact_role" value="Môi giới" {{ (isset($home) && $home->contact_role == 'Môi giới') ? 'checked' : '' }} class="sr-only"> Môi giới
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Hẹn thời gian liên hệ tốt nhất</label>
                                <input type="text" name="contact_time" value="{{ $home->contact_time ?? '' }}" placeholder="Ví dụ: 8h00 - 21h00 các ngày trong tuần" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Tên người liên hệ <span class="text-red-500">*</span></label>
                                <input type="text" name="contact_name" value="{{ $home->contact_name ?? '' }}" required placeholder="Họ và tên người đại diện" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-600 uppercase mb-3">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                                <input type="tel" name="contact_phone" value="{{ $home->contact_phone ?? '' }}" required placeholder="Ví dụ: 090xxxxxxx" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                           
                        </div>
                       
                    </div>
                        <!-- Form submission action row -->
                        <div class="flex items-center justify-end gap-4 pt-4">
                            <a href="admin/manage_home/list" class="px-6 py-3.5 bg-slate-100 hover:bg-slate-200 active:scale-95 text-slate-700 font-bold text-sm rounded-xl transition-all flex items-center gap-2">
                                Hủy bỏ
                            </a>
                            <button type="button" id="btn-save-property" class="px-10 py-3.5 bg-brand-500 hover:bg-brand-600 active:bg-brand-700 text-white font-black text-sm rounded-xl shadow-md shadow-brand-500/20 transition-all flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> LƯU LẠI
                            </button>
                        </div>
                    </form>
                </div><!-- end col-md-10 -->
            </div><!-- end row -->
        </div><!-- end view-create -->

        </div><!-- end card-box -->
      </div><!-- end col-sm-12 -->
    </div><!-- end row -->

    <!-- CUSTOM CONFIRM MODAL SYSTEM (Xác nhận an toàn, không block ứng dụng) -->
    <div id="confirm-modal-wrapper" class="fixed inset-0 bg-slate-950/60 z-50 flex items-center justify-center p-4 hidden backdrop-blur-sm">
        <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-xl border border-slate-100 transform scale-95 opacity-0 transition-all duration-300 space-y-4">
            <div class="w-12 h-12 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-lg shadow-sm">
                <i class="fa-solid fa-triangle-exclamation"></i>
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

    <!-- NOTIFICATION SYSTEM TOASTS CONTAINER -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-2 pointer-events-none"></div>
@endsection

@section('script')
    <script>
        // System Data store loaded from Backend
        let uploadedFormVideo = @json(isset($home) ? $home->video_url : '');
        let selectedVideoFile = null; // Stores new File object if selected

        // Unified media items array containing both existing and new files
        let mediaItems = [];
        
        // Initialize with existing media
        const initialMedia = @json(isset($home) && !empty($home->media) ? (is_array($home->media) ? $home->media : json_decode($home->media, true)) : []);
        const initialCaptions = @json(isset($home) && !empty($home->media_captions) ? (is_array($home->media_captions) ? $home->media_captions : json_decode($home->media_captions, true)) : []);
        
        initialMedia.forEach((url, idx) => {
            mediaItems.push({
                type: 'existing',
                url: url,
                caption: initialCaptions[idx] || ""
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
            let icon = '<i class="fa-solid fa-circle-info text-blue-500 text-sm"></i>';
            
            if (type === "success") {
                bgClass = "bg-emerald-50/90 backdrop-blur-md border-emerald-200 text-emerald-900";
                icon = '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>';
            } else if (type === "warning") {
                bgClass = "bg-amber-50/90 backdrop-blur-md border-amber-200 text-amber-900";
                icon = '<i class="fa-solid fa-circle-notch animate-spin text-amber-500 text-sm"></i>';
            } else if (type === "error") {
                bgClass = "bg-rose-50/90 backdrop-blur-md border-rose-200 text-rose-900";
                icon = '<i class="fa-solid fa-triangle-exclamation text-rose-500 text-sm"></i>';
            } else if (type === "info") {
                bgClass = "bg-blue-50/90 backdrop-blur-md border-blue-200 text-blue-900";
                icon = '<i class="fa-solid fa-circle-info text-blue-500 text-sm"></i>';
            }

            const toastHTML = `
                <div id="${toastId}" class="flex items-center gap-3 p-3.5 rounded-2xl border shadow-lg max-w-xs pointer-events-auto transition-all duration-300 transform translate-x-12 opacity-0 text-xs font-bold ${bgClass}">
                    ${icon}
                    <div class="flex-1">${message}</div>
                    <button onclick="removeToast('${toastId}')" class="text-slate-400 hover:text-slate-600 focus:outline-none"><i class="fa-solid fa-xmark"></i></button>
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
                indicator.html('<i class="fa-solid fa-check"></i>');
            } else {
                indicator.removeClass("bg-emerald-100 text-emerald-600").addClass("bg-red-100 text-red-600");
                indicator.html('<i class="fa-solid fa-xmark"></i>');
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
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            ${idx === 0 ? '<span class="absolute bottom-1 left-1 bg-brand-500 text-white font-black px-1.5 py-0.5 rounded text-[10px] uppercase">Ảnh bìa</span>' : ''}
                        </div>
                    </div>
                `);
            });
        }

        $(document).ready(function() {
            // Setup CSRF Header for JQuery AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Render existing media if any
            renderFormMediaThumbnails();
            checkMinPhotoCriteria();

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
                $("#status-video-state").removeClass("bg-brand-100 text-brand-600").addClass("bg-emerald-100 text-emerald-600").html('<i class="fa-solid fa-check"></i>');
                $("#btn-delete-video").removeClass("hidden");
            }

            // Province change handler
            $("#select-province").on("change", function() {
                let provinceId = $(this).val();
                let selectDistrict = $("#select-district");
                let selectWard = $("#select-ward");
                
                selectDistrict.empty().append('<option value="">-- Chọn Quận/Huyện --</option>');
                selectWard.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
                
                if (provinceId) {
                    $.ajax({
                        url: 'admin/manage_home/getDistricts',
                        type: 'POST',
                        data: { province_id: provinceId },
                        success: function(data) {
                            data.forEach(function(district) {
                                selectDistrict.append('<option value="' + district.district_id + '">' + district.name + '</option>');
                            });
                        },
                        error: function() {
                            showToast("Lỗi khi tải danh sách Quận/Huyện", "error");
                        }
                    });
                }
            });

            // District change handler
            $("#select-district").on("change", function() {
                let districtId = $(this).val();
                let selectWard = $("#select-ward");
                
                selectWard.empty().append('<option value="">-- Chọn Phường/Xã --</option>');
                
                if (districtId) {
                    $.ajax({
                        url: 'admin/manage_home/getWards',
                        type: 'POST',
                        data: { district_id: districtId },
                        success: function(data) {
                            data.forEach(function(ward) {
                                selectWard.append('<option value="' + ward.wards_id + '">' + ward.name + '</option>');
                            });
                        },
                        error: function() {
                            showToast("Lỗi khi tải danh sách Phường/Xã", "error");
                        }
                    });
                }
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
                    $("#status-video-state").removeClass("bg-brand-100 text-brand-600").addClass("bg-emerald-100 text-emerald-600").html('<i class="fa-solid fa-check"></i>');
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
                $("#status-video-state").removeClass("bg-emerald-100 text-emerald-600").addClass("bg-brand-100 text-brand-600").html('<i class="fa-solid fa-exclamation"></i>');
                $("#btn-delete-video").addClass("hidden");
                showToast("Đã gỡ bỏ video đại diện!", "info");
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

                // 3. Assemble FormData
                let id = $("#edit-property-id").val();
                let formData = new FormData(form);

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

                // Add video
                if (selectedVideoFile) {
                    formData.append('video_file', selectedVideoFile);
                } else if (uploadedFormVideo) {
                    formData.append('video_url', uploadedFormVideo);
                } else {
                    formData.append('video_url', '');
                }

                showToast("Đang lưu thông tin...", "warning");
                
                let submitUrl = 'admin/manage_home/detail' + (id ? '/' + id : '');
                
                $.ajax({
                    url: submitUrl,
                    type: 'POST',
                    data: formData,
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

                if (activeId) {
                    $(".form-nav-tab")
                        .removeClass("border-brand-500 bg-brand-50 text-brand-700")
                        .addClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100");

                    $(`.form-nav-tab[data-scroll-target="${activeId}"]`)
                        .addClass("border-brand-500 bg-brand-50 text-brand-700")
                        .removeClass("border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100");
                }
            });
        });
    </script>
@endsection