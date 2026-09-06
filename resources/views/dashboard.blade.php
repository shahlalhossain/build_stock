@extends('layout.master')

@section('title', __('Dashboard'))

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">

{{--            <div class="row">--}}
{{--                <div class="col-sm-12">--}}
{{--                    <button class="btn btn-success"><i class="ri-add-line"></i> Create</button>--}}
{{--                    <button class="btn btn-primary"><i class="ri-list-check-2"></i> Go to List</button>--}}
{{--                    <button class="btn btn-dark"><i class="ri-delete-bin-2-line"></i> Trash Box</button>--}}
{{--                    <button class="btn btn-secondary"><i class="ri-eye-line"></i> Show</button>--}}
{{--                    <button class="btn btn-info"><i class="ri-edit-line"></i> Edit</button>--}}
{{--                    <button class="btn btn-warning"><i class="ri-delete-bin-line"></i> Destroy</button>--}}
{{--                    <button class="btn btn-soft-success"><i class="ri-recycle-line"></i> Restore</button>--}}
{{--                    <button class="btn btn-danger"><i class="ri-close-line"></i> Delete</button>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <br>--}}
{{--            <div class="row">--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <!-- card -->--}}
{{--                    <div class="card card-animate">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="flex-grow-1">--}}
{{--                                    <p class="text-uppercase fw-medium text-muted mb-0">Total Earnings</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0">--}}
{{--                                    <h5 class="text-success fs-14 mb-0">--}}
{{--                                        <i class="ri-arrow-right-up-line fs-13 align-middle"></i> +16.24 %--}}
{{--                                    </h5>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex align-items-end justify-content-between mt-4">--}}
{{--                                <div>--}}
{{--                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<span class="counter-value" data-target="559.25">0</span>k</h4>--}}
{{--                                    <a href="" class="text-decoration-underline">View net earnings</a>--}}
{{--                                </div>--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-success-subtle rounded fs-3 material-shadow">--}}
{{--                                        <i class="bx bx-dollar-circle text-success"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div><!-- end card -->--}}
{{--                </div><!-- end col -->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <!-- card -->--}}
{{--                    <div class="card card-animate bg-info">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="flex-grow-1">--}}
{{--                                    <p class="text-uppercase fw-medium text-white-50 mb-0">Orders</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0">--}}
{{--                                    <h5 class="text-warning fs-14 mb-0">--}}
{{--                                        <i class="ri-arrow-right-down-line fs-13 align-middle"></i> -3.57 %--}}
{{--                                    </h5>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex align-items-end justify-content-between mt-4">--}}
{{--                                <div>--}}
{{--                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4 text-white"><span class="counter-value" data-target="36894">0</span></h4>--}}
{{--                                    <a href="" class="text-decoration-underline text-white-50">View all orders</a>--}}
{{--                                </div>--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-white bg-opacity-25 rounded fs-3 material-shadow">--}}
{{--                                        <i class="bx bx-shopping-bag text-white"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div><!-- end card -->--}}
{{--                </div><!-- end col -->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <!-- card -->--}}
{{--                    <div class="card card-animate">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="flex-grow-1">--}}
{{--                                    <p class="text-uppercase fw-medium text-muted mb-0">Customers</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0">--}}
{{--                                    <h5 class="text-success fs-14 mb-0">--}}
{{--                                        <i class="ri-arrow-right-up-line fs-13 align-middle"></i> +29.08 %--}}
{{--                                    </h5>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex align-items-end justify-content-between mt-4">--}}
{{--                                <div>--}}
{{--                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="183.35">0</span>M</h4>--}}
{{--                                    <a href="" class="text-decoration-underline">See details</a>--}}
{{--                                </div>--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-warning-subtle rounded fs-3 material-shadow">--}}
{{--                                        <i class="bx bx-user-circle text-warning"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div><!-- end card -->--}}
{{--                </div><!-- end col -->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <!-- card -->--}}
{{--                    <div class="card card-animate">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="flex-grow-1">--}}
{{--                                    <p class="text-uppercase fw-medium text-muted mb-0">My Balance</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0">--}}
{{--                                    <h5 class="text-muted fs-14 mb-0">+0.00 %</h5>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="d-flex align-items-end justify-content-between mt-4">--}}
{{--                                <div>--}}
{{--                                    <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<span class="counter-value" data-target="165.89">0</span>k</h4>--}}
{{--                                    <a href="" class="text-decoration-underline">Withdraw money</a>--}}
{{--                                </div>--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-primary-subtle rounded fs-3 material-shadow">--}}
{{--                                        <i class="bx bx-wallet text-primary"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div><!-- end card -->--}}
{{--                </div><!-- end col -->--}}
{{--            </div>--}}
{{--            <div class="row">--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <div>--}}
{{--                                    <p class="fw-medium text-muted mb-0">Users</p>--}}
{{--                                    <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="28.05">0</span>k</h2>--}}
{{--                                    <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0"><i class="ri-arrow-up-line align-middle"></i> 16.24 % </span> vs. previous month</p>--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <div class="avatar-sm flex-shrink-0">--}}
{{--                                        <span class="avatar-title bg-info-subtle rounded-circle fs-2">--}}
{{--                                            <i data-feather="users" class="text-info material-shadow"></i>--}}
{{--                                        </span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div> <!-- end card-->--}}
{{--                </div> <!-- end col-->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <div>--}}
{{--                                    <p class="fw-medium text-muted mb-0">Sessions</p>--}}
{{--                                    <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="97.66">0</span>k</h2>--}}
{{--                                    <p class="mb-0 text-muted"><span class="badge bg-light text-danger mb-0"><i class="ri-arrow-down-line align-middle"></i> 3.96 % </span> vs. previous month</p>--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <div class="avatar-sm flex-shrink-0">--}}
{{--                                        <span class="avatar-title bg-info-subtle rounded-circle fs-2 material-shadow">--}}
{{--                                            <i data-feather="activity" class="text-info"></i>--}}
{{--                                        </span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div> <!-- end card-->--}}
{{--                </div> <!-- end col-->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate bg-primary">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <div>--}}
{{--                                    <p class="fw-medium text-white-50 mb-0">Avg. Visit Duration</p>--}}
{{--                                    <h2 class="mt-4 ff-secondary fw-semibold text-white"><span class="counter-value" data-target="3">0</span>m <span class="counter-value" data-target="40">0</span>sec</h2>--}}
{{--                                    <p class="mb-0 text-white-50"><span class="badge bg-white bg-opacity-25 text-white mb-0"><i class="ri-arrow-down-line align-middle"></i> 0.24 % </span> vs. previous month</p>--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <div class="avatar-sm flex-shrink-0">--}}
{{--                                        <span class="avatar-title bg-white bg-opacity-25 rounded-circle fs-2 material-shadow">--}}
{{--                                            <i data-feather="clock" class="text-white"></i>--}}
{{--                                        </span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div> <!-- end card-->--}}
{{--                </div> <!-- end col-->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex justify-content-between">--}}
{{--                                <div>--}}
{{--                                    <p class="fw-medium text-muted mb-0">Bounce Rate</p>--}}
{{--                                    <h2 class="mt-4 ff-secondary fw-semibold"><span class="counter-value" data-target="33.48">0</span>%</h2>--}}
{{--                                    <p class="mb-0 text-muted"><span class="badge bg-light text-success mb-0"><i class="ri-arrow-up-line align-middle"></i> 7.05 % </span> vs. previous month</p>--}}
{{--                                </div>--}}
{{--                                <div>--}}
{{--                                    <div class="avatar-sm flex-shrink-0">--}}
{{--                                        <span class="avatar-title bg-info-subtle rounded-circle fs-2 material-shadow">--}}
{{--                                            <i data-feather="external-link" class="text-info"></i>--}}
{{--                                        </span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div> <!-- end card-->--}}
{{--                </div> <!-- end col-->--}}
{{--            </div>--}}
{{--            <div class="row">--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate bg-success card-height-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-white bg-opacity-25 text-white rounded-2 fs-2 material-shadow">--}}
{{--                                        <i class="bx bx-shopping-bag"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                                <div class="flex-grow-1 ms-3">--}}
{{--                                    <p class="text-uppercase fw-medium text-white-50 mb-3">Total Sales</p>--}}
{{--                                    <h4 class="fs-4 mb-3 text-white"><span class="counter-value" data-target="2045">0</span></h4>--}}
{{--                                    <p class="text-white-50 mb-0">From 1930 last year</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0 align-self-center">--}}
{{--                                    <span class="badge bg-white bg-opacity-25 text-white fs-12"><i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>6.11 %<span></span></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div>--}}
{{--                </div> <!-- end col-->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-warning-subtle text-warning rounded-2 fs-2 material-shadow">--}}
{{--                                        <i class="bx bxs-user-account"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                                <div class="flex-grow-1 ms-3">--}}
{{--                                    <p class="text-uppercase fw-medium text-muted mb-3">Number of Users</p>--}}
{{--                                    <h4 class="fs-4 mb-3"><span class="counter-value" data-target="7522">0</span></h4>--}}
{{--                                    <p class="text-muted mb-0">From 9530 last year</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0 align-self-center">--}}
{{--                                    <span class="badge bg-danger-subtle text-danger fs-12"><i class="ri-arrow-down-s-line fs-13 align-middle me-1"></i>10.35 %</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div>--}}
{{--                </div> <!-- end col-->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-danger-subtle text-danger rounded-2 fs-2 material-shadow">--}}
{{--                                        <i class="bx bxs-badge-dollar"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                                <div class="flex-grow-1 ms-3">--}}
{{--                                    <p class="text-uppercase fw-medium text-muted mb-3">Total Revenue</p>--}}
{{--                                    <h4 class="fs-4 mb-3">$<span class="counter-value" data-target="2845.05">0</span></h4>--}}
{{--                                    <p class="text-muted mb-0">From $1,750.04 last year</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0 align-self-center">--}}
{{--                                    <span class="badge bg-success-subtle text-success fs-12"><i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>22.96 %<span></span></span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div>--}}
{{--                </div> <!-- end col-->--}}

{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="d-flex align-items-center">--}}
{{--                                <div class="avatar-sm flex-shrink-0">--}}
{{--                                    <span class="avatar-title bg-info-subtle text-info rounded-2 fs-2 material-shadow">--}}
{{--                                        <i class="bx bx-store-alt"></i>--}}
{{--                                    </span>--}}
{{--                                </div>--}}
{{--                                <div class="flex-grow-1 ms-3">--}}
{{--                                    <p class="text-uppercase fw-medium text-muted mb-3">Number of Stores</p>--}}
{{--                                    <h4 class="fs-4 mb-3"><span class="counter-value" data-target="405">0</span>k</h4>--}}
{{--                                    <p class="text-muted mb-0">From 308 last year</p>--}}
{{--                                </div>--}}
{{--                                <div class="flex-shrink-0 align-self-center">--}}
{{--                                    <span class="badge bg-success-subtle text-success fs-12"><i class="ri-arrow-up-s-line fs-13 align-middle me-1"></i>16.31 %</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div><!-- end card body -->--}}
{{--                    </div>--}}
{{--                </div> <!-- end col-->--}}
{{--            </div>--}}
{{--            <div class="row">--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="dropdown float-end">--}}
{{--                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                                    <span class="text-muted fs-18"><i class="mdi mdi-dots-vertical"></i></span>--}}
{{--                                </a>--}}
{{--                                <div class="dropdown-menu dropdown-menu-end">--}}
{{--                                    <a class="dropdown-item" href="#">Favorite</a>--}}
{{--                                    <a class="dropdown-item" href="#">Apply Now</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="mb-4 pb-2">--}}
{{--                                <img src="{{ asset('assets/images/companies/img-3.png') }}" alt="" class="avatar-sm">--}}
{{--                            </div>--}}
{{--                            <a href="#">--}}
{{--                                <h6 class="fs-15 fw-semibold">Marketing Director <span class="text-muted fs-13">(2-4 Yrs Exp.)</span></h6>--}}
{{--                            </a>--}}
{{--                            <p class="text-muted mb-0"><i class="ri-building-line align-bottom"></i> Themesbrand <span class="ms-2"><i class="ri-map-pin-2-line align-bottom"></i> California</span></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="dropdown float-end">--}}
{{--                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                                    <span class="text-muted fs-18"><i class="mdi mdi-dots-vertical"></i></span>--}}
{{--                                </a>--}}
{{--                                <div class="dropdown-menu dropdown-menu-end">--}}
{{--                                    <a class="dropdown-item" href="#">Favorite</a>--}}
{{--                                    <a class="dropdown-item" href="#">Apply Now</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="mb-4 pb-2">--}}
{{--                                <img src="{{ asset('assets/images/companies/img-4.png') }}" alt="" class="avatar-sm">--}}
{{--                            </div>--}}
{{--                            <a href="#">--}}
{{--                                <h6 class="fs-15 fw-semibold">Sr. Web Designer <span class="text-muted fs-13">(2+ Yrs Exp.)</span></h6>--}}
{{--                            </a>--}}
{{--                            <p class="text-muted mb-0"><i class="ri-building-line align-bottom"></i> Themesbrand <span class="ms-2"><i class="ri-map-pin-2-line align-bottom"></i> California</span></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100 bg-primary-subtle shadow-none bg-opacity-10">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="dropdown float-end">--}}
{{--                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                                    <span class="text-muted fs-18"><i class="mdi mdi-dots-vertical"></i></span>--}}
{{--                                </a>--}}
{{--                                <div class="dropdown-menu dropdown-menu-end">--}}
{{--                                    <a class="dropdown-item" href="#">Favorite</a>--}}
{{--                                    <a class="dropdown-item" href="#">Apply Now</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="mb-4 pb-2">--}}
{{--                                <img src="{{ asset('assets/images/companies/img-6.png') }}" alt="" class="avatar-sm">--}}
{{--                            </div>--}}
{{--                            <a href="#">--}}
{{--                                <h6 class="fs-15 fw-semibold">Sr. Web Designer <span class="text-muted fs-13">(2+ Yrs Exp.)</span></h6>--}}
{{--                            </a>--}}
{{--                            <p class="text-muted mb-0"><i class="ri-building-line align-bottom"></i> Themesbrand <span class="ms-2"><i class="ri-map-pin-2-line align-bottom"></i> California</span></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="col-xl-3 col-md-6">--}}
{{--                    <div class="card card-animate card-height-100 bg-info-subtle shadow-none bg-opacity-10">--}}
{{--                        <div class="card-body">--}}
{{--                            <div class="dropdown float-end">--}}
{{--                                <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                                    <span class="text-muted fs-18"><i class="mdi mdi-dots-vertical"></i></span>--}}
{{--                                </a>--}}
{{--                                <div class="dropdown-menu dropdown-menu-end">--}}
{{--                                    <a class="dropdown-item" href="#">Favorite</a>--}}
{{--                                    <a class="dropdown-item" href="#">Apply Now</a>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="mb-4 pb-2">--}}
{{--                                <img src="{{ asset('assets/images/companies/img-8.png') }}" alt="" class="avatar-sm">--}}
{{--                            </div>--}}
{{--                            <a href="#">--}}
{{--                                <h6 class="fs-15 fw-semibold">Sr. Web Designer <span class="text-muted fs-13">(2+ Yrs Exp.)</span></h6>--}}
{{--                            </a>--}}
{{--                            <p class="text-muted mb-0"><i class="ri-building-line align-bottom"></i> Themesbrand <span class="ms-2"><i class="ri-map-pin-2-line align-bottom"></i> California</span></p>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div> <!-- end col-->--}}
{{--            </div>--}}


        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->
@endsection


