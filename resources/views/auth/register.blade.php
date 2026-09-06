<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">

<head>

    <meta charset="utf-8" />
    <title>Microzen | Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="" />
    <meta name="author" content="Analyzen" />>
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Layout config Js -->
    <script src="{{ asset('assets/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <!-- Icons Css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/icons.min.css') }}" />
    <!-- App Css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/app.min.css') }}" />
    <!-- custom Css-->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.min.css') }}" />

</head>

<body>

<div class="auth-page-wrapper pt-5">
    <!-- auth page bg -->
    <div class="auth-one-bg-position auth-one-bg" id="auth-particles">
        <div class="bg-overlay"></div>

        <div class="shape">
            <svg xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 1440 120">
                <path d="M 0,36 C 144,53.6 432,123.2 720,124 C 1008,124.8 1296,56.8 1440,40L1440 140L0 140z"></path>
            </svg>
        </div>
    </div>

    <!-- auth page content -->
    <div class="auth-page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center mt-sm-5 mb-4 text-white-50">
                        <div>
                            <a href="{{ route('home') }}" class="d-inline-block auth-logo">
                                {{--<img src="assets/images/logo-light.png" alt="" height="20">--}}
                            </a>
                        </div>
                        {{--<p class="mt-3 fs-15 fw-medium">Premium Admin & Dashboard Template</p>--}}
                    </div>
                </div>
            </div>
            <!-- end row -->

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card mt-4 card-bg-fill">
                        <div class="card-header">
                            <div class="text-center mt-2">
                                <h5 class="text-primary">Welcome Back</h5>
                                <p class="text-muted">Register to Continue with Microzen2.0</p>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="p-2 mt-4">
                                <form class="needs-validation" novalidate action="{{ route('register') }}" method="POST">
                                    @csrf

                                    <div class="row mb-3">
                                        <label for="name" class="col-md-4 col-form-label text-md-end">{{ __('Name') }}<span class="text-danger">*</span></label>

                                        <div class="col-md-6">
                                            <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus>
                                            @error('name')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('Email Address') }}<span class="text-danger">*</span></label>
                                        <div class="col-md-6">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email">
                                            @error('email')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="mobile" class="col-md-4 col-form-label text-md-end">{{ __('Mobile Number') }}<span class="text-danger">*</span></label>
                                        <div class="col-md-6">
                                            <input id="mobile" type="text" class="form-control @error('mobile') is-invalid @enderror" name="mobile" value="{{ old('mobile') }}" required autocomplete="mobile">
                                            @error('mobile')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}<span class="text-danger">*</span></label>
                                        <div class="col-md-6">
                                            <input id="password" type="password" class="form-control pe-5 password-input @error('password') is-invalid @enderror" name="password" onpaste="return false" placeholder="Password" aria-describedby="passwordInput" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required autocomplete="new_password">
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                            @error('password')<span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>@enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="password_confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}<span class="text-danger">*</span></label>
                                        <div class="col-md-6">
                                            <input id="password_confirm" type="password" class="form-control pe-5 password-input" name="password_confirmation" onpaste="return false" placeholder="Confirm Password" aria-describedby="passwordInput" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                                        </div>
                                    </div>

                                    <div id="password-contain" class="p-3 bg-light mb-2 rounded">
                                        <h5 class="fs-13">Password must contain:</h5>
                                        <p id="pass-length" class="invalid fs-12 mb-2">Minimum <b>6 Characters</b></p>
                                        <p id="pass-lower" class="invalid fs-12 mb-2">At least a <b>lowercase</b> letter (a-z)</p>
                                        <p id="pass-upper" class="invalid fs-12 mb-2">At least a <b>uppercase</b> letter (A-Z)</p>
                                        <p id="pass-number" class="invalid fs-12 mb-0">At least a <b>number</b> (0-9)</p>
                                    </div>

                                    <div class="row mb-0">
                                        <div class="col-md-6 offset-md-4 text-end">
                                            <button type="reset" class="btn btn-warning">{{ __('Reset') }}</button>
                                            <button type="submit" class="btn btn-primary">{{ __('Register') }}</button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <div class="mt-4 text-center">
                        <p class="mb-0">Already have an Account ? <a href="{{ route('login') }}" class="fw-semibold text-primary text-decoration-underline"> Login </a> </p>
                    </div>

                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
    <!-- end auth page content -->

    <!-- footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="text-center">
                        <p class="mb-0 text-muted">&copy;
                            <script>document.write(new Date().getFullYear())</script> Microzen. Crafted and Developed with <i class="mdi mdi-heart text-danger"></i> by Shahlal Hossain
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- end Footer -->
</div>
<!-- end auth-page-wrapper -->

<!-- JAVASCRIPT -->
<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('assets/js/plugins.js') }}"></script>

<!-- particles js -->
<script src="{{ asset('assets/libs/particles.js/particles.js') }}"></script>
<!-- particles app js -->
<script src="{{ asset('assets/js/pages/particles.app.js') }}"></script>
<!-- password-addon init -->
<script src="{{ asset('assets/js/pages/password-addon.init.js') }}"></script>
<!-- validation init -->
<script src="{{ assert('assets/js/pages/form-validation.init.js') }}"></script>
<!-- password create init -->
<script src="{{ asset('assets/js/pages/password-create.init.js') }}"></script>
</body>

</html>
