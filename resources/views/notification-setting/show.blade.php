@extends('layout.master')

@section('title', __('Notification Setting'))

@section('content')
    @php
        $ruleGroups = $notificationSetting->receiverRules->groupBy('receiver_type');
    @endphp
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification Setting Details') }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification-setting.create')
                                    <a href="{{ route('notification-setting.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                @endcan
                                @can('notification-setting.index')
                                    <a href="{{ route('notification-setting.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                @endcan
                                @can('notification-setting.trash')
                                    <a href="{{ route('notification-setting.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-7 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $notificationSetting->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Event Code') }}</th><td class="text-start ps-2"><code>{{ $notificationSetting->event_code }}</code></td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Permission Name') }}</th><td class="text-start ps-2">{{ $notificationSetting->permission_name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Permission Code') }}</th><td class="text-start ps-2">{{ $notificationSetting->permission_code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($notificationSetting->is_active)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @else
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ $notificationSetting->description }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $notificationSetting->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $notificationSetting->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $notificationSetting->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $notificationSetting->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($notificationSetting->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $notificationSetting->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $notificationSetting->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($notificationSetting->trashed())
                                            @can('notification-setting.restore')
                                                <button class="btn btn-sm btn-soft-success restore-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            @endcan
                                            @can('notification-setting.delete')
                                                <button class="btn btn-sm btn-danger delete-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                            @endcan
                                        @else
                                            @can('notification-setting.edit')
                                                <a href="{{ route('notification-setting.edit', $notificationSetting->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            @endcan
                                            @can('notification-setting.destroy')
                                                <button class="btn btn-sm btn-warning destroy-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                            @endcan
                                        @endif
                                    </div>
                                </div>

                                <div class="col-12 col-md-5 order-2">
                                    <h6>{{ __('Channels') }}</h6>
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                        <tr><th>{{ __('Channel') }}</th><th>{{ __('Status') }}</th><th>{{ __('Template') }}</th></tr>
                                        </thead>
                                        <tbody>
                                        @forelse($notificationSetting->channels as $channel)
                                            @php $template = $templatesByChannel->get($channel->id); @endphp
                                            <tr>
                                                <td>{{ $channel->name }}</td>
                                                <td>
                                                    @if($channel->pivot->is_active && $channel->is_active)
                                                        <span class="badge bg-success">{{ __('Active') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($template)
                                                        <span class="badge bg-success">{{ __('Ready') }}</span>
                                                        @can('notification-template.edit')
                                                            <a href="{{ route('notification-template.edit', $template->id) }}">{{ __('Edit Template') }}</a>
                                                        @endcan
                                                    @else
                                                        <span class="badge bg-warning">{{ __('Missing') }}</span>
                                                        @can('notification-template.create')
                                                            <a href="{{ route('notification-template.create', ['notification_setting_id' => $notificationSetting->id, 'channel_id' => $channel->id]) }}">{{ __('Add Template') }}</a>
                                                        @endcan
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-muted">{{ __('No channels linked.') }}</td></tr>
                                        @endforelse
                                        </tbody>
                                    </table>

                                    <h6 class="mt-3">{{ __('Who receives it') }}</h6>
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                        <tr>
                                            <th>{{ __('Specific Users') }}</th>
                                            <td>
                                                @forelse($ruleGroups->get('user', collect()) as $rule)
                                                    <span class="badge bg-primary">{{ $userNames[$rule->receiver_value] ?? __('User #:id', ['id' => $rule->receiver_value]) }}</span>
                                                @empty
                                                    <span class="text-muted">-</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Roles') }}</th>
                                            <td>
                                                @forelse($ruleGroups->get('role', collect()) as $rule)
                                                    <span class="badge bg-primary">{{ $rule->receiver_value }}</span>
                                                @empty
                                                    <span class="text-muted">-</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>{{ __('Permissions') }}</th>
                                            <td>
                                                @forelse($ruleGroups->get('permission', collect()) as $rule)
                                                    <span class="badge bg-primary">{{ $rule->receiver_value }}</span>
                                                @empty
                                                    <span class="text-muted">-</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
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
            const listUrl = '/notification-setting';

            /*
            |--------------------------------------------------------------------------
            | Asks for confirmation, sends the AJAX call, stores the toast and redirects.
            |--------------------------------------------------------------------------
            */
            function runAction(settingID, options) {
                Swal.fire({
                    title: 'Are You Sure?',
                    text: options.text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: options.confirmText,
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (!result.isConfirmed) {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                        return;
                    }
                    $.ajax({
                        url: options.url(settingID),
                        method: options.method,
                        data: { _token: csrfToken },
                        success: function (response) {
                            sessionStorage.setItem('notificationSettingToastMessage', response.message || options.successMessage);
                            sessionStorage.setItem('notificationSettingToastType', 'success');
                            window.location.href = options.redirectUrl;
                        },
                        error: function (xhr) {
                            sessionStorage.setItem('notificationSettingToastMessage', xhr.responseJSON?.message || options.failMessage);
                            sessionStorage.setItem('notificationSettingToastType', 'failed');
                            window.location.href = options.redirectUrl;
                        }
                    });
                });
            }

            $(document).on('click', '.destroy-notification-setting', function () {
                runAction($(this).data('notification-setting-id'), {
                    text: 'You want to Destroy this Data',
                    confirmText: 'Yes, Destroy',
                    method: 'DELETE',
                    url: (id) => listUrl + '/' + id,
                    redirectUrl: listUrl,
                    successMessage: 'Notification Setting Destroyed Successfully',
                    failMessage: 'There was an Issue on Destroying the Record.'
                });
            });

            $(document).on('click', '.restore-notification-setting', function () {
                runAction($(this).data('notification-setting-id'), {
                    text: 'You want to Restore this Data',
                    confirmText: 'Yes, Restore',
                    method: 'POST',
                    url: (id) => listUrl + '/' + id + '/restore',
                    redirectUrl: listUrl + '/trash',
                    successMessage: 'Notification Setting Restored Successfully',
                    failMessage: 'There was an Issue on Restoring the Record.'
                });
            });

            $(document).on('click', '.delete-notification-setting', function () {
                runAction($(this).data('notification-setting-id'), {
                    text: 'You want to Delete this Data Permanently',
                    confirmText: 'Yes, Delete',
                    method: 'DELETE',
                    url: (id) => listUrl + '/' + id + '/force-delete',
                    redirectUrl: listUrl + '/trash',
                    successMessage: 'Notification Setting Deleted Successfully',
                    failMessage: 'There was an Issue on Deleting the Record.'
                });
            });
        });
    </script>
@endpush
