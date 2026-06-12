<style>
    .modal-overlay {
        top: 0 !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }
</style>
<div class="header-left">
    <button type="button" class="header-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
        <i class="fa fa-bars"></i>
    </button>
    <div class="header-breadcrumb">
        <span class="header-breadcrumb-title">@yield('page_title', 'Dashboard')</span>
        <span class="header-breadcrumb-sep">›</span>
        <span class="header-breadcrumb-sub">{{ get_option('name_company') ?: 'Admin Panel' }}</span>
    </div>
</div>

<div class="header-right">

    <!-- Notification -->
    <div class="dropdown keep-inside-clicks-open" style="position:relative;">
        <input type="hidden" name="next_noti" class="next_noti" value="">
        <button class="header-icon-btn dropdown-toggle clickNoti" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-bell-o"></i>
            @if(countNotiNotRead() > 0)
            <span class="badge-dot"></span>
            @endif
        </button>
        <span class="noti-count-badge" data-count="{{countNotiNotRead()}}" style="display:none;">{{countNotiNotRead()}}</span>
        <ul class="dropdown-menu dropdown-menu-lg" style="right:0;left:auto;min-width:360px;border-radius:12px;border:1px solid #e5e7eb;box-shadow:0 8px 24px rgba(0,0,0,0.1);margin-top:8px;">
            <li class="notifi-title" style="display:flex;flex-flow:row-reverse;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #f3f4f6;font-weight:600;font-size:15px;">
                <span style="cursor:pointer" onclick="readAllNoti(this)"><img src="admin/assets/images/noti-check.svg" style="width:30px;"></span>
                Thông báo
            </li>
            <li class="list-group slimscroll-noti notification-list" style="max-height:360px;overflow-y:auto;">
                <div class="div-data-noti"></div>
            </li>
        </ul>
    </div>

    @if(Auth::guard('admin')->check())
    <div class="dropdown">
        <a href="" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
            <img class="header-avatar" src="{{ !empty(Auth::guard('admin')->user()->image) ? asset('storage/'.Auth::guard('admin')->user()->image) : 'admin/assets/images/users/avatar-1.jpg' }}" alt="Avatar">
        </a>
        <ul class="dropdown-menu" style="right:0;left:auto;border-radius:10px;border:1px solid #e5e7eb;box-shadow:0 8px 24px rgba(0,0,0,0.1);margin-top:8px;">
            <li><a href="javascript:void(0)"><i class="ti-user text-custom m-r-10"></i> {{ Auth::guard('admin')->user()->name }}</a></li>
            <li id="profile" data-id=""><a href="admin/user/profile/{{Auth::guard('admin')->user()->id}}" class="dt-modal"><i class="ti-user text-custom m-r-10"></i> Profile</a></li>
            <li class="divider"></li>
            <li><a href="admin/logout"><i class="ti-power-off text-danger m-r-10"></i> Logout</a></li>
        </ul>
    </div>
    @endif
</div>