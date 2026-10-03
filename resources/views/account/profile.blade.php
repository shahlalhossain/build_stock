@extends('layout.master')

@section('title', __('Profile'))

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
                                    {{-- TODO: For Mobile-View Need to Load Meaningful and Relavent Icon instead of Title-Text --}}
                                    <a class="nav-link active" data-bs-toggle="tab" href="#profileDetails" role="tab">{{ __('Profile Details') }}</a>
                                </li>
                                <li class="nav-item">
                                    {{-- TODO: For Mobile-View Need to Load Meaningful and Relavent Icon instead of Title-Text --}}
                                    <a class="nav-link" data-bs-toggle="tab" href="#rolesPermissions" role="tab">{{ __('Roles & Permissions') }}</a>
                                </li>
                                <li class="nav-item">
                                    {{-- TODO: For Mobile-View Need to Load Meaningful and Relavent Icon instead of Title-Text --}}
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">{{ __('Change Password') }}</a>
                                </li>
                                <li class="nav-item">
                                    {{-- TODO: For Mobile-View Need to Load Meaningful and Relavent Icon instead of Title-Text --}}
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePasswordLogs" role="tab">{{ __('Password Change Logs') }}</a>
                                </li>
                                <li class="nav-item">
                                    {{-- TODO: For Mobile-View Need to Load Meaningful and Relavent Icon instead of Title-Text --}}
                                    <a class="nav-link" data-bs-toggle="tab" href="#loginHistory" role="tab">{{ __('Login History') }}</a>
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
                                    {{-- TODO: LoggedIn User's Assigned Roles and Permissions --}}
                                </div>
                                <div class="tab-pane" id="changePassword" role="tabpanel">
                                    {{-- TODO: LoggedIn User's Password Change Functionality --}}
                                    <form action="javascript:void(0);">
                                        <div class="row g-2">
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="oldpasswordInput" class="form-label">Old Password*</label>
                                                    <input type="password" class="form-control" id="oldpasswordInput" placeholder="Enter current password">
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="newpasswordInput" class="form-label">New Password*</label>
                                                    <input type="password" class="form-control" id="newpasswordInput" placeholder="Enter new password">
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-4">
                                                <div>
                                                    <label for="confirmpasswordInput" class="form-label">Confirm Password*</label>
                                                    <input type="password" class="form-control" id="confirmpasswordInput" placeholder="Confirm password">
                                                </div>
                                            </div>
                                            <!--end col-->
                                            <div class="col-lg-6">
                                                <div class="mb-3">
                                                    <a href="javascript:void(0);" class="link-primary text-decoration-underline">Forgot Password ?</a>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 text-end">

                                                <button type="submit" class="btn btn-success">Change Password</button>

                                            </div>
                                        </div>
                                        <!--end col-->
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
                                    <img src="assets/images/users/avatar-1.jpg" class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow" alt="user-profile-image">
                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <input id="profile-img-file-input" type="file" class="profile-img-file-input">
                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <h5 class="fs-16 mb-1">{{ auth()->user()->name }}</h5>
                                <p class="text-muted mb-0">
                                    {{-- TODO: LoggedIn User's Designation --}}
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

    </script>
@endpush
