@php $menuHelper = menuHelper(); @endphp

<div class="sidebar-logo" style="display: flex; justify-content: center; align-items: center; padding: 15px 10px;">
    <a href="{{ url('admin/dashboard') }}" style="display: block; width: 100%; text-align: center;">
        <img src="{{ get_option('logo') ? get_option('logo') : asset('images/default-logo.png') }}" alt="Logo" onerror="this.onerror=null;this.src='{{ asset('images/default-logo.png') }}';" style="width: 140px; max-width: 100%; height: auto; object-fit: contain; margin: 0 auto;">
    </a>
</div>

<nav class="sidebar-nav">
    <ul class="sidebar-nav-list">
        @if(!empty($menuHelper))
        @foreach($menuHelper as $menu)
            @php
                $isActive = false;
                $currentUrl = request()->path();
                $menuLink = !empty($menu['link']) ? trim($menu['link'], '/') : '';
                
                if (!empty($menuLink) && (request()->is($menuLink) || request()->is($menuLink . '/*'))) {
                    $isActive = true;
                }
                
                // Check if any child is active
                if (!$isActive && !empty($menu['child'])) {
                    foreach ($menu['child'] as $child) {
                        $childLink = !empty($child['link']) ? trim($child['link'], '/') : '';
                        if (!empty($childLink) && (request()->is($childLink) || request()->is($childLink . '/*'))) {
                            $isActive = true;
                            break;
                        }
                    }
                }
            @endphp
            <li class="sidebar-nav-item {{ !empty($menu['child']) ? 'has-children' : '' }} {{ $isActive ? 'open' : '' }}">
                @if(!empty($menu['child']))
                    <a class="sidebar-nav-link {{ $isActive ? 'active' : '' }}" href="javascript:void(0);">
                        <span class="sidebar-nav-icon">
                            <i class="{{ !empty($menu['image']) ? $menu['image'] : 'ti-folder' }}" style="font-size: 16px;"></i>
                        </span>
                        {{ $menu['name'] ?? '' }}
                        @if($menu['id'] == 'manager_payment' && getCountRequestMoney() > 0)
                            <span class="sidebar-nav-badge">{{ getCountRequestMoney() }}</span>
                        @endif
                        <span class="sidebar-arrow">&#9654;</span>
                    </a>
                    <ul class="sidebar-submenu">
                        @foreach($menu['child'] as $child)
                            @php
                                $childActive = false;
                                $cLink = !empty($child['link']) ? trim($child['link'], '/') : '';
                                if (!empty($cLink) && (request()->is($cLink) || request()->is($cLink . '/*'))) {
                                    $childActive = true;
                                }
                            @endphp
                            <li>
                                <a href="{{ $child['link'] ?? '#' }}" class="{{ $childActive ? 'active' : '' }}" style="{{ $childActive ? 'color: var(--accent-blue); font-weight: 600;' : '' }}">
                                    {{ $child['name'] ?? '' }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <a class="sidebar-nav-link {{ $isActive ? 'active' : '' }}" href="{{ $menu['link'] ?? '#' }}">
                        <span class="sidebar-nav-icon">
                            <i class="{{ !empty($menu['image']) ? $menu['image'] : 'ti-file' }}" style="font-size: 16px;"></i>
                        </span>
                        {{ $menu['name'] ?? '' }}
                    </a>
                @endif
            </li>
        @endforeach
        @endif
    </ul>
</nav>

<div class="sidebar-footer">
    @if(Auth::guard('admin')->check())
    <div class="sidebar-user">
        <img class="sidebar-user-avatar" src="{{ !empty(Auth::guard('admin')->user()->image) ? asset('storage/'.Auth::guard('admin')->user()->image) : 'admin/assets/images/users/avatar-1.jpg' }}" alt="Avatar">
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ Auth::guard('admin')->user()->name }}</div>
            <div class="sidebar-user-role">Quản trị viên</div>
        </div>
    </div>
    <a href="admin/logout" class="sidebar-logout">
        <i class="fa fa-sign-out"></i> Đăng xuất
    </a>
    @endif
</div>