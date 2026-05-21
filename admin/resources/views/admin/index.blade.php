@extends('admin.layouts.index')
@section('page_title', 'Dashboard')
@section('content')

<!-- Page Header -->
<div class="dash-page-header">
    <div>
        <h1 class="dash-page-title">Dashboard</h1>
        <div class="dash-page-subtitle">
            <i class="fa fa-calendar-o"></i> Tổng quan nội dung · Hôm nay, {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </div>
    </div>
    <a href="admin/dashboard" class="dash-btn-refresh">
        <i class="fa fa-refresh"></i> Làm mới
    </a>
</div>

<!-- Stats Row -->
<div class="dash-stats-grid">
    <div class="dash-stat-card">
        <div class="dash-stat-top">
            <div class="dash-stat-icon blue"><i class="fa fa-eye"></i></div>
            <span class="dash-stat-badge info">✦ Hôm nay</span>
        </div>
        <div class="dash-stat-label">Lượt truy cập hôm nay</div>
        <div class="dash-stat-value"><span class="counter">128</span></div>
        <div class="dash-stat-trend up"><i class="fa fa-arrow-up"></i> +12% so với hôm qua</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-top">
            <div class="dash-stat-icon teal"><i class="fa fa-bar-chart"></i></div>
            <span class="dash-stat-badge active-badge">✦ Tổng cộng</span>
        </div>
        <div class="dash-stat-label">Tổng lượt truy cập</div>
        <div class="dash-stat-value"><span class="counter">48250</span></div>
        <div class="dash-stat-trend up"><i class="fa fa-arrow-up"></i> +5.3% tháng này</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-top">
            <div class="dash-stat-icon teal"><i class="fa fa-file-text"></i></div>
            <span class="dash-stat-badge up">✦ Đã đăng</span>
        </div>
        <div class="dash-stat-label">Tổng số bài viết</div>
        <div class="dash-stat-value"><span class="counter">{{ $totalPosts ?? 0 }}</span></div>
        <div class="dash-stat-trend neutral"><i class="fa fa-plus"></i> +{{ $newPostsToday ?? 3 }} bài hôm nay</div>
    </div>

    <div class="dash-stat-card">
        <div class="dash-stat-top">
            <div class="dash-stat-icon orange"><i class="fa fa-envelope"></i></div>
            <span class="dash-stat-badge warning">✦ Liên hệ</span>
        </div>
        <div class="dash-stat-label">Tổng liên hệ</div>
        <div class="dash-stat-value"><span class="counter">24</span></div>
        <div class="dash-stat-trend down"><i class="fa fa-circle"></i> 5 chưa đọc</div>
    </div>
</div>

<!-- Charts Row -->
<div class="dash-charts-grid">
    <div class="dash-chart-card">
        <div class="dash-chart-header">
            <span class="dash-chart-title">Lượt truy cập 7 ngày qua</span>
            <span class="dash-chart-badge">Realtime</span>
        </div>
        <div class="dash-chart-body" id="chart-visits"></div>
    </div>
    <div class="dash-chart-card">
        <div class="dash-chart-header">
            <span class="dash-chart-title">Bài viết mới theo tháng</span>
            <span class="dash-chart-badge">6 tháng gần nhất</span>
        </div>
        <div class="dash-chart-body" id="chart-posts"></div>
    </div>
</div>

<!-- Bottom: Top Viewed Posts + Recent Contacts -->
<div class="dash-tables-grid">
    <!-- Top Viewed Posts -->
    <div class="dash-table-card">
        <div class="dash-table-header">
            <h3 class="dash-table-title"><i class="fa fa-fire" style="color:#f59e0b;margin-right:6px;"></i>Bài viết xem nhiều nhất</h3>
            <a href="admin/posts" class="dash-table-link">Xem tất cả</a>
        </div>
        <ul class="dash-post-list">
            <li class="dash-post-item">
                <span class="dash-member-rank rank-1">1</span>
                <div class="dash-post-info">
                    <div class="dash-post-title">Giới thiệu về PTGrow và sứ mệnh phát triển bền vững</div>
                    <div class="dash-post-meta"><i class="fa fa-eye"></i> 1,248 lượt xem · <i class="fa fa-calendar"></i> 10/05/2026</div>
                </div>
                <span class="dash-post-views">1,248</span>
            </li>
            <li class="dash-post-item">
                <span class="dash-member-rank rank-2">2</span>
                <div class="dash-post-info">
                    <div class="dash-post-title">Hướng dẫn sử dụng nền tảng quản lý nội dung</div>
                    <div class="dash-post-meta"><i class="fa fa-eye"></i> 986 lượt xem · <i class="fa fa-calendar"></i> 08/05/2026</div>
                </div>
                <span class="dash-post-views">986</span>
            </li>
            <li class="dash-post-item">
                <span class="dash-member-rank rank-3">3</span>
                <div class="dash-post-info">
                    <div class="dash-post-title">Chính sách bảo mật và điều khoản dịch vụ mới</div>
                    <div class="dash-post-meta"><i class="fa fa-eye"></i> 754 lượt xem · <i class="fa fa-calendar"></i> 06/05/2026</div>
                </div>
                <span class="dash-post-views">754</span>
            </li>
            <li class="dash-post-item">
                <span class="dash-member-rank rank-default">4</span>
                <div class="dash-post-info">
                    <div class="dash-post-title">Top 10 xu hướng công nghệ năm 2026</div>
                    <div class="dash-post-meta"><i class="fa fa-eye"></i> 612 lượt xem · <i class="fa fa-calendar"></i> 04/05/2026</div>
                </div>
                <span class="dash-post-views">612</span>
            </li>
            <li class="dash-post-item">
                <span class="dash-member-rank rank-default">5</span>
                <div class="dash-post-info">
                    <div class="dash-post-title">Cập nhật tính năng mới tháng 5/2026</div>
                    <div class="dash-post-meta"><i class="fa fa-eye"></i> 489 lượt xem · <i class="fa fa-calendar"></i> 02/05/2026</div>
                </div>
                <span class="dash-post-views">489</span>
            </li>
        </ul>
    </div>

    <!-- Recent Contacts -->
    <div class="dash-table-card">
        <div class="dash-table-header">
            <h3 class="dash-table-title"><i class="fa fa-envelope-o" style="color:#3b82f6;margin-right:6px;"></i>Liên hệ gần đây</h3>
            <a href="admin/contacts" class="dash-table-link">Xem tất cả</a>
        </div>
        <ul class="dash-contact-list">
            <li class="dash-contact-item unread">
                <div class="dash-contact-avatar">N</div>
                <div class="dash-contact-info">
                    <div class="dash-contact-name">Nguyễn Văn An <span class="dash-contact-badge new">Mới</span></div>
                    <div class="dash-contact-msg">Tôi muốn hỏi về dịch vụ đăng tin bất động sản...</div>
                    <div class="dash-contact-time"><i class="fa fa-clock-o"></i> 30 phút trước</div>
                </div>
            </li>
            <li class="dash-contact-item unread">
                <div class="dash-contact-avatar" style="background:#ec4899;">T</div>
                <div class="dash-contact-info">
                    <div class="dash-contact-name">Trần Thị Bích <span class="dash-contact-badge new">Mới</span></div>
                    <div class="dash-contact-msg">Tôi gặp lỗi khi đăng ký tài khoản, cần hỗ trợ...</div>
                    <div class="dash-contact-time"><i class="fa fa-clock-o"></i> 2 giờ trước</div>
                </div>
            </li>
            <li class="dash-contact-item">
                <div class="dash-contact-avatar" style="background:#10b981;">L</div>
                <div class="dash-contact-info">
                    <div class="dash-contact-name">Lê Minh Hoàng</div>
                    <div class="dash-contact-msg">Hợp tác truyền thông, quảng cáo banner trên website...</div>
                    <div class="dash-contact-time"><i class="fa fa-clock-o"></i> 5 giờ trước</div>
                </div>
            </li>
            <li class="dash-contact-item">
                <div class="dash-contact-avatar" style="background:#8b5cf6;">P</div>
                <div class="dash-contact-info">
                    <div class="dash-contact-name">Phạm Quốc Tuấn</div>
                    <div class="dash-contact-msg">Báo lỗi hiển thị bài viết trên thiết bị di động...</div>
                    <div class="dash-contact-time"><i class="fa fa-clock-o"></i> Hôm qua</div>
                </div>
            </li>
            <li class="dash-contact-item">
                <div class="dash-contact-avatar" style="background:#f59e0b;">H</div>
                <div class="dash-contact-info">
                    <div class="dash-contact-name">Hoàng Thị Mai</div>
                    <div class="dash-contact-msg">Xin chào, tôi muốn đăng quảng cáo trên website...</div>
                    <div class="dash-contact-time"><i class="fa fa-clock-o"></i> 2 ngày trước</div>
                </div>
            </li>
        </ul>
    </div>
</div>

@endsection
@section('script')
<script>
    $(document).ready(function() {
        if (typeof $.fn.counterUp !== 'undefined') {
            $('.counter').counterUp({
                delay: 100,
                time: 1200
            });
        }
    });
</script>
@endsection