@extends('layout.master')

@section('title', __('Notification Channel'))

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification Channel Details') }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification-channel.create')
                                    <a href="{{ route('notification-channel.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                @endcan
                                @can('notification-channel.index')
                                    <a href="{{ route('notification-channel.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                @endcan
                                @can('notification-channel.trash')
                                    <a href="{{ route('notification-channel.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-7">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $channel->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $channel->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Driver') }}</th><td class="text-start ps-2">{{ $channel->driver }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ $channel->description }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Sort Order') }}</th><td class="text-start ps-2">{{ $channel->sort_order }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($channel->is_active)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Templates') }}</th><td class="text-start ps-2">{{ $channel->templates_count }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Notification Settings') }}</th><td class="text-start ps-2">{{ $channel->settings_count }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $channel->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $channel->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $channel->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $channel->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($channel->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $channel->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $channel->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($channel->trashed())
                                            @can('notification-channel.restore')
                                                <button class="btn btn-sm btn-soft-success restore-notification-channel" data-notification-channel-id="{{ $channel->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            @endcan
                                            @can('notification-channel.delete')
                                                <button class="btn btn-sm btn-danger delete-notification-channel" data-notification-channel-id="{{ $channel->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                            @endcan
                                        @else
                                            @can('notification-channel.edit')
                                                <a href="{{ route('notification-channel.edit', $channel->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            @endcan
                                            @can('notification-channel.destroy')
                                                <button class="btn btn-sm btn-warning destroy-notification-channel" data-notification-channel-id="{{ $channel->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                            @endcan
                                        @endif
                                    </div>
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

            $(document).on('click', '.restore-notification-channel', function () {
                const channelID = $(this).data('notification-channel-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore this Data',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/notification-channel/' + channelID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('notificationChannelToastMessage', response.message || 'Notification Channel Restored Successfully');
                                sessionStorage.setItem('notificationChannelToastType', 'success');
                                window.location.href = '/notification-channel/trash';
                            },
                            error: function (xhr) {
                                sessionStorage.setItem('notificationChannelToastMessage', xhr.responseJSON?.message || 'There was an Issue on Restoring the Record.');
                                sessionStorage.setItem('notificationChannelToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-notification-channel', function () {
                const channelID = $(this).data('notification-channel-id');
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
                            url: '/notification-channel/' + channelID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('notificationChannelToastMessage', response.message || 'Notification Channel Deleted Successfully');
                                sessionStorage.setItem('notificationChannelToastType', 'success');
                                window.location.href = '/notification-channel/trash';
                            },
                            error: function (xhr) {
                                sessionStorage.setItem('notificationChannelToastMessage', xhr.responseJSON?.message || 'There was an Issue on Deleting the Record.');
                                sessionStorage.setItem('notificationChannelToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Back to Trash', 'info');
                    }
                });
            });
        });
    </script>
@endpush
