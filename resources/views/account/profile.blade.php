@extends('layout.master')

@section('title', __('Profile'))

@section('content')

    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->

            <div class="row pt-5">
                <div class="col-xxl-3">
                    <div class="card mt-n5">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
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
                                <p class="text-muted mb-0">Lead Developer</p>
                            </div>
                        </div>
                    </div>
                    <!--end card-->
                </div>
                <!--end col-->
                <div class="col-xxl-9">
                    <div class="card mt-xxl-n5">
                        <div class="card-header">
                            <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#profileDetails" role="tab">Profile Details</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#changePassword" role="tab">Change Password</a>
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
                                        <h5 class="card-title">Personal Details</h5>
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

                                    <div class="mt-4 mb-3 border-bottom pb-2 pt-5">
                                        <div class="float-end"></div>
                                        <h5 class="card-title">Employment Details</h5>
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
                                <div class="tab-pane" id="changePassword" role="tabpanel">
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
                            </div>
                        </div>
                    </div>
                </div>
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
