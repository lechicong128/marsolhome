<div id="navigation">
    <!-- Navigation Menu-->
    @php
        $menuHelper = menuHelper();
    @endphp
    <ul class="navigation-menu">
        @if(!empty($menuHelper))
            @foreach($menuHelper as $menu)
                @php
                    $checkPermissonParent = has_permission_parent_all(!empty($menu['child']) ? $menu['child'] : [$menu]);
                @endphp
                @if(!empty($checkPermissonParent))
                    <li class="has-submenu text-capitalize">
                        @if($menu['id'] == 'manager_payment')
                            @if(getCountRequestMoney() > 0)
                                <span class="badge-danger badge badge_manager_payment">{{getCountRequestMoney()}}</span>
                            @endif
                        @endif
                        <a {{ !empty($menu['link']) ? 'href='.$menu['link'].'' : ''  }} style="cursor: pointer">
                            <div class="text-center"><img style="{{$menu['id'] == 'find_driver' ? 'width:30px;height:30px' : ''}}" src="{{ !empty($menu['image']) ? $menu['image'] : ''  }}" class="{{ !empty($menu['class']) ? $menu['class'] : ''  }} img-icon-menu"></div>
                            <div style="{{$menu['id'] == 'find_driver' ? 'color:#46b0b9' : ''}}" class="text-center mtop2">{{ !empty($menu['name']) ? $menu['name'] : ''  }}</div>
                        </a>
                        @if(!empty($menu['child']))
                            <ul class="submenu">
                            @foreach($menu['child'] as $menuChild)
                                @php
                                    $checkPermissonParentChild = has_permission_parent($menuChild['id']);
                                @endphp
                                @if(!empty($checkPermissonParentChild))
                                <li>
                                    @if($menuChild['id'] == 'withdraw_money')
                                        @if(getCountRequestMoney() > 0)
                                            <p class="badge-danger badge badge_manager_payment">{{getCountRequestMoney()}}</p>
                                        @endif
                                    @endif
                                    <a href="{{ !empty($menuChild['link']) ? $menuChild['link'] : '#'  }}"><i class="fa fa-circle"></i> {{ !empty($menuChild['name']) ? $menuChild['name'] : ''  }}</a></li>
                                @endif
                            @endforeach
                            </ul>
                        @endif
                    </li>
                @endif
            @endforeach
        @endif
    </ul>
    <!-- End navigation menu        -->
</div>
