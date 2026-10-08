<!-- Start Header -->
<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="{{ route('dashboard') }}" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt="" height="17">
                        </span>
                    </a>

                    <a href="{{ route('dashboard') }}" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{ asset('assets/images/logo-light.png') }}" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>
            </div>

            <div class="d-flex align-items-center">

                <div class="ms-1 header-item d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" data-toggle="fullscreen">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="ms-1 header-item d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle light-dark-mode">
                        <i class='bx bx-moon fs-22'></i>
                    </button>
                </div>

                {{-- Notification bell: the list is filled from "my-notification.summary" by layout/includes/notification-bell-script.blade.php --}}
                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">

                    <!-- Notification Bell -->
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-bell fs-22"></i>
                        <!-- Unread Count -->
                        <span id="notification-count" class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger d-none">0</span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown" style="width: 480px; max-width: 95vw;">

                        <!-- Header -->
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="ps-3 pt-2 pb-2">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">Notifications</h6>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-light text-body fs-13 d-none"><span id="notification-header-count">0</span> New</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification List (scrolls after about 8 notifications) -->
                        <div id="notification-list" class="overflow-y-auto" style="max-height: 480px;"></div>

                        <!-- Empty State -->
                        <div id="notification-empty" class="text-center py-5">
                            <div class="avatar-md mx-auto mb-3">
                                <div class="avatar-title bg-light text-primary rounded-circle fs-24">
                                    <i class="bx bx-bell-off"></i>
                                </div>
                            </div>
                            <h6 class="mb-1">No Notifications</h6>
                            <p class="text-muted mb-0 fs-13">You're all caught up!</p>
                        </div>

                        <!-- Fixed Footer -->
                        <div class="border-top p-2 text-center">
                            <a href="{{ route('my-notification.index') }}" class="btn btn-sm btn-soft-success waves-effect waves-light w-100">
                                See All Notifications
                                <i class="ri-arrow-right-line align-middle ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">
                    <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <img class="rounded-circle header-profile-user" src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- Start Dropdown Items -->
                        <a class="dropdown-item" href="{{ route('profile') }}"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Notifications</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Change Password</span></a>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-history text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Login History</span></a>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-file-document-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Activity Logs</span></a>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Account Settings</span></a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock Screen</span></a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle" data-key="t-logout">{{ __('Logout') }}</span></button>
                        </form>
                        <!-- End Dropdown Items -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- End Header -->
