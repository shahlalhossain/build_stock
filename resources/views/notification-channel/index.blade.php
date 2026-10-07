@extends('layout.master')

@section('title', __('Notification Channel'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Notification Channels') }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification-channel.create')
                                    <a href="{{ route('notification-channel.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                @endcan
                                @can('notification-channel.trash')
                                    <a href="{{ route('notification-channel.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
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

                            <table id="notification-channels-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
            const toastMessage = sessionStorage.getItem('notificationChannelToastMessage');
            const toastType = sessionStorage.getItem('notificationChannelToastType');

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
                sessionStorage.removeItem('notificationChannelToastMessage');
                sessionStorage.removeItem('notificationChannelToastType');
            }

            $(document).on('click', '.toggle-notification-channel', function () {
                const channelID = $(this).data('notification-channel-id');
                const isActive = $(this).data('is-active') === 1;
                Swal.fire({
                    title: 'Are You Sure?',
                    text: isActive ? 'You want to Disable this Channel' : 'You want to Enable this Channel',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: isActive ? 'Yes, Disable' : 'Yes, Enable',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/notification-channel/' + channelID + '/toggle-status',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('notificationChannelToastMessage', response.message || 'Notification Channel Status Updated Successfully');
                                sessionStorage.setItem('notificationChannelToastType', 'success');
                                window.location.reload();
                            },
                            error: function (xhr) {
                                sessionStorage.setItem('notificationChannelToastMessage', xhr.responseJSON?.message || 'There was an Issue on Changing the Channel Status.');
                                sessionStorage.setItem('notificationChannelToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'No Changes were Made.', 'info');
                    }
                });
            });

            $(document).on('click', '.destroy-notification-channel', function () {
                const channelID = $(this).data('notification-channel-id');
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
                            url: '/notification-channel/' + channelID,
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('notificationChannelToastMessage', response.message || 'Notification Channel Destroyed Successfully');
                                sessionStorage.setItem('notificationChannelToastType', 'success');
                                window.location.href = '/notification-channel';
                            },
                            error: function (xhr) {
                                sessionStorage.setItem('notificationChannelToastMessage', xhr.responseJSON?.message || 'There was an Issue on Destroying the Record.');
                                sessionStorage.setItem('notificationChannelToastType', 'failed');
                                window.location.href = '/notification-channel';
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
