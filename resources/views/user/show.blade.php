@extends('layout.master')

@section('title', __('User'))

@push('styles')
    <style>
        .badge-label {
            max-width: 100%;
            white-space: normal;
            line-height: 1.2;
        }
        .otp-input {
            width: 40px;
            height: 40px;
            text-align: center;
            font-size: 16px;
        }

        @media (max-width: 576px) {
            .otp-input {
                width: 30px;
                height: 30px;
            }
        }

        .success-toast {
            background: linear-gradient(135deg, #00c9a7, #1f3c88);
            color: #fff;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .failed-toast {
            background: linear-gradient(135deg, #ff922b, #e8590c);
            color: #fff;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }

        .otp-error-text {
            margin-top: 6px;
            font-size: 13px;
            color: #dc3545; /* Bootstrap danger */
            min-height: 18px; /* Prevent layout jump */
            padding-left: 2px;
        }

        .password-error,
        .confirm-password-error {
            font-size: 13px;
        }
    </style>
@endpush

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('User Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('user.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('user.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('user.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6 order-2 order-sm-1">
                                    <table class="table table-sm table-hover table-responsive table-bordered">
                                        <tbody>
                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('User Type') }}</th>
                                            <td class="text-start">{{ ucwords($user->type) }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('UserID') }}</th>
                                            <td class="text-start">{{ $user->username }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('Name') }}</th>
                                            <td class="text-start">{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="text-end align-middle" style="width: 30%;">{{ __('Mobile') }}</th>
                                            <td class="text-start d-flex justify-content-between align-items-center">
                                                <div class="text-start">
                                                    {{ $user->mobile }}
                                                    @if($user->is_mobile_verified == 1)
                                                        <span class="text-success ms-1"><i class="ri-checkbox-circle-fill"></i></span>
                                                    @elseif($user->is_mobile_verified == 0)
                                                        <span class="text-danger ms-1"><i class="ri-close-circle-fill"></i></span>
                                                    @endif
                                                </div>
                                                @if(!$user->trashed() && !$user->is_mobile_verified)
                                                    <div class="text-end">
                                                        <button class="btn btn-sm btn-outline-info" onclick="sendMobileOTP({{ $user->id }})">
                                                            <i class="ri-smartphone-line"></i>
                                                            <span class="d-none d-sm-inline">{{ __('Send OTP & Verify') }}</span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-end align-middle">{{ __('Email') }}</th>
                                            <td class="text-start d-flex justify-content-between align-items-center">
                                                <div class="text-start">
                                                    {{ $user->email }}
                                                    @if($user->is_email_verified == 1)
                                                        <span class="text-success ms-1"><i class="ri-checkbox-circle-fill"></i></span>
                                                    @elseif($user->is_email_verified == 0)
                                                        <span class="text-danger ms-1"><i class="ri-close-circle-fill"></i></span>
                                                    @endif
                                                </div>
                                                @if(!$user->trashed() && !$user->is_email_verified)
                                                    <div class="text-end">
                                                        <button class="btn btn-sm btn-outline-info" onclick="sendEmailOTP({{ $user->id }})">
                                                            <i class="ri-mail-send-line"></i>
                                                            <span class="d-none d-sm-inline">{{ __('Send OTP & Verify') }}</span>
                                                        </button>
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('Status') }}</th>
                                            <td class="text-start">
                                                @if($user->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @elseif($user->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('Not Active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr><th class="text-end" style="width: 20%;">{{ __('Created By') }}</th><td class="text-start">{{ $user->creator?->name }}</td></tr>
                                        <tr><th class="text-end" style="width: 20%;">{{ __('Created At') }}</th><td class="text-start">{{ $user->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end" style="width: 20%;">{{ __('Updated By') }}</th><td class="text-start">{{ $user->updater?->name }}</td></tr>
                                        <tr><th class="text-end" style="width: 20%;">{{ __('Updated At') }}</th><td class="text-start">{{ $user->updated_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @if($user->trashed())
                                            <tr><th class="text-end" style="width: 20%;">{{ __('Deleted By') }}</th><td class="text-start">{{ $user->deleter?->name }}</td></tr>
                                            <tr><th class="text-end" style="width: 20%;">{{ __('Deleted At') }}</th><td class="text-start">{{ $user->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif

                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('Has Roles') }}</th>
                                            <td class="text-start">
                                                @if(count($user->roles) > 0)
                                                    <div class="d-flex justify-content-between align-items-center w-100">
                                                        {{ __('Yes') }}
                                                        <div class="form-check form-switch form-switch-md d-flex align-items-center gap-3">
                                                            <label for="hide-show-roles" class="mb-0">{{ __('Hide') }}</label>
                                                            <input class="form-check-input code-switcher ms-3" type="checkbox" id="hide-show-roles">
                                                            <label for="hide-show-roles" class="mb-0" style="padding-right: 10px;">{{ __('Show') }}</label>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{ __('No') }}
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('Has Permission') }}</th>
                                            <td class="text-start">
                                                @if(count($user->getAllPermissions()) > 0)
                                                    <div class="d-flex justify-content-between align-items-center w-100">
                                                        {{ __('Yes') }}
                                                        <div class="form-check form-switch form-switch-md d-flex align-items-center gap-3">
                                                            <label for="hide-show-permissions" class="mb-0">{{ __('Hide') }}</label>
                                                            <input class="form-check-input code-switcher ms-3" type="checkbox" id="hide-show-permissions">
                                                            <label for="hide-show-permissions" class="mb-0" style="padding-right: 10px;">{{ __('Show') }}</label>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{ __('No') }}
                                                @endif
                                            </td>
                                        </tr>

                                        </tbody>
                                    </table>

                                    <div class="row pb-3">
                                        <div class="col-12 text-start">
                                            @if($user->trashed())
                                                <button class="btn btn-sm btn-soft-success restore-user" id="restoreUser" data-user-id="{{ $user->id }}">
                                                    <i class="ri-recycle-line"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Restore') }}</span>
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-user" id="deleteUser" data-user-id="{{ $user->id }}">
                                                    <i class="ri-close-line"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Delete') }}</span>
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-soft-danger" onclick="openChangePasswordModal()">
                                                    <i class="ri-key-line"></i>
                                                    <span class="d-none d-sm-inline">{{ __('Change Password') }}</span>
                                                </button>
                                                <a href="{{ route('user.assign-roles', $user->id) }}" class="btn btn-sm btn-soft-success">
                                                    <i class="ri-shield-user-line"></i>
                                                    <span class="d-none d-sm-inline">{{ __('Assign Roles') }}</span>
                                                </a>
                                                <a href="{{ route('user.assign-permissions', $user->id) }}" class="btn btn-sm btn-soft-primary">
                                                    <i class="ri-settings-3-line"></i>
                                                    <span class="d-none d-sm-inline">{{ __('Assign Permissions') }}</span>
                                                </a>
                                                <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-soft-info">
                                                    <i class="ri-edit-line"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Edit') }}</span>
                                                </a>
                                                <button class="btn btn-sm btn-warning destroy-user" id="destroyUser" data-user-id="{{ $user->id }}">
                                                    <i class="ri-delete-bin-line"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Destroy') }}</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                </div>

                                <div class="col-sm-4 order-3 order-sm-2">
                                    <div class="w-100">
                                        <div id="assigned-roles-table" style="display: none;">
                                            <table class="table table-hover table-responsive table-bordered table-sm">
                                                <tbody>
                                                <tr>
                                                    <td class="px-3 text-start">
                                                        @if(count($user->roles) > 0)
                                                            <strong class="fw-bold border-bottom border-primary border-1 d-inline-block mb-1">
                                                                {{ __('Assigned Roles') }}
                                                            </strong>

                                                            <div class="row flex-wrap">
                                                                @foreach($user->roles as $role)
                                                                    <div class="col-lg-6 col-12 mb-2">
                                                                        <span class="badge badge-label bg-success text-start">
                                                                            <i class="mdi mdi-circle-medium"></i>
                                                                            {{ ucwords($role->name) }}
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            {{ __('No Assigned Role Found') }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div id="assigned-permissions-table" style="display: none;">
                                            <table class="table table-hover table-responsive table-bordered table-sm">
                                                <tbody>
                                                <tr>
                                                    <td class="px-3 text-start">
                                                        @if(count($user->getAllPermissions()) > 0)
                                                            <strong class="fw-bold border-bottom border-primary border-1 d-inline-block mb-1">
                                                                {{ __('Assigned Permissions') }}
                                                            </strong>
                                                            <div class="row flex-wrap">
                                                                @foreach($user->getAllPermissions() as $permission)
                                                                    <div class="col-lg-6 col-12 mb-2">
                                                                    <span class="badge badge-label bg-success text-start">
                                                                        <i class="mdi mdi-circle-medium"></i>
                                                                        {{ ucwords($permission->description) }}
                                                                    </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            {{ __('No Assigned Permission Found') }}
                                                        @endif
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-2 order-1 order-sm-3 mb-4 mb-sm-0 text-center">
                                    <img class="rounded" src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('assets/images/users/user_avatar.png') }}" alt="Profile Picture" style="width:220px; height:220px; object-fit:cover; border:5px solid blanchedalmond;">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->

            <div class="modal fade" id="mobileOTPVerificationModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Verify Mobile OTP') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <hr>

                        <div class="modal-body text-center p-4">
                            <p>
                                {{ __('OTP has been sent to') }} <strong id="modelMobile">
                                    {{-- In Here, Mobile Number will be Shown which is come from Ajax API Call Response --}}
                                </strong><br>
                                {{ __('OTP expires in') }} <strong id="mobileOTPValidity">
                                    {{-- In Here, OTP Validity will be Shown which is come from Ajax API Call Response --}}
                                </strong>
                            </p>

                            <input type="hidden" id="mobileOTPModelID" name="user_id" value="{{ $user->id }}">

                            <div class="mb-3">
                                <label class="form-label">{{ __('Enter Mobile OTP') }}</label>
                                <div class="d-flex justify-content-center gap-2">
                                    <input class="form-control otp-input mobile-otp" maxlength="1" inputmode="numeric" autocomplete="one-time-code">
                                    <input class="form-control otp-input mobile-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input mobile-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input mobile-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input mobile-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input mobile-otp" maxlength="1" inputmode="numeric">
                                </div>

                                <div id="mobileOTPSendingResponseMessage" class="otp-error-text">
                                    {{-- Validation and Error Message will be Shown Here for the Verify OTP Process --}}
                                </div>

                            </div>
                        </div>

                        <hr>

                        <div class="modal-footer">
                            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Close')  }}</button>
                            <button class="btn btn-sm btn-primary" onclick="verifyMobileOTP(this)">{{ __('Verify OTP') }}</button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="emailOTPVerificationModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Verify Email OTP') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <hr>

                        <div class="modal-body text-center p-4">
                            <p>
                                {{ __('OTP has been sent to') }} <strong id="modelEmail">
                                    {{-- In Here, Email Address will be Shown which is come from Ajax API Call Response --}}
                                </strong><br>
                                {{__('OTP expires in')}} <strong id="emailOTPValidity">
                                    {{-- In Here, OTP Validity will be Shown which is come from Ajax API Call Response --}}
                                </strong>
                            </p>

                            <input type="hidden" id="emailOTPUserID" name="user_id" value="{{ $user->id }}">

                            <div class="mb-3">
                                <label class="form-label">{{ __('Enter Email OTP') }}</label>
                                <div class="d-flex justify-content-center gap-2">
                                    <input class="form-control otp-input email-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input email-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input email-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input email-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input email-otp" maxlength="1" inputmode="numeric">
                                    <input class="form-control otp-input email-otp" maxlength="1" inputmode="numeric">
                                </div>

                                <div id="emailOTPSendingResponseMessage" class="otp-error-text">
                                    {{-- Validation and Error Message will be Shown Here for the Verify OTP Process --}}
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="modal-footer">
                            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                            <button class="btn btn-sm btn-primary" onclick="verifyEmailOTP(this)">{{ __('Verify OTP') }}</button>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal fade" id="changePasswordModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('Change Password') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <hr>

                        <div class="modal-body">

                            <!-- API Error List -->
                            <div id="changePasswordApiErrors" class="mb-2"></div>

                            <form id="changePasswordForm">
                                <input type="hidden" id="changePasswordUserID" value="{{ $user->id }}">

                                <!-- Password -->
                                <div class="row mb-2 align-items-center">
                                    <label for="password" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Password') }}</label>
                                    <div class="col-12 col-md-8">
                                        <div class="position-relative">
                                            <input type="password" id="password" name="password"
                                                   class="form-control pe-5 @error('password') is-invalid @enderror"
                                                   placeholder="Enter Password" required
                                                   oninvalid="this.setCustomValidity('Password is Required')"
                                                   oninput="this.setCustomValidity('')">

                                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="togglePassword">
                                                <i id="togglePasswordIcon" class="ri-eye-line"></i>
                                            </span>
                                        </div>
                                        @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                                        <small class="text-danger password-error"></small>
                                    </div>
                                </div>

                                <!-- Confirm Password -->
                                <div class="row mb-2 align-items-center">
                                    <label for="confirm_password" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Confirm Password') }}</label>
                                    <div class="col-12 col-md-8">
                                        <div class="position-relative">
                                            <input type="password" id="confirm_password" name="password_confirmation"
                                                   class="form-control pe-5 @error('confirm_password') is-invalid @enderror"
                                                   placeholder="Confirm Password" required
                                                   oninvalid="this.setCustomValidity('Password Confirmation is Required')"
                                                   oninput="this.setCustomValidity('')">

                                            <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="toggleConfirmPassword">
                                                <i id="toggleConfirmPasswordIcon" class="ri-eye-line"></i>
                                            </span>
                                        </div>
                                        @error('confirm_password')<small class="text-danger">{{ $message }}</small>@enderror
                                        <small class="text-danger confirm-password-error"></small>
                                        <small class="text-danger d-none" id="confirmPasswordError">{{ __('Passwords Does NOT Matched') }}</small>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <hr>

                        <div class="modal-footer">
                            <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button class="btn btn-sm btn-danger" id="changePasswordBtn">{{ __('Change Password') }}</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->
@endsection

@push('scripts')
    @if (session('success'))
        <script>
            Toastify({
                text: @json(session('success')),
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                className: "success-toast",
                stopOnFocus: true
            }).showToast();
        </script>
    @endif

    <script>
        $(document).ready(function () {

            const hideShowPermissionSwitch = $('#hide-show-permissions');
            const assignedPermissionTable  = $('#assigned-permissions-table');

            // Initial State
            if (hideShowPermissionSwitch.is(':checked')) {
                assignedPermissionTable.show();
            } else {
                assignedPermissionTable.hide();
            }

            // On Change
            hideShowPermissionSwitch.on('change', function () {
                if ($(this).is(':checked')) {
                    assignedPermissionTable.slideDown(500);   // Show
                } else {
                    assignedPermissionTable.slideUp(500);     // Hide
                }
            });

            const hideShowRoleSwitch = $('#hide-show-roles');
            const assignedRoleTable  = $('#assigned-roles-table');

            // Initial State
            if (hideShowRoleSwitch.is(':checked')) {
                assignedRoleTable.show();
            } else {
                assignedRoleTable.hide();
            }

            // On Change
            hideShowRoleSwitch.on('change', function () {
                if ($(this).is(':checked')) {
                    assignedRoleTable.slideDown(500);   // Show
                } else {
                    assignedRoleTable.slideUp(500);     // Hide
                }
            });

            const csrfToken = "{{ csrf_token() }}";

            $(document).on('click', '.destroy-user', function () {
                const userID = $(this).data('user-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Destroy this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Destroy',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/user/' + userID,
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Destroyed', 'The Record has been Destroyed.', 'success')
                                    .then(() => { window.location.href = '/admin/user'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Destroying the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-user', function () {
                const userID = $(this).data('user-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore Record?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/user/' + userID + '/restore',
                            method: 'POST',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record is Now in Active List.', 'success')
                                    .then(() => { window.location.href = '/admin/user/trash'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Restoring the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-user', function () {
                const userID = $(this).data('user-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Delete this Data',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/user/' + userID + '/force-delete',
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record has been Deleted.', 'success')
                                    .then(() => { window.location.href = '/admin/user/trash'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Deleting the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });

            });
        });

        function getOTPValue(selector) {
            let otp = '';
            $(selector).each(function () {
                otp += $(this).val();
            });
            return otp;
        }

        function clearOTPInputs(selector) {
            $(selector).val('');
        }

        function showMobileOTPError(message) {
            $('#mobileOTPSendingResponseMessage').text(message);
        }

        function clearMobileOTPError() {
            $('#mobileOTPSendingResponseMessage').text('');
        }

        // Send Mobile OTP (AJAX + Open Modal)
        function sendMobileOTP(modelID) {
            $.ajax({
                url: "{{ route('otp.send.mobile') }}",
                type: "POST",
                data: {
                    model_id: modelID,
                    model_name: 'User',
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    clearMobileOTPError();
                },
                success: function (response) {
                    if (response.status === 'success' || response.otp_sent === true) {

                        $('#modelMobile').text(response.mobile);
                        $('#mobileOTPValidity').text(response.validity + ' Minutes');
                        $('#mobileOTPModelID').val(modelID);

                        clearOTPInputs('.mobile-otp');

                        let mobileOTPModal = new bootstrap.Modal(document.getElementById('mobileOTPVerificationModal'));
                        mobileOTPModal.show();

                    } else {
                        Toastify({
                            text: response.message || "Failed to Send OTP",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "failed-toast",
                            stopOnFocus: true
                        }).showToast();
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || "Technical Error - Try Again";
                    Toastify({
                        text: msg,
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        close: true,
                        className: "failed-toast",
                        stopOnFocus: true
                    }).showToast();
                }
            });
        }

        $('#mobileOTPVerificationModal').on('shown.bs.modal', function () {
            $('.mobile-otp').first().focus();
        });

        // Verify Mobile OTP (Validation + Toaster + Reload)
        function verifyMobileOTP(btn) {
            let modelID = $('#mobileOTPModelID').val();
            let otp = getOTPValue('.mobile-otp');

            clearMobileOTPError();

            if (otp.length !== 6) {
                showMobileOTPError('Please Enter a Valid 6-Digit OTP');
                return;
            }

            $(btn).prop('disabled', true);

            $.ajax({
                url: "{{ route('otp.verify.mobile') }}",
                type: "POST",
                data: {
                    model_id: modelID,
                    model_name: 'User',
                    otp: otp,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Toastify({
                            text: response.message || "Validate Mobile OTP Successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "success-toast",
                            stopOnFocus: true
                        }).showToast();

                        bootstrap.Modal.getInstance(document.getElementById('mobileOTPVerificationModal')).hide();
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showMobileOTPError(response.message || 'Invalid OTP');
                    }
                },
                error: function (xhr) {
                    let message = xhr.responseJSON?.message || 'Invalid OTP';
                    showMobileOTPError(message);
                },
                complete: function () {
                    $(btn).prop('disabled', false);
                }
            });
        }

        function showEmailOTPError(message) {
            $('#emailOTPSendingResponseMessage').text(message);
        }

        function clearEmailOTPError() {
            $('#emailOTPSendingResponseMessage').text('');
        }

        // Send Email OTP (AJAX + Open Modal)
        function sendEmailOTP(modelID) {
            $.ajax({
                url: "{{ route('otp.send.email') }}",
                type: "POST",
                data: {
                    model_id: modelID,
                    model_name: 'User',
                    _token: "{{ csrf_token() }}"
                },
                beforeSend: function () {
                    clearEmailOTPError();
                },
                success: function (response) {
                    if (response.status) {
                        $('#modelEmail').text(response.email);
                        $('#emailOTPValidity').text(response.validity + ' Minutes');
                        $('#emailOTPUserID').val(modelID);

                        clearOTPInputs('.email-otp');

                        let emailOTPModal = new bootstrap.Modal(document.getElementById('emailOTPVerificationModal'));
                        emailOTPModal.show();
                    } else {
                        Toastify({
                            text: response.message || "Failed to Send OTP",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "failed-toast",
                            stopOnFocus: true
                        }).showToast();
                    }
                },
                error: function (xhr) {
                    let msg = xhr.responseJSON?.message || 'Technical Error - Try Again';
                    Toastify({
                        text: msg,
                        duration: 4000,
                        gravity: "top",
                        position: "right",
                        close: true,
                        className: "failed-toast",
                        stopOnFocus: true
                    }).showToast();
                }
            });
        }

        $('#emailOTPVerificationModal').on('shown.bs.modal', function () {
            $('.email-otp').first().focus();
        });

        // Verify Email OTP (Validation + Close Modal + Reload)
        function verifyEmailOTP(btn) {
            let modelID = $('#emailOTPUserID').val();
            let otp = getOTPValue('.email-otp');

            clearEmailOTPError();

            if (otp.length !== 6) {
                showEmailOTPError('Please Enter a Valid 6-Digit OTP');
                return;
            }

            $(btn).prop('disabled', true);

            $.ajax({
                url: "{{ route('otp.verify.email') }}",
                type: "POST",
                data: {
                    model_id: modelID,
                    model_name: 'User',
                    otp: otp,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Toastify({
                            text: response.message || "Validate Email OTP Successfully",
                            duration: 4000,
                            gravity: "top",
                            position: "right",
                            close: true,
                            className: "success-toast",
                            stopOnFocus: true
                        }).showToast();

                        bootstrap.Modal.getInstance(document.getElementById('emailOTPVerificationModal')).hide();
                        setTimeout(() => location.reload(), 1200);
                    } else {
                        showEmailOTPError(response.message || 'Invalid OTP');
                    }
                },
                error: function (xhr) {
                    let message = xhr.responseJSON?.message || 'Invalid OTP';
                    showEmailOTPError(message);
                },
                complete: function () {
                    $(btn).prop('disabled', false);
                }
            });
        }

        // Auto Move Cursor Between OTP Boxes
        $(document).on('input', '.otp-input', function () {
            if (this.value.length === 1) {
                $(this).next('.otp-input').focus();
            }
        });

        $(document).on('keydown', '.otp-input', function (e) {
            if (e.key === "Backspace" || e.key === "Delete") {

                // Clear Current Input
                $(this).val('');

                // Move Focus to Previous Input
                let prev = $(this).prev('.otp-input');
                if (prev.length) {
                    prev.val('').focus();
                }

                e.preventDefault();
            }
        });



        // Password Toggle
        $('#togglePassword').on('click', function () {
            const password = $('#password');
            const icon = $('#togglePasswordIcon');

            if (password.attr('type') === 'password') {
                password.attr('type', 'text');
                icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
            } else {
                password.attr('type', 'password');
                icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
            }
        });

        // Confirm Password Toggle
        $('#toggleConfirmPassword').on('click', function () {
            const confirmPassword = $('#confirm_password');
            const icon = $('#toggleConfirmPasswordIcon');

            if (confirmPassword.attr('type') === 'password') {
                confirmPassword.attr('type', 'text');
                icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
            } else {
                confirmPassword.attr('type', 'password');
                icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
            }
        });

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

        function openChangePasswordModal() {
            // Clear Input Values
            $('#password').val('');
            $('#confirm_password').val('');

            // Reset Eye Icons
            $('#password').attr('type', 'password');
            $('#confirm_password').attr('type', 'password');

            $('#togglePasswordIcon').removeClass('ri-eye-off-line').addClass('ri-eye-line');
            $('#toggleConfirmPasswordIcon').removeClass('ri-eye-off-line').addClass('ri-eye-line');

            // Clear Validation Errors
            clearChangePasswordErrors();
            new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
        }

        function validatePassword(password, confirmPassword) {
            let valid = true;
            clearChangePasswordErrors();

            if (password.length < 6) {
                $('.password-error').text('Password must be at Least 6 Characters');
                valid = false;
            } else if (!/[A-Z]/.test(password)) {
                $('.password-error').text('Need at Least One Uppercase Letter');
                valid = false;
            } else if (!/[!@#$%^&*]/.test(password)) {
                $('.password-error').text('Need at Least One Special Character');
                valid = false;
            }

            if (password !== confirmPassword) {
                $('.confirm-password-error').text('Confirm Password does not Match');
                valid = false;
            }

            return valid;
        }

        function clearChangePasswordErrors() {
            $('.password-error').text('');
            $('.confirm-password-error').text('');
            $('#changePasswordApiErrors').html('');
        }

        $('#changePasswordBtn').on('click', function () {
            let userID = $('#changePasswordUserID').val();
            let password = $('#password').val();
            let confirmPassword = $('#confirm_password').val();

            if (!validatePassword(password, confirmPassword)) {
                return;
            }

            $(this).prop('disabled', true);

            $.ajax({
                url: "{{ route('user.change-password', $user->id) }}",
                type: "POST",
                data: {
                    user_id: userID,
                    password: password,
                    password_confirmation: confirmPassword,
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
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


    </script>
@endpush
