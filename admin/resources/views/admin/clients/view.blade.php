@extends('admin.layouts.index')
@section('page_title', lang('dt_client'))
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

    <style>
        html, body { overflow-x: hidden; }
        .view-page { font-family: 'Inter', sans-serif; font-size: 14px; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        /* Hiệu ứng nhấp nháy cho badge trạng thái */
        @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 0.5; } 100% { transform: scale(2.5); opacity: 0; } }
        .status-dot { position: relative; }
        .status-dot::before { content: ''; position: absolute; left: 0; top: 0; width: 100%; height: 100%; background: inherit; border-radius: inherit; animation: pulse-ring 2s cubic-bezier(0.215, 0.61, 0.355, 1) infinite; }

        /* Sticky Layouts */
        .sticky-header-custom {
            position: sticky !important;
            top: var(--header-height, 60px) !important;
            z-index: 1020 !important;
        }
        .form-nav-tab,
        .form-nav-tab * {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
        }
        .form-nav-tab.active {
            border-color: #005ae0;
            background-color: #eff6ff;
            color: #003ea1;
        }
        .tab-pane {
            display: none !important;
        }
        .tab-pane.active {
            display: block !important;
        }
        
        /* CSS tối ưu hiển thị bảng không bị cuộn ngang */
        .tab-pane .table-responsive {
            overflow-x: hidden !important;
        }
        .tab-pane table {
            width: 100% !important;
            table-layout: auto !important;
        }
        .tab-pane table th,
        .tab-pane table td {
            padding: 8px 6px !important;
            font-size: 13px !important;
            white-space: normal !important;
            word-break: break-word !important;
            vertical-align: middle !important;
            text-align: center !important;
        }
        .tab-pane table th.text-right,
        .tab-pane table td.text-right {
            text-align: right !important;
        }
        .tab-pane table th.text-left,
        .tab-pane table td.text-left {
            text-align: left !important;
        }
        .tab-pane table .label,
        .tab-pane table .btn {
            white-space: nowrap !important;
            display: inline-block !important;
        }

        /* Tree Diagram Styles */
        .tree ul {
            padding-top: 20px;
            position: relative;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li {
            float: left;
            text-align: center;
            list-style-type: none;
            position: relative;
            padding: 20px 5px 0 5px;
            transition: all 0.5s;
            -webkit-transition: all 0.5s;
            -moz-transition: all 0.5s;
        }

        .tree li::before, .tree li::after {
            content: '';
            position: absolute;
            top: 0;
            right: 50%;
            border-top: 1px solid #ccc;
            width: 50%;
            height: 20px;
        }

        .tree li::after {
            right: auto;
            left: 50%;
            border-left: 1px solid #ccc;
        }

        .tree li:only-child::before, .tree li:only-child::after {
            display: none;
        }

        .tree li:only-child {
            padding-top: 0;
        }

        .tree li:first-child::before, .tree li:last-child::after {
            border: 0 none;
        }

        .tree li:last-child::before {
            border-right: 1px solid #ccc;
            border-radius: 0 5px 0 0;
        }

        .tree li:first-child::after {
            border-radius: 5px 0 0 0;
        }
        .tree ul ul::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            border-left: 1px solid #ccc;
            width: 0;
            height: 20px;
        }

        /* Package Subscriptions Premium Badges & Modal Styles */
        .pkg-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .pkg-badge.viewer { background: #eef6ff; color: #1d6fe3; }
        .pkg-badge.broker { background: #fff7ed; color: #c2610c; }
        .pkg-badge.owner  { background: #f0fdf4; color: #15803d; }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
        }
        .status-badge::before {
            content: '';
            display: inline-block;
            width: 6px; height: 6px;
            border-radius: 50%;
        }
        .status-active  { background: #dcfce7; color: #15803d; }
        .status-active::before  { background: #22c55e; }
        .status-expired { background: #fef2f2; color: #b91c1c; }
        .status-expired::before { background: #ef4444; }
        .status-cancel  { background: #f1f5f9; color: #64748b; }
        .status-cancel::before  { background: #94a3b8; }

        .days-good   { color: #15803d; font-weight: 700; }
        .days-warn   { color: #c2610c; font-weight: 700; }
        .days-danger { color: #b91c1c; font-weight: 700; }

        .points-val { color: #005ae0; font-weight: 800; font-size: 14px; }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px; height: 32px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            color: #64748b;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.16s ease;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.03);
        }

        .btn-action-view:hover {
            color: #005ae0;
            border-color: #bfdbfe;
            background: #eff6ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 90, 224, 0.08);
        }

        #detailModal .modal-content { border-radius: 18px; border: none; box-shadow: 0 20px 60px rgba(15,23,42,0.14); }
        #detailModal .modal-header  {
            background: linear-gradient(135deg, #005ae0, #2563eb);
            border-radius: 18px 18px 0 0;
            padding: 18px 24px;
        }
        #detailModal .modal-title { color: #fff; font-weight: 800; font-size: 16px; }
        #detailModal .modal-header .close { color: #fff; opacity: .8; text-shadow: none; }
        #detailModal .modal-body  { padding: 24px; }
        #detailModal .modal-footer { padding: 14px 24px; border-top: 1px solid #f1f5f9; }

        .detail-member-card {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 16px;
            background: #f8fbff;
            border: 1px solid #e5edf8;
            border-radius: 14px;
            margin-bottom: 18px;
        }
        .detail-avatar {
            width: 50px; height: 50px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid #e2e8f0; flex-shrink: 0;
        }
        .detail-avatar-ph {
            width: 50px; height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #93b5e5, #5e8bd4);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 20px; color: #fff; font-weight: 800;
        }
        .detail-name { font-weight: 800; font-size: 16px; color: #0f172a; }
        .detail-sub  { font-size: 12.5px; color: #64748b; margin-top: 3px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .info-box {
            padding: 11px 14px;
            background: #f8fafc;
            border: 1px solid #eef2f7;
            border-radius: 10px;
        }
        .info-lbl { font-size: 10.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .info-val { font-size: 13.5px; font-weight: 700; color: #0f172a; }

        #table_client_homes tbody td .dropdown .dropdown-toggle {
            height: 34px !important;
            padding: 0 12px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 7px !important;
            border: 1px solid var(--home-border) !important;
            border-radius: 10px !important;
            background: #ffffff !important;
            color: #475569 !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05) !important;
            transition: all .16s ease !important;
        }
        
        .form-nav-badge {
            background-color: #f1f5f9;
            color: #64748b;
            transition: all 0.2s ease;
        }
        .form-nav-tab.active .form-nav-badge {
            background-color: #005ae0;
            color: #ffffff;
        }
        .status-dropdown-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 170px;
            z-index: 9999;
            padding: 7px;
            border: 1px solid var(--home-border);
            border-radius: 14px;
            background: #ffffff;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.16);
            list-style: none !important;
            margin: 0;
        }

        .status-dropdown-menu.hidden {
            display: none !important;
        }

        .home-status-item {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 9px 10px !important;
            border-radius: 10px !important;
            color: #475569 !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            transition: all .14s ease !important;
        }

        .home-status-item:hover {
            background: #eff6ff !important;
            color: var(--home-primary) !important;
        }
    </style>

    <div class="view-page bg-slate-50/50 min-h-screen py-8 -m-5">
        <div class="max-w-[1300px] mx-auto px-4 sm:px-6 space-y-6 lg:space-y-8">

            <!-- THANH ĐIỀU HƯỚNG TRÊN CÙNG -->
            <div class="sticky-header-custom bg-white p-5 rounded-2xl border border-slate-200 shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-brand-50 rounded-xl flex items-center justify-center text-brand-600 shadow-sm border border-brand-100 shrink-0">
                        <i class="fa fa-user text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold uppercase text-slate-800 tracking-tight">
                            Chi tiết thành viên: <span class="text-brand-600 ml-1">{{ $client['phone'] }}</span>
                        </h2>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">
                            Ngày đăng ký: {{ _dt($client['created_at']) }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <a href="admin/clients/list" class="flex-1 sm:flex-none px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-base rounded-xl transition-colors flex items-center justify-center gap-2">
                        <i class="fa fa-arrow-left text-lg"></i> Quay lại
                    </a>
                </div>
            </div>

            <input type="hidden" id="customer_id" value="{{ $client['id'] ?? 0 }}">

            <!-- Tab Client Contents (hidden tabs list to keep compatibility with bootstrap JS) -->
            <ul class="nav nav-tabs hidden" id="tab_client">
                <li class="active"><a href="#info" data-toggle="tab" aria-expanded="false">Thông tin chung</a></li>
                <li><a href="#homes" data-toggle="tab" aria-expanded="false">Danh sách BĐS</a></li>
                <li><a href="#home_favourite" data-toggle="tab" aria-expanded="false">Danh sách BĐS đã lưu</a></li>
                <li><a href="#client_follow" data-toggle="tab" aria-expanded="false">Danh sách người theo dõi</a></li>
            </ul>

            <!-- 2-COLUMN LAYOUT: Sidebar + Content -->
            <div class="flex gap-6 items-start">

                <!-- LEFT SIDEBAR: Danh mục các bước -->
                <div class="w-[260px] shrink-0 sticky top-[76px] z-10">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                        <div class="space-y-3">
                            <!-- Tab 1: Thông tin chung (active) -->
                            <button class="form-nav-tab w-full flex items-center gap-3 px-4 py-4 rounded-xl border text-left transition-all duration-200 border-brand-500 bg-brand-50 text-brand-700 active" data-tab-target="#info">
                                <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 bg-white text-brand-700">1</span>
                                    <span class="text-[12px] font-semibold leading-tight">Thông tin chung</span>
                            </button>
                            <!-- Tab 4: Danh sách BĐS -->
                            <button class="form-nav-tab w-full flex items-center justify-between px-4 py-4 rounded-xl border text-left transition-all duration-200 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100" data-tab-target="#homes">
                                <div class="flex items-center gap-3">
                                    <span class="form-nav-step w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 bg-slate-100 text-slate-500">2</span>
                                    <span class="text-[12px] font-semibold leading-tight">Danh sách BĐS</span>
                                </div>
                                @if(!empty($client['count_homes']))
                                <span class="form-nav-badge text-[10px] font-semibold px-2.5 py-0.5 rounded-full">{{ $client['count_homes'] ?? 0 }}</span>
                                @endif
                            </button>

                            <!-- Tab 5: Danh sách BĐS yêu thích -->
                            <button class="form-nav-tab w-full flex items-center justify-between px-4 py-4 rounded-xl border text-left transition-all duration-200 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100" data-tab-target="#home_favourite">
                                <div class="flex items-center gap-3">
                                    <span class="form-nav-step w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shrink-0 bg-slate-100 text-slate-500">3</span>
                                    <span class="text-[12px] font-semibold leading-tight">Danh sách BĐS yêu thích</span>
                                </div>
                                @if(!empty($client['count_favourite']))
                                <span class="form-nav-badge text-[10px] font-semibold px-2.5 py-0.5 rounded-full">{{ $client['count_favourite'] ?? 0 }}</span>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CONTENT -->
                <div class="flex-1 min-w-0">
            <div class="tab-content">
                <div class="tab-pane active" id="info">
                    <div id="info-step-1" class="grid grid-cols-1 gap-6">
                        <!-- CARD THÔNG TIN CHI TIẾT KHÁCH HÀNG -->
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7 relative overflow-hidden">
                            <!-- Decorative gradient blend -->
                            <div class="absolute -top-12 -right-12 w-32 h-32 bg-brand-100 rounded-full blur-3xl opacity-60 pointer-events-none"></div>

                            <!-- Avatar & Header info -->
                            <div class="flex flex-col items-center text-center pt-4 pb-8 border-b border-slate-100">
                                @php
                                    $src = !empty($client['avatar']) ? ($client['avatar']) : asset('admin/assets/images/users/avatar-1.jpg');
                                    
                                    $classes = $client['active'] == 1 ? "bg-emerald-50 text-emerald-700 border-emerald-200" : "bg-rose-50 text-rose-700 border-rose-200";
                                    $content = $client['active'] == 1 ? "Hoạt động" : "Khoá";
                                    $dotColor = $client['active'] == 1 ? "#10b981" : "#f43f5e";
                                    $strStatus = "<a class='dt-update text-center px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase border rounded-full inline-flex items-center gap-2 $classes'><span class='w-2 h-2 rounded-full status-dot' style='background-color: $dotColor'></span>$content</a>";

                                    $classesT = 'bg-slate-50 text-slate-700 border-slate-200';
                                    $contentT = lang('Chưa xác định');
                                    if($client['type_client'] == 0){
                                        $classesT = 'bg-slate-50 text-slate-700 border-slate-200';
                                        $contentT = lang('Khách hàng');
                                    }elseif ($client['type_client'] == 1) {
                                        $classesT = 'bg-blue-50 text-blue-700 border-blue-200';
                                        $contentT = lang('Nhân viên sale');
                                    } elseif($client['type_client'] == 2){
                                        $classesT = 'bg-rose-50 text-rose-700 border-rose-200';
                                        $contentT = lang('Admin');
                                    }
                                    $str = "<a class='text-center px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase border rounded-full inline-flex items-center gap-1.5 $classesT'><i class='fa fa-user'></i> $contentT</a>";

                                    $partnerText = '';
                                    $partnerColors = '';
                                    if ($client['type_partner'] == 1){
                                        $partnerText = 'Đại lý';
                                        $partnerColors = 'bg-purple-50 text-purple-700 border-purple-200';
                                    } elseif($client['type_partner'] == 2){
                                        $partnerText = 'Spa';
                                        $partnerColors = 'bg-pink-50 text-pink-700 border-pink-200';
                                    } elseif ($client['type_partner'] == 3){
                                        $partnerText = 'CTV';
                                        $partnerColors = 'bg-amber-50 text-amber-700 border-amber-200';
                                    }
                                @endphp
                                <div class="relative group mb-4">
                                    <a href="{{$src}}" data-lightbox="customer-profile" class="block">
                                        <img class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-md ring-2 ring-slate-100 hover:ring-brand-400 transition-all duration-300" src="{{$src}}" alt="{{$client['fullname']}}">
                                    </a>
                                </div>
                                <h3 class="text-xl font-bold text-slate-900 mb-3">{{$client['fullname']}}</h3>
                                
                                <!-- Status Badges -->
                                <div class="flex items-center gap-2 flex-wrap justify-center">
                                    <!-- Active Status Link (dt-update target) -->
                                    {!! $strStatus !!}

                                    <!-- Member Type -->
                                    {!! $str !!}

                                    <!-- Partner Badge if exists -->
                                    @if(!empty($partnerText))
                                    <span class="px-3 py-1.5 text-[11px] font-bold tracking-wide uppercase border rounded-full inline-flex items-center gap-1.5 {{ $partnerColors }}">
                                        <i class="fa fa-briefcase"></i> {{ $partnerText }}
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Detail Fields -->
                            <div class="mt-6">
                                <div class="font-bold text-slate-900 text-lg uppercase tracking-wide flex items-center gap-3 mb-4">
                                    <div class="w-10 h-10 bg-sky-50 rounded-lg flex items-center justify-center text-sky-500">
                                        <i class="fa fa-user text-xl"></i>
                                    </div>
                                    Thông tin chi tiết
                                </div>
                                <div class="divide-y divide-slate-100">
                                    <div class="flex items-center justify-between py-4">
                                        <span class="text-[15px] text-slate-500 font-medium">{{ lang('dt_phone_user') }}</span>
                                        <span class="text-[15px] text-slate-900 font-bold">{{ $client['phone'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-4">
                                        <span class="text-[15px] text-slate-500 font-medium">{{ lang('dt_email_user') }}</span>
                                        <span class="text-[15px] text-slate-900 font-bold">{{ $client['email'] }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-4">
                                        <span class="text-[15px] text-slate-500 font-medium">Giới tính</span>
                                        <span class="text-[15px] text-slate-900 font-bold">{{ !empty($client['gender']) ? getListGender($client['gender']) : 'Chưa xác định' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between py-4">
                                        <span class="text-[15px] text-slate-500 font-medium">Ngày tham gia</span>
                                        <span class="text-[15px] text-slate-900 font-bold">{{ _dt($client['created_at']) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- SƠ ĐỒ NHÁNH CÁC CẤP (IF NOT HIDDEN) -->
                        <div class="col-md-6 hide">
                            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-7">
                                <div class="title_driving_liscense">
                                    <h4 class="font-bold text-slate-900 text-sm uppercase tracking-wide flex items-center gap-2.5 mb-4">
                                        <div class="w-8 h-8 bg-purple-50 rounded-lg flex items-center justify-center text-purple-500">
                                            <i class="fa fa-sitemap text-lg"></i>
                                        </div>
                                        Sơ đồ nhánh các cấp
                                    </h4>
                                </div>
                                <div class="text-[15px] text-slate-600 mb-2 font-medium">Tổng số thành viên: <span class="font-bold text-slate-900">{{$countMember}}</span></div>
                                <div class="hide text-[15px] text-slate-600 font-medium">Tổng số cấp: <span class="font-bold text-slate-900">{{$level}}</span></div>
                                <div class="tree" style="overflow-x: auto;overflow-y: hidden; white-space: nowrap;">
                                    @php
                                        $html = get_parent_id_referral_level_html($dataReferralLevel)
                                    @endphp
                                    {!! $html !!}
                                </div>
                            </div>
                </div>
            </div>
        </div>

                <!-- Tab 2: Các gói đã mua -->
                <div class="tab-pane" id="packages">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-900 text-base uppercase tracking-wide flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center text-blue-500">
                                <i class="fa fa-briefcase text-lg"></i>
                            </div>
                            Lịch sử đăng ký gói cước
                        </h4>
                        <div class="table-responsive">
                            <table id="table_client_packages" class="table table-bordered table-hover w-full">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                        <th>Mã đơn hàng</th>
                                        <th style="text-align:center;">Gói cước</th>
                                        <th>Giá (điểm M)</th>
                                        <th style="text-align:center;">Bắt đầu</th>
                                        <th style="text-align:center;">Hết hạn</th>
                                        <th style="text-align:center;">Còn lại</th>
                                        <th style="text-align:center;">Trạng thái</th>
                                        <th style="text-align:center; width:80px;">Chi tiết</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Lịch sử nạp tiền -->
                <div class="tab-pane" id="recharges">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-900 text-base uppercase tracking-wide flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center text-emerald-500">
                                <i class="fa fa-credit-card text-lg"></i>
                            </div>
                            Lịch sử nạp tiền
                        </h4>
                        <div class="table-responsive">
                            <table id="table_client_recharges" class="table table-bordered table-hover w-full">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-widest text-center">
                                        <th>Mã giao dịch</th>
                                        <th>Số tiền</th>
                                        <th>Phương thức</th>
                                        <th>Thời gian</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Danh sách BĐS -->
                <div class="tab-pane" id="homes">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-900 text-base uppercase tracking-wide flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                                <i class="fa fa-home text-lg"></i>
                            </div>
                            Danh sách bất động sản
                        </h4>
                        <div class="table-responsive">
                            <table id="table_client_homes" class="table table-bordered table-hover w-full">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 tracking-widest text-center">
                                        <th>Mã BĐS</th>
                                        <th>Bất động sản</th>
                                        <th>Giao dịch/Loại nhà</th>
                                        <th>Diện tích</th>
                                        <th>Giá trị</th>
                                        <th>Ngày tạo</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="home_favourite">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-900 text-base uppercase tracking-wide flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                                <i class="fa fa-home text-lg"></i>
                            </div>
                            Danh sách bất động sản yêu thích
                        </h4>
                        <div class="table-responsive">
                            <table id="table_client_home_favourite" class="table table-bordered table-hover w-full">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 tracking-widest text-center">
                                        <th>Mã BĐS</th>
                                        <th>Bất động sản</th>
                                        <th>Giao dịch/Loại nhà</th>
                                        <th>Diện tích</th>
                                        <th>Giá trị</th>
                                        <th>Người đăng</th>
                                        <th>Ngày tạo</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="home_save">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-900 text-base uppercase tracking-wide flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                                <i class="fa fa-home text-lg"></i>
                            </div>
                            Danh sách bất động sản đã lưu
                        </h4>
                        <div class="table-responsive">
                            <table id="table_client_home_save" class="table table-bordered table-hover w-full">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 tracking-widest text-center">
                                        <th>Mã BĐS</th>
                                        <th>Bất động sản</th>
                                        <th>Giao dịch/Loại nhà</th>
                                        <th>Diện tích</th>
                                        <th>Giá trị</th>
                                        <th>Người đăng</th>
                                        <th>Ngày tạo</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                 <div class="tab-pane" id="client_follow">
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <h4 class="font-bold text-slate-900 text-base uppercase tracking-wide flex items-center gap-2.5 mb-4">
                            <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center text-amber-500">
                                <i class="fa fa-home text-lg"></i>
                            </div>
                            Danh sách người theo dõi
                        </h4>
                        <div class="table-responsive">
                            <table id="table_client_follow" class="table table-bordered table-hover w-full">
                                <thead>
                                    <tr class="bg-slate-50 text-[11px] font-bold text-slate-400 tracking-widest text-center">
                                        <th>STT</th>
                                        <th>Hình ảnh</th>
                                        <th>Tên thành viên</th>
                                        <th>Số điện thoại</th>
                                        <th>Loại thành viên</th>
                                        <th>Ngày theo dõi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
                </div><!-- end RIGHT CONTENT -->
            </div><!-- end 2-COLUMN LAYOUT -->
        </div>
    </div>

    <!-- Detail Modal (Read-only) -->
    <div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="max-width:580px;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff; opacity:0.8; text-shadow:none;">&times;</button>
                    <h5 class="modal-title" style="color:#fff; font-weight:800; font-size:16px;"><i class="fa fa-credit-card" style="margin-right:8px;"></i> Chi tiết đăng ký gói</h5>
                </div>
                <div class="modal-body" id="detail-body" style="padding:24px;">
                    <div style="text-align:center;padding:30px;color:#94a3b8;">
                        <i class="fa fa-spinner fa-spin" style="font-size:24px;"></i>
                    </div>
                </div>
                <div class="modal-footer" style="padding:14px 24px; border-top:1px solid #f1f5f9;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
            $("#infomation_vat_form").validate({
            ignore: [],
            rules: {
                type_vat: {
                    required: true,
                },
                address: {
                    required: true,
                },
                payment_method: {
                    required: true,
                },
            },
            messages: {
                type_vat: {
                    required: "{{lang('dt_required')}}",
                },
                address: {
                    required: "{{lang('dt_required')}}",
                },
                payment_method: {
                    required: "{{lang('dt_required')}}",
                },
            },
            invalidHandler: function (event, validator) {
                if (validator.errorList.length) {

                    // Lấy input lỗi đầu tiên
                    var firstError = validator.errorList[0].element;

                    // Tìm tab-pane chứa input lỗi
                    var tabPane = $(firstError).closest('.tab-pane');

                    // Lấy id của tab-pane
                    var tabId = tabPane.attr('id');

                    // Active tab đó (Bootstrap tab)
                    $('a[href="#' + tabId + '"]').tab('show');
                }
            },
            submitHandler: function (form) {
                var url = form.action;
                var form = $(form),
                    formData = new FormData(),
                    formParams = form.serializeArray();

                $.each(form.find('input[type="file"]'), function (i, tag) {
                    $.each($(tag)[0].files, function (i, file) {
                        formData.append(tag.name, file);
                    });
                });
                $.each(formParams, function (i, val) {
                    formData.append(val.name, val.value);
                });

                $.ajax({
                    url: url,
                    type: 'POST',
                    dataType: 'JSON',
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: formData,
                })
                    .done(function (data) {
                        if (data.result) {
                            alert_float('success', data.message);
                            setTimeout(function () {
                                localStorage.setItem('activeTab', 'infomation_vat');
                                location.reload();
                            }, 1000);
                        } else {
                            $(".show_error").html(data.message);
                            alert_float('error', data.message);
                        }
                    })
                    .fail(function (err) {
                    });
                return false;
            }
        });

        function changeTypeVat() {
            var type_vat = $('#type_vat').val();
            if (type_vat == 1) {
                $('.name_hide').addClass('hide');
                $('.company_hide').removeClass('hide');
                $('.vat_hide').removeClass('hide');
            } else {
                $('.name_hide').removeClass('hide');
                $('.company_hide').addClass('hide');
                $('.vat_hide').addClass('hide');
            }
        }
        
        changeTypeVat();
        $(document).ready(function () {
            searchAjaxSelect2('#customer_search_favourite', 'admin/category/searchCustomer')
            searchAjaxSelect2('#group_category_service_search_favourite', 'admin/category/searchGroupCategoryService')
            searchAjaxSelect2('#category_service_search_favourite', 'admin/category/searchCategoryService')
            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#tab_client a[href="#' + activeTab + '"]').tab('show');
                localStorage.removeItem('activeTab'); // dùng xong thì xóa
            }
        });
        var fnserverparamsNew = {
            'customer_favourite': '#customer_id',
            'group_category_service_search': '#group_category_service_search_favourite',
            'category_service_search': '#category_service_search_favourite',
            'status_search': '#status_search_favourite',
            'customer_search': '#customer_search_favourite',
        };
        var fnserverparamsClient = {
            'customer_id': '#customer_id',
        };
        var fnserverparamsReferralReview = {
            'customer_id': '#customer_id',
        };
        var fnserverparamsReferralOrder = {
            'customer_id': '#customer_id',
        };
        var fnserverparamsOrderLeader = {
            'customer_id': '#customer_id',
            'year_search': '#year_search'
        };

    </script>
    <script>
        var fnserverparams = {
            'id_customer': '#customer_id',
        };
        var oTable;
        oTable = InitDataTable('#table_clients_review', 'admin/clients_review/getTableReview', {
            'order': [
                [6, 'desc']
            ],
            'responsive': true,
            "ajax": {
                "type": "POST",
                "url": "admin/clients_review/getTableReview",
                "data": function (d) {
                    for (var key in fnserverparams) {
                        d[key] = $(fnserverparams[key]).val();
                    }
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'DT_RowIndex', name: 'DT_RowIndex',width: "80px" },
                {data: 'code_review', name: 'code_review',width: "80px"},
                {data: 'product', name: 'product'},
                {data: 'evaluate', name: 'evaluate'},
                {data: 'video_review', name: 'video_review'},
                // {data: 'status', name: 'status',width: "100px"},
                {data: 'active', name: 'active',width: "100px"},
                {data: 'date_review', name: 'date_review',width: "100px"},
                {data: 'options', name: 'options', orderable: false, searchable: false,width: "150px" },

            ]
        });

        function changeStatus(id, status) {
            $.ajax({
                url: 'admin/clients_review/changeStatus',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    id: id,
                    status: status,
                },
            }).done(function(data) {
                if (data.result) {
                    alert_float('success', data.message);
                } else {
                    alert_float('error', data.message);
                }
                oTable.draw('page');
            }).fail(function() {});
            return false;
        }

    </script>

    <script>
        oTableIntroduce = InitDataTable('#table_list_client_referral', 'api/customer/getClientsIntroduce', {
            'order': [
                [6, 'desc']
            ],
            'responsive': false,
            "ajax": {
                "type": "POST",
                "url": "api/customer/getClientsIntroduce",
                "data": function (d) {
                    for (var key in fnserverparamsClient) {
                        d[key] = $(fnserverparamsClient[key]).val();
                    }
                    d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                },
                "dataSrc": function (json) {
                    return json.data;
                }
            },
            columnDefs: [
                {data: 'avatar', name: 'avatar',width: "90px",},
                {data: 'code', name: 'code',width: "110px",},
                {data: 'fullname', name: 'fullname'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'phone', name: 'phone'
                },
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'type_client', name: 'type_client'
                },
                {data: 'created_at', name: 'created_at'},
                {
                    "render": function (data, type, row) {
                        return `<div class="text-center">${data}</div>`;
                    },
                    data: 'active', name: 'active'
                },
            ]
        })

        var oTableReferralReview;
        var oTableReferralOrder;
        var oTableOrderLeader;
        function loadTableReferralReview() {
            oTableReferralReview = InitDataTable('#table_list_client_referral_review', 'admin/clients_review/getClientsIntroduceReview', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients_review/getClientsIntroduceReview",
                    "data": function (d) {
                        for (var key in fnserverparamsReferralReview) {
                            d[key] = $(fnserverparamsReferralReview[key]).val();
                        }
                        d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {data: 'avatar', name: 'avatar',width: "90px",},
                    {data: 'code', name: 'code',width: "110px",},
                    {data: 'fullname', name: 'fullname'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'phone', name: 'phone'
                    },
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'type_client', name: 'type_client'
                    },
                    {data: 'created_at', name: 'created_at'},
                    {data: 'code_review', name: 'code_review'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active'
                    },
                ]
            });
        }

        function loadTableReferralOrder() {
            oTableReferralReview = InitDataTable('#table_list_client_referral_order', 'admin/clients/getClientsIntroduceOrder', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients/getClientsIntroduceOrder",
                    "data": function (d) {
                        for (var key in fnserverparamsReferralOrder) {
                            d[key] = $(fnserverparamsReferralOrder[key]).val();
                        }
                        d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {data: 'avatar', name: 'avatar',width: "90px",},
                    {data: 'code', name: 'code',width: "110px",},
                    {data: 'fullname', name: 'fullname'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'phone', name: 'phone'
                    },
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'type_client', name: 'type_client'
                    },
                    {data: 'created_at', name: 'created_at'},
                    {data: 'code_review', name: 'code_review'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'active', name: 'active'
                    },
                ]
            });
        }

        
        function loadTableOrderLeader() {
            oTableOrderLeader = InitDataTable('#table_list_client_leader', 'admin/clients/getClientsOrderLeader', {
                'order': [
                    [0, 'desc']
                ],
                'responsive': true,
                "ajax": {
                    "type": "POST",
                    "url": "admin/clients/getClientsOrderLeader",
                    "data": function (d) {
                        for (var key in fnserverparamsOrderLeader) {
                            d[key] = $(fnserverparamsOrderLeader[key]).val();
                        }
                        d['_locale'] = '{{session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang)}}';
                    },
                    "dataSrc": function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {   "render": function (data, type, row) {
                        return `<div class="text-center">${data}</data>`;
                    },
                    data: 'id', name: 'id',width: "80px" },
                    {data: 'date', name: 'date',width: "110px",},
                    {data: 'code_order', name: 'code_order'},
                    {data: 'customer', name: 'customer'},
                    {data: 'total_order', name: 'total_order',width: "110px",
                        "render": function (data, type, row) {
                            return `<div class="text-right">${data}</div>`;
                        },
                    },
                    {data: 'total_leader', name: 'total_leader',width: "110px",
                        "render": function (data, type, row) {
                            return `<div class="text-right">${data}</div>`;
                        },
                    },
                ]
            });
        }



        function fmtDate(d) {
            if (!d) return '—';
            var x = new Date(d);
            if (isNaN(x.getTime())) return d;
            return ('0'+x.getDate()).slice(-2)+'/'+('0'+(x.getMonth()+1)).slice(-2)+'/'+x.getFullYear();
        }

        $(document).on('click', '.form-nav-tab', function (e) {
            e.preventDefault();
            var targetTab = $(this).attr('data-tab-target');
            
            // Toggle active styles on sidebar buttons
            $('.form-nav-tab').removeClass('border-brand-500 bg-brand-50 text-brand-700 active')
                              .addClass('border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100');
            $('.form-nav-tab .form-nav-step').removeClass('bg-white text-brand-700')
                                   .addClass('bg-slate-100 text-slate-500');
            
            $(this).addClass('border-brand-500 bg-brand-50 text-brand-700 active')
                   .removeClass('border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-100');
            $(this).find('.form-nav-step').addClass('bg-white text-brand-700')
                                .removeClass('bg-slate-100 text-slate-500');
            
            // Switch Bootstrap Tab
            var $link = $('#tab_client a[href="' + targetTab + '"]');
            if (typeof $.fn.tab !== 'undefined' && $link.length) {
                $link.tab('show');
            } else {
                $('.tab-content .tab-pane').removeClass('active');
                $(targetTab).addClass('active');
                $link.trigger('shown.bs.tab');
            }
        });

        $(document).on('click', '.trigger-cccd-modal', function() {
            var rowData = JSON.parse(decodeURIComponent($(this).attr('data-row')));
            
            // Populate fields
            $('#cccd_fullname').text(rowData.cccd_fullname || '-');
            $('#cccd_number').text(rowData.number_cccd || '-');
            $('#cccd_birthday').text(fmtDate(rowData.birthday));
            $('#cccd_issued_place').text(rowData.issued_cccd || '-');
            $('#cccd_issued_date').text(fmtDate(rowData.date_cccd));
            $('#cccd_expired_date').text(fmtDate(rowData.cccd_expired_date));
            
            // Set up images
            if (rowData.cccd_front_image_url) {
                $('#cccd_front_preview').attr('src', rowData.cccd_front_image_url).show();
                $('#cccd_front_placeholder').hide();
            } else {
                $('#cccd_front_preview').hide();
                $('#cccd_front_placeholder').css('display', 'flex');
            }
            
            if (rowData.cccd_back_image_url) {
                $('#cccd_back_preview').attr('src', rowData.cccd_back_image_url).show();
                $('#cccd_back_placeholder').hide();
            } else {
                $('#cccd_back_preview').hide();
                $('#cccd_back_placeholder').css('display', 'flex');
            }
            
            // Set up status and action buttons
            var statusHtml = '';
            if (rowData.cccd_verified == 1) {
                statusHtml = '<span class="cccd-status-verified"><i class="fa fa-check-circle"></i> Đã xác thực</span>';
            } else if (rowData.cccd_verified == 2) {
                statusHtml = '<span class="label label-danger" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #fef2f2; color: #dc2626;"><i class="fa fa-times-circle"></i> Đã từ chối</span>';
            } else {
                statusHtml = '<span class="cccd-status-unverified"><i class="fa fa-exclamation-circle"></i> Chưa xác thực</span>';
            }
            $('#cccd_status_text').html(statusHtml);
            
            // Populate and show/hide reject reason text field
            $('#cccd_reject_reason').val(rowData.cccd_reject_reason || '');
            if (rowData.cccd_verified == 1) {
                $('#cccd_reject_reason_container').hide();
            } else {
                $('#cccd_reject_reason_container').show();
                if (rowData.cccd_verified == 2) {
                    $('#cccd_reject_reason').prop('placeholder', 'Lý do từ chối trước đó...');
                } else {
                    $('#cccd_reject_reason').prop('placeholder', 'Nhập lý do từ chối nếu không xác thực...');
                }
            }
            
            var actionBtnHtml = '';
            if (rowData.cccd_verified == 1) {
                actionBtnHtml = `<button type="button" class="btn btn-danger btn-toggle-cccd" data-id="${rowData.id}" data-status="0" style="border-radius: 8px; padding: 8px 20px; font-weight: 500;"><i class="fa fa-times-circle"></i> Hủy xác thực</button>`;
            } else {
                actionBtnHtml = `
                    <button type="button" class="btn btn-success btn-toggle-cccd" data-id="${rowData.id}" data-status="1" style="border-radius: 8px; padding: 8px 20px; font-weight: 500; margin-right: 8px;"><i class="fa fa-check-circle"></i> Xác thực</button>
                    <button type="button" class="btn btn-danger btn-toggle-cccd" data-id="${rowData.id}" data-status="2" style="border-radius: 8px; padding: 8px 20px; font-weight: 500;"><i class="fa fa-times-circle"></i> Từ chối</button>
                `;
            }
            $('#cccd_action_buttons').html(actionBtnHtml);
            
            // Show modal
            $('#cccdModal').modal('show');
        });

        $(document).on('click', '.btn-toggle-cccd', function() {
            var clientId = $(this).attr('data-id');
            var newStatus = $(this).attr('data-status');
            var rejectReason = $('#cccd_reject_reason').val() || '';
            
            if (newStatus == '2' && !rejectReason.trim()) {
                alert_float('warning', 'Vui lòng nhập lý do từ chối!');
                return;
            }
            
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Đang xử lý...');
            
            $.ajax({
                url: 'admin/clients/changeStatusCccd',
                type: 'POST',
                dataType: 'JSON',
                cache: false,
                data: {
                    id: clientId,
                    status: newStatus,
                    reject_reason: rejectReason,
                    _token: '{{ csrf_token() }}'
                },
                success: function(data) {
                    $('#cccdModal').modal('hide');
                    if (data.result) {
                        alert_float('success', data.message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        alert_float('error', data.message);
                        btn.prop('disabled', false).html(newStatus == '1' ? '<i class="fa fa-check-circle"></i> Xác thực' : '<i class="fa fa-times-circle"></i> Từ chối');
                    }
                },
                error: function() {
                    $('#cccdModal').modal('hide');
                    alert_float('error', 'Có lỗi xảy ra trong quá trình xử lý.');
                }
            });
        });

        var oTablePackages, oTableRecharges, oTableHomes;

        $(document).on('shown.bs.tab', 'a[href="#packages"]', function () {
            if (!oTablePackages) {
                oTablePackages = InitDataTable('#table_client_packages', 'admin/packages/subscriptions/getTable', {
                    'order': [[3, 'desc']],
                    'responsive': true,
                    "ajax": {
                        "type": "POST",
                        "url": "admin/packages/subscriptions/getTable",
                        "data": function (d) {
                            d.filter = {
                                client_id: '{{ $client['id'] ?? 0 }}'
                            };
                            d['_locale'] = 'vi';
                        }
                    },
                    columnDefs: [
                        { data: 'reference_no', name: 'reference_no', render: function(d){ return '<b>'+(d || '—')+'</b>'; } },
                        { data: 'package', name: 'package_id', render: function(p) {
                            if (!p) return '<div style="text-align:center;">—</div>';
                            var t = p.type || 'viewer';
                            var typeNames = { viewer: 'Viewer', broker: 'Môi giới', owner: 'Chủ nhà' };
                            return '<div style="text-align:center;">'
                                 + '<span class="pkg-badge '+t+'"><i class="fa fa-briefcase"></i> '+(typeNames[t]||t)+'</span>'
                                 + '<div style="font-size:11px;color:#94a3b8;margin-top:3px;">'+(p.period_months === 0 ? 'Không giới hạn' : (p.period_months || 1) + ' tháng')+'</div>'
                                 + '</div>';
                        }},
                        { data: 'price', name: 'price', render: function(d) {
                            return '<span class="points-val">'+Number(d).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' M'+'</span>';
                        }},
                        { data: 'start_date', name: 'start_date', render: function(d){ return fmtDate(d); } },
                        { data: 'end_date', name: 'end_date', render: function(d){ return fmtDate(d); } },
                        { data: 'days_remaining', name: 'days_remaining', orderable: false, render: function(d, t, r) {
                            if (r.status != 1) return '<div style="text-align:center;color:#94a3b8;">—</div>';
                            if (r.period_months === 0) return '<div style="text-align:center;" class="days-good"><i class="fa fa-check-circle"></i> Vĩnh viễn</div>';
                            var days = parseInt(d);
                            if (days < 0) return '<div style="text-align:center;" class="days-danger"><i class="fa fa-times-circle"></i> Hết hạn</div>';
                            var cls  = days > 30 ? 'days-good' : days > 7 ? 'days-warn' : 'days-danger';
                            var icon = days > 30 ? 'check-circle' : days > 7 ? 'exclamation-triangle' : 'times-circle';
                            return '<div style="text-align:center;" class="'+cls+'"><i class="fa fa-'+icon+'"></i> '+days+' ngày</div>';
                        }},
                        { data: 'status', name: 'status', render: function(d) {
                            if (d==1) return '<div style="text-align:center;"><span class="status-badge status-active">Đang hoạt động</span></div>';
                            if (d==0) return '<div style="text-align:center;"><span class="status-badge status-expired">Hết hạn</span></div>';
                            if (d==2) return '<div style="text-align:center;"><span class="status-badge status-cancel">Đã hủy</span></div>';
                            return '—';
                        }},
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function (d, t, r) {
                                return '<div style="text-align:center;">'
                                     + '<button type="button" class="btn-action btn-action-view btn-view-package-detail" data-row=\''+JSON.stringify(r).replace(/'/g,"&#39;")+'\' title="Xem chi tiết">'
                                     + '<i class="fa fa-eye"></i></button>'
                                     + '</div>';
                            }
                        }
                    ]
                });
            } else {
                oTablePackages.draw('page');
            }
        });

        $(document).on('shown.bs.tab', 'a[href="#recharges"]', function () {
            if (!oTableRecharges) {
                oTableRecharges = InitDataTable('#table_client_recharges', 'admin/payment/recharge/getTable', {
                    'order': [[3, 'desc']],
                    'responsive': true,
                    "ajax": {
                        "type": "POST",
                        "url": "admin/payment/recharge/getTable",
                        "data": function (d) {
                            d.filter = {
                                client_id: '{{ $client['id'] ?? 0 }}'
                            };
                            d['_locale'] = 'vi';
                        }
                    },
                    columnDefs: [
                        { data: 'reference_no', name: 'reference_no', render: function(d){ return '<b>'+(d || '—')+'</b>'; } },
                        { data: 'amount', name: 'amount', render: function(d){ return '<span class="text-success font-bold">+ ' + formatNumber(d) + ' M</span>'; } },
                        { data: 'payment_method', name: 'payment_method', render: function(d){ return d || 'Chuyển khoản'; } },
                        { data: 'created_at', name: 'created_at', render: function(d){ return fmtDate(d); } },
                        { data: 'status', name: 'status', render: function(d) {
                            if (d == 1) return '<span class="label label-success">Thành công</span>';
                            if (d == 0) return '<span class="label label-warning">Đang xử lý</span>';
                            if (d == 2) return '<span class="label label-danger">Đã hủy</span>';
                            return '—';
                        }}
                    ]
                });
            } else {
                oTableRecharges.draw('page');
            }
        });


        var oTableHomes;
        var fnserverparamsHome= {
            'customer_search': '#customer_id',
        };
        function loadHomes(){
            oTableHomes = InitDataTable('#table_client_homes', 'admin/manage_home/getList', {
                order: [
                    [5, 'desc']
                ],
                responsive: true,
                ajax: {
                    type: 'POST',
                    url: 'admin/manage_home/getList',
                    data: function (d) {
                        for (var key in fnserverparamsHome) {
                            d[key] = $(fnserverparamsHome[key]).val();
                        }
                        d['check_foryou'] = true;

                        d['_locale'] = '{{ session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang) }}';
                    },
                    dataSrc: function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {
                        data: 'code',
                        name: 'code',
                        width: '90px'
                    },
                    {
                        data: 'title',
                        name: 'title',
                        width: '200px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'type',
                        name: 'type',
                         width: '100px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'area',
                        name: 'area',
                         width: '80px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'price',
                        name: 'price',
                         width: '100px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'created_at',
                        name: 'created_at',
                        width: '160px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'status',
                        name: 'status',
                         width: '120px'
                    },
                    {
                        data: 'options',
                        name: 'options',
                        orderable: false,
                        searchable: false,
                        width: '150px',
                        visible: false
                    }
                ]
            });
        }
        $(document).on('shown.bs.tab', 'a[href="#homes"]', function () {
            if (!oTableHomes) {
                loadHomes();
            } else {
                oTableHomes.draw('page');
            }
        });

        $(document).on('shown.bs.tab', 'a[href="#list_client_referral_review"]', function () {
            loadTableReferralReview();
        });

        $(document).on('shown.bs.tab', 'a[href="#list_client_referral_order"]', function () {
            loadTableReferralOrder();
        });

        $(document).on('shown.bs.tab', 'a[href="#list_client_leader"]', function () {
            loadTableOrderLeader();
        });

        $('#table_list_client_leader').on('draw.dt', function () {
            var table = $(this).DataTable();
            var total =  table.column(4).data().sum();
            var total_leader =  table.column(5).data().sum();
            $("#table_list_client_leader").find('tfoot .total').html(formatNumber(total));
            $("#table_list_client_leader").find('tfoot .total_leader').html(formatNumber(total_leader));
        });

        $.each(fnserverparamsOrderLeader, function(filterIndex, filterItem) {
            $('' + filterItem).on('change', function() {
                oTableOrderLeader.draw('page')
            });
        });

        // Click handler to view package detail
        $(document).on('click', '.btn-view-package-detail', function () {
            var r = $(this).data('row');
            if (typeof r === 'string') { try { r = JSON.parse(r); } catch(e){} }
            showPackageDetail(r);
        });

        function showPackageDetail(r) {
            var c = r.client   || {};
            var p = r.package  || {};
            var t = p.type || 'viewer';
            var typeNames = { viewer: 'Viewer', broker: 'Môi giới', owner: 'Chủ nhà' };

            var av = c.avatar_url
                ? '<img src="'+c.avatar_url+'" class="detail-avatar" onerror="this.outerHTML=\'<div class=detail-avatar-ph>\'+\''+( (c.fullname||'?').charAt(0).toUpperCase() )+'\'+\'</div>\'">'
                : '<div class="detail-avatar-ph">'+(c.fullname||'?').charAt(0).toUpperCase()+'</div>';

            var typeLabel = c.type_client==1 ? 'Môi giới' : c.type_client==2 ? 'Chủ nhà' : 'Viewer';

            var statusHtml;
            if (r.status==1) statusHtml = '<span class="status-badge status-active">Đang hoạt động</span>';
            else if (r.status==0) statusHtml = '<span class="status-badge status-expired">Hết hạn</span>';
            else statusHtml = '<span class="status-badge status-cancel">Đã hủy</span>';

            var pkgHtml = '<span class="pkg-badge '+t+'">'+(typeNames[t]||t)+'</span>'
                        + ' <span style="font-size:11px;color:#94a3b8;">'+(p.period_months === 0 ? 'Không giới hạn' : (p.period_months || 1) + ' tháng')+'</span>';

            var daysEl = '—';
            if (r.status == 1) {
                if (r.period_months === 0) {
                    daysEl = '<span class="days-good">Vĩnh viễn</span>';
                } else {
                    var days = parseInt(r.days_remaining || 0);
                    if (days < 0) daysEl = '<span class="days-danger">Đã hết hạn</span>';
                    else {
                        var cls = days > 30 ? 'days-good' : days > 7 ? 'days-warn' : 'days-danger';
                        daysEl = '<span class="'+cls+'">'+days+' ngày</span>';
                    }
                }
            }

            var html = '<div class="detail-member-card">'+av
                + '<div><div class="detail-name">'+(c.fullname||'—')+'</div>'
                + '<div class="detail-sub">'+(c.phone||'')+(c.email ? ' · '+c.email : '')+' · '+typeLabel+'</div></div></div>';

            html += '<div class="info-grid">'
                + ib('Mã đơn hàng',    r.reference_no || '—')
                + ib('Gói cước',       pkgHtml)
                + ib('Giá (điểm M)',   '<span class="points-val">'+Number(r.price).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') + ' M'+'</span>')
                + ib('Trạng thái',     statusHtml)
                + ib('Ngày bắt đầu',   fmtDate(r.start_date))
                + ib('Ngày hết hạn',   fmtDate(r.end_date))
                + ib('Thời gian còn',  daysEl)
                + ib('Thanh toán',     r.payment_mode_id > 0 ? 'Phương thức #'+r.payment_mode_id : 'Điểm M')
                + '</div>';

            if (r.note) {
                html += '<div style="margin-top:12px;padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;">'
                      + '<div style="font-size:10.5px;font-weight:700;color:#92400e;text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;"><i class="fa fa-sticky-note-o"></i> Ghi chú</div>'
                      + '<div style="font-size:13px;color:#78350f;">'+r.note+'</div>'
                      + '</div>';
            }

            $('#detail-body').html(html);
            $('#detailModal').modal('show');
        }

        function ib(label, value) {
            return '<div class="info-box"><div class="info-lbl">'+label+'</div><div class="info-val">'+value+'</div></div>';
        }


        //dt
        var oTableHomeFavourite;
        var fnserverparamsHomeFavourite= {
            'customer_favourite_id': '#customer_id'
        };
        function loadHomeFavourite(){
            oTableHomeFavourite = InitDataTable('#table_client_home_favourite', 'admin/manage_home/getList', {
                order: [
                    [6, 'desc']
                ],
                responsive: true,
                ajax: {
                    type: 'POST',
                    url: 'admin/manage_home/getList',
                    data: function (d) {
                        for (var key in fnserverparamsHomeFavourite) {
                            d[key] = $(fnserverparamsHomeFavourite[key]).val();
                        }

                        d['_locale'] = '{{ session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang) }}';
                    },
                    dataSrc: function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {
                        data: 'code',
                        name: 'code',
                        width: '90px'
                    },
                    {
                        data: 'title',
                        name: 'title',
                        width: '200px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'type',
                        name: 'type',
                         width: '100px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'area',
                        name: 'area',
                         width: '80px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'price',
                        name: 'price',
                         width: '100px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-left">' + (data || '') + '</div>';
                        },
                        data: 'poster',
                        name: 'poster',
                        width: '120px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'created_at',
                        name: 'created_at',
                        width: '160px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'status',
                        name: 'status',
                         width: '140px'
                    },
                ]
            });
        }

         $(document).on('shown.bs.tab', 'a[href="#home_favourite"]', function () {
            if(!oTableHomeFavourite){
                loadHomeFavourite();
            } else {
                oTableHomeFavourite.draw('page');
            }
         });

        var oTableHomeSave;
        var fnserverparamsHomeSave= {
            'customer_save_id': '#customer_id'
        };
        function loadHomeSave(){
            oTableHomeSave = InitDataTable('#table_client_home_save', 'admin/manage_home/getList', {
                order: [
                    [6, 'desc']
                ],
                responsive: true,
                ajax: {
                    type: 'POST',
                    url: 'admin/manage_home/getList',
                    data: function (d) {
                        for (var key in fnserverparamsHomeSave) {
                            d[key] = $(fnserverparamsHomeSave[key]).val();
                        }

                        d['_locale'] = '{{ session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang) }}';
                    },
                    dataSrc: function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {
                        data: 'code',
                        name: 'code',
                        width: '90px'
                    },
                    {
                        data: 'title',
                        name: 'title',
                        width: '200px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'type',
                        name: 'type',
                         width: '100px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'area',
                        name: 'area',
                         width: '80px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'price',
                        name: 'price',
                         width: '100px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-left">' + (data || '') + '</div>';
                        },
                        data: 'poster',
                        name: 'poster',
                        width: '120px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'created_at',
                        name: 'created_at',
                        width: '160px'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'status',
                        name: 'status',
                         width: '140px'
                    },
                ]
            });
        }

         $(document).on('shown.bs.tab', 'a[href="#home_save"]', function () {
            if(!oTableHomeSave){
                loadHomeSave();
            } else {
                oTableHomeSave.draw('page');
            }
         });

        var oTableClientFollow;
        var fnserverparamsClientFollow= {
            'customer_id': '#customer_id'
        };
        function loadClientFollow(){
            oTableClientFollow = InitDataTable('#table_client_follow', 'api/customer/getListCustomerFollow', {
                order: [
                    [5, 'desc']
                ],
                responsive: true,
                ajax: {
                    type: 'POST',
                    url: 'api/customer/getListCustomerFollow',
                    data: function (d) {
                        for (var key in fnserverparamsClientFollow) {
                            d[key] = $(fnserverparamsClientFollow[key]).val();
                        }

                        d['_locale'] = '{{ session('lang', \Illuminate\Support\Facades\Auth::guard('admin')->user()->lang) }}';
                    },
                    dataSrc: function (json) {
                        return json.data;
                    }
                },
                columnDefs: [
                    {   "render": function (data, type, row) {
                            return `<div class="text-center">${data}</data>`;
                        },
                        data: 'id', name: 'id',width: "50px"
                    },
                    {data: 'avatar', name: 'avatar',width: "90px",},
                    {data: 'fullname', name: 'fullname'},
                    {data: 'phone', name: 'phone'},
                    {
                        "render": function (data, type, row) {
                            return `<div class="text-center">${data}</div>`;
                        },
                        data: 'type_client', name: 'type_client'
                    },
                    {
                        render: function (data) {
                            return '<div class="text-center">' + (data || '') + '</div>';
                        },
                        data: 'followed_at',
                        name: 'followed_at',
                        width: '160px'
                    },
                ]
            });
        }

         $(document).on('shown.bs.tab', 'a[href="#client_follow"]', function () {
            if(!oTableClientFollow){
                loadClientFollow();
            } else {
                oTableClientFollow.draw('page');
            }
         });

        $(document).on('click', '.status-badge-btn', function (e) {
            e.stopPropagation();

            var $wrap = $(this).closest('.status-dropdown-wrap');

            $('.status-dropdown-menu')
                .not($wrap.find('.status-dropdown-menu'))
                .addClass('hidden');

            $wrap.find('.status-dropdown-menu').toggleClass('hidden');
        });

        $(document).on('click', function () {
            $('.status-dropdown-menu').addClass('hidden');
        });

        $(document).on('click', '.home-status-item', function (e) {
            e.preventDefault();

            var $a = $(this);
            var homeId = $a.data('id');
            var status = $a.data('status');
            var $wrap = $a.closest('.status-dropdown-wrap');
            var $btn = $wrap.find('.status-badge-btn');

            $wrap.find('.status-dropdown-menu').addClass('hidden');

            $.ajax({
                url: 'admin/manage_home/changeStatus',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: homeId,
                    status: status,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (res) {
                    if (res.success) {
                        alert_float('success', res.message);

                        $btn
                            .attr('data-current', res.status)
                            .removeClass()
                            .addClass(
                                'status-badge-btn inline-flex items-center gap-1.5 px-3 py-1.5 text-[12px] font-semibold border rounded-full cursor-pointer shadow-sm hover:shadow transition-all ' +
                                res.badge_class
                            )
                            .html(
                                '<span class="w-1.5 h-1.5 rounded-full" style="background-color: ' + res.color + '"></span>' +
                                '<span>' + res.status_name + '</span>' +
                                '<i class="fa fa-chevron-down text-[8px] ml-0.5 opacity-60"></i>'
                            );

                        $wrap
                            .find('.home-status-item')
                            .removeClass('font-bold bg-slate-50 text-slate-900')
                            .addClass('text-slate-600');

                        $wrap.find('.home-status-item').find('.fa-check').remove();

                        var $activeItem = $wrap.find('.home-status-item[data-status="' + res.status + '"]');

                        $activeItem
                            .addClass('font-bold bg-slate-50 text-slate-900')
                            .removeClass('text-slate-600')
                            .append('<i class="fa fa-check text-[10px] ml-auto text-slate-500"></i>');

                        countAll();
                    } else {
                        alert_float('error', res.message);
                    }
                },
                error: function () {
                    alert_float('error', 'Có lỗi xảy ra, vui lòng thử lại!');
                }
            });
        });
    </script>
@endsection
