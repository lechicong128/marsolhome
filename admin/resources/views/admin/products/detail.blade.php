<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queen Realty - Hệ Thống Đăng Tin & Quản Trị Bất Động Sản</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        /* Custom scrollbar to look sleek */
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
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col lg:flex-row">

    <!-- SIDEBAR NAVIGATION (Desktop) -->
    <aside class="bg-slate-950 text-white w-full lg:w-64 flex-shrink-0 flex flex-col border-r border-slate-800 sticky top-0 lg:h-screen z-50">
        <!-- Logo Header -->
        <div class="px-6 py-5 flex items-center justify-between border-b border-slate-800 bg-slate-900/50">
            <div class="flex items-center gap-3">
                <div class="bg-amber-500 text-slate-950 p-2 rounded-xl font-black flex items-center justify-center">
                    <i class="fa-solid fa-hotel text-lg"></i>
                </div>
                <div>
                    <span class="text-base font-black tracking-wider bg-gradient-to-r from-amber-400 to-amber-200 bg-clip-text text-transparent block">QUEEN REALTY</span>
                    <span class="text-[10px] block text-slate-500">Hệ Thống Admin SPA</span>
                </div>
            </div>
            <!-- Mobile Menu Toggle Button -->
            <button id="mobile-menu-toggle" class="lg:hidden text-slate-400 hover:text-white transition-colors">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Sidebar Navigation Menu -->
        <nav id="sidebar-nav-links" class="hidden lg:flex flex-col flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <div class="text-[10px] uppercase font-bold text-slate-500 px-3 tracking-wider mb-2">Không gian làm việc</div>
            
            <!-- Dashboard Link -->
            <button data-target="dashboard" class="sidebar-link w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold transition-all text-slate-400 hover:bg-slate-900 hover:text-white text-left">
                <i class="fa-solid fa-chart-line text-sm"></i> Tổng Quan Dashboard
            </button>

            <!-- Create Property Link -->
            <button data-target="create" class="sidebar-link w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold transition-all text-slate-400 hover:bg-slate-900 hover:text-white text-left">
                <i class="fa-solid fa-circle-plus text-sm"></i> Đăng Tin BĐS Mới
            </button>

            <!-- Property List Link -->
            <button data-target="list" class="sidebar-link w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-bold transition-all text-slate-400 hover:bg-slate-900 hover:text-white text-left">
                <i class="fa-solid fa-folder-open text-sm"></i> Quản Lý Danh Sách Tin
            </button>

            <div class="text-[10px] uppercase font-bold text-slate-500 px-3 tracking-wider pt-6 mb-2">Hệ thống</div>
            
            <button onclick="showToast('Hệ thống máy chủ đang hoạt động ổn định.', 'success')" class="w-full flex items-center gap-3 px-3 py-3 rounded-xl text-xs font-semibold text-slate-500 hover:bg-slate-900 hover:text-slate-300 text-left">
                <i class="fa-solid fa-circle-nodes"></i> Trạng Thái Server
            </button>
        </nav>

        <!-- Sidebar Profile Footer -->
        <div class="hidden lg:flex p-4 border-t border-slate-900 bg-slate-900/30 items-center gap-3">
            <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=100&q=80" alt="Avatar Admin" class="w-10 h-10 rounded-full object-cover border border-amber-500" onerror="this.src='https://via.placeholder.com/150'">
            <div class="truncate text-left">
                <p class="text-xs font-bold text-slate-100 truncate">Nguyễn Thu Hà</p>
                <p class="text-[10px] text-slate-500 truncate">Quản trị viên cấp cao</p>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTAINER -->
    <div class="flex-1 flex flex-col min-w-0 min-h-screen">
        
        <!-- Header status bar (Visible on desktop only) -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 hidden lg:flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500">
                <span class="text-slate-400">Hệ Thống Phê Duyệt</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800" id="current-breadcrumb">Tổng Quan Dashboard</span>
            </div>
            
            <div class="flex items-center gap-4 text-xs font-semibold text-slate-600">
                <span class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div> Live Mode</span>
                <span class="text-slate-300">|</span>
                <span>Cập nhật cuối: Hôm nay, 11:14 AM</span>
            </div>
        </header>

        <!-- MAIN VIEW WORKSPACE (SPA) -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
            <!-- 2. VIEW: CREATE PROPERTY (TRANG ĐĂNG TIN MỚI TOÀN MÀN HÌNH) -->
            <div id="view-create" class="app-view  max-w-4xl mx-auto space-y-6">
                <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                    <div>
                        <h1 id="form-header-title" class="text-xl sm:text-2xl font-black text-slate-950 flex items-center gap-2">
                            <i class="fa-solid fa-circle-plus text-amber-500"></i> TẠO TIN ĐĂNG BẤT ĐỘNG SẢN MỚI
                        </h1>
                        <p class="text-xs text-slate-500 mt-1">Vui lòng hoàn thành các trường thông tin chuẩn theo thứ tự các bước bên dưới</p>
                    </div>
                    <button type="button" id="btn-reset-form-top" class="text-xs font-bold text-amber-600 hover:text-amber-700 bg-amber-50 px-3 py-2 rounded-xl transition-all">
                        <i class="fa-solid fa-rotate-left"></i> Tạo mới hoàn toàn
                    </button>
                </div>

                <form id="property-form" class="space-y-8" onsubmit="return false;">
                    <input type="hidden" id="edit-property-id">

                    <!-- BƯỚC 1: THÔNG TIN CHI TIẾT BẤT ĐỘNG SẢN & NỘI THẤT -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-600 pb-3 border-b border-slate-100 flex items-center gap-2">
                            <span class="w-5 h-5 bg-amber-100 rounded-full inline-flex items-center justify-center text-[10px]">1</span>
                            Thông tin chi tiết tài sản & Nội thất
                        </h3>

                        <!-- Loại giao dịch -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Loại Giao Dịch <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3 max-w-md">
                                <label class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-amber-500 has-[:checked]:border-amber-500 has-[:checked]:text-slate-950 font-bold text-xs">
                                    <input type="radio" name="type" value="Mua bán" checked class="sr-only"> Mua Bán
                                </label>
                                <label class="flex items-center justify-center gap-2 py-3 px-4 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-amber-500 has-[:checked]:border-amber-500 has-[:checked]:text-slate-950 font-bold text-xs">
                                    <input type="radio" name="type" value="Cho thuê" class="sr-only"> Cho Thuê
                                </label>
                            </div>
                        </div>

                        <!-- Loại nhà ở & Pháp lý -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Loại Nhà Ở <span class="text-red-500">*</span></label>
                                <select name="property_type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="Nhà biệt thự">Nhà biệt thự</option>
                                    <option value="Nhà mặt phố">Nhà mặt phố, mặt tiền</option>
                                    <option value="Nhà ngõ hẻm">Nhà ngõ hẻm</option>
                                    <option value="Nhà phố liền kề">Nhà phố liền kề</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pháp lý bất động sản</label>
                                <select name="legal" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="Sổ hồng / Sổ đỏ">Sổ hồng / Sổ đỏ chính chủ</option>
                                    <option value="Đang chờ sổ">Đang chờ ra sổ hồng</option>
                                    <option value="Giấy tờ viết tay">Giấy tờ viết tay hợp pháp</option>
                                </select>
                            </div>
                        </div>

                        <!-- Vị trí Địa Lý chi tiết -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase">Địa chỉ chi tiết của tài sản <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <select id="select-province" name="province" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="Hồ Chí Minh">TP. Hồ Chí Minh</option>
                                    <option value="Hà Nội">Hà Nội</option>
                                    <option value="Đà Nẵng">Đà Nẵng</option>
                                </select>
                                <select id="select-district" name="district" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <!-- Loaded via JS -->
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <input type="text" name="ward" required placeholder="Phường/Xã (Ví dụ: Phường 2)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                                <input type="text" name="street" required placeholder="Số nhà, Tên đường (Ví dụ: Đường Quốc Lộ 32)" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        <!-- Giá & Diện tích & Số phòng -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mức Giá (VNĐ) <span class="text-red-500">*</span></label>
                                <input type="number" id="form-price-input" name="price" required placeholder="Ví dụ: 3000000000" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-extrabold text-amber-600">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Diện Tích (m²) <span class="text-red-500">*</span></label>
                                <input type="number" name="area" required placeholder="Ví dụ: 100" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-extrabold">
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 text-center">Phòng ngủ</label>
                                <input type="number" name="beds" min="0" value="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-center font-bold focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 text-center">Phòng tắm</label>
                                <input type="number" name="baths" min="0" value="1" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-center font-bold focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 text-center">Hướng nhà</label>
                                <select name="direction" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold focus:outline-none focus:ring-2 focus:ring-amber-500">
                                    <option value="Đông">Đông</option>
                                    <option value="Tây">Tây</option>
                                    <option value="Nam">Nam</option>
                                    <option value="Bắc">Bắc</option>
                                    <option value="Đông Nam">Đông Nam</option>
                                    <option value="Tây Nam">Tây Nam</option>
                                    <option value="Đông Bắc">Đông Bắc</option>
                                    <option value="Tây Bắc">Tây Bắc</option>
                                </select>
                            </div>
                        </div>

                        <!-- THÔNG TIN NỘI THẤT BỔ SUNG -->
                        <div class="bg-slate-50/70 p-5 rounded-2xl border border-slate-200/60 space-y-4">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wide flex items-center gap-1.5">
                                <i class="fa-solid fa-couch text-amber-500"></i> Hiện trạng & Chi tiết nội thất
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Tình trạng bàn giao</label>
                                    <select name="interior_status" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                        <option value="Nội thất cao cấp">Nội thất cao cấp (Đầy đủ sang trọng)</option>
                                        <option value="Đầy đủ nội thất">Đầy đủ nội thất (Xách vali vào ở)</option>
                                        <option value="Nội thất cơ bản">Nội thất cơ bản (Liền tường)</option>
                                        <option value="Nhà trống">Nhà trống (Không nội thất)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1.5">Ghi chú thêm về nội thất</label>
                                    <input type="text" name="interior_note" placeholder="Ví dụ: Sofa da thật, bếp từ nhập khẩu Đức..." class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2">Trang thiết bị nội thất có sẵn</label>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Điều hòa" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Điều hòa
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Tủ lạnh" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Tủ lạnh
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Máy giặt" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Máy giặt
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Giường ngủ" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Giường ngủ
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Sofa" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Sofa phòng khách
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Bếp điện" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Hệ thống bếp
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Tủ quần áo" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Tủ quần áo
                                    </label>
                                    <label class="flex items-center gap-2 text-xs text-slate-700 bg-white px-3 py-2 border border-slate-200 rounded-xl cursor-pointer">
                                        <input type="checkbox" name="interior_items" value="Nóng lạnh" class="rounded text-amber-500 focus:ring-amber-500 w-4 h-4"> Bình nóng lạnh
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Tiêu đề & Mô tả -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tiêu Đề Tin Đăng <span class="text-red-500">*</span></label>
                            <input type="text" id="form-title-input" name="title" required placeholder="Ví dụ: Bán căn hộ 1PN, 1WC tại The Queen Villas, 3 tỷ VND, 100m2..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mô Tả Chi Tiết Nội Dung <span class="text-red-500">*</span></label>
                            <textarea name="description" required rows="4" placeholder="Mô tả chi tiết cấu trúc, nội thất, tiện ích lân cận..." class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500 leading-relaxed"></textarea>
                        </div>
                    </div>

                    <!-- BƯỚC 2: HÌNH ẢNH & VIDEO (Tối thiểu 3 ảnh - Thiết kế giống clip 1) -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-600 pb-3 border-b border-slate-100 flex items-center gap-2">
                            <span class="w-5 h-5 bg-amber-100 rounded-full inline-flex items-center justify-center text-[10px]">2</span>
                            Tải lên hình ảnh & video bất động sản
                        </h3>

                        <!-- Video checklists status -->
                        <div class="flex flex-col sm:flex-row gap-3 sm:items-center text-xs">
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
                                <div id="status-img-count" class="w-4 h-4 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-[9px] font-black">
                                    <i class="fa-solid fa-xmark"></i>
                                </div>
                                <span class="text-slate-600 font-bold">Đăng tối thiểu 3 ảnh</span>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-full border border-slate-200">
                                <div class="w-4 h-4 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-[9px]">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <span class="text-slate-500 font-medium">Khuyên dùng thêm video chân thật</span>
                            </div>
                        </div>

                        <!-- Drag and drop trigger region -->
                        <div id="trigger-upload-area" class="p-8 border-2 border-dashed border-slate-300 hover:border-amber-500 rounded-2xl bg-slate-50/50 hover:bg-amber-50/10 text-center cursor-pointer transition-all flex flex-col items-center justify-center">
                            <div class="w-12 h-12 bg-white rounded-2xl shadow-sm flex items-center justify-center text-amber-500 text-lg mb-3">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <p class="text-xs font-bold text-slate-800">Bấm hoặc kéo thả ảnh & video vào đây (<span id="count-media-txt">0</span>/24)</p>
                            <p class="text-[10px] text-slate-400 mt-1">Định dạng PNG, JPG, JPEG, GIF, MP4, MOV. Dung lượng video tối đa 300MB.</p>
                        </div>

                        <!-- Dynamic Upload Loading Spinner -->
                        <div id="form-upload-loader" class="hidden p-4 bg-amber-50 border border-amber-100 rounded-2xl text-xs text-amber-800 flex items-center justify-center gap-2">
                            <i class="fa-solid fa-circle-notch animate-spin text-amber-500"></i> Đang tải dữ liệu đa phương tiện lên hệ thống...
                        </div>

                        <!-- Uploaded Preview grid (With direct captions inputs inside cards) -->
                        <div id="form-media-list" class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <!-- Injected JS -->
                        </div>

                        <!-- Video External Link -->
                        <div class="space-y-2 pt-2">
                            <label class="block text-xs font-bold text-slate-600"><i class="fa-solid fa-link text-slate-400 mr-1"></i> Liên kết video ngoài (Youtube / Tiktok)</label>
                            <input type="text" name="video_url" placeholder="Dán đường dẫn link liên kết tại đây" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                        </div>
                    </div>

                    <!-- BƯỚC 3: ĐỊA CHỈ LIÊN HỆ ĐĂNG TIN -->
                    <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm space-y-6">
                        <h3 class="text-xs font-black uppercase tracking-wider text-amber-600 pb-3 border-b border-slate-100 flex items-center gap-2">
                            <span class="w-5 h-5 bg-amber-100 rounded-full inline-flex items-center justify-center text-[10px]">3</span>
                            Thông tin địa chỉ liên hệ chủ tin
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tên người liên hệ <span class="text-red-500">*</span></label>
                                <input type="text" name="contact_name" required placeholder="Họ và tên người đại diện" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Số điện thoại liên hệ <span class="text-red-500">*</span></label>
                                <input type="tel" name="contact_phone" required placeholder="Ví dụ: 090xxxxxxx" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Địa chỉ liên hệ chính xác <span class="text-red-500">*</span></label>
                                <input type="text" name="contact_address" required placeholder="Địa chỉ giao dịch trực tiếp" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Vai trò liên hệ <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center justify-center gap-1 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-amber-500 has-[:checked]:border-amber-500 has-[:checked]:text-slate-950 text-xs font-bold">
                                        <input type="radio" name="contact_role" value="Chính chủ" checked class="sr-only"> Chính chủ
                                    </label>
                                    <label class="flex items-center justify-center gap-1 py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl cursor-pointer transition-all has-[:checked]:bg-amber-500 has-[:checked]:border-amber-500 has-[:checked]:text-slate-950 text-xs font-bold">
                                        <input type="radio" name="contact_role" value="Môi giới" class="sr-only"> Môi giới
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Hẹn thời gian liên hệ tốt nhất</label>
                                <input type="text" name="contact_time" placeholder="Ví dụ: 8h00 - 21h00 các ngày trong tuần" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500">
                            </div>
                        </div>

                        <!-- Dark Summary Board -->
                        <div class="bg-slate-950 text-white p-5 rounded-2xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <span class="text-[9px] uppercase tracking-wider text-amber-400 font-extrabold block">Bản tóm tắt tin đăng bất động sản</span>
                                <h5 id="checkout-title-display" class="font-bold text-xs text-slate-100 mt-1 line-clamp-1">Chưa có tiêu đề tin đăng...</h5>
                                <p class="text-[10px] text-slate-500 mt-0.5">Người liên hệ: <strong id="checkout-name-display" class="text-slate-200">Chưa điền</strong> • Vai trò: <strong id="checkout-role-display" class="text-slate-300">Chính chủ</strong></p>
                            </div>
                            <div class="text-left sm:text-right flex-shrink-0">
                                <span class="text-[10px] text-slate-400 block">Mức giá đề xuất</span>
                                <span id="checkout-price-display" class="text-base font-black text-amber-400">0 VNĐ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" id="btn-reset-form-bottom" class="px-6 py-3 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition-all">
                            Xóa Trắng Form
                        </button>
                        <button type="button" id="btn-save-property" class="px-8 py-3 bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-slate-950 font-black text-xs rounded-xl shadow-md shadow-amber-500/20 transition-all flex items-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> LƯU & XUẤT BẢN TIN
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <!-- NOTIFICATION SYSTEM TOASTS -->
    <div id="toast-container" class="fixed top-6 right-6 z-50 space-y-2 pointer-events-none"></div>

    <!-- JAVASCRIPT APP WORKSPACE -->
    <script>
        // System Data store
        let mockProperties = [
            {
                id: "BĐS-9821",
                type: "Mua bán",
                property_type: "Nhà biệt thự",
                title: "Biệt thự The Queen Villas 3 tầng cao cấp có sân vườn rộng",
                description: "Căn biệt thự cao cấp nằm trong khu khép kín The Queen Villas. Có sân vườn rộng, thiết kế 3 tầng phong cách Châu Âu hiện đại. Giao thông thuận tiện, an ninh 24/7.",
                province: "Hồ Chí Minh",
                district: "Quận 2",
                ward: "Thạnh Mỹ Lợi",
                street: "Đường Quốc lộ 32",
                price: 3200000000,
                area: 120,
                direction: "Đông Nam",
                beds: 4,
                baths: 3,
                legal: "Sổ hồng / Sổ đỏ",
                interior_status: "Nội thất cao cấp",
                interior_note: "Trần thạch cao, sàn gỗ tự nhiên, thiết bị vệ sinh nhập khẩu",
                interior_items: ["Điều hòa", "Tủ lạnh", "Máy giặt", "Giường ngủ", "Sofa"],
                contact_name: "Nguyễn Văn Hùng",
                contact_phone: "0901234567",
                contact_address: "Văn phòng Queen Realty, Quận 2, HCM",
                contact_role: "Môi giới",
                contact_time: "Cả ngày",
                date_created: "21/05/2026"
            },
            {
                id: "BĐS-4392",
                type: "Mua bán",
                property_type: "Nhà mặt phố",
                title: "Nhà mặt phố liền kề thiết kế phong cách tối giản Bắc Âu",
                description: "Thích hợp làm văn phòng đại diện hoặc showroom trưng bày. Nhà mới xây 100%, nội thất cơ bản cao cấp phong cách tối giản ấm cúng.",
                province: "Hà Nội",
                district: "Cầu Giấy",
                ward: "Dịch Vọng",
                street: "Đường Xuân Thủy",
                price: 5400000000,
                area: 85,
                direction: "Tây",
                beds: 3,
                baths: 2,
                legal: "Sổ hồng / Sổ đỏ",
                interior_status: "Nội thất cơ bản",
                interior_note: "Có điều hòa các phòng, kệ tủ bếp liền tường",
                interior_items: ["Điều hòa", "Bếp điện"],
                contact_name: "Trần Thị Lan",
                contact_phone: "0918888999",
                contact_address: "Xuân Thủy, Cầu Giấy, Hà Nội",
                contact_role: "Chính chủ",
                contact_time: "Giờ hành chính",
                date_created: "18/05/2026"
            },
            {
                id: "BĐS-1283",
                type: "Cho thuê",
                property_type: "Nhà ngõ hẻm",
                title: "Nhà nguyên căn ngõ hẻm xe hơi tránh nhau Nguyễn Trãi",
                description: "Nhà nguyên căn cho thuê làm căn hộ dịch vụ hoặc hộ gia đình ở lâu dài. Khu vực an ninh yên tĩnh, gần nhiều trường Đại học lớn.",
                province: "Hồ Chí Minh",
                district: "Quận 1",
                ward: "Phường Nguyễn Cư Trinh",
                street: "Hẻm 120 Nguyễn Trãi",
                price: 25000000,
                area: 60,
                direction: "Đông",
                beds: 2,
                baths: 2,
                legal: "Sổ hồng / Sổ đỏ",
                interior_status: "Đầy đủ nội thất",
                interior_note: "Đầy đủ sofa, tivi, giường nệm cao cấp mới mua",
                interior_items: ["Điều hòa", "Tủ lạnh", "Máy giặt", "Giường ngủ", "Sofa", "Bếp điện"],
                contact_name: "Phạm Hải Nam",
                contact_phone: "0933445566",
                contact_address: "120/4 Nguyễn Trãi, Quận 1, HCM",
                contact_role: "Chính chủ",
                contact_time: "Sau 18h00",
                date_created: "15/05/2026"
            }
        ];

        // Districts list mapping
        const districtsData = {
            "Hồ Chí Minh": ["Quận 1", "Quận 2", "Quận 3", "Quận 7", "Quận Bình Thạnh", "Quận Gò Vấp"],
            "Hà Nội": ["Cầu Giấy", "Ba Đình", "Hoàn Kiếm", "Đống Đa", "Tây Hồ", "Nam Từ Liêm"],
            "Đà Nẵng": ["Sơn Trà", "Hải Châu", "Thanh Khê", "Liên Chiểu", "Ngũ Hành Sơn"]
        };

        let isGridView = true;
        let uploadedFormMedia = [];
        let uploadedFormMediaCaptions = [];

        // Declare functions globally before document ready to prevent iFrame cross-context binding errors
        window.updateFormCaptionValue = function(idx, val) {
            uploadedFormMediaCaptions[idx] = val;
        };

        window.deleteFormPhoto = function(idx) {
            uploadedFormMedia.splice(idx, 1);
            uploadedFormMediaCaptions.splice(idx, 1);
            renderFormMediaThumbnails();
            checkMinPhotoCriteria();
            showToast("Đã xóa tệp đính kèm!", "info");
        };

        window.removeToast = function(id) {
            $(`#${id}`).removeClass("translate-x-0 opacity-100").addClass("translate-x-12 opacity-0");
            setTimeout(() => {
                $(`#${id}`).remove();
            }, 300);
        };

        // Switch dynamic views in single app workflow
        window.switchView = function(viewId) {
            $('.app-view').addClass('hidden');
            $('#view-' + viewId).removeClass('hidden');

            // Sidebar styling active class updating
            $('.sidebar-link').removeClass('bg-amber-500 text-slate-950 font-black').addClass('text-slate-400 hover:bg-slate-900 hover:text-white');
            $('[data-target="' + viewId + '"]').addClass('bg-amber-500 text-slate-950 font-black').removeClass('text-slate-400 hover:bg-slate-900 hover:text-white');

            // Update breadcrumb navigation
            let bcTitle = "Tổng Quan Dashboard";
            if (viewId === 'create') bcTitle = "Đăng Tin BĐS Mới";
            else if (viewId === 'list') bcTitle = "Quản Lý Danh Sách Tin";
            $("#current-breadcrumb").text(bcTitle);
        };

        $(document).ready(function() {
            // Set initial dynamic states
            let today = new Date().toISOString().split('T')[0];
            $("#form-start-date").val(today);
            handleProvinceChange();

            // Render views
            switchView('create');
            renderProperties();
            updateDashboardCounters();
            syncFormCheckoutPreviews();

            // Mobile menu toggling behavior
            $("#mobile-menu-toggle").on("click", function() {
                $("#sidebar-nav-links").toggleClass("hidden flex");
            });

            // SPA View switcher binding
            $(".sidebar-link").on("click", function() {
                let target = $(this).data("target");
                switchView(target);
                
                // Close drawer navigation on mobile after selection
                if (window.innerWidth < 1024) {
                    $("#sidebar-nav-links").addClass("hidden").removeClass("flex");
                }
            });

            // Price slider dynamically updating breadcrumbs text
            $("#filter-price-range").on("input", function() {
                $("#price-val-display").text(formatPrice(parseFloat($(this).val())));
                renderProperties();
            });

            $("#filter-area-range").on("input", function() {
                $("#area-val-display").text($(this).val() + " m²");
                renderProperties();
            });

            // Live filter list action
            $("#filter-direction, #filter-type, #search-input").on("change input", function() {
                renderProperties();
            });

            // Reset list search filters
            $("#btn-reset-filters").on("click", function() {
                $("#filter-price-range").val(10000000000);
                $("#price-val-display").text("10 tỷ VNĐ");
                $("#filter-area-range").val(0);
                $("#area-val-display").text("0 m²");
                $("#filter-direction").val("");
                $("#filter-type").val("all");
                $("#search-input").val("");
                showToast("Đã khôi phục bộ lọc tìm kiếm!", "info");
                renderProperties();
            });

            // Live sync inputs to dark summary box
            $("#form-title-input, #form-price-input, input[name='contact_name'], input[name='contact_role']").on("input change", function() {
                syncFormCheckoutPreviews();
            });

            $("#select-province").on("change", function() {
                handleProvinceChange();
            });

            // Layout switching grid vs list
            $("#view-grid-btn").on("click", function() {
                isGridView = true;
                $(this).addClass("text-amber-600 bg-amber-50").removeClass("text-slate-500");
                $("#view-list-btn").addClass("text-slate-500").removeClass("text-amber-600 bg-amber-50");
                renderProperties();
            });

            $("#view-list-btn").on("click", function() {
                isGridView = false;
                $(this).addClass("text-amber-600 bg-amber-50").removeClass("text-slate-500");
                $("#view-grid-btn").addClass("text-slate-500").removeClass("text-amber-600 bg-amber-50");
                renderProperties();
            });

            // Action Triggers inside Wizard form
            $("#btn-reset-form-top, #btn-reset-form-bottom").on("click", function() {
                resetForm();
                showToast("Đã đưa dữ liệu form nhập liệu về trống!", "info");
            });

            // Media files upload simulate trigger
            $("#trigger-upload-area").on("click", function() {
                $("#form-upload-loader").removeClass("hidden");
                setTimeout(() => {
                    $("#form-upload-loader").addClass("hidden");

                    const mockImagesList = [
                        "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1484154218962-a197022b5858?auto=format&fit=crop&w=600&q=80",
                        "https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=600&q=80"
                    ];

                    let randomImg = mockImagesList[Math.floor(Math.random() * mockImagesList.length)];
                    uploadedFormMedia.push(randomImg);
                    uploadedFormMediaCaptions.push(""); // default empty

                    renderFormMediaThumbnails();
                    checkMinPhotoCriteria();
                    showToast("Đã tải hình ảnh thành công!", "success");
                }, 1000);
            });

            // Save Property wizard action
            $("#btn-save-property").on("click", function() {
                let form = document.getElementById("property-form");
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                let id = $("#edit-property-id").val();
                let type = $('input[name="type"]:checked').val();
                let property_type = $('select[name="property_type"]').val();
                let title = $('input[name="title"]').val();
                let description = $('textarea[name="description"]').val();
                let province = $('select[name="province"]').val();
                let district = $('select[name="district"]').val();
                let ward = $('input[name="ward"]').val();
                let street = $('input[name="street"]').val();
                let price = parseFloat($('input[name="price"]').val());
                let area = parseFloat($('input[name="area"]').val());
                let direction = $('select[name="direction"]').val();
                let beds = parseInt($('input[name="beds"]').val());
                let baths = parseInt($('input[name="baths"]').val());
                let legal = $('select[name="legal"]').val();
                
                // New Furniture & Contact fields
                let interior_status = $('select[name="interior_status"]').val();
                let interior_note = $('input[name="interior_note"]').val();
                let interior_items = [];
                $('input[name="interior_items"]:checked').each(function() {
                    interior_items.push($(this).val());
                });

                let contact_name = $('input[name="contact_name"]').val();
                let contact_phone = $('input[name="contact_phone"]').val();
                let contact_address = $('input[name="contact_address"]').val();
                let contact_role = $('input[name="contact_role"]:checked').val() || "Chính chủ";
                let contact_time = $('input[name="contact_time"]').val();

                let captions = [];
                $(".media-caption-input").each(function() {
                    captions.push($(this).val());
                });

                let finalMedia = uploadedFormMedia.length > 0 ? uploadedFormMedia : [
                    "https://images.unsplash.com/photo-1564013799919-ab600027ffc6?auto=format&fit=crop&w=600&q=80"
                ];

                if (id) {
                    // Update list
                    let idx = mockProperties.findIndex(p => p.id === id);
                    if (idx !== -1) {
                        mockProperties[idx] = {
                            ...mockProperties[idx],
                            type, property_type, title, description, province, district, ward, street,
                            price, area, direction, beds, baths, legal,
                            interior_status, interior_note, interior_items,
                            contact_name, contact_phone, contact_address, contact_role, contact_time,
                            media: finalMedia, media_captions: captions
                        };
                        showToast(`Cập nhật thành công thông tin tin đăng ${id}!`, "success");
                    }
                } else {
                    // Save new list BĐS
                    let newId = "BĐS-" + Math.floor(1000 + Math.random() * 9000);
                    let todayString = new Date().toLocaleDateString('vi-VN');
                    
                    let newProperty = {
                        id: newId,
                        type, property_type, title, description, province, district, ward, street,
                        price, area, direction, beds, baths, legal,
                        interior_status, interior_note, interior_items,
                        contact_name, contact_phone, contact_address, contact_role, contact_time,
                        media: finalMedia, media_captions: captions, date_created: todayString
                    };
                    mockProperties.unshift(newProperty);
                    showToast(`Đăng bài viết mới mã ${newId} thành công!`, "success");
                }

                resetForm();
                renderProperties();
                updateDashboardCounters();
                switchView('list'); // Switch tab right back to listings
            });

            // Action Sửa: Đổ ngược dữ liệu vào form mượt mà không dùng modal
            $(document).on("click", ".btn-edit-prop", function() {
                let id = $(this).data("id");
                let prop = mockProperties.find(p => p.id === id);
                if (prop) {
                    resetForm();
                    // Update header title to signify editing state
                    $("#form-header-title").html(`ĐANG CHỈNH SỬA TIN: <span class="text-rose-600 font-extrabold">${prop.id}</span>`);
                    $("#edit-property-id").val(prop.id);
                    
                    $(`input[name="type"][value="${prop.type}"]`).prop("checked", true);
                    $('select[name="property_type"]').val(prop.property_type);
                    $('input[name="title"]').val(prop.title).trigger("input");
                    $('textarea[name="description"]').val(prop.description);
                    
                    $('select[name="province"]').val(prop.province).trigger("change");
                    $('select[name="district"]').val(prop.district);
                    $('input[name="ward"]').val(prop.ward);
                    $('input[name="street"]').val(prop.street);
                    
                    $('input[name="price"]').val(prop.price);
                    $('input[name="area"]').val(prop.area);
                    $('select[name="direction"]').val(prop.direction);
                    $('input[name="beds"]').val(prop.beds);
                    $('input[name="baths"]').val(prop.baths);
                    $('select[name="legal"]').val(prop.legal);
                    
                    // Interior values backfill
                    $('select[name="interior_status"]').val(prop.interior_status || "Nội thất cơ bản");
                    $('input[name="interior_note"]').val(prop.interior_note || "");
                    if (prop.interior_items) {
                        prop.interior_items.forEach(item => {
                            $(`input[name="interior_items"][value="${item}"]`).prop("checked", true);
                        });
                    }

                    // Contact values backfill
                    $('input[name="contact_name"]').val(prop.contact_name || "");
                    $('input[name="contact_phone"]').val(prop.contact_phone || "");
                    $('input[name="contact_address"]').val(prop.contact_address || "");
                    
                    let finalRole = prop.contact_role || "Chính chủ";
                    $(`input[name="contact_role"][value="${finalRole}"]`).prop("checked", true);
                    $('input[name="contact_time"]').val(prop.contact_time || "");

                    uploadedFormMedia = [...prop.media];
                    uploadedFormMediaCaptions = prop.media_captions ? [...prop.media_captions] : [];

                    renderFormMediaThumbnails();
                    checkMinPhotoCriteria();
                    syncFormCheckoutPreviews();

                    showToast(`Đã tải thông tin tin đăng ${prop.id}. Đang chuyển trang...`, "warning");
                    
                    // Delay and route view to form
                    setTimeout(() => {
                        switchView('create');
                    }, 500);
                }
            });

            // Action Xoá tin trực tiếp
            $(document).on("click", ".btn-delete-prop", function() {
                let id = $(this).data("id");
                if (confirm(`Bạn có chắc chắn muốn xóa tin đăng ${id} trực tiếp khỏi hệ thống?`)) {
                    mockProperties = mockProperties.filter(p => p.id !== id);
                    showToast(`Đã xóa tin đăng ${id} khỏi hệ thống!`, "error");
                    renderProperties();
                    updateDashboardCounters();
                }
            });
        });

        // Dropdowns reload on provinces choice
        function handleProvinceChange() {
            let selectedProvince = $("#select-province").val();
            let selectDistrict = $("#select-district");
            selectDistrict.empty();

            if (districtsData[selectedProvince]) {
                districtsData[selectedProvince].forEach(d => {
                    selectDistrict.append(`<option value="${d}">${d}</option>`);
                });
            } else {
                selectDistrict.append(`<option value="Khác">Khu vực Khác</option>`);
            }
        }

        // Checks photos count checklist criteria status
        function checkMinPhotoCriteria() {
            let indicator = $("#status-img-count");
            if (uploadedFormMedia.length >= 3) {
                indicator.removeClass("bg-red-100 text-red-600").addClass("bg-emerald-100 text-emerald-600");
                indicator.html('<i class="fa-solid fa-check"></i>');
            } else {
                indicator.removeClass("bg-emerald-100 text-emerald-600").addClass("bg-red-100 text-red-600");
                indicator.html('<i class="fa-solid fa-xmark"></i>');
            }
        }

        // Load photo preview cards
        function renderFormMediaThumbnails() {
            let container = $("#form-media-list");
            container.empty();
            $("#count-media-txt").text(uploadedFormMedia.length);

            uploadedFormMedia.forEach((src, idx) => {
                let savedCaption = uploadedFormMediaCaptions[idx] || "";
                container.append(`
                    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden p-2 flex flex-col gap-2 relative">
                        <div class="relative aspect-video rounded-lg overflow-hidden bg-slate-100">
                            <img src="${src}" class="w-full h-full object-cover">
                            <button type="button" onclick="deleteFormPhoto(${idx})" class="absolute top-1 right-1 w-5 h-5 bg-red-600 hover:bg-red-700 text-white rounded-full flex items-center justify-center text-[10px] transition-colors shadow shadow-black/20">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            ${idx === 0 ? '<span class="absolute bottom-1 left-1 bg-amber-500 text-slate-950 font-black px-1.5 py-0.5 rounded text-[8px] uppercase">Ảnh chính</span>' : ''}
                        </div>
                        <input type="text" value="${savedCaption}" placeholder="Mô tả cho ảnh..." class="media-caption-input w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[10px] focus:outline-none focus:bg-white focus:ring-1 focus:ring-amber-500" onchange="updateFormCaptionValue(${idx}, this.value)">
                    </div>
                `);
            });
        }

        // Live synchronizer for invoice card summary at form footer
        function syncFormCheckoutPreviews() {
            let title = $("#form-title-input").val();
            let price = parseFloat($("#form-price-input").val()) || 0;
            let contactName = $("input[name='contact_name']").val();
            let contactRole = $("input[name='contact_role']:checked").val() || "Chính chủ";

            $("#checkout-title-display").text(title ? title : "Chưa có tiêu đề tin đăng...");
            $("#checkout-price-display").text(price > 0 ? formatPrice(price) : "0 VNĐ");
            $("#checkout-name-display").text(contactName ? contactName : "Chưa điền");
            $("#checkout-role-display").text(contactRole);
        }

        // Load stat numbers to top dashboard widgets
        function updateDashboardCounters() {
            let total = mockProperties.length;
            let sellCount = mockProperties.filter(p => p.type === "Mua bán").length;
            let rentCount = mockProperties.filter(p => p.type === "Cho thuê").length;

            $("#stat-total").text(total);
            $("#stat-sell").text(sellCount);
            $("#stat-rent").text(rentCount);
            $("#stat-approved").text(total); // Defaults to total in simplified version
            $("#properties-count-txt").text(total);
        }

        function resetForm() {
            $("#property-form")[0].reset();
            $("#edit-property-id").val("");
            uploadedFormMedia = [];
            uploadedFormMediaCaptions = [];
            $("#count-media-txt").text("0");
            $("#form-media-list").empty();
            
            $("#form-header-title").html('<i class="fa-solid fa-circle-plus text-amber-500"></i> TẠO TIN ĐĂNG BẤT ĐỘNG SẢN MỚI');
            
            handleProvinceChange();
            checkMinPhotoCriteria();
            syncFormCheckoutPreviews();
        }

        function formatPrice(num) {
            if (num >= 1000000000) {
                return (num / 1000000000).toFixed(1).replace(".0", "") + " Tỷ VNĐ";
            }
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1).replace(".0", "") + " Triệu VNĐ";
            }
            return num.toLocaleString('vi-VN') + " VNĐ";
        }

        function getInteriorBadgeStyle(status) {
            if (status === "Nội thất cao cấp" || status === "Đầy đủ nội thất") {
                return "bg-amber-100 text-amber-800 ring-1 ring-amber-300";
            }
            return "bg-slate-100 text-slate-700 ring-1 ring-slate-200";
        }

        function showToast(message, type = "success") {
            const container = $("#toast-container");
            const toastId = "toast-" + Math.floor(Math.random() * 1000);
            
            let bgClass = "bg-white border-slate-200 text-slate-800";
            let icon = '<i class="fa-solid fa-circle-info text-blue-500 text-sm"></i>';
            
            if (type === "success") {
                bgClass = "bg-emerald-50 border-emerald-200 text-emerald-900";
                icon = '<i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>';
            } else if (type === "warning") {
                bgClass = "bg-amber-50 border-amber-200 text-amber-900";
                icon = '<i class="fa-solid fa-circle-notch animate-spin text-amber-500 text-sm"></i>';
            } else if (type === "error") {
                bgClass = "bg-rose-50 border-rose-200 text-rose-900";
                icon = '<i class="fa-solid fa-trash-can text-rose-500 text-sm"></i>';
            }

            const toastHTML = `
                <div id="${toastId}" class="flex items-center gap-3 p-3.5 rounded-2xl border shadow-md max-w-xs pointer-events-auto transition-all duration-300 transform translate-x-12 opacity-0 text-xs font-bold ${bgClass}">
                    ${icon}
                    <div class="flex-1">${message}</div>
                    <button onclick="removeToast('${toastId}')" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark"></i></button>
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

        // Property grid layout renderer
        function renderProperties() {
            const container = $("#property-list-container");
            const emptyState = $("#empty-state");
            container.empty();

            let filterType = $("#filter-type").val();
            let maxPrice = parseFloat($("#filter-price-range").val());
            let minArea = parseFloat($("#filter-area-range").val());
            let filterDirection = $("#filter-direction").val();
            let searchKeyword = $("#search-input").val().toLowerCase().trim();

            let filtered = mockProperties.filter(p => {
                if (filterType !== "all" && p.type !== filterType) return false;
                if (p.price > maxPrice) return false;
                if (p.area < minArea) return false;
                if (filterDirection && p.direction !== filterDirection) return false;
                if (searchKeyword) {
                    let text = (p.title + " " + p.description + " " + p.province + " " + p.district + " " + p.id + " " + p.contact_name + " " + p.contact_phone).toLowerCase();
                    if (!text.includes(searchKeyword)) return false;
                }
                return true;
            });

            $("#properties-count-txt").text(filtered.length);

            if (filtered.length === 0) {
                emptyState.removeClass("hidden");
                container.addClass("hidden");
                return;
            } else {
                emptyState.addClass("hidden");
                container.removeClass("hidden");
            }

            if (isGridView) {
                container.removeClass("flex flex-col").addClass("grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5");
                filtered.forEach(p => {
                    let interiorBadge = p.interior_status ? p.interior_status : "Nội thất cơ bản";
                    let badgeClass = getInteriorBadgeStyle(interiorBadge);
                    container.append(`
                        <div class="bg-white rounded-3xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col h-full group">
                            <div class="relative aspect-video w-full overflow-hidden bg-slate-100">
                                <img src="${p.media[0]}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="${p.title}" onerror="this.src='https://via.placeholder.com/600x400'">
                                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 items-start">
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded shadow-sm ${badgeClass}">
                                        <i class="fa-solid fa-couch mr-1"></i>${interiorBadge}
                                    </span>
                                    <span class="px-1.5 py-0.5 text-[8px] font-bold rounded bg-slate-950/80 text-white shadow">
                                        Mã: ${p.id}
                                    </span>
                                </div>
                                <span class="absolute bottom-2.5 right-2.5 bg-amber-500 text-slate-950 font-black px-2 py-0.5 rounded text-[9px] uppercase shadow-sm">
                                    ${p.type}
                                </span>
                            </div>

                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div class="space-y-3">
                                    <div class="text-[9px] font-extrabold text-amber-600 uppercase tracking-wide">
                                        <i class="fa-solid fa-building"></i> ${p.property_type}
                                    </div>
                                    <h4 class="font-bold text-slate-900 text-xs sm:text-sm leading-snug line-clamp-2" title="${p.title}">
                                        ${p.title}
                                    </h4>
                                    
                                    <div class="grid grid-cols-3 gap-1 bg-slate-50 p-2 rounded-xl text-[10px] font-bold text-slate-600 text-center border border-slate-100">
                                        <div>
                                            <p class="text-[9px] font-normal text-slate-400">Diện tích</p>
                                            <p class="text-slate-950 mt-0.5">${p.area} m²</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-normal text-slate-400">P.Ngủ</p>
                                            <p class="text-slate-950 mt-0.5">${p.beds} PN</p>
                                        </div>
                                        <div>
                                            <p class="text-[9px] font-normal text-slate-400">Hướng</p>
                                            <p class="text-slate-950 mt-0.5">${p.direction}</p>
                                        </div>
                                    </div>

                                    <!-- Contact info display on card -->
                                    <div class="bg-slate-50/50 p-2.5 rounded-xl border border-dashed border-slate-200 space-y-1 text-[10px]">
                                        <div class="flex items-center justify-between text-slate-700">
                                            <span class="font-bold"><i class="fa-solid fa-user text-slate-400 mr-1"></i>${p.contact_name || "Chưa rõ"}</span>
                                            <span class="bg-slate-200 px-1.5 py-0.5 rounded text-[8px] uppercase font-black text-slate-600">${p.contact_role || "Chính chủ"}</span>
                                        </div>
                                        <div class="text-slate-500 font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-phone text-slate-400"></i> ${p.contact_phone || "Không có SĐT"}
                                        </div>
                                    </div>

                                    <div class="text-[10px] text-slate-400 flex items-center gap-1">
                                        <i class="fa-solid fa-location-dot"></i>
                                        <span class="truncate">${p.street}, ${p.district}, ${p.province}</span>
                                    </div>
                                </div>

                                <div class="border-t border-slate-100 pt-3 mt-4 flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-black text-rose-600">${formatPrice(p.price)}</span>
                                    </div>
                                    
                                    <div class="flex items-center gap-0.5">
                                        <button data-id="${p.id}" class="btn-edit-prop p-2 text-blue-600 hover:bg-blue-50 hover:text-blue-700 rounded-lg transition-all text-xs font-bold flex items-center gap-1" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen"></i> Sửa
                                        </button>
                                        <button data-id="${p.id}" class="btn-delete-prop p-2 text-rose-600 hover:bg-rose-50 hover:text-rose-700 rounded-lg transition-all text-xs font-bold flex items-center gap-1" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i> Xóa
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });
            } else {
                container.removeClass("grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3").addClass("flex flex-col gap-3");
                filtered.forEach(p => {
                    let interiorBadge = p.interior_status ? p.interior_status : "Nội thất cơ bản";
                    let badgeClass = getInteriorBadgeStyle(interiorBadge);
                    container.append(`
                        <div class="bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-sm p-4 flex gap-4">
                            <div class="relative w-28 h-28 flex-shrink-0 bg-slate-100 rounded-xl overflow-hidden">
                                <img src="${p.media[0]}" class="w-full h-full object-cover" alt="${p.title}">
                                <span class="absolute top-1 left-1 px-1 py-0.5 text-[8px] font-black uppercase rounded ${badgeClass}">
                                    ${interiorBadge}
                                </span>
                            </div>

                            <div class="flex-1 flex flex-col justify-between">
                                <div class="space-y-1">
                                    <div class="flex justify-between items-start gap-2">
                                        <h4 class="font-bold text-slate-900 text-xs sm:text-sm line-clamp-1">
                                            ${p.title}
                                        </h4>
                                        <span class="text-xs sm:text-sm font-black text-rose-600">${formatPrice(p.price)}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 line-clamp-1">
                                        ${p.description}
                                    </p>
                                    <div class="text-[10px] text-slate-500 flex gap-4">
                                        <span><i class="fa-solid fa-ruler-combined"></i> ${p.area} m²</span>
                                        <span><i class="fa-solid fa-user"></i> Liên hệ: <strong>${p.contact_name} (${p.contact_phone})</strong></span>
                                        <span class="text-amber-600 font-bold uppercase">${p.type}</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-[10px] pt-1.5 border-t border-slate-100">
                                    <span class="text-slate-400">Mã bài viết: <strong>${p.id}</strong> • Đăng ngày: <strong>${p.date_created}</strong></span>
                                    <div class="flex gap-1">
                                        <button data-id="${p.id}" class="btn-edit-prop px-3 py-1.5 bg-slate-50 hover:bg-blue-50 text-blue-600 rounded font-bold">Sửa</button>
                                        <button data-id="${p.id}" class="btn-delete-prop px-3 py-1.5 bg-slate-50 hover:bg-rose-50 text-rose-600 rounded font-bold">Xóa</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                });
            }
        }
    </script>
</body>
</html>