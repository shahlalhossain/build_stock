@extends('layout.master')

@section('title', __('User'))

@push('styles')
    <style>
        .form-control.readonly-disabled,
        .form-control.readonly-disabled:focus,
        .form-control.readonly-disabled:hover,
        .form-control[readonly].readonly-disabled {
            background-color: #e9ecef !important;
            cursor: not-allowed;
        }
        .user-profile-image {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 50%;
        }
        .user-profile-image.img-thumbnail {
            padding: 4px;
            border-width: 1px;
        }
        .avatar-custom {
            width: 38px;
            height: 38px;
        }
        .avatar-custom .avatar-title {
            width: 100%;
            height: 100%;
            font-size: 18px;
        }
        .profile-user {
            position: relative;
        }
        .profile-photo-edit {
            bottom: 10px;
            right: 10px;
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('User Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('user.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i>
                                    <span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">

                                <!-- Start Page Error Section -->
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ $error }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endforeach
                                @endif
                                <!-- End Page Error Section -->

                                <div class="row">
                                    <!-- Start Left Column -->
                                    <div class="col-12 col-md-9">
                                        <div class="row mb-2">
                                            <label for="type" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('User Type') }}</label>
                                            <div class="col-12 col-md-6">
                                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                                    <option value="">{{ __('=== Select User Type ===') }}</option>
                                                    <option value="admin" selected>{{ __('Admin') }}</option>
                                                    <option value="employee">{{ __('Employee') }}</option>
                                                    <option value="user">{{ __('User') }}</option>
                                                    <option value="member">{{ __('Member') }}</option>
                                                </select>
                                                @error('type')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Name') }}</label>
                                            <div class="col-12 col-md-6">
                                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Name" required oninvalid="this.setCustomValidity('Name is Required')" oninput="this.setCustomValidity('')">
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <!-- Start Mobile Input -->
                                        <div class="row mb-2" id="mobileSection">
                                            <label for="mobile" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Mobile Number') }}</label>
                                            <div class="col-12 col-md-6">
                                                <div class="position-relative">
                                                    <input type="text" id="mobile" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile') }}" placeholder="{{ __('Enter Mobile') }}" required>
                                                    <input type="hidden" id="isMobileVerified" name="is_mobile_verified" value="{{ old('is_mobile_verified', 0) }}">
{{--                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3">--}}
{{--                                                        <button type="button" id="sendOTPToMobile" class="btn btn-link p-0 text-decoration-none">{{ __('Send OTP') }}</button>--}}
{{--                                                    </span>--}}
                                                    <span id="mobileVerifiedIcon" class="position-absolute top-50 end-0 translate-middle-y me-3 text-success d-none">
                                                        <i class="ri-checkbox-circle-fill fs-5"></i>
                                                    </span>
                                                </div>
                                                <small id="mobileClientError" class="text-danger d-none">{{ __('Please Enter a Valid Mobile Number (01XXXXXXXXX)') }}</small>
                                                <small id="mobileOTPSuccess" class="text-success d-none"></small>
                                                @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>

                                            <div class="col-12 col-md-4" id="mobileOTPWrapper" style="display:none;">
                                                <div class="position-relative">
                                                    <input type="text" id="mobileOTP" name="mobileOTP" class="form-control" placeholder="{{ __('Mobile OTP') }}">
                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3">
                                                        <button type="button" id="verifyMobileOTP" class="btn btn-link p-0 text-decoration-none">{{ __('Verify OTP') }}</button>
                                                    </span>
                                                </div>
                                                <small id="mobileOTPClientError" class="text-danger d-none"></small>
                                                @error('mobileOTP')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <!-- End Mobile Input -->

                                        <!-- Start Email Input -->
                                        <div class="row mb-2" id="emailSection">
                                            <label for="email" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Email Address') }}</label>
                                            <div class="col-12 col-md-6">
                                                <div class="position-relative">
                                                    <input type="text" id="email" name="email" class="form-control pe-5 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="{{ __('Enter Email') }}" required>
                                                    <input type="hidden" id="isEmailVerified" name="is_email_verified" value="{{ old('is_email_verified', 0) }}">
{{--                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3">--}}
{{--                                                        <button type="button" id="sendOTPToEmail" class="btn btn-link p-0 text-decoration-none">{{ __('Send OTP') }}</button>--}}
{{--                                                    </span>--}}
                                                    <span id="emailVerifiedIcon" class="position-absolute top-50 end-0 translate-middle-y me-3 text-success d-none">
                                                        <i class="ri-checkbox-circle-fill fs-5"></i>
                                                    </span>
                                                </div>
                                                <small id="emailClientError" class="text-danger d-none">{{ __('Please Enter a Valid Email Address') }}</small>
                                                <small id="emailOTPSuccess" class="text-success d-none"></small>
                                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>

                                            <div class="col-12 col-md-4" id="emailOTPWrapper" style="display:none;">
                                                <div class="position-relative">
                                                    <input type="text" id="emailOTP" name="emailOTP" class="form-control" placeholder="{{ __('Email OTP') }}">
                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3">
                                                        <button type="button" id="verifyEmailOTP" class="btn btn-link p-0 text-decoration-none">{{ __('Verify OTP') }}</button>
                                                    </span>
                                                </div>
                                                <small id="emailOTPClientError" class="text-danger d-none"></small>
                                                @error('emailOTP')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        <!-- End Email Input -->


                                        <div class="row mb-2">
                                            <label for="password" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Password') }}</label>
                                            <div class="col-12 col-md-6">
                                                <div class="position-relative">
                                                    <input type="password" id="password" name="password" class="form-control pe-5 @error('password') is-invalid @enderror" placeholder="Enter Password" required oninvalid="this.setCustomValidity('Password is Required')" oninput="this.setCustomValidity('')">
                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="togglePassword">
                                                        <i id="togglePasswordIcon" class="ri-eye-line"></i>
                                                    </span>
                                                </div>
                                                @error('password')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="confirm_password" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Confirm Password') }}</label>
                                            <div class="col-12 col-md-6">
                                                <div class="position-relative">
                                                    <input type="password" id="confirm_password" name="password_confirmation" class="form-control pe-5 @error('confirm_password') is-invalid @enderror" placeholder="Confirm Password" required oninvalid="this.setCustomValidity('Password Confirmation is Required')" oninput="this.setCustomValidity('')">
                                                    <span class="position-absolute top-50 end-0 translate-middle-y me-3 cursor-pointer" id="toggleConfirmPassword">
                                                        <i id="toggleConfirmPasswordIcon" class="ri-eye-line"></i>
                                                    </span>
                                                </div>
                                                @error('confirm_password')<small class="text-danger">{{ $message }}</small>@enderror
                                                <small class="text-danger d-none" id="confirmPasswordError">{{ __('Passwords Does NOT Matched') }}</small>
                                            </div>
                                        </div>

                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-3 text-center">
                                        @error('profile_picture')<small class="text-danger">{{ $message }}</small>@enderror
                                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                            <img src="{{ asset('assets/images/avatar_3.png') }}" class="rounded-circle avatar-xxl img-thumbnail user-profile-image" alt="Profile-Photo">
                                            <div class="avatar-custom p-0 rounded-circle profile-photo-edit">
                                                <input type="file" id="profile-img-file-input" name="profile_picture" class="profile-img-file-input @error('profile_picture') is-invalid @enderror">
                                                <label for="profile-img-file-input" class="profile-photo-edit avatar-custom">
                                                    <span class="avatar-title rounded-circle bg-light text-body">
                                                        <i class="ri-camera-fill"></i>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('user.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Create') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @if(old('is_mobile_verified') == 1)
        <script>
            $(function () {
                $('#mobile').prop('readonly', true);
                $('#sendOTPToMobile').hide();
                $('#mobileVerifiedIcon').removeClass('d-none');
            });
        </script>
    @endif

    @if(old('is_email_verified') == 1)
        <script>
            $(function () {
                $('#email').prop('readonly', true);
                $('#sendOTPToEmail').hide();
                $('#emailVerifiedIcon').removeClass('d-none');
            });
        </script>
    @endif

    <script>
        $(document).ready(function () {

            /* ================== START MOBILE OTP SENDING SCRIPT ================== */

            // Global timer reference (to stop countdown on verify)
            let mobileOTPTimer = null;

            /* ================== SEND AND RESEND MOBILE OTP ================== */
            $('#sendOTPToMobile').on('click', function (e) {
                e.preventDefault();

                const btn = $(this);

                // Prevent Multiple Clicks
                if (btn.data('state') === 'sending' || btn.data('state') === 'countdown') return;

                const mobileInput = $('#mobile');
                const mobile = mobileInput.val().trim();
                const errorBox = $('#mobileClientError');
                const otpWrapper = $('#mobileOTPWrapper');

                const mobileRegex = /^01\d{9}$/;

                // Reset Validation
                mobileInput.removeClass('is-invalid');
                errorBox.addClass('d-none');

                // Mobile Validation
                if (!mobileRegex.test(mobile)) {
                    mobileInput.addClass('is-invalid').focus();
                    errorBox.removeClass('d-none');
                    return;
                }

                // Button Sending State
                btn.text('Sending...').data('state', 'sending').css('pointer-events', 'none');

                $.ajax({
                    url: "",
                    type: "POST",
                    data: {
                        mobile,
                        _token: "{{ csrf_token() }}"
                    },
                    success(response) {
                        if (response.status === 'success') {
                            // Reset OTP Field on Send/Resend
                            resetMobileOTPField();
                            // Lock Mobile Input
                            mobileInput.prop('readonly', true).addClass('readonly-disabled');
                            // Show OTP Field
                            otpWrapper.slideDown(300, () => $('#mobileOTP').focus());
                            // Show Success Message
                            $('#mobileOTPSuccess').text('OTP Sent Successfully to the given Mobile Number').removeClass('d-none');
                            // Start Resend Countdown
                            startMobileOTPTimer(btn, 60);
                        } else {
                            resetMobileButton(btn);
                        }
                    },
                    error() {
                        resetMobileButton(btn);
                    }
                });
            });

            /* ================== OTP COUNTDOWN TIMER ================== */
            function startMobileOTPTimer(button, seconds) {
                let remaining = seconds;

                button.data('state', 'countdown').css('pointer-events', 'none');

                mobileOTPTimer = setInterval(() => {
                    button.text(`Resend in ${remaining}s`);
                    remaining--;

                    if (remaining < 0) {
                        clearInterval(mobileOTPTimer);
                        mobileOTPTimer = null;

                        // Hide OTP Sent Message After Countdown
                        $('#mobileOTPSuccess').addClass('d-none');

                        button
                            .html('Resend OTP <span class="mx-1">|</span>' + '<span id="cancelMobileOTP" class="text-danger" style="cursor:pointer;">Cancel</span>')
                            .data('state', 'ready')
                            .css('pointer-events', 'auto');

                        bindCancelMobileOTP(button);
                    }
                }, 1000);
            }

            /* ================== CANCEL OTP ================== */
            function bindCancelMobileOTP(button) {
                $('#cancelMobileOTP').one('click', function (e) {
                    e.stopPropagation();
                    // Stop Timer if Running
                    if (mobileOTPTimer) {
                        clearInterval(mobileOTPTimer);
                        mobileOTPTimer = null;
                    }
                    // Unlock Mobile Input
                    $('#mobile').prop('readonly', false).removeClass('readonly-disabled').focus();

                    // Reset OTP UI
                    resetMobileOTPField();
                    $('#mobileOTPWrapper').slideUp(300);
                    $('#mobileOTPSuccess').addClass('d-none');

                    // Reset Button
                    button.text('Send OTP').data('state', 'idle');
                });
            }

            /* ================== VERIFY MOBILE OTP ================== */
            $('#verifyMobileOTP').on('click', function (e) {
                e.preventDefault();

                const btn = $(this);
                const otpInput = $('#mobileOTP');
                const otp = otpInput.val().trim();
                const errorBox = $('#mobileOTPClientError');

                otpInput.removeClass('is-invalid');
                errorBox.addClass('d-none').text('');

                // OTP Validation
                if (!/^\d{6}$/.test(otp)) {
                    otpInput.addClass('is-invalid').focus();
                    errorBox.text('Please Enter a Valid 6-Digits OTP').removeClass('d-none');
                    return;
                }

                btn.text('Verifying...').css('pointer-events', 'none');

                $.ajax({
                    url: "",
                    type: "POST",
                    data: {
                        mobile: $('#mobile').val().trim(),
                        otp,
                        _token: "{{ csrf_token() }}"
                    },

                    success(response) {
                        if (response.status === 'verified') {

                            $('#isMobileVerified').val(1);

                            // Stop Countdown Immediately
                            if (mobileOTPTimer) {
                                clearInterval(mobileOTPTimer);
                                mobileOTPTimer = null;
                            }

                            // Hide OTP Sent Message Immediately
                            $('#mobileOTPSuccess').addClass('d-none');

                            // Final Verified UI
                            $('#mobileOTPWrapper').slideUp(300);
                            $('#sendOTPToMobile').hide();
                            $('#mobileVerifiedIcon').removeClass('d-none');

                        } else {
                            otpInput.addClass('is-invalid');
                            errorBox.text('Invalid OTP').removeClass('d-none');
                        }
                    },
                    error(xhr) {
                        if (xhr.status === 422) {
                            $('#mobileOTPClientError').text('OTP Verification Failed. Please Try Again.').removeClass('d-none');
                        }
                    },
                    complete() {
                        btn.text('Verify OTP').css('pointer-events', 'auto');
                    }
                });
            });

            /* ================== HELPERS ================== */

            // Reset Send Button
            function resetMobileButton(btn) {
                btn.text('Send OTP').data('state', 'idle').css('pointer-events', 'auto');
            }

            // Reset OTP Input & Error
            function resetMobileOTPField() {
                $('#mobileOTP').val('').removeClass('is-invalid');
                $('#mobileOTPClientError').addClass('d-none').text('');
            }

            $('#mobile').on('input', function () {
                if ($('#isMobileVerified').val() == 1) {
                    resetMobileVerification();
                }
            });

            function resetMobileVerification() {
                $('#mobile_verified').val(0);
                $('#mobileVerifiedIcon').addClass('d-none');
                $('#sendOTPToMobile').show().text('Send OTP').data('state', 'idle');
                $('#mobile').prop('readonly', false).removeClass('readonly-disabled');
            }


            /* ================== END MOBILE OTP SENDING SCRIPT ================== */


            /* ================== START EMAIL OTP SENDING SCRIPT ================== */

            // Global Timer Reference (to Stop Countdown on Verify)
            let emailOTPTimer = null;

            /* ================== SEND AND RESEND EMAIL OTP ================== */
            $('#sendOTPToEmail').on('click', function (e) {
                e.preventDefault();

                const btn = $(this);

                // Prevent Multiple Clicks
                if (btn.data('state') === 'sending' || btn.data('state') === 'countdown') return;

                const emailInput = $('#email');
                const email = emailInput.val().trim();
                const errorBox = $('#emailClientError');
                const otpWrapper = $('#emailOTPWrapper');

                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

                // Reset Validation
                emailInput.removeClass('is-invalid');
                errorBox.addClass('d-none');

                // Email Validation
                if (!emailRegex.test(email)) {
                    emailInput.addClass('is-invalid').focus();
                    errorBox.removeClass('d-none');
                    return;
                }

                // Button Sending State
                btn.text('Sending...').data('state', 'sending').css('pointer-events', 'none');

                $.ajax({
                    url: "",
                    type: "POST",
                    data: {
                        email,
                        _token: "{{ csrf_token() }}"
                    },

                    success(response) {
                        if (response.status === 'success') {

                            // Reset OTP Field on Send and Resend
                            resetEmailOTPField();

                            // Lock Email Input
                            emailInput.prop('readonly', true).addClass('readonly-disabled');

                            // Show OTP Field
                            otpWrapper.slideDown(300, () => $('#emailOTP').focus());

                            // Show Success Message
                            $('#emailOTPSuccess').text('OTP Sent Successfully to the given Email Address').removeClass('d-none');

                            // Start Resend Countdown
                            startEmailOTPTimer(btn, 60);
                        } else {
                            resetEmailButton(btn);
                        }
                    },
                    error() {
                        resetEmailButton(btn);
                    }
                });
            });

            /* ================== OTP COUNTDOWN TIMER ================== */
            function startEmailOTPTimer(button, seconds) {
                let remaining = seconds;

                button.data('state', 'countdown').css('pointer-events', 'none');

                emailOTPTimer = setInterval(() => {
                    button.text(`Resend in ${remaining}s`);
                    remaining--;

                    if (remaining < 0) {
                        clearInterval(emailOTPTimer);
                        emailOTPTimer = null;

                        // Hide OTP Sent Message After Countdown
                        $('#emailOTPSuccess').addClass('d-none');

                        button
                            .html('Resend OTP <span class="mx-1">|</span>' + '<span id="cancelEmailOTP" class="text-danger" style="cursor:pointer;">Cancel</span>')
                            .data('state', 'ready')
                            .css('pointer-events', 'auto');

                        bindCancelEmailOTP(button);
                    }
                }, 1000);
            }

            /* ================== CANCEL OTP ================== */
            function bindCancelEmailOTP(button) {
                $('#cancelEmailOTP').one('click', function (e) {
                    e.stopPropagation();

                    // Stop Timer if Running
                    if (emailOTPTimer) {
                        clearInterval(emailOTPTimer);
                        emailOTPTimer = null;
                    }

                    // Unlock Email Input
                    $('#email').prop('readonly', false).removeClass('readonly-disabled').focus();

                    // Reset OTP UI
                    resetEmailOTPField();
                    $('#emailOTPWrapper').slideUp(300);
                    $('#emailOTPSuccess').addClass('d-none');

                    // Reset Button
                    button.text('Send OTP').data('state', 'idle');
                });
            }

            /* ================== VERIFY EMAIL OTP ================== */
            $('#verifyEmailOTP').on('click', function (e) {
                e.preventDefault();

                const btn = $(this);
                const otpInput = $('#emailOTP');
                const otp = otpInput.val().trim();
                const errorBox = $('#emailOTPClientError');

                otpInput.removeClass('is-invalid');
                errorBox.addClass('d-none').text('');

                // OTP Validation
                if (!/^\d{6}$/.test(otp)) {
                    otpInput.addClass('is-invalid').focus();
                    errorBox.text('Please Enter a Valid 6-Digits OTP').removeClass('d-none');
                    return;
                }

                btn.text('Verifying...').css('pointer-events', 'none');

                $.ajax({
                    url: "",
                    type: "POST",
                    data: {
                        email: $('#email').val().trim(),
                        otp,
                        _token: "{{ csrf_token() }}"
                    },

                    success(response) {
                        if (response.status === 'verified') {

                            $('#isEmailVerified').val(1);

                            // Stop Countdown Immediately
                            if (emailOTPTimer) {
                                clearInterval(emailOTPTimer);
                                emailOTPTimer = null;
                            }

                            // Hide OTP Sent Message Immediately
                            $('#emailOTPSuccess').addClass('d-none');

                            // Final Verified UI
                            $('#emailOTPWrapper').slideUp(300);
                            $('#sendOTPToEmail').hide();
                            $('#emailVerifiedIcon').removeClass('d-none');

                        } else {
                            otpInput.addClass('is-invalid');
                            errorBox.text('Invalid OTP').removeClass('d-none');
                        }
                    },
                    error(xhr) {
                        if (xhr.status === 422) {
                            $('#emailOTPClientError').text('OTP Verification Failed. Please Try Again.').removeClass('d-none');
                        }
                    },
                    complete() {
                        btn.text('Verify OTP').css('pointer-events', 'auto');
                    }
                });
            });

            /* ================== HELPERS ================== */

            // Reset Send Button
            function resetEmailButton(btn) {
                btn.text('Send OTP').data('state', 'idle').css('pointer-events', 'auto');
            }

            // Reset OTP Input & Error
            function resetEmailOTPField() {
                $('#emailOTP').val('').removeClass('is-invalid');
                $('#emailOTPClientError').addClass('d-none').text('');
            }

            $('#email').on('input', function () {
                if ($('#isEmailVerified').val() == 1) {
                    resetEmailVerification();
                }
            });

            function resetEmailVerification() {
                $('#email_verified').val(0);
                $('#emailVerifiedIcon').addClass('d-none');
                $('#sendOTPToEmail').show().text('Send OTP').data('state', 'idle');
                $('#email').prop('readonly', false).removeClass('readonly-disabled');
            }

            /* ================== END EMAIL OTP SENDING SCRIPT ================== */


            // Toggle for Password Field
            $('#togglePassword').on('click', function () {
                const password = $('#password');
                const passwordIcon = $('#togglePasswordIcon');

                if (password.attr('type') === 'password') {
                    password.attr('type', 'text');
                    passwordIcon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
                } else {
                    password.attr('type', 'password');
                    passwordIcon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
                }
            });

            // Toggle for Confirm_Password Field
            $('#toggleConfirmPassword').on('click', function () {
                const confirmPassword = $('#confirm_password');
                const confirmPasswordIcon = $('#toggleConfirmPasswordIcon');

                if (confirmPassword.attr('type') === 'password') {
                    confirmPassword.attr('type', 'text');
                    confirmPasswordIcon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
                } else {
                    confirmPassword.attr('type', 'password');
                    confirmPasswordIcon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
                }
            });

            function checkPasswordMatch() {
                const password = $('#password').val();
                const confirmPassword = $('#confirm_password').val();

                if (confirmPassword !== '' && password !== confirmPassword) {
                    $('#confirm_password').addClass('is-invalid');
                    $('#confirmPasswordError').removeClass('d-none');
                    return false;
                } else {
                    $('#confirm_password').removeClass('is-invalid');
                    $('#confirmPasswordError').addClass('d-none');
                    return true;
                }
            }

            // Check While Typing
            $('#password, #confirm_password').on('keyup', function () {
                checkPasswordMatch();
            });

            // Check on Form Submit
            $('form').on('submit', function (exception) {
                if (!checkPasswordMatch()) { exception.preventDefault(); }
            });
        });
    </script>
@endpush