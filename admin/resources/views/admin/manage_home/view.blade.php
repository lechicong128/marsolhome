@extends('admin.layouts.index')
@section('page_title', $title)
@section('content')
    <!-- Tailwind CSS & Config -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: { preflight: false },
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe',
                            300: '#93c5fd', 400: '#60a5fa', 500: '#005ae0',
                            600: '#004ec4', 700: '#003ea1', 800: '#1e40af',
                            900: '#1e3a8a', 950: '#172554',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <style>
        .view-page { font-family: 'Inter', sans-serif; font-size: 14px; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Hiệu ứng nhấp nháy cho badge trạng thái */
        @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 0.5; } 100% { transform: scale(2.5); opacity: 0; } }
        .status-dot { position: relative; }
        .status-dot::before { content: ''; position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: inherit; border-radius: inherit; animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite; }

        /* Lightbox Gallery mượt mà */
        .gallery-overlay {
            position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95); z-index: 9999;
            display: none; align-items: center; justify-content: center; backdrop-filter: blur(8px);
        }
        .gallery-overlay.active { display: flex; }
        .gallery-overlay img { 
            max-width: 90vw; max-height: 85vh; border-radius: 12px; 
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); object-fit: contain; border: 1px solid rgba(255,255,255,0.1); 
        }
        
        .gallery-btn {
            position: absolute; width: 48px; height: 48px; border-radius: 50%;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: white; display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; top: 50%; transform: translateY(-50%); font-size: 18px;
        }
        .gallery-btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-50%) scale(1.1); }
        .gallery-prev { left: 32px; } .gallery-next { right: 32px; }
        
        .gallery-close {
            position: absolute; top: 32px; right: 32px; width: 44px; height: 44px;
            border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
            color: white; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .gallery-close:hover { background: rgba(239, 68, 68, 0.8); border-color: rgba(239, 68, 68, 1); }
        
        .gallery-counter {
            position: absolute; bottom: 32px; left: 50%; transform: translateX(-50%);
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 6px 20px;
            border-radius: 999px; font-size: 14px; font-weight: 600; backdrop-filter: blur(4px);
        }

        /* Hover animations */
        .img-thumb { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .img-thumb:hover { transform: scale(1.05); z-index: 10; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        .bento-card { transition: all 0.3s ease; }
        .bento-card:hover { transform: translateY(-3px); background: #f8fafc; }
        .bento-icon { transition: transform 0.3s; }
        .bento-card:hover .bento-icon { transform: scale(1.15); }

        /* Sticky Layouts */
        .sticky-header-custom {
            position: sticky !important;
            top: var(--header-height, 60px) !important;
            z-index: 1020 !important;
        }
        @media (min-width: 992px) {
            .sticky-sidebar-custom {
                position: sticky !important;
                top: calc(var(--header-height, 60px) + 105px) !important;
                max-height: calc(100vh - var(--header-height, 60px) - 120px) !important;
                overflow-y: auto !important;
            }
        }
    </style>

    <div class="view-page bg-slate-50/50 min-h-screen py-8 -m-5">
        <div class="max-w-[1000px] mx-auto px-4 sm:px-6 lg:px-8 space-y-6 lg:space-y-8">

            <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG -->
            <div class="sticky-header-custom bg-white p-5 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center text-brand-600 shadow-sm border border-brand-100 shrink-0">
                        <i class="fa fa-building-o text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold uppercase text-slate-800 tracking-tight">
                            Chi tiết BĐS: <span class="text-brand-600 ml-1">{{ $home->code ?? 'BĐS-'.$home->id }}</span>
                        </h2>
                        <p class="text-sm text-slate-500 font-medium mt-0.5">
                            Đã đăng lúc {{ $home->created_at ? $home->created_at->format('d/m/Y H:i') : 'N/A' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="admin/manage_home/list" class="flex-1 sm:flex-none px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                    <a href="admin/manage_home/detail/{{ $home->id }}" class="flex-1 sm:flex-none px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-semibold text-sm rounded-xl transition-all shadow-md shadow-brand-500/20 flex items-center justify-center gap-2">
                        <i class="fa fa-pencil"></i> Chỉnh sửa
                    </a>
                </div>
            </div>

            <!-- CHIA CỘT MAIN -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- CỘT TRÁI (KHU VỰC ẢNH & MÔ TẢ) -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- GALLERY HÌNH ẢNH -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden group">
                        @if(count($mediaUrls) > 0)
                            <!-- Thumbnail Ảnh Lớn -->
                            <div class="relative cursor-pointer overflow-hidden bg-slate-100 aspect-video md:aspect-[16/9]" onclick="openGallery(0)">
                                <img id="hero-img" src="{{ $mediaUrls[0] }}" alt="Ảnh đại diện" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" onerror="this.src='https://placehold.co/800x400/e2e8f0/1e293b?text=BĐS'">
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="absolute bottom-4 left-4 z-10 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
                                    <span class="bg-white/90 backdrop-blur-md text-slate-800 text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 border border-white/20 shadow">
                                        <i class="fa fa-picture-o"></i> {{ count($mediaUrls) }} ảnh
                                    </span>
                                </div>
                                <div class="absolute top-4 right-4 z-10 opacity-0 group-hover:opacity-100 transition-all duration-300 -translate-y-2 group-hover:translate-y-0">
                                    <span class="bg-black/40 hover:bg-black/60 backdrop-blur-md text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5 border border-white/10 transition-colors">
                                        <i class="fa fa-expand"></i> Xem lớn
                                    </span>
                                </div>
                            </div>

                            <!-- List Hình Nhỏ -->
                            @if(count($mediaUrls) > 1)
                            <div class="p-3 grid grid-cols-5 gap-2.5 bg-white relative z-20 border-t border-slate-100">
                                @foreach($mediaUrls as $idx => $url)
                                    @if($idx < 5)
                                    <div class="relative img-thumb rounded-lg overflow-hidden aspect-[4/3] {{ $idx == 0 ? 'ring-2 ring-brand-500 ring-offset-1' : '' }}" onclick="openGallery({{ $idx }})">
                                        <img src="{{ $url }}" alt="Ảnh {{ $idx + 1 }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/200x100/e2e8f0/1e293b?text=Ảnh'">
                                        @if($idx == 4 && count($mediaUrls) > 5)
                                            <div class="absolute inset-0 bg-black/50 hover:bg-black/60 backdrop-blur-[2px] transition-colors flex items-center justify-center">
                                                <span class="text-white font-bold text-sm tracking-widest">+{{ count($mediaUrls) - 5 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        @else
                            <div class="aspect-[16/9] bg-slate-50 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fa fa-picture-o text-5xl text-slate-300 mb-3 block"></i>
                                    <p class="text-sm text-slate-500 font-medium tracking-wide">Chưa có hình ảnh</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- MỤC VIDEO -->
                    @if(!empty($videoUrl))
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-rose-50 rounded-lg flex items-center justify-center text-rose-500">
                                <i class="fa fa-play-circle"></i>
                            </div>
                            Video bất động sản
                        </h4>
                        <div class="rounded-xl overflow-hidden border border-slate-200 bg-black shadow-inner">
                            <video controls class="w-full max-h-[400px]" preload="metadata">
                                <source src="{{ $videoUrl }}" type="video/mp4">
                                Trình duyệt không hỗ trợ video.
                            </video>
                        </div>
                    </div>
                    @endif
                      <!-- HỒ SƠ PHÁP LÝ (CHỈ HIỆN KHI CÓ GIẤY TỜ) -->
                    @if(!empty($redBookUrls) || !empty($otherDocUrls))
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center text-brand-500">
                                <i class="fa fa-file-text-o"></i>
                            </div>
                            Hồ sơ pháp lý bất động sản
                        </h4>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Sổ đỏ, sổ hồng -->
                            @if(!empty($redBookUrls))
                            <div class="space-y-3">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-wider block">Giấy tờ sổ đỏ, sổ hồng</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($redBookUrls as $url)
                                        @php
                                            $isPdf = strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'pdf' || str_contains(strtolower($url), '.pdf');
                                        @endphp
                                        <div class="relative rounded-xl overflow-hidden aspect-video border border-slate-200 bg-slate-50 flex items-center justify-center">
                                            @if($isPdf)
                                                <a href="{{ $url }}" target="_blank" class="w-full h-full flex flex-col items-center justify-center p-2 text-slate-500 hover:text-brand-600 transition-colors">
                                                    <i class="fa fa-file-pdf-o text-red-500 text-2xl mb-1"></i>
                                                    <span class="text-[9px] truncate max-w-full font-semibold px-1" title="{{ basename($url) }}">{{ basename($url) }}</span>
                                                </a>
                                            @else
                                                <a href="{{ $url }}" data-lightbox="red-book" class="w-full h-full">
                                                    <img src="{{ $url }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/e2e8f0/1e293b?text=BĐS'">
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Giấy tờ khác -->
                            @if(!empty($otherDocUrls))
                            <div class="space-y-3">
                                <span class="text-sm font-bold text-slate-500 uppercase tracking-wider block">Giấy tờ khác</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($otherDocUrls as $url)
                                        @php
                                            $isPdf = strtolower(pathinfo($url, PATHINFO_EXTENSION)) === 'pdf' || str_contains(strtolower($url), '.pdf');
                                        @endphp
                                        <div class="relative rounded-xl overflow-hidden aspect-video border border-slate-200 bg-slate-50 flex items-center justify-center">
                                            @if($isPdf)
                                                <a href="{{ $url }}" target="_blank" class="w-full h-full flex flex-col items-center justify-center p-2 text-slate-500 hover:text-brand-600 transition-colors">
                                                    <i class="fa fa-file-pdf-o text-red-500 text-2xl mb-1"></i>
                                                    <span class="text-[9px] truncate max-w-full font-semibold px-1" title="{{ basename($url) }}">{{ basename($url) }}</span>
                                                </a>
                                            @else
                                                <a href="{{ $url }}" data-lightbox="other-docs" class="w-full h-full">
                                                    <img src="{{ $url }}" class="w-full h-full object-cover" onerror="this.src='https://placehold.co/600x400/e2e8f0/1e293b?text=BĐS'">
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center text-brand-500">
                                <i class="fa fa-align-left"></i>
                            </div>
                            Mô tả video 
                        </h4>
                        <div class="text-[15px] text-slate-600 leading-relaxed whitespace-pre-line">{!! $home->detail !!}</div>
                    </div>
                    <!-- MỤC MÔ TẢ CHI TIẾT -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center text-brand-500">
                                <i class="fa fa-align-left"></i>
                            </div>
                            Mô tả chi tiết
                        </h4>
                        <div class="text-[15px] text-slate-600 leading-relaxed whitespace-pre-line">{!! $home->description !!}</div>
                    </div>

                    <!-- BẢN ĐỒ -->
                    @if($home->latitude && $home->longitude)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-500">
                                <i class="fa fa-map-o"></i>
                            </div>
                            Vị trí trên bản đồ
                        </h4>
                        <div id="view-map" class="w-full h-[320px] rounded-xl border border-slate-200 shadow-inner z-10"></div>
                        <div class="flex items-center gap-2 text-xs text-slate-500 mt-4 font-medium bg-slate-50 px-3 py-2 rounded-lg inline-flex border border-slate-100 shadow-sm">
                            <i class="fa fa-crosshairs text-slate-400"></i>
                            <span>Tọa độ: {{ $home->latitude }}, {{ $home->longitude }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- BÌNH LUẬN -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7 mt-6">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-brand-50 rounded-lg flex items-center justify-center text-brand-500">
                                <i class="fa fa-comments"></i>
                            </div>
                            Bình luận (<span id="total-comment-count">0</span>)
                        </h4>


                        <!-- Danh sách bình luận -->
                        <div id="comments-container" class="space-y-6">
                            <div class="text-center py-6 text-slate-400">
                                <i class="fa fa-spinner fa-spin text-2xl mb-2"></i>
                                <p class="text-[15px]">Đang tải bình luận...</p>
                            </div>
                        </div>

                        <!-- Nút xem thêm -->
                        <div class="text-center mt-6 hidden" id="load-more-container">
                            <button id="btn-load-more" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-[15px] rounded-lg transition-colors cursor-pointer outline-none appearance-none">
                                Xem thêm bình luận
                            </button>
                        </div>
                    </div>

                    <!-- ĐÁNH GIÁ -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7 mt-6">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-5">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                                <i class="fa fa-star animate-pulse"></i>
                            </div>
                            Đánh giá (<span id="total-review-count">0</span>)
                        </h4>

                        <!-- Stats Dashboard -->
                        <div id="reviews-stats-container" class="grid grid-cols-1 md:grid-cols-12 gap-6 bg-slate-50/60 rounded-2xl p-5 border border-slate-100 mb-6 hidden">
                            <!-- Left col: Average rating -->
                            <div class="md:col-span-4 flex flex-col items-center justify-center text-center">
                                <div class="text-4xl font-black text-slate-800 tracking-tight" id="avg-star-value">0.0</div>
                                <div class="flex items-center gap-0.5 text-amber-400 my-2" id="avg-star-stars">
                                </div>
                                <div class="text-xs text-slate-500 font-bold uppercase tracking-wider" id="stats-total-reviews-text">0 đánh giá</div>
                            </div>
                            <!-- Right col: Rating bars -->
                            <div class="md:col-span-8 space-y-2">
                                @for($i = 5; $i >= 1; $i--)
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-extrabold text-slate-500 w-3">{{ $i }}</span>
                                    <i class="fa fa-star text-amber-400 text-xs"></i>
                                    <div class="flex-1 h-2 bg-slate-200/50 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-amber-400 to-orange-400 rounded-full transition-all duration-700" id="bar-star-{{ $i }}" style="width: 0%"></div>
                                    </div>
                                    <span class="text-xs font-bold text-slate-400 min-w-[20px] text-right" id="count-star-{{ $i }}">0</span>
                                </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div id="reviews-container" class="space-y-6">
                            <div class="text-center py-6 text-slate-400">
                                <i class="fa fa-spinner fa-spin text-2xl mb-2"></i>
                                <p class="text-[15px]">Đang tải đánh giá...</p>
                            </div>
                        </div>

                        <!-- Load More Button -->
                        <div class="text-center mt-6 hidden" id="load-more-reviews-container">
                            <button id="btn-load-more-reviews" class="px-4 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold text-[15px] rounded-lg transition-colors cursor-pointer outline-none appearance-none">
                                Xem thêm đánh giá
                            </button>
                        </div>
                    </div>
                </div>

                <!-- CỘT PHẢI (THÔNG TIN CHÍNH) -->
                <div class="lg:col-span-5 space-y-6 lg:self-start sticky-sidebar-custom lg:pr-2">

                    <!-- CARD TIÊU ĐỀ BĐS -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7 relative overflow-hidden">
                        <!-- Decorative gradient blend -->
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

                        <!-- Các thẻ nhãn -->
                        <div class="flex items-center gap-2 mb-4 flex-wrap relative z-10">
                            @php
                                $statusInfo = getListStatusHome($home->status);
                                $statusText = $statusInfo['name'] ?? 'Không xác định';
                                $statusColors = $statusInfo['badge_class'] ?? 'bg-slate-50 text-slate-600 border-slate-200';
                                $dotColor = $statusInfo['color'] ?? '#64748b';
                            @endphp
                            <span class="px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase border rounded-full inline-flex items-center gap-2 {{ $statusColors }}">
                                <span class="w-2 h-2 rounded-full status-dot" style="background-color: {{ $dotColor }}"></span>
                                {{ $statusText }}
                            </span>

                            @if(!empty($home->end_date) && $home->end_date < date('Y-m-d'))
                            <span class="px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase border rounded-full inline-flex items-center gap-2 bg-red-50 text-red-700 border-red-300">
                                <span class="w-2 h-2 rounded-full status-dot" style="background-color: #ef4444"></span>
                                Tin Hết Hạn
                            </span>
                            @endif
                            
                            @if($typeHome)
                            <span class="px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase rounded-full border" style="color: {{ $typeHome['color'] }}; background-color: {{ $typeHome['background_color'] }}; border-color: {{ $typeHome['background_color'] }};">
                                {{ $typeHome['name'] }}
                            </span>
                            @endif
                            
                            @if($home->propertyType)
                            <span class="px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase rounded-full bg-brand-50 text-brand-700 border border-brand-100 flex items-center gap-1.5">
                                <i class="fa fa-tag"></i> {{ $home->propertyType->name }}
                            </span>
                            @endif
                            
                        </div>

                        <!-- Tiêu đề lớn -->
                        <h1 class="text-2xl font-bold text-slate-900 leading-tight mb-4 relative z-10">
                            {{ $home->title ?? 'Chưa có tiêu đề' }}
                        </h1>

                        <!-- Địa chỉ -->
                        <div class="flex items-start gap-2.5 mb-5 relative z-10">
                            <i class="fa fa-map-marker text-brand-500 text-lg mt-0.5 shrink-0"></i>
                            <p class="text-[15px] text-slate-600 font-medium leading-relaxed">
                                {{ $full_address ?: 'Chưa cập nhật địa chỉ' }}
                            </p>
                        </div>

                        <!-- Stats Box: Yêu thích, Like, Lưu -->
                        <div class="bg-slate-50/60 border border-slate-100 rounded-xl p-4 flex items-center justify-around text-center mb-6 relative z-10">
                            <div class="flex-1">
                                <span class="text-xl font-extrabold text-slate-800">{{ $home->favourite->count() }}</span>
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mt-1 flex items-center justify-center gap-1.5">
                                    <i class="fa fa-bookmark text-emerald-500"></i> Lưu
                                </p>
                            </div>
                            <div class="h-8 w-px bg-slate-200"></div>
                            <div class="flex-1">
                                <span class="text-xl font-extrabold text-slate-800">0</span>
                                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wide mt-1 flex items-center justify-center gap-1.5">
                                    <i class="fa fa-share text-brand-500"></i> Chia sẻ
                                </p>
                            </div>
                        </div>

                        <!-- Mức giá -->
                        <div class="bg-gradient-to-r from-rose-50 to-orange-50 rounded-2xl p-5 border border-rose-100/50 relative z-10">
                            <p class="text-xs text-rose-500 font-bold uppercase tracking-wider mb-1">Mức giá đề xuất</p>
                            <div class="flex items-baseline gap-2">
                                <p class="text-3xl font-black text-rose-600 tracking-tight">{{ number_format($home->price ?? 0, 0, ',', '.') }}</p>
                                <span class="text-sm font-bold text-rose-500/80">VNĐ{{ $home->type == 2 ? ' / Tháng' : '' }}</span>
                            </div>
                        </div>

                        <!-- Chỉ Số Đầu Tư & Tài Chính -->
                         @if($home->type == 1)
                        <div class="mt-4 relative z-10">
                            <h4 class="font-bold text-slate-800 text-[16px] mb-3">Chỉ Số Đầu Tư & Tài Chính</h4>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="bg-brand-50/70 border border-brand-100/30 rounded-xl p-3 flex flex-col justify-between">
                                    <span class="text-[11px] text-slate-500 font-medium leading-tight">Khả năng vay</span>
                                    <span class="text-[13px] font-extrabold text-slate-800 mt-1.5">{{$home->loanability}}</span>
                                </div>
                                <div class="bg-brand-50/70 border border-brand-100/30 rounded-xl p-3 flex flex-col justify-between">
                                    <span class="text-[11px] text-slate-500 font-medium leading-tight">Dòng tiền/ tháng</span>
                                    <span class="text-[13px] font-extrabold text-slate-800 mt-1.5">{{formatMoneyVN($home->currently_rent)}}</span>
                                </div>
                                <div class="bg-brand-50/70 border border-brand-100/30 rounded-xl p-3 flex flex-col justify-between">
                                    <span class="text-[11px] text-slate-500 font-medium leading-tight">Tỷ suất lợi nhuận</span>
                                    <span class="text-[13px] font-extrabold text-slate-800 mt-1.5">{{formatMoney($home->profit)}} %</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- PHÁP LÝ & CHI TIẾT -->
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center text-sky-500">
                                <i class="fa fa-file-text-o text-lg"></i>
                            </div>
                            Pháp lý & Thông số chi tiết
                        </h4>
                        <div class="divide-y divide-slate-100/80">
                            @if($home->area)
                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Diện tích</span>
                                <span class="text-[15px] text-slate-900 font-bold">{{ $home->area }} m²</span>
                            </div>
                            @endif

                            @if($home->type == 1)
                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Giá/m2</span>
                                <span class="text-[15px] text-slate-900 font-bold">{{ formatMoneyVN($home->price_m2)}} / m2</span>
                            </div>
                            @endif

                            @if($home->direction)
                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Hướng nhà</span>
                                <span class="text-[15px] text-slate-900 font-bold">{{ $home->direction->name }}</span>
                            </div>
                            @endif

                            @if($home->entrance)
                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Đường vào</span>
                                <span class="text-[15px] text-slate-900 font-bold">{{ $home->entrance }} m</span>
                            </div>
                            @endif

                            @if($home->facade)
                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Mặt tiền</span>
                                <span class="text-[15px] text-slate-900 font-bold">{{ $home->facade }} m</span>
                            </div>
                            @endif

                            @if($home->legal)
                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Giấy tờ pháp lý</span>
                                <span class="text-[15px] text-slate-900 font-bold text-right pl-4">{{ $home->legal->name }}</span>
                            </div>
                            @endif
                            
                            @if($home->type == 2)
                                @if($home->move_in_time)
                                <div class="flex items-center justify-between py-3.5">
                                    <span class="text-[15px] text-slate-500 font-medium">Thời gian vào ở</span>
                                    <span class="text-[15px] text-slate-900 font-bold text-right pl-4">{{ $home->move_in_time }}</span>
                                </div>
                                @endif
                                @if($home->electricity_price)
                                <div class="flex items-center justify-between py-3.5">
                                    <span class="text-[15px] text-slate-500 font-medium">Giá điện</span>
                                    <span class="text-[15px] text-slate-900 font-bold text-right pl-4">{{ $home->electricity_price }}</span>
                                </div>
                                @endif
                                @if($home->water_price)
                                <div class="flex items-center justify-between py-3.5">
                                    <span class="text-[15px] text-slate-500 font-medium">Giá nước</span>
                                    <span class="text-[15px] text-slate-900 font-bold text-right pl-4">{{ $home->water_price }}</span>
                                </div>
                                @endif
                                @if($home->internet_price)
                                <div class="flex items-center justify-between py-3.5">
                                    <span class="text-[15px] text-slate-500 font-medium">Giá internet</span>
                                    <span class="text-[15px] text-slate-900 font-bold text-right pl-4">{{ $home->internet_price }}</span>
                                </div>
                                @endif
                            @endif

                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Mã trên hệ thống</span>
                                <span class="text-[15px] text-brand-600 font-black">{{ $home->code ?? 'BĐS-'.$home->id }}</span>
                            </div>

                            <div class="flex items-center justify-between py-3.5">
                                <span class="text-[15px] text-slate-500 font-medium">Cập nhật lần cuối</span>
                                <span class="text-[15px] text-slate-700 font-semibold">{{ $home->updated_at ? $home->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- THÔNG SỐ BỔ SUNG -->
                    @php
                        $homeUtilities = $home->utilities()->with('options')->get();
                        $additionalSpecs = $homeUtilities->filter(function($u) {
                            return !in_array($u->name, ['Số tầng', 'Đường vào (m)', 'Mặt tiền (m)']);
                        });
                    @endphp
                    @if($additionalSpecs->count() > 0)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                        <h4 class="font-bold text-slate-900 text-[16px] uppercase tracking-wide flex items-center gap-2.5 mb-3">
                            <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center text-sky-500">
                                <i class="fa fa-list-alt text-lg"></i>
                            </div>
                            Thông số bổ sung
                        </h4>
                        <div class="divide-y divide-slate-100/80">
                            @foreach($additionalSpecs as $spec)
                                @php
                                    $displayVal = $spec->pivot->value;
                                    if ($spec->input_type === 'select' && $spec->options->isNotEmpty()) {
                                        $selectedOpt = $spec->options->firstWhere('id', $spec->pivot->value);
                                        if ($selectedOpt) {
                                            $displayVal = $selectedOpt->name;
                                        }
                                    } elseif ($spec->input_type === 'number') {
                                        $cleanVal = $displayVal;
                                        if (is_numeric($cleanVal) && $cleanVal !== '') {
                                            $displayVal = formatNumberNew($cleanVal);
                                        }
                                    }
                                @endphp
                                <div class="flex items-center justify-between py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        @if(!empty($spec->icon))
                                            <img src="{{ asset('storage/' . $spec->icon) }}" class="w-5 h-5 object-contain shrink-0" alt="{{ $spec->name }}">
                                        @else
                                            <i class="fa fa-info-circle text-slate-400 text-base shrink-0"></i>
                                        @endif
                                        <span class="text-[15px] text-slate-500 font-medium">{{ $spec->name }}</span>
                                    </div>
                                    <span class="text-[15px] text-slate-900 font-bold text-right pl-4">{{ $displayVal }} {{ $spec->unit ?? '' }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif


                    <!-- CARD LIÊN HỆ GIAO DIỆN PREMIUM TỐI -->
                    <div class="bg-slate-900 rounded-2xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.3)] p-6 sm:p-7 text-white relative overflow-hidden bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-blend-soft-light">
                        <!-- Glow effect overlay -->
                        <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/30 rounded-full blur-[80px] pointer-events-none"></div>
                        
                        <h4 class="font-bold text-white/50 text-[16px] uppercase tracking-widest flex items-center gap-2 mb-6 relative z-10">
                            Khách hàng
                        </h4>
                        
                        <div class="space-y-5 relative z-10">
                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 bg-white/10 border border-white/20 rounded-full flex items-center justify-center backdrop-blur-md shadow-inner">
                                    <i class="fa fa-user text-xl text-white/80"></i>
                                </div>
                                <div>
                                    <p class="text-xl font-bold tracking-tight">{{ $home->contact_name ?? 'Chưa cập nhật' }}</p>
                                    @if($home->contact_role)
                                    <span class="text-[11px] font-bold tracking-wide uppercase text-brand-300">
                                        {{ $home->contact_role == 1 ? 'Nhân viên sale' : 'Admin' }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-3 pt-2">
                                <div class="flex items-center gap-3.5 bg-white/5 hover:bg-white/10 transition-colors rounded-xl p-3.5 border border-white/5 backdrop-blur-md cursor-pointer group">
                                    <div class="w-10 h-10 bg-brand-500/20 text-brand-300 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <i class="fa fa-phone text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-white/50 font-bold uppercase tracking-wide">Số điện thoại</p>
                                        <p class="text-[17px] font-bold tracking-wide text-white">{{ $home->contact_phone ?? 'Chưa cập nhật' }}</p>
                                    </div>
                                </div>

                                @if($home->contact_time)
                                <div class="flex items-center gap-3.5 bg-white/5 rounded-xl p-3.5 border border-white/5 backdrop-blur-md">
                                    <div class="w-10 h-10 bg-white/10 text-white/60 rounded-lg flex items-center justify-center">
                                        <i class="fa fa-clock-o text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-white/50 font-bold uppercase tracking-wide">Thời gian liên hệ</p>
                                        <p class="text-[15px] font-semibold text-white/90">{{ $home->contact_time }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- MODAL LIGHTBOX ẢNH -->
    <div class="gallery-overlay" id="gallery-overlay">
        <button class="gallery-close" onclick="closeGallery()"><i class="fa fa-times"></i></button>
        <button class="gallery-btn gallery-prev" onclick="prevImage()"><i class="fa fa-chevron-left"></i></button>
        <img id="gallery-img" src="" alt="Gallery View">
        <button class="gallery-btn gallery-next" onclick="nextImage()"><i class="fa fa-chevron-right"></i></button>
        <div class="gallery-counter"><span id="gallery-counter-text">1 / 1</span></div>
    </div>

@endsection

@section('script')
    <script>
        // --- XỬ LÝ LIGHTBOX THƯ VIỆN ẢNH ---
        const mediaUrls = @json($mediaUrls);
        let currentIndex = 0;
        const overlay = document.getElementById('gallery-overlay');
        const img = document.getElementById('gallery-img');
        const counter = document.getElementById('gallery-counter-text');

        function openGallery(index) {
            if(!mediaUrls || mediaUrls.length === 0) return;
            currentIndex = index;
            updateGalleryImage();
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeGallery() {
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        function nextImage() {
            if(!mediaUrls || mediaUrls.length === 0) return;
            currentIndex = (currentIndex + 1) % mediaUrls.length;
            updateGalleryImage();
        }

        function prevImage() {
            if(!mediaUrls || mediaUrls.length === 0) return;
            currentIndex = (currentIndex - 1 + mediaUrls.length) % mediaUrls.length;
            updateGalleryImage();
        }

        function updateGalleryImage() {
            if(mediaUrls[currentIndex]) {
                img.src = mediaUrls[currentIndex];
                counter.textContent = (currentIndex + 1) + ' / ' + mediaUrls.length;
            }
        }

        // Bắt phím điều hướng bàn phím
        document.addEventListener('keydown', function(e) {
            if (!overlay.classList.contains('active')) return;
            if (e.key === 'Escape') closeGallery();
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
        });

        // Đóng khi click ngoài khoảng rỗng
        overlay.addEventListener('click', function(e) {
            if (e.target === this) closeGallery();
        });

        // --- KHỞI TẠO BẢN ĐỒ LEAFLET ---
        @if($home->latitude && $home->longitude)
        document.addEventListener('DOMContentLoaded', function() {
            const mapElement = document.getElementById('view-map');
            if(!mapElement) return;
            
            const map = L.map('view-map').setView([{{ $home->latitude }}, {{ $home->longitude }}], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            
            // Tinh chỉnh map pin nhẹ nhàng tinh tế hơn
            L.marker([{{ $home->latitude }}, {{ $home->longitude }}]).addTo(map)
                .bindPopup('<div style="font-family:Inter,sans-serif"><strong style="color:#005ae0">{{ addslashes($home->title ?? "Bất động sản") }}</strong><br><span style="font-size:12px;color:#64748b">{{ addslashes($full_address) }}</span></div>')
                .openPopup();

            // Fix map bị lỗi layer nếu nằm trong tab
            setTimeout(function() { map.invalidateSize(); }, 400);
        });
        @endif

        // --- XỬ LÝ BÌNH LUẬN HOME (AJAX) ---
        const homeId = {{ $home->id }};
        const apiToken = "{{ $apiToken }}";
        const currentUserId = {{ Auth::guard('admin')->user()->id ?? 0 }};
        
        let commentCurrentPage = 1;
        let commentHasNextPage = false;
        let isSubmittingComment = false;

        const apiHeaders = {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        };
        if (apiToken) {
            apiHeaders['Authorization'] = 'Bearer ' + apiToken;
        }

        // load first page of comments
        loadComments(1, false);


        // Load more comments button handler
        $('#btn-load-more').on('click', function() {
            if (commentHasNextPage) {
                loadComments(commentCurrentPage + 1, true);
            }
        });

        function loadComments(page = 1, append = false) {
            $.ajax({
                url: 'api/home/list_comment',
                type: 'GET',
                headers: apiHeaders,
                data: {
                    id_home: homeId,
                    current_page: page,
                    per_page: 5
                },
                success: function(res) {
                    if (res.result) {
                        commentCurrentPage = res.data.meta.current_page;
                        commentHasNextPage = res.data.meta.current_page < res.data.meta.last_page;
                        
                        $('#total-comment-count').text(res.count_comment);
                        
                        if (!append) {
                            $('#comments-container').empty();
                        }
                        
                        if (res.data.data.length === 0) {
                            if (!append) {
                                $('#comments-container').html(`
                                    <div class="text-center py-8 text-slate-400">
                                        <i class="fa fa-comments-o text-4xl mb-2 block"></i>
                                        <p class="text-[15px]">Chưa có bình luận nào cho bất động sản này.</p>
                                    </div>
                                `);
                            }
                            $('#load-more-container').addClass('hidden');
                            return;
                        }
                        
                        res.data.data.forEach(function(comment) {
                            renderCommentItem(comment, $('#comments-container'));
                        });
                        
                        if (commentHasNextPage) {
                            $('#load-more-container').removeClass('hidden');
                        } else {
                            $('#load-more-container').addClass('hidden');
                        }
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi tải bình luận", err);
                    if (!append) {
                        $('#comments-container').html(`
                            <div class="text-center py-6 text-rose-500">
                                <i class="fa fa-exclamation-triangle text-2xl mb-1"></i>
                                <p class="text-[15px]">Không thể tải bình luận. Vui lòng tải lại trang.</p>
                            </div>
                        `);
                    }
                }
            });
        }

        function renderCommentItem(comment, container, isReply = false) {
            const id = comment.id;
            const content = escapeHtml(comment.comment);
            const likes = comment.count_like || 0;
            const dislikes = comment.count_dislike || 0;
            const replyCount = comment.count_reply || 0;
            const isLiked = comment.is_like == 1;
            const isDisliked = comment.is_dislike == 1;
            const createdTime = timeAgo(comment.created_at);
            
            const client = comment.client || {};
            const clientName = client.fullname || client.name || 'Người dùng';
            const clientAvatar = client.avatar || '';
            const isAuthor = comment.author == 1;

            const clientReply = comment.client_reply || null;
            let replyToHtml = '';
            if (clientReply) {
                const replyToName = clientReply.fullname || clientReply.name || 'Người dùng';
                replyToHtml = `
                    <span class="text-[13px] text-slate-400 font-medium flex items-center gap-1">
                        <i class="fa fa-caret-right text-slate-300 text-sm"></i>
                        trả lời <span class="font-bold text-slate-600">${escapeHtml(replyToName)}</span>
                    </span>
                `;
            }

            const likeClass = isLiked 
                ? 'text-brand-600 font-bold' 
                : 'text-slate-400';

            const dislikeClass = isDisliked 
                ? 'text-rose-600 font-bold' 
                : 'text-slate-400';

            const deleteBtnHtml = `
                <button class="hide flex items-center gap-1.5 bg-transparent border-0 p-0 outline-none cursor-pointer text-slate-400 hover:text-red-600 hover:scale-105 transition-all duration-150 text-[15px]" onclick="deleteComment(${id})">
                    <i class="fa fa-trash-o"></i> <span>Xóa</span>
                </button>
            `;

            const authorBadgeHtml = isAuthor ? `
                <span class="px-2 py-0.5 text-[9px] font-bold bg-brand-100 text-brand-700 rounded border border-brand-200">Tác giả</span>
            ` : '';

            let avatarHtml = '';
            if (clientAvatar) {
                avatarHtml = `<img src="${clientAvatar}" class="w-9 h-9 rounded-full object-cover border border-slate-100 shrink-0" onerror="this.outerHTML='<div class=&quot;w-9 h-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0&quot;>${escapeHtml(clientName.substring(0,2).toUpperCase())}</div>'">`;
            } else {
                avatarHtml = `<div class="w-9 h-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">${escapeHtml(clientName.substring(0,2).toUpperCase())}</div>`;
            }

            let commentHtml = '';
            if (!isReply) {
                commentHtml = `
                    <div class="flex gap-3 border-b border-slate-100/60 pb-5" id="comment-node-${id}">
                        ${avatarHtml}
                        <div class="flex-1 space-y-1.5 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-800 text-[15px]">${escapeHtml(clientName)}</span>
                                ${replyToHtml}
                                ${authorBadgeHtml}
                                <span class="text-[13px] text-slate-400 font-medium">${createdTime}</span>
                            </div>
                            <p class="text-[15px] text-slate-600 leading-relaxed whitespace-pre-line">${content}</p>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-4 text-[15px] pt-1">
                                <span class="flex items-center gap-1.5 ${likeClass}">
                                    <i class="fa ${isLiked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}"></i> <span>${likes}</span>
                                </span>
                                <span class="flex items-center gap-1.5 ${dislikeClass}">
                                    <i class="fa ${isDisliked ? 'fa-thumbs-down' : 'fa-thumbs-o-down'}"></i> <span>${dislikes}</span>
                                </span>
                                ${deleteBtnHtml}
                            </div>
                            
                            <!-- Replies List Container -->
                            <div class="replies-list-box space-y-4 mt-4 border-l-2 border-slate-100 pl-4 hidden" id="replies-box-${id}">
                            </div>
                            
                            <!-- View replies toggle link -->
                            ${replyCount > 0 ? `
                                <div class="pt-1.5" id="reply-toggle-link-${id}">
                                    <button class="bg-transparent border-0 p-0 outline-none cursor-pointer text-[15px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1.5 transition-colors" onclick="loadReplies(${id})">
                                        <i class="fa fa-chevron-down text-[12px]"></i> Xem ${replyCount} câu trả lời
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                container.append(commentHtml);
            } else {
                commentHtml = `
                    <div class="flex gap-3 pt-1" id="comment-node-${id}">
                        ${avatarHtml}
                        <div class="flex-1 space-y-1.5 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-bold text-slate-800 text-sm">${escapeHtml(clientName)}</span>
                                ${replyToHtml}
                                ${authorBadgeHtml}
                                <span class="text-xs text-slate-400 font-medium">${createdTime}</span>
                            </div>
                            <p class="text-[15px] text-slate-600 leading-relaxed whitespace-pre-line">${content}</p>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-4 text-[15px] pt-0.5">
                                <span class="flex items-center gap-1.5 ${likeClass}">
                                    <i class="fa ${isLiked ? 'fa-thumbs-up' : 'fa-thumbs-o-up'}"></i> <span>${likes}</span>
                                </span>
                                <span class="flex items-center gap-1.5 ${dislikeClass}">
                                    <i class="fa ${isDisliked ? 'fa-thumbs-down' : 'fa-thumbs-o-down'}"></i> <span>${dislikes}</span>
                                </span>
                                ${deleteBtnHtml}
                            </div>
                        </div>
                    </div>
                `;
                container.append(commentHtml);
            }
        }


        window.loadReplies = function(parentId) {
            const repliesBox = $(`#replies-box-${parentId}`);
            const toggleLink = $(`#reply-toggle-link-${parentId}`);
            
            repliesBox.empty().removeClass('hidden').html(`
                <div class="text-center py-2 text-slate-400">
                    <i class="fa fa-spinner fa-spin text-sm"></i>
                </div>
            `);

            $.ajax({
                url: 'api/home/list_comment',
                type: 'GET',
                headers: apiHeaders,
                data: {
                    id_home: homeId,
                    id_parent: parentId,
                    per_page: 50
                },
                success: function(res) {
                    if (res.result) {
                        repliesBox.empty();
                        
                        if (res.data.data.length === 0) {
                            repliesBox.addClass('hidden');
                            toggleLink.remove();
                            return;
                        }

                        res.data.data.forEach(function(reply) {
                            renderCommentItem(reply, repliesBox, true);
                        });

                        toggleLink.html(`
                            <button class="bg-transparent border-0 p-0 outline-none cursor-pointer text-[15px] font-bold text-slate-500 hover:text-slate-600 flex items-center gap-1.5 transition-colors" onclick="collapseReplies(${parentId})">
                                <i class="fa fa-chevron-up text-[12px]"></i> Ẩn câu trả lời
                            </button>
                        `);
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi tải câu trả lời", err);
                    repliesBox.html(`
                        <p class="text-xs text-rose-500 py-1">Lỗi khi tải câu trả lời</p>
                    `);
                }
            });
        }

        window.collapseReplies = function(parentId) {
            $(`#replies-box-${parentId}`).addClass('hidden');
            
            $.ajax({
                url: 'api/home/list_comment',
                type: 'GET',
                headers: apiHeaders,
                data: { id_home: homeId, id_parent: parentId, per_page: 1 },
                success: function(res) {
                    const totalReplies = res.data.meta.total;
                    $(`#reply-toggle-link-${parentId}`).html(`
                        <button class="bg-transparent border-0 p-0 outline-none cursor-pointer text-[15px] font-bold text-brand-600 hover:text-brand-700 flex items-center gap-1.5 transition-colors" onclick="loadReplies(${parentId})">
                            <i class="fa fa-chevron-down text-[12px]"></i> Xem ${totalReplies} câu trả lời
                        </button>
                    `);
                }
            });
        }

        window.likeComment = function(commentId, like) {
            $.ajax({
                url: `api/home/like_comment/${commentId}/${like}`,
                type: 'GET',
                headers: apiHeaders,
                success: function(res) {
                    if (res.result) {
                        showToast(res.message, "success");
                        loadComments(commentCurrentPage, false);
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi thích bình luận", err);
                }
            });
        }

        window.dislikeComment = function(commentId, dislike) {
            $.ajax({
                url: `api/home/dislike_comment/${commentId}/${dislike}`,
                type: 'GET',
                headers: apiHeaders,
                success: function(res) {
                    if (res.result) {
                        showToast(res.message, "success");
                        loadComments(commentCurrentPage, false);
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi không thích bình luận", err);
                }
            });
        }

        window.deleteComment = function(commentId) {
            if (!confirm("Bạn có chắc chắn muốn xóa bình luận này?")) return;

            $.ajax({
                url: `api/home/delete_comment/${commentId}`,
                type: 'GET',
                headers: apiHeaders,
                success: function(res) {
                    if (res.result) {
                        showToast(res.message, "success");
                        
                        const node = $(`#comment-node-${commentId}`);
                        const parentBox = node.closest('.replies-list-box');
                        
                        if (parentBox.length > 0) {
                            const parentId = parentBox.attr('id').replace('replies-box-', '');
                            loadReplies(parentId);
                        } else {
                            loadComments(commentCurrentPage, false);
                        }
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi xóa bình luận", err);
                }
            });
        }

        // --- XỬ LÝ ĐÁNH GIÁ (AJAX) ---
        let reviewCurrentPage = 1;
        let reviewHasNextPage = false;

        // Load stats and first page of reviews
        loadReviewStats();
        loadReviews(1, false);

        // Load more reviews button handler
        $('#btn-load-more-reviews').on('click', function() {
            if (reviewHasNextPage) {
                loadReviews(reviewCurrentPage + 1, true);
            }
        });

        function loadReviewStats() {
            $.ajax({
                url: 'api/home/get_review_stats',
                type: 'GET',
                headers: apiHeaders,
                data: { home_id: homeId },
                success: function(res) {
                    if (res.result && res.data) {
                        const stats = res.data;
                        $('#total-review-count').text(stats.total_reviews);
                        $('#stats-total-reviews-text').text(stats.total_reviews + ' đánh giá');
                        $('#avg-star-value').text(stats.average_star.toFixed(1));
                        
                        // Render stars for average rating
                        $('#avg-star-stars').html(renderStarsHtml(stats.average_star));

                        // Render distribution bars
                        const total = stats.total_reviews;
                        const dist = stats.star_distribution || {};
                        for (let i = 1; i <= 5; i++) {
                            const count = dist[i] || 0;
                            const pct = total > 0 ? (count / total * 100) : 0;
                            $(`#bar-star-${i}`).css('width', pct + '%');
                            $(`#count-star-${i}`).text(count);
                        }
                        
                        if (total > 0) {
                            $('#reviews-stats-container').removeClass('hidden');
                        } else {
                            $('#reviews-stats-container').addClass('hidden');
                        }
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi tải thống kê đánh giá", err);
                }
            });
        }

        function loadReviews(page = 1, append = false) {
            $.ajax({
                url: 'api/home/list_review',
                type: 'GET',
                headers: apiHeaders,
                data: {
                    home_id: homeId,
                    current_page: page,
                    per_page: 5
                },
                success: function(res) {
                    if (res.result) {
                        reviewCurrentPage = res.data.meta.current_page;
                        reviewHasNextPage = res.data.meta.current_page < res.data.meta.last_page;
                        
                        if (!append) {
                            $('#reviews-container').empty();
                        }
                        
                        if (res.data.data.length === 0) {
                            if (!append) {
                                $('#reviews-container').html(`
                                    <div class="text-center py-8 text-slate-400">
                                        <i class="fa fa-star-o text-4xl mb-2 block"></i>
                                        <p class="text-[15px]">Chưa có đánh giá nào cho bất động sản này.</p>
                                    </div>
                                `);
                            }
                            $('#load-more-reviews-container').addClass('hidden');
                            return;
                        }
                        
                        res.data.data.forEach(function(review) {
                            renderReviewItem(review, $('#reviews-container'));
                        });
                        
                        if (reviewHasNextPage) {
                            $('#load-more-reviews-container').removeClass('hidden');
                        } else {
                            $('#load-more-reviews-container').addClass('hidden');
                        }
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi tải đánh giá", err);
                    if (!append) {
                        $('#reviews-container').html(`
                            <div class="text-center py-6 text-rose-500">
                                <i class="fa fa-exclamation-triangle text-2xl mb-1"></i>
                                <p class="text-[15px]">Không thể tải đánh giá. Vui lòng tải lại trang.</p>
                            </div>
                        `);
                    }
                }
            });
        }

        function renderStarsHtml(starCount) {
            let html = '';
            const fullStars = Math.floor(starCount);
            const hasHalf = (starCount - fullStars) >= 0.3;
            for (let i = 1; i <= 5; i++) {
                if (i <= fullStars) {
                    html += '<i class="fa fa-star text-amber-400"></i>';
                } else if (i === fullStars + 1 && hasHalf) {
                    html += '<i class="fa fa-star-half-o text-amber-400"></i>';
                } else {
                    html += '<i class="fa fa-star-o text-slate-300"></i>';
                }
            }
            return html;
        }

        function renderReviewItem(review, container) {
            const id = review.id;
            const content = escapeHtml(review.content || '');
            const star = review.star;
            const status = review.status;
            const createdTime = timeAgo(review.created_at);
            
            const client = review.customer || {};
            const clientName = client.name || 'Người dùng';
            const clientAvatar = client.avatar || '';

            let statusBadgeHtml = '';
            if (status === 0) {
                statusBadgeHtml = `
                    <span class="px-2 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-800 rounded border border-amber-200">Chờ duyệt</span>
                `;
            } else if (status === 1) {
                statusBadgeHtml = `
                    <span class="px-2 py-0.5 text-[9px] font-bold bg-emerald-100 text-emerald-800 rounded border border-emerald-200">Đã duyệt</span>
                `;
            }

            // Escape single quotes in review content for javascript function call
            const escapedContent = (review.content || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');

            const approveBtnHtml = status === 0 ? `
                <button class="flex items-center gap-1 bg-transparent border-0 p-0 outline-none cursor-pointer text-emerald-600 hover:text-emerald-700 hover:scale-105 transition-all duration-150 font-bold text-[15px]" onclick="approveReview(${id}, ${star}, '${escapedContent}')">
                    <i class="fa fa-check"></i> <span>Duyệt</span>
                </button>
            ` : '';

            const deleteBtnHtml = `
                <button class="flex items-center gap-1.5 bg-transparent border-0 p-0 outline-none cursor-pointer text-slate-400 hover:text-red-600 hover:scale-105 transition-all duration-150 text-[15px]" onclick="deleteReview(${id})">
                    <i class="fa fa-trash-o"></i> <span>Xóa</span>
                </button>
            `;

            let avatarHtml = '';
            if (clientAvatar) {
                avatarHtml = `<img src="${clientAvatar}" class="w-9 h-9 rounded-full object-cover border border-slate-100 shrink-0" onerror="this.outerHTML='<div class=&quot;w-9 h-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0&quot;>${escapeHtml(clientName.substring(0,2).toUpperCase())}</div>'">`;
            } else {
                avatarHtml = `<div class="w-9 h-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shrink-0">${escapeHtml(clientName.substring(0,2).toUpperCase())}</div>`;
            }

            const starsHtml = renderStarsHtml(star);

            const reviewHtml = `
                <div class="flex gap-3 border-b border-slate-100/60 pb-5" id="review-node-${id}">
                    ${avatarHtml}
                    <div class="flex-1 space-y-1.5 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-800 text-[15px]">${escapeHtml(clientName)}</span>
                            ${statusBadgeHtml}
                            <span class="text-[13px] text-slate-400 font-medium">${createdTime}</span>
                        </div>
                        <div class="flex items-center gap-0.5 text-amber-400">
                            ${starsHtml}
                        </div>
                        <p class="text-[15px] text-slate-600 leading-relaxed whitespace-pre-line">${content || '<span class="text-slate-400 italic">Không có nội dung đánh giá</span>'}</p>
                        
                        <!-- Actions -->
                        <div class="flex items-center gap-4 text-[15px] pt-1">
                            ${approveBtnHtml}
                            ${deleteBtnHtml}
                        </div>
                    </div>
                </div>
            `;
            container.append(reviewHtml);
        }

        window.deleteReview = function(reviewId) {
            if (!confirm("Bạn có chắc chắn muốn xóa đánh giá này?")) return;

            $.ajax({
                url: `admin/manage_home/delete_review/${reviewId}`,
                type: 'GET',
                headers: apiHeaders,
                success: function(res) {
                    if (res.result) {
                        alert_float('success',res.message);
                        loadReviewStats();
                        loadReviews(1, false);
                    } else {
                        alert_float('error',res.message);
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi xóa đánh giá", err);
                    alert_float('error',"Lỗi khi kết nối hệ thống");
                }
            });
        }

        window.approveReview = function(reviewId, star, content) {
            $.ajax({
                url: `admin/manage_home/edit_review/${reviewId}`,
                type: 'POST',
                headers: apiHeaders,
                data: {
                    star: star,
                    content: content,
                    status: 1
                },
                success: function(res) {
                    if (res.result) {
                        alert_float('success',res.message);
                        loadReviewStats();
                        loadReviews(1, false);
                    } else {
                        alert_float('error',res.message);
                    }
                },
                error: function(err) {
                    console.error("Lỗi khi duyệt đánh giá", err);
                    alert_float('error',"Lỗi khi kết nối hệ thống");
                }
            });
        }

        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function timeAgo(dateString) {
            const now = new Date();
            const past = new Date(dateString);
            const msPerMinute = 60 * 1000;
            const msPerHour = msPerMinute * 60;
            const msPerDay = msPerHour * 24;
            const msPerMonth = msPerDay * 30;
            const msPerYear = msPerDay * 365;

            const elapsed = now - past;

            if (elapsed < msPerMinute) {
                 return 'Vừa xong';   
            } else if (elapsed < msPerHour) {
                 return Math.round(elapsed/msPerMinute) + ' phút trước';   
            } else if (elapsed < msPerDay ) {
                 return Math.round(elapsed/msPerHour ) + ' giờ trước';   
            } else if (elapsed < msPerMonth) {
                return Math.round(elapsed/msPerDay) + ' ngày trước';   
            } else if (elapsed < msPerYear) {
                return Math.round(elapsed/msPerMonth) + ' tháng trước';   
            } else {
                return Math.round(elapsed/msPerYear ) + ' năm trước';   
            }
        }
    </script>
@endsection
