@extends('layout.master')

@section('title', __('Notification Setting'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Notification Settings') }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification-setting.create')
                                    <a href="{{ route('notification-setting.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                @endcan
                                @can('notification-setting.trash')
                                    <a href="{{ route('notification-setting.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">

                            @if (session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @elseif(session('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ session('error') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <table id="notification-settings-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->


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
    @if (session('error'))
        <script>
            Toastify({
                text: @json(session('error')),
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                className: "failed-toast",
                stopOnFocus: true
            }).showToast();
        </script>
    @endif

@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function () {
            const csrfToken = "{{ csrf_token() }}";

            /*
            |--------------------------------------------------------------------------
            | PAGE LOAD TOAST
            |--------------------------------------------------------------------------
            | Shows Success/Failed Toast After Page Reload
            |--------------------------------------------------------------------------
            */
            const toastMessage = sessionStorage.getItem('notificationSettingToastMessage');
            const toastType = sessionStorage.getItem('notificationSettingToastType');

            if (toastMessage) {
                Toastify({
                    text: toastMessage,
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    className: toastType === 'success' ? 'success-toast' : 'failed-toast',
                    stopOnFocus: true
                }).showToast();

                // Remove After Display the Toast Message
                sessionStorage.removeItem('notificationSettingToastMessage');
                sessionStorage.removeItem('notificationSettingToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | ENABLE / DISABLE NOTIFICATION SETTING
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.toggle-notification-setting', function () {
                const settingID = $(this).data('notification-setting-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Change the Status of this Setting',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Change',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/notification-setting/' + settingID + '/toggle-status',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('notificationSettingToastMessage', response.message || 'Status Updated Successfully');
                                sessionStorage.setItem('notificationSettingToastType', 'success');
                                window.location.href = '/notification-setting';
                            },
                            error: function (xhr) {
                                sessionStorage.setItem('notificationSettingToastMessage', xhr.responseJSON?.message || 'There was an Issue on Updating the Status.');
                                sessionStorage.setItem('notificationSettingToastType', 'failed');
                                window.location.href = '/notification-setting';
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.destroy-notification-setting', function() {
                const settingID = $(this).data('notification-setting-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Destroy this Data',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Destroy',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/notification-setting/' + settingID,
                            method: 'DELETE',
                            data: { _token: csrfToken},
                            success: function (response) {
                                // Store Success Toast Message & Type
                                sessionStorage.setItem( 'notificationSettingToastMessage', response.message || 'Notification Setting Destroyed Successfully' );
                                sessionStorage.setItem( 'notificationSettingToastType', 'success' );
                                // Reload/Redirect to Notification Setting List Page
                                window.location.href = '/notification-setting';
                            },
                            error: function (xhr, status, error) {
                                // Get Laravel Error Message if Available
                                const message = xhr.responseJSON?.message || 'There was an Issue on Destroying the Record.';
                                // Store Failed Toast Message and Type
                                sessionStorage.setItem( 'notificationSettingToastMessage', message ); sessionStorage.setItem( 'notificationSettingToastType', 'failed' );
                                // Reload/Redirect to Notification Setting List Page
                                window.location.href = '/notification-setting';
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });
        });

    </script>
@endpush
