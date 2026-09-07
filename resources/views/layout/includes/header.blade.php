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

{{--                TODO: --}}
{{--                1. After 8 Notification Records, Section Inside Content will be Scrollable--}}
{{--                2. Not Mobile Responsive, should be Mobile Responsive--}}
{{--                3. No CSS is Expected--}}
{{--                4. On Click "Mark as Read", Will be create new Record (logs) in Database (notification_id, user_id, is_read, ready_at). Ajax Route Call - Future Scope - We will work on it later on.--}}
{{--                5. After "Mark as Read", Title will be Normal (Not Bold). Update "Unread Notification Count".--}}
{{--                6. On Click "Delete", Will be create new Record (logs) in Database (notification_id, user_id, is_deleted, deleted_at). Ajax Route Call - Future Scope - We will work on it later on.--}}
{{--                7. After "Delete", Notification will be vanish (DOM Delete), No Page Refresh/Reload. Update "Unread Notification Count" - if it is "Unread".--}}
{{--                8. "See All Notifications" will be link to a Notification List Page--}}
{{--                Notes: Velzon Admin Template (Bootstrap) is Using--}}

                <div class="dropdown topbar-head-dropdown ms-1 header-item" id="notificationDropdown">

                    <!-- Notification Bell -->
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" id="page-header-notifications-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-bell fs-22"></i>
                        <!-- Unread Count -->
                        <span id="notification-count" class="position-absolute topbar-badge fs-10 translate-middle badge rounded-pill bg-danger">3</span>
                    </button>

                    <!-- Notification Dropdown -->
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-notifications-dropdown" style="width: 480px;">

                        <!-- Header -->
                        <div class="dropdown-head bg-primary bg-pattern rounded-top">
                            <div class="p-3">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h6 class="m-0 fs-16 fw-semibold text-white">Notifications</h6>
                                    </div>
                                    <div class="col-auto">
                                        <span class="badge bg-light text-body fs-13"><span id="notification-header-count">3</span> New</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notification List -->
                        <div id="notification-list">

                            <!-- Notification 1 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative notification-unread" data-notification-id="1">
                                <div class="d-flex align-items-start">
                                    <!-- Icon -->
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-16"><i class="bx bx-badge-check"></i></span>
                                    </div>
                                    <!-- Content -->
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Your Elite author reward is ready!</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">30 minutes ago</p>
                                    </div>
                                    <!-- Actions -->
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" data-id="1" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <!-- Delete -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="1" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>

                                    </div>

                                </div>
                            </div>

                            <!-- Notification 2 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative notification-unread" data-notification-id="2">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-16"><i class="bx bx-message-square-dots"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Angela Bernier replied to your comment.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 hours ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" data-id="2" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="2" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 3 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative notification-unread" data-notification-id="3">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-16"><i class="bx bx-message-square-dots"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">You have received 20 new messages.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">1 day ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" data-id="3" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="3" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 4 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notification-id="4">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16"><i class="bx bx-check-circle"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Invoice #12501 has been approved.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 days ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read hidden for already-read notification -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read d-none" data-id="4" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="4" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 5 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notification-id="5">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16"><i class="bx bx-check-circle"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Invoice #12501 has been approved.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 days ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read hidden for already-read notification -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read d-none" data-id="4" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="4" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 6 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notification-id="6">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16"><i class="bx bx-check-circle"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Invoice #12501 has been approved.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 days ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read hidden for already-read notification -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read d-none" data-id="4" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="4" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 7 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative notification-unread" data-notification-id="7">
                                <div class="d-flex align-items-start">
                                    <!-- Icon -->
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle text-info rounded-circle fs-16"><i class="bx bx-badge-check"></i></span>
                                    </div>
                                    <!-- Content -->
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Your Elite author reward is ready!</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">30 minutes ago</p>
                                    </div>
                                    <!-- Actions -->
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" data-id="1" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <!-- Delete -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="1" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>

                                    </div>

                                </div>
                            </div>

                            <!-- Notification 8 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative notification-unread" data-notification-id="8">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle text-warning rounded-circle fs-16"><i class="bx bx-message-square-dots"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Angela Bernier replied to your comment.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 hours ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" data-id="2" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="2" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 9 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative notification-unread" data-notification-id="9">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-danger-subtle text-danger rounded-circle fs-16"><i class="bx bx-message-square-dots"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">You have received 20 new messages.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">1 day ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" data-id="3" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="3" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 10 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notification-id="10">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16"><i class="bx bx-check-circle"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Invoice #12501 has been approved.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 days ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read hidden for already-read notification -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read d-none" data-id="4" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="4" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 11 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notification-id="11">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16"><i class="bx bx-check-circle"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Invoice #12501 has been approved.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 days ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read hidden for already-read notification -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read d-none" data-id="4" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="4" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Notification 12 -->
                            <div class="text-reset notification-item d-block dropdown-item position-relative" data-notification-id="12">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-xs me-3 flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle text-success rounded-circle fs-16"><i class="bx bx-check-circle"></i></span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mt-0 mb-1 fs-13 fw-semibold">Invoice #12501 has been approved.</h6>
                                        <p class="mb-0 fs-11 fw-medium text-muted">2 days ago</p>
                                    </div>
                                    <div class="d-flex align-items-center ms-2 gap-2">
                                        <!-- Mark as Read hidden for already-read notification -->
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read d-none" data-id="4" title="Mark as read">
                                            <i class="bx bx-check fs-16"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" data-id="4" title="Delete">
                                            <i class="bx bx-trash fs-16"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div id="notification-empty" class="text-center py-5 d-none">
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
{{--                            <a href="{{ route('notifications.index') }}"--}}
                            <a href="#"  class="btn btn-soft-success waves-effect waves-light w-100">
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
