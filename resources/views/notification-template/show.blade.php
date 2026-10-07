@extends('layout.master')

@section('title', __('Notification Template'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification Template Details') }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification-template.create')
                                    <a href="{{ route('notification-template.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                @endcan
                                <a href="{{ route('notification-template.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-9">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Setting') }}</th><td class="text-start ps-2">{{ $notificationTemplate->setting?->name }} ({{ $notificationTemplate->setting?->event_code }})</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Channel') }}</th><td class="text-start ps-2">{{ $notificationTemplate->channel?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Subject') }}</th><td class="text-start ps-2">{{ $notificationTemplate->subject }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Title') }}</th><td class="text-start ps-2">{{ $notificationTemplate->title }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Body') }}</th><td class="text-start ps-2" style="white-space: pre-wrap;">{{ $notificationTemplate->body }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Variables') }}</th>
                                            <td class="text-start ps-2">
                                                @forelse($notificationTemplate->variables ?? [] as $variable)
                                                    <span class="badge bg-secondary">{{ $variable }}</span>
                                                @empty
                                                    {{ '--' }}
                                                @endforelse
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($notificationTemplate->is_active)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $notificationTemplate->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $notificationTemplate->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $notificationTemplate->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $notificationTemplate->updated_at->format('Y-m-d H:i:s') }}</td></tr>
                                        </tbody>
                                    </table>

                                    <!-- Plain Text Preview (placeholders are not replaced) -->
                                    <div class="border rounded p-3 mb-3 bg-light">
                                        <h6 class="text-muted">{{ __('Preview') }}</h6>
                                        @if($notificationTemplate->title)<strong>{{ $notificationTemplate->title }}</strong><br>@endif
                                        @if($notificationTemplate->subject)<em>{{ $notificationTemplate->subject }}</em><br>@endif
                                        <div style="white-space: pre-wrap;">{{ $notificationTemplate->body }}</div>
                                    </div>

                                    <div class="text-start mt-2 pb-2">
                                        @can('notification-template.edit')
                                            <a href="{{ route('notification-template.edit', $notificationTemplate->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                        @endcan
                                        @can('notification-template.delete')
                                            <button class="btn btn-sm btn-danger delete-notification-template" data-notification-template-id="{{ $notificationTemplate->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @endcan
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
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
            const toastMessage = sessionStorage.getItem('notificationTemplateToastMessage');
            const toastType = sessionStorage.getItem('notificationTemplateToastType');

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
                sessionStorage.removeItem('notificationTemplateToastMessage');
                sessionStorage.removeItem('notificationTemplateToastType');
            }

            $(document).on('click', '.delete-notification-template', function() {
                const notificationTemplateID = $(this).data('notification-template-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Delete this Data Permanently',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/notification-template/' + notificationTemplateID,
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('notificationTemplateToastMessage', response.message || 'Notification Template Deleted Successfully.');
                                sessionStorage.setItem('notificationTemplateToastType', 'success');
                                window.location.href = '/notification-template';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an Issue on Deleting the Record.';
                                sessionStorage.setItem('notificationTemplateToastMessage', message);
                                sessionStorage.setItem('notificationTemplateToastType', 'failed');
                                window.location.reload();
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
