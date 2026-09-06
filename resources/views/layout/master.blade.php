<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      data-layout="vertical"
      data-bs-theme="light"
      data-topbar="light"
      data-sidebar="light"
      data-sidebar-size="lg"
      data-sidebar-image="none"
      data-preloader="disable"
      data-theme="light"
      data-theme-colors="default">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Microzen') }} | @yield('title')</title>

    <script>
        // Apply the saved theme before any CSS loads, so the page never paints
        // in light mode first and then flips (this must stay the first thing in <head>).
        if (localStorage.getItem('theme-mode') === 'dark') {
            var html = document.documentElement;
            html.setAttribute('data-bs-theme', 'dark');
            html.setAttribute('data-sidebar', 'dark');
        }
    </script>

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}"> <!-- App Favicon -->


    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/multi.js/multi.min.css') }}"> <!-- MultiJS CSS -->
{{--    <link rel="stylesheet" href="{{ asset('assets/libs/@tarekraafat/autocomplete.js/css/autoComplete.css') }}"> <!-- Autocomplete CSS -->--}}

    <!-- Core layout CSS loads first so the sidebar/header chrome is styled before any
         page content paints, avoiding a flash of unstyled/wrongly-sized content. -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}"> <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/icons.min.css') }}"> <!-- Icons CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/app.min.css') }}"> <!-- App CSS-->

    <link rel="stylesheet" href="{{ asset('assets/libs/dropzone/dropzone.css') }}" type="text/css"> <!-- Dropzone CSS -->

    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"> <!--DataTable Responsive CSS-->
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/sweetalert2/sweetalert2.min.css') }}"> <!-- Sweet Alert CSS -->

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/jsvectormap/jsvectormap.min.css') }}"> <!-- Plugin CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/libs/choices.js/public/assets/styles/choices.min.css') }}"> <!-- Choice CSS for Multi Select -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/custom.min.css') }}"> <!-- Custom CSS-->

    <script src="{{ asset('assets/js/layout.js') }}"></script> <!-- Layout Config JS -->

    <!-- Scripts -->
    {{--@vite(['resources/css/app.css', 'resources/js/app.js'])--}}

    <style>
        .divider {
            height: 0;
            margin: 0;
            overflow: hidden;
            border-top: 1px solid #DAD8D857;
            opacity: 1;
        }

        .horizontal-line {
            margin: 0;
            padding: 0;
            width: 100%;
            align-content: center;
            border-top: 1px solid #dee2e6;
        }

        /* Submenu Items with Icons */
        .menu-dropdown .nav-sm .nav-link {
            padding-bottom: 0.35rem !important;
        }

        /* Adjust Icon Spacing */
        .menu-dropdown .nav-sm .nav-link i {
            font-size: 14px !important;
            min-width: 1.10rem !important;
        }

        /* Remove the Dash Prefix */
        .menu-dropdown .nav-sm .nav-link::before {
            display: none !important;
        }

        /* Active Menu Background Color */
        .active-menu {
            background-color: rgba(218, 216, 216, 0.34);
        }
        /* Active Menu Text Color */
        .active-menu .nav-link {
            color: dodgerblue !important;
            font-weight: 400;
        }

        /* Active Menu Icon Color */
        .active-menu .nav-link i {
            color: dodgerblue !important;
        }

        /* Chrome, Safari, Edge, Opera */
        input[type=number]::-webkit-outer-spin-button,
        input[type=number]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* Firefox */
        input[type=number] {
            -moz-appearance: textfield;
        }

        label.form-mandatory::after {
            content: " *";
            color: red;
        }

        #back-to-top {
            bottom: 40px !important;
            right: 22px !important;
        }

    </style>

    @stack('styles')
</head>
<body>
<!-- Start Layout Wrapper -->
<div id="layout-wrapper">

    <!-- Start Header -->
    @include('layout.includes.header')
    <!-- End Header -->

    <!-- Start Left Sidebar Menu -->
    @include('layout.includes.sidebar')
    <!-- End Left Sidebar Menu -->

    <!-- Start Vertical Overlay-->
    <div class="vertical-overlay"></div>
    <!-- End Vertical Overlay-->

    <!-- Start Main Content -->
    <div class="main-content">

        <!-- Start Page Content -->
       @yield('content')
        <!-- End Page Content -->

        <!-- Start Footer -->
        @include('layout.includes.footer')
        <!-- End Footer -->
    </div>
    <!-- End Main Content-->

</div>
<!-- END Layout Wrapper -->


<!-- Start Back-to-Top -->
<button onclick="topFunction()" class="btn btn-danger btn-icon" id="back-to-top">
    <i class="ri-arrow-up-line"></i>
</button>
<!-- End Back-to-Top -->

<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!--datatable js-->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="{{ asset('assets/js/pages/select2.init.js') }}"></script>

{{--<script async src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2PMHASZHdwCcWzqoDdtnCDQp0g9N6u9o&callback=console.debug&libraries=maps,marker&v=beta"></script>--}}
{{--<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC2PMHASZHdwCcWzqoDdtnCDQp0g9N6u9o&callback=initMap" async defer></script>--}}

<script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>
<script src="{{ asset('assets/libs/feather-icons/feather.min.js') }}"></script>
<script src="{{ asset('assets/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
<script src="{{ asset('assets/js/plugins.js') }}"></script>
<script src="{{ asset('assets/js/pages/card.init.js') }}"></script>

<!-- ApexCharts -->
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>

<!-- Vector Map-->
<script src="{{ asset('assets/libs/jsvectormap/jsvectormap.min.js') }}"></script>
<script src="{{ asset('assets/libs/jsvectormap/maps/world-merc.js') }}"></script>

<!-- Dashboard Init -->
<script src="{{ asset('assets/js/pages/dashboard-analytics.init.js') }}"></script>

<!-- DataTable JS -->
<script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>

<!-- Profile Setting JS -->
<script src="{{ asset('assets/js/pages/profile-setting.init.js') }}"></script>

<!-- Sweet Alerts JS -->
<script src="{{ asset('assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<!-- Sweet Alert Init JS-->
<script src="{{ asset('assets/js/pages/sweetalerts.init.js') }}"></script>

<!-- Validation JS -->
<script src="{{ asset('assets/js/pages/form-validation.init.js') }}"></script>

<!-- Dropzone min -->
<script src="{{ asset('assets/libs/dropzone/dropzone-min.js') }}"></script>
<script src="{{ asset('/assets/js/pages/form-file-upload.init.js') }}"></script>

<!-- ChoiceJS for Multi Select -->
<script src="{{ asset('assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

<!-- Toastify -->
<script src="{{ asset('assets/libs/toastify-js/src/toastify.js') }}"></script>

<!-- MultiJS -->
<script src="{{ asset('assets/libs/multi.js/multi.min.js') }}"></script>
<!-- Autocomplete JS -->
{{--<script src="{{ asset('assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js') }}"></script>--}}

<!-- init js -->
<script src="{{ asset('assets/js/pages/form-advanced.init.js') }}"></script>
{{--<!-- input spin init -->--}}
{{--<script src="{{ asset('assets/js/pages/form-input-spin.init.js') }}"></script>--}}
{{--<!-- input flag init -->--}}
{{--<script src="{{ asset('assets/js/pages/flag-input.init.js') }}"></script>--}}

<!-- App JS -->
<script src="{{ asset('assets/js/app.js') }}"></script>

@stack('scripts')

<script>
    $('.light-dark-mode').click(() => {
        const $html = $('html');
        const isDark = $html.attr('data-bs-theme') === 'dark';
        const newMode = isDark ? 'light' : 'dark';

        $html.attr('data-bs-theme', newMode);
        $html.attr('data-sidebar', newMode);

        localStorage.setItem('theme-mode', newMode);
    });

    $(window).scroll(function () {
        if ($(this).scrollTop() > 200) {
            $('#back-to-top').fadeIn();
        } else {
            $('#back-to-top').fadeOut();
        }
    });

    function topFunction() {
        $('html, body').animate({ scrollTop: 0 }, 600);
    }
</script>

</body>

</html>
