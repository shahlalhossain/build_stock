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
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab" title="{{ __('Change Password') }}">
                                        <i class="ri-key-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Change Password') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePasswordLogs" role="tab" title="{{ __('Password Change Logs') }}">
                                        <i class="ri-history-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Password Change Logs') }}</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#loginHistory" role="tab" title="{{ __('Login History') }}">
                                        <i class="ri-login-circle-line profile-tab-icon d-inline d-md-none"></i>
                                        <span class="d-none d-md-inline">{{ __('Login History') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-4">
                            <div class="tab-content">
                                <div class="tab-pane active" id="profileDetails" role="tabpanel">
                                    <div class="mb-3 border-bottom">
                                        <div class="float-end">
                                            <a href="javascript:void(0);" class="link-primary">Update Information</a>
                                        </div>
                                        <h5 class="card-title" style="font-size: 14px; font-weight: bold; font-style: italic;">
                                            {{ __('Personal Details') }}
                                        </h5>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table mb-0">
                                            <tbody>
                                            <tr><th scope="row" style="width: 200px;">Name</th><td>Md Shahlal Hossain</td></tr>
                                            <tr><th scope="row">Email</th><td>shahlal@gmail.com</td></tr>
                                            <tr><th scope="row">Mobile</th><td>+8801731479874</td></tr>
                                            <tr><th scope="row">Role</th><td>Manager</td></tr>
                                            <tr><th scope="row">Registered At</th><td>2012-12-12</td></tr>
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
                                <div class="tab-pane" id="changePasswordLogs" role="tabpanel">
                                    {{-- TODO: LoggedIn User's Password Change HIstory/Logs --}}
                                </div>
                                <div class="tab-pane" id="loginHistory" role="tabpanel">

                                    {{-- TODO: Have make this Dynamic and Workiable (Logout All and Individual Login Session Destroy - Not Delete) --}}

                                    <div class="mb-3 border-bottom pb-2">
                                        <div class="float-end">
                                            <a href="javascript:void(0);" class="link-primary">{{ __('All Logout') }}</a>
                                        </div>
                                        <h5 class="card-title" style="font-size: 14px; font-weight: bold; font-style: italic;">
                                            {{ __('Login History') }}
                                        </h5>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-smartphone-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>iPhone 12 Pro</h6>
                                            <p class="text-muted mb-0">Los Angeles, United States - March 16 at 2:47PM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">{{ __('Logout') }}</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-tablet-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>Apple iPad Pro</h6>
                                            <p class="text-muted mb-0">Washington, United States - November 06 at 10:43AM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">{{ __('Logout') }}</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-smartphone-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>Galaxy S21 Ultra 5G</h6>
                                            <p class="text-muted mb-0">Conneticut, United States - June 12 at 3:24PM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">{{ __('Logout') }}</a>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 avatar-sm">
                                            <div class="avatar-title bg-light text-primary rounded-3 fs-18 material-shadow">
                                                <i class="ri-macbook-line"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6>Dell Inspiron 14</h6>
                                            <p class="text-muted mb-0">Phoenix, United States - July 26 at 8:10AM</p>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0);">{{ __('Logout') }}</a>
                                        </div>
                                    </div>
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
                                    <img src="assets/images/users/avatar-1.jpg" class="profile-photo-lg img-thumbnail user-profile-image material-shadow" alt="user-profile-image">
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
    <script>
        $(document).ready(function () {
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
                let oldPassword = $('#oldpassword').val();
                let password = $('#newpassword').val();
                let confirmPassword = $('#confirmpassword').val();

                if (!validateChangePasswordForm(oldPassword, password, confirmPassword)) {
                    return;
                }

                $('#changePasswordBtn').prop('disabled', true);

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
                        $('#changePasswordBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush
