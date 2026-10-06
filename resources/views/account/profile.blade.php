@extends('layout.master')

@section('title', __('Profile'))

@push('styles')
    <style>
        /* avatar-xl is Velzon's shared 120x120 avatar size, used elsewhere in
           the theme — sized up just for this profile photo instead of
           changing the shared utility. */
        .profile-photo-lg {
            width: 280px;
            height: 280px;
            object-fit: cover;
        }

        /* Mobile-View Tab Icons — .nav-link's inherited font-size
           (13px) is too small for an icon-only tab target. */
        .profile-tab-icon {
            font-size: 18px;
        }
    </style>
@endpush

@section('content')

    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->

            <div class="row pt-xxl-5">
                <div class="col-xxl-9 order-2 order-xxl-1">
                    <div class="card mt-xxl-n5">
                        <div class="card-header">
                            <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#profileDetails" role="tab" title="{{ __('Profile Details') }}">
                                        <i class="ri-user-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Profile Details') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#rolesPermissions" role="tab" title="{{ __('Roles & Permissions') }}">
                                        <i class="ri-shield-user-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Roles & Permissions') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#notification" role="tab" title="{{ __('Notification') }}">
                                        <i class="ri-notification-3-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Notification') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab" title="{{ __('Change Password') }}">
                                        <i class="ri-key-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Change Password') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#loginHistory" role="tab" title="{{ __('Login History') }}">
                                        <i class="ri-login-circle-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Login History') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#activityLogs" role="tab" title="{{ __('Activity Logs') }}">
                                        <i class="ri-file-list-3-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Activity Logs') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content">
                                <div class="tab-pane active" id="profileDetails" role="tabpanel">
                                    <div class="mb-3 border-bottom">
                                        <div class="float-end">
                                            <a href="javascript:void(0);" class="link-primary">{{ __('Update Information') }}</a>
                                        </div>
                                        <h5 class="card-title" style="font-size: 14px; font-weight: bold; font-style: italic;">
                                            {{ __('Personal Details') }}
                                        </h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <tbody>
                                            @php $profileUser = auth()->user(); @endphp
                                            <tr><th scope="row" style="width: 200px;">{{ __('Name') }}</th><td>{{ $profileUser->name }}</td></tr>
                                            <tr><th scope="row">{{ __('Email') }}</th><td>{{ $profileUser->email ?: '-' }}</td></tr>
                                            <tr><th scope="row">{{ __('Mobile') }}</th><td>{{ $profileUser->mobile ?: '-' }}</td></tr>
                                            <tr><th scope="row">{{ __('Registered At') }}</th><td>{{ $profileUser->created_at?->format('Y-m-d') ?? '-' }}</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="rolesPermissions" role="tabpanel">
                                    {{-- TODO: LoggedIn User's Assigned Roles and Permissions. Task 4 --}}
                                    @php
                                        $authUser = auth()->user();
                                        $authRoles = $authUser->roles;
                                        $authPermissionsViaRoles = $authUser->getPermissionsViaRoles();
                                        $authDirectPermissions = $authUser->permissions;
                                    @endphp

                                    <div class="mb-4">
                                        <strong class="fw-bold border-bottom border-primary border-1 d-inline-block mb-2">{{ __('Assigned Roles') }}</strong>
                                        @if(count($authRoles) > 0)
                                            <div class="row flex-wrap">
                                                @foreach($authRoles as $role)
                                                    <div class="col-md-4 col-12 mb-2">
                                                        <span class="badge badge-label bg-success text-start">
                                                            <i class="mdi mdi-circle-medium"></i> {{ ucwords($role->name) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">{{ __('No Assigned Role Found') }}</p>
                                        @endif
                                    </div>

                                    <div class="mb-4">
                                        <strong class="fw-bold border-bottom border-primary border-1 d-inline-block mb-2">{{ __('Assigned Permissions (Via Roles)') }}</strong>
                                        @if(count($authPermissionsViaRoles) > 0)
                                            <div class="row flex-wrap">
                                                @foreach($authPermissionsViaRoles as $permission)
                                                    <div class="col-md-4 col-12 mb-2">
                                                        <span class="badge badge-label bg-success text-start">
                                                            <i class="mdi mdi-circle-medium"></i> {{ ucwords($permission->description ?? $permission->name) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">{{ __('No Permission Found via Roles') }}</p>
                                        @endif
                                    </div>

                                    <div>
                                        <strong class="fw-bold border-bottom border-primary border-1 d-inline-block mb-2">{{ __('Assigned Direct Permissions') }}</strong>
                                        @if(count($authDirectPermissions) > 0)
                                            <div class="row flex-wrap">
                                                @foreach($authDirectPermissions as $permission)
                                                    <div class="col-md-4 col-12 mb-2">
                                                        <span class="badge badge-label bg-info text-start">
                                                            <i class="mdi mdi-circle-medium"></i> {{ ucwords($permission->description ?? $permission->name) }}
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted mb-0">{{ __('No Assigned Direct Permission Found') }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="tab-pane" id="notification" role="tabpanel">
                                    {{-- TODO: Design Only (Static Sample Data). Replace $sampleNotifications with the LoggedIn User's Notifications, then wire "Mark as Read" / "Delete" / "Mark All as Read" / Filters / Paginator. Markup mirrors the Header Notification Dropdown (layout/includes/header.blade.php) and reuses its class hooks (.notification-item, .notification-unread, .mark-notification-read, .delete-notification, data-id). --}}
                                    @php
                                        $sampleNotifications = [
                                            ['id' => 1, 'unread' => true, 'color' => 'info', 'icon' => 'bx-badge-check', 'title' => 'Your Elite author reward is ready!', 'body' => 'Claim your reward before it expires at the end of this month.', 'time' => '30 minutes ago'],
                                            ['id' => 2, 'unread' => true, 'color' => 'warning', 'icon' => 'bx-message-square-dots', 'title' => 'Angela Bernier replied to your comment.', 'body' => 'Thanks for the update, I will review the purchase order today.', 'time' => '2 hours ago'],
                                            ['id' => 3, 'unread' => true, 'color' => 'danger', 'icon' => 'bx-message-square-dots', 'title' => 'You have received 20 new messages.', 'body' => 'Open the inbox to read and reply to your latest messages.', 'time' => '1 day ago'],
                                            ['id' => 4, 'unread' => false, 'color' => 'success', 'icon' => 'bx-check-circle', 'title' => 'Invoice #12501 has been approved.', 'body' => 'The invoice was approved by the Finance Manager.', 'time' => '2 days ago'],
                                            ['id' => 5, 'unread' => false, 'color' => 'success', 'icon' => 'bx-check-circle', 'title' => 'Invoice #12498 has been approved.', 'body' => 'The invoice was approved by the Finance Manager.', 'time' => '3 days ago'],
                                        ];
                                        $sampleUnreadCount = collect($sampleNotifications)->where('unread', true)->count();
                                    @endphp

                                    <div class="mb-3 border-bottom pb-2">
                                        <div class="float-end">
                                            <a href="javascript:void(0);" class="link-primary" id="markAllNotificationsRead">{{ __('Mark All as Read') }}</a>
                                        </div>
                                        <h5 class="card-title" style="font-size: 14px; font-weight: bold; font-style: italic;">
                                            {{ __('Notifications') }}
                                            <span class="badge bg-danger-subtle text-danger ms-1" id="profile-notification-unread-count">{{ $sampleUnreadCount }} {{ __('Unread') }}</span>
                                        </h5>
                                    </div>

                                    <!-- Filters (design only) -->
                                    <ul class="nav nav-pills nav-sm mb-3 gap-1" id="notificationFilters">
                                        <li class="nav-item"><a class="nav-link active py-1" href="javascript:void(0);" data-filter="all">{{ __('All') }}</a></li>
                                        <li class="nav-item"><a class="nav-link py-1" href="javascript:void(0);" data-filter="unread">{{ __('Unread') }}</a></li>
                                        <li class="nav-item"><a class="nav-link py-1" href="javascript:void(0);" data-filter="read">{{ __('Read') }}</a></li>
                                    </ul>

                                    <!-- Notification List -->
                                    <div class="list-group list-group-flush border border-secondary border-opacity-50 rounded" id="profile-notification-list">
                                        @forelse($sampleNotifications as $notification)
                                            <div class="list-group-item border-secondary border-opacity-50 notification-item {{ $notification['unread'] ? 'notification-unread bg-primary-subtle' : '' }}" data-notification-id="{{ $notification['id'] }}">
                                                <div class="d-flex align-items-center">
                                                    <!-- Icon -->
                                                    <div class="avatar-xs me-3 flex-shrink-0">
                                                        <span class="avatar-title bg-{{ $notification['color'] }}-subtle text-{{ $notification['color'] }} rounded-circle fs-16"><i class="bx {{ $notification['icon'] }}"></i></span>
                                                    </div>
                                                    <!-- Content -->
                                                    <div class="flex-grow-1 min-w-0">
                                                        <h6 class="mt-0 mb-1 fs-13 {{ $notification['unread'] ? 'fw-bold' : 'fw-normal' }}">
                                                            {{ $notification['title'] }}
                                                            @if($notification['unread'])
                                                                <span class="badge bg-danger ms-1 align-middle">{{ __('New') }}</span>
                                                            @endif
                                                        </h6>
                                                        <p class="mb-1 fs-12 text-muted text-truncate">{{ $notification['body'] }}</p>
                                                        <p class="mb-0 fs-11 fw-medium text-muted"><i class="ri-time-line align-middle"></i> {{ $notification['time'] }}</p>
                                                    </div>
                                                    <!-- Actions -->
                                                    <div class="d-flex align-items-center ms-2 gap-2 flex-shrink-0">
                                                        <button type="button" class="btn btn-sm btn-soft-success mark-notification-read {{ $notification['unread'] ? '' : 'd-none' }}" data-id="{{ $notification['id'] }}" title="{{ __('Mark as Read') }}">
                                                            <i class="bx bx-check"></i><span class="d-none d-md-inline"> {{ __('Mark as Read') }}</span>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-soft-danger delete-notification" data-id="{{ $notification['id'] }}" title="{{ __('Delete') }}">
                                                            <i class="bx bx-trash"></i><span class="d-none d-md-inline"> {{ __('Delete') }}</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="list-group-item text-center text-muted py-4">
                                                <i class="bx bx-bell-off fs-24 d-block mb-1"></i>
                                                {{ __('No Notification Found') }}
                                            </div>
                                        @endforelse
                                    </div>

                                    <!-- Paginator (design only) -->
                                    <nav class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 mt-3" aria-label="{{ __('Notification Pages') }}">
                                        <span class="text-muted fs-12">{{ __('Showing 1 to :count of :count Notifications', ['count' => count($sampleNotifications)]) }}</span>
                                        <ul class="pagination pagination-sm mb-0">
                                            <li class="page-item disabled"><a class="page-link" href="javascript:void(0);">&laquo;</a></li>
                                            <li class="page-item active"><a class="page-link" href="javascript:void(0);">1</a></li>
                                            <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                            <li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
                                            <li class="page-item"><a class="page-link" href="javascript:void(0);">&raquo;</a></li>
                                        </ul>
                                    </nav>
                                </div>
                                <div class="tab-pane" id="changePassword" role="tabpanel">
                                    <form id="changePasswordForm" action="javascript:void(0);">
                                        <div id="changePasswordApiErrors" class="mb-2"></div>
                                        <div class="row g-2">
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="old_password" class="form-label form-mandatory">{{ __('Old Password') }}*</label>
                                                    <div class="position-relative">
                                                        <input type="password" class="form-control pe-5" id="old_password" placeholder="Enter Current Password">
                                                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="toggleOldPassword">
                                                            <i id="toggleOldPasswordIcon" class="ri-eye-line"></i>
                                                        </span>
                                                    </div>
                                                    <div class="text-danger small mt-1 old-password-error"></div>
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="new_password" class="form-label form-mandatory">{{ __('New Password') }}*</label>
                                                    <div class="position-relative">
                                                        <input type="password" class="form-control pe-5" id="new_password" placeholder="Enter New Password">
                                                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="toggleNewPassword">
                                                            <i id="toggleNewPasswordIcon" class="ri-eye-line"></i>
                                                        </span>
                                                    </div>
                                                    <div class="text-danger small mt-1 new-password-error"></div>
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="confirm_password" class="form-label form-mandatory">{{ __('Confirm Password') }}</label>
                                                    <div class="position-relative">
                                                        <input type="password" class="form-control pe-5" id="confirm_password" placeholder="Confirm New Password">
                                                        <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="toggleConfirmNewPassword">
                                                            <i id="toggleConfirmNewPasswordIcon" class="ri-eye-line"></i>
                                                        </span>
                                                    </div>
                                                    <div class="text-danger small mt-1 confirm-password-error"></div>
                                                </div>
                                            </div>
                                            <!--end col-->
                                        </div>
                                        <!--end row-->

                                        <div class="row pt-2">
                                            <div class="col-md-6 text-start">

                                            </div>
                                            <div class="col-md-6 text-end">
                                                <button type="submit" class="btn btn-sm btn-success" id="changePasswordButton">{{ __('Change Password') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane" id="loginHistory" role="tabpanel">
                                    @php
                                        $loginActivities = auth()->user()->loginActivities()->latest('login_at')->get();

                                        $currentLoginActivityId = \App\Models\LoginActivity::currentIdFor(auth()->user(), request());

                                        $loginActivityIcon = function (?string $device) {
                                            $device = blank($device) || $device === '0' ? '' : strtolower($device);

                                            return match (true) {
                                                str_contains($device, 'mobile') => 'ri-smartphone-line',
                                                str_contains($device, 'tablet') => 'ri-tablet-line',
                                                str_contains($device, 'desktop') => 'ri-macbook-line',
                                                default => 'ri-computer-line',
                                            };
                                        };

                                        $loginActivityValue = function (?string $value, string $fallback) {
                                            return blank($value) || $value === '0' ? $fallback : $value;
                                        };
                                    @endphp

                                    <div class="mb-3 border-bottom pb-2">
                                        <div class="float-end">
                                            <a href="javascript:void(0);" class="link-primary" id="logoutAllLoginActivities">{{ __('All Logout') }}</a>
                                        </div>
                                        <h5 class="card-title" style="font-size: 14px; font-weight: bold; font-style: italic;">
                                            {{ __('Login History') }}
                                        </h5>
                                    </div>

                                    @forelse($loginActivities as $loginActivity)
                                        <div class="d-flex align-items-center mb-3" data-login-activity-id="{{ $loginActivity->id }}">
                                            @php $isCurrentDevice = $loginActivity->id === $currentLoginActivityId; @endphp
                                            <div class="flex-shrink-0 avatar-xs">
                                                <div class="avatar-title rounded-2 fs-18 material-shadow {{ $isCurrentDevice ? 'bg-success text-white' : 'bg-light text-primary' }}"
                                                     @if($isCurrentDevice) title="{{ __('This Device') }}" @endif>
                                                    <i class="{{ $loginActivityIcon($loginActivity->device) }}"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <span class="fw-semibold">{{ $loginActivityValue($loginActivity->device, 'Unknown Device') }} -- {{ $loginActivityValue($loginActivity->os, 'Unknown OS') }} -- {{ $loginActivityValue($loginActivity->browser, 'Unknown Browser') }}</span>
                                                <span class="text-muted"> -- {{ $loginActivityValue($loginActivity->ip_address, 'Unknown IP') }} -- {{ $loginActivity->login_at?->format('M d \a\t h:iA') ?? '-' }}</span>
                                            </div>
                                            <div>
                                                @if($loginActivity->is_active)
                                                    <a href="javascript:void(0);" class="logout-login-activity" data-id="{{ $loginActivity->id }}">{{ __('Logout') }}</a>
                                                @else
                                                    <span class="text-muted">{{ __('Logged Out') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <hr style="border-color: darkblue;">
                                    @empty
                                        <p class="text-muted mb-0">{{ __('No Login History Found') }}</p>
                                    @endforelse
                                </div>
                                <div class="tab-pane" id="activityLogs" role="tabpanel">
                                    <div class="mb-3 border-bottom pb-2">
                                        <h5 class="card-title" style="font-size: 14px; font-weight: bold; font-style: italic;">
                                            {{ __('Activity Logs') }}
                                        </h5>
                                    </div>

                                    <table id="profile-activity-logs-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-3 order-1 order-xxl-2">
                    <div class="card mt-xxl-n5">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                    {{-- TODO: Profile Photo/Image Size will be Square/Passport Size rather then Circle View. And Camera Icon should not be there, we will set the Camera Icon on Edit-Profile Mode. Task 2 --}}
                                    <img src="assets/images/users/user_avatar.png" class="profile-photo-lg img-thumbnail user-profile-image material-shadow" alt="user-profile-image">
                                </div>
                                <h5 class="fs-16 mb-1">{{ auth()->user()->name }}</h5>
                                <p class="text-muted mb-0">
                                    {{-- TODO: LoggedIn User's Designation. Task 3 --}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
            </div>

            <!-- End Row -->
        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->

@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function () {
            // The table is initialised while its tab is hidden, so column widths need a recalculation on show.
            $('a[href="#activityLogs"]').on('shown.bs.tab', function () {
                $('#profile-activity-logs-table').DataTable().columns.adjust();
            });

            /*
            |--------------------------------------------------------------------------
            | CHANGE PASSWORD: SHOW/HIDE TOGGLE FOR EACH INPUT
            |--------------------------------------------------------------------------
            | Same toggle pattern as user/show.blade.php's Change Password Modal,
            | repeated for all 3 fields on this page (Old/New/Confirm) instead of 2.
            */
            function bindPasswordToggle(toggleId, inputId, iconId) {
                $(toggleId).on('click', function () {
                    const input = $(inputId);
                    const icon = $(iconId);

                    if (input.attr('type') === 'password') {
                        input.attr('type', 'text');
                        icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
                    } else {
                        input.attr('type', 'password');
                        icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
                    }
                });
            }

            bindPasswordToggle('#toggleOldPassword', '#old_password', '#toggleOldPasswordIcon');
            bindPasswordToggle('#toggleNewPassword', '#new_password', '#toggleNewPasswordIcon');
            bindPasswordToggle('#toggleConfirmNewPassword', '#confirm_password', '#toggleConfirmNewPasswordIcon');

            /*
            |--------------------------------------------------------------------------
            | CHANGE PASSWORD: SUBMIT
            |--------------------------------------------------------------------------
            | Same validate-then-AJAX pattern as user/show.blade.php's Change Password
            | Modal, with an added Old Password check (self-service password change).
            */
            function clearChangePasswordErrors() {
                $('.old-password-error, .new-password-error, .confirm-password-error').text('');
                $('#changePasswordApiErrors').html('');
            }

            function showApiErrors(errors) {
                let html = '<ul class="text-danger ps-3 mb-2">';

                if (typeof errors === 'object') {
                    Object.values(errors).forEach(errArr => {
                        errArr.forEach(msg => {
                            html += `<li>${msg}</li>`;
                        });
                    });
                } else {
                    html += `<li>${errors}</li>`;
                }

                html += '</ul>';

                $('#changePasswordApiErrors').html(html);
            }

            function validateChangePasswordForm(oldPassword, password, confirmPassword) {
                let valid = true;
                clearChangePasswordErrors();

                if (!oldPassword) {
                    $('.old-password-error').text('Old Password is Required');
                    valid = false;
                }

                if (password.length < 6) {
                    $('.new-password-error').text('Password must be at Least 6 Characters');
                    valid = false;
                } else if (!/[A-Z]/.test(password)) {
                    $('.new-password-error').text('Need at Least One Uppercase Letter');
                    valid = false;
                } else if (!/[!@#$%^&*]/.test(password)) {
                    $('.new-password-error').text('Need at Least One Special Character');
                    valid = false;
                }

                if (password !== confirmPassword) {
                    $('.confirm-password-error').text('Confirm Password does not Match');
                    valid = false;
                }

                return valid;
            }

            $('#changePasswordForm').on('submit', function () {
                let oldPassword = $('#old_password').val();
                let password = $('#new_password').val();
                let confirmPassword = $('#confirm_password').val();

                if (!validateChangePasswordForm(oldPassword, password, confirmPassword)) {
                    return;
                }

                $('#changePasswordButton').prop('disabled', true);

                $.ajax({
                    url: "{{ route('change-password') }}",
                    type: "POST",
                    data: {
                        old_password: oldPassword,
                        password: password,
                        password_confirmation: confirmPassword,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            $('#changePasswordForm')[0].reset();
                            Toastify({
                                text: response.message || "Password has been changed successfully",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                close: true,
                                className: "success-toast",
                                stopOnFocus: true
                            }).showToast();
                        } else {
                            showApiErrors(response.message);
                        }
                    },
                    error: function (xhr) {
                        let errors = xhr.responseJSON?.errors || xhr.responseJSON?.message;
                        showApiErrors(errors);
                    },
                    complete: function () {
                        $('#changePasswordButton').prop('disabled', false);
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | LOGIN HISTORY: LOGOUT (SINGLE / ALL)
            |--------------------------------------------------------------------------
            | Marks login_histories rows as inactive for this user. Cannot kill a
            | specific browser session (no session-id linkage), only closes the
            | history record — "All Logout" includes the current session's row too.
            */
            $(document).on('click', '.logout-login-activity', function () {
                const row = $(this).closest('[data-login-activity-id]');
                const activityID = $(this).data('id');

                $.ajax({
                    url: '/login-history/' + activityID + '/logout',
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        row.find('.logout-login-activity').replaceWith('<span class="text-muted">{{ __("Logged Out") }}</span>');
                        Toastify({
                            text: response.message || "Session has been Logged Out Successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "success-toast",
                            stopOnFocus: true
                        }).showToast();
                    },
                    error: function (xhr) {
                        Toastify({
                            text: xhr.responseJSON?.message || "Something went wrong. Please try again",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "error-toast",
                            stopOnFocus: true
                        }).showToast();
                    }
                });
            });

            $('#logoutAllLoginActivities').on('click', function () {
                $.ajax({
                    url: '/login-history/logout-all',
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        $('.logout-login-activity').each(function () {
                            $(this).replaceWith('<span class="text-muted">{{ __("Logged Out") }}</span>');
                        });
                        Toastify({
                            text: response.message || "All Sessions have been Logged Out Successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "success-toast",
                            stopOnFocus: true
                        }).showToast();
                    },
                    error: function (xhr) {
                        Toastify({
                            text: xhr.responseJSON?.message || "Something went wrong. Please try again",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "error-toast",
                            stopOnFocus: true
                        }).showToast();
                    }
                });
            });
        });
    </script>
@endpush
