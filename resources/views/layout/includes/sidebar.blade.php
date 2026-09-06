<!-- Start Left Sidebar Menu -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Start Dark Logo -->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm"><img src="{{ asset('assets/images/logo/build_stock_light_sm.jpeg') }}" alt="" height="55"></span>
            {{--TODO: Have Change SM Logo--}}
            <span class="logo-lg"><img src="{{ asset('assets/images/logo/build_stock_light.png') }}" alt="" height="35"></span>
        </a>
        <!-- End Dark Logo -->

        <!-- Start Light Logo -->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm"><img src="{{ asset('assets/images/logo/build_stock_dark_sm.jpeg') }}" alt="" height="55"></span>
            {{--TODO: Have Change SM Logo--}}
            <span class="logo-lg"><img src="{{ asset('assets/images/logo/build_stock_dark.png') }}" alt="" height="35"></span>
        </a>
        <!-- End Light Logo -->
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    <div id="scrollbar">
        <!-- Start Sidebar -->
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="nav-item {{ request()->routeIs('dashboard') ? 'active-menu' : '' }}">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="ri-apps-2-line"></i><span>{{ __('Dashboards') }}</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('brand.*') ? 'active-menu' : '' }}">
                    <a class="nav-link" href="{{ route('brand.index') }}">
                        <i class="ri-apps-2-line"></i><span>{{ __('Manage Brands') }}</span>
                    </a>
                </li>

                <div class="divider"></div>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#manageAccess" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('permission.*', 'role.*', 'user.*') ? 'true' : 'false' }}" aria-controls="manageAccess">
                        <i class="ri-apps-2-line"></i><span>{{ __('Manage Access') }}</span>
                    </a>

                    <div class="collapse menu-dropdown {{ request()->routeIs('permission.*', 'role.*', 'user.*') ? 'show' : '' }}" id="manageAccess">
                        <ul class="nav nav-sm flex-column">
                            <div class="divider"></div>
                            <li class="nav-item {{ request()->routeIs('permission.*') ? 'active-menu' : '' }}">
                                <a href="{{ route('permission.index') }}" class="nav-link"><i class="ri-lock-line"></i> {{ __('Manage Permission') }}</a>
                            </li>
                            <div class="divider"></div>
                            <li class="nav-item {{ request()->routeIs('role.*') ? 'active-menu' : '' }}">
                                <a href="{{ route('role.index') }}" class="nav-link"><i class="ri-shield-user-line"></i> {{ __('Manage Roles') }}</a>
                            </li>
                            <div class="divider"></div>
                            <li class="nav-item {{ request()->routeIs('user.*') ? 'active-menu' : '' }}">
                                <a href="{{ route('user.index') }}" class="nav-link"><i class="ri-team-line"></i> {{ __('Manage Users') }}</a>
                            </li>
                        </ul>
                    </div>
                </li>
                <div class="divider"></div>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#manageGeoLocation" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs(['division.*', 'district.*', 'thana.*']) ? 'true' : 'false' }}" aria-controls="manageGeoLocation">
                        <i class="ri-apps-2-line"></i><span>{{ __('Manage GeoLocation') }}</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('division.*') || request()->routeIs('district.*') || request()->routeIs('thana.*') ? 'show' : '' }}" id="manageGeoLocation">
                        <ul class="nav nav-sm flex-column">
                            <div class="divider"></div>
                            <li class="nav-item {{ request()->routeIs('division.*') ? 'active-menu' : '' }}"><a href="{{ route('division.index') }}" class="nav-link"><i class="ri-apps-2-line"></i> {{ __('Manage Divisions') }} </a></li>
                            <div class="divider"></div>
                            <li class="nav-item {{ request()->routeIs('district.*') ? 'active-menu' : '' }}"><a href="{{ route('district.index') }}" class="nav-link"><i class="ri-apps-2-line"></i> {{ __('Manage Districts') }} </a></li>
                            <div class="divider"></div>
                            <li class="nav-item {{ request()->routeIs('thana.*') ? 'active-menu' : '' }}"><a href="{{ route('thana.index') }}" class="nav-link"><i class="ri-apps-2-line"></i> {{ __('Manage Upazilas') }} </a></li>
                        </ul>
                    </div>
                </li>
                <div class="divider"></div>

                <li class="menu-title"><span data-key="t-menu" style="font-size: 14px;">History & Logs</span></li>
                <div class="divider"></div>

                <li class="nav-item {{ request()->routeIs('login-history') ? 'active-menu' : '' }}">
                    <a class="nav-link" href="{{ route('login-history') }}">
                        <i class="ri-apps-2-line"></i><span>{{ __('Login History') }}</span>
                    </a>
                </li>
                <div class="divider"></div>

                <li class="nav-item {{ request()->routeIs('audit-log') ? 'active-menu' : '' }}">
                    <a class="nav-link" href="{{ route('audit-log') }}">
                        <i class="ri-apps-2-line"></i><span>{{ __('User Activities') }}</span>
                    </a>
                </li>
                <div class="divider"></div>

                <li class="nav-item {{ request()->is('log-viewer.*') ? 'active-menu' : '' }}">
                    <a class="nav-link" href="{{ route('log-viewer.dashboard') }}" target="_blank">
                        <i class="ri-apps-2-line"></i><span>{{ __('Application Logs') }}</span>
                    </a>
                </li>
                <div class="divider"></div>
            </ul>
        </div>
        <!-- End Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
<!-- End Left Sidebar Menu -->
