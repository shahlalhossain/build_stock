@extends('layout.master')

@section('title', __('Permission'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Permission Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('permission.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('permission.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('permission.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-6 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end">{{ __('Name') }}</th><td class="text-start">{{ $permission->name }}</td></tr>
                                        <tr><th class="text-end">{{ __('Description') }}</th><td class="text-start">{{ ucwords($permission->description) }}</td></tr>
                                        <tr><th class="text-end">{{ __('Status') }}</th>
                                            <td class="text-start">
                                                @if($permission->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @elseif($permission->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('Not Active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end">{{ __('Permission For') }}</th><td class="text-start">{{ ucwords($permission->type) }}</td></tr>
                                        <tr><th class="text-end">{{ __('Guard') }}</th><td class="text-start">{{ ucwords($permission->guard_name) }}</td></tr>
                                        <tr>
                                            <th class="text-end">{{ __('Type') }}</th>
                                            <td class="text-start">
                                                @if($permission->parent_id == null)
                                                    {{ __('Parent Permission') }}
                                                @else
                                                    {{ __('Child Permission') }}
                                                @endif
                                            </td>
                                        </tr>
                                        @if($permission->parent_id == null)
                                            <tr>
                                                <th class="text-end">{{ __('Child Permissions') }}</th>
                                                <td class="text-start">
                                                    <div class="form-check form-switch form-switch-md d-flex align-items-center gap-3">
                                                        <label for="hide-show-child-permissions" class="mb-0">{{ __('Hide') }}</label>
                                                        <input class="form-check-input code-switcher ms-3" type="checkbox" id="hide-show-child-permissions">
                                                        <label for="hide-show-child-permissions" class="mb-0">{{ __('Show') }}</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr><th class="text-end">{{ __('Created By') }}</th><td class="text-start">{{ $permission->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end">{{ __('Created At') }}</th><td class="text-start">{{ $permission->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end">{{ __('Updated By') }}</th><td class="text-start">{{ $permission->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end">{{ __('Updated At') }}</th><td class="text-start">{{ $permission->updated_at->format('Y-m-d H:i:s') }}</td></tr>
                                        </tbody>
                                    </table>

                                    <div class="text-center mt-2 pb-2">
                                        @if($permission->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-permission" id="restorePermission" data-permission-id="{{ $permission->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-permission" id="deletePermission" data-permission-id="{{ $permission->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('permission.edit', $permission->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-permission" id="destroyPermission" data-permission-id="{{ $permission->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-sm-6 order-2">
                                    <div id="child-permissions-table" style="display: none;">
                                        <table class="table table-hover table-responsive table-bordered table-sm">
                                            <tbody>
                                            @if($permission->parent_id == null)
                                                <tr>
                                                    <th class="text-end">{{ __('Child Permissions') }}</th>
                                                    <td class="text-start">
                                                        @if(count($permission->children) > 0)
                                                            @foreach($permission->children as $childPermission)
                                                                <span class="badge badge-label bg-success"><i class="mdi mdi-circle-medium"></i> {{ ucwords($childPermission->description) }}</span>
                                                                <br>
                                                            @endforeach
                                                        @else
                                                            {{ __('No Child Permission Found') }}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endif
                                            </tbody>
                                        </table>
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

            const hideShowChildPermissionSwitch = $('#hide-show-child-permissions');
            const childPermissionTable  = $('#child-permissions-table');

            // Initial State
            if (hideShowChildPermissionSwitch.is(':checked')) {
                childPermissionTable.show();
            } else {
                childPermissionTable.hide();
            }

            // On Change
            hideShowChildPermissionSwitch.on('change', function () {
                if ($(this).is(':checked')) {
                    childPermissionTable.slideDown(500);   // Show
                } else {
                    childPermissionTable.slideUp(500);     // Hide
                }
            });

            const csrfToken = "{{ csrf_token() }}";

            $(document).on('click', '.destroy-permission', function () {
                var permissionID = $(this).data('permission-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Destroy this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/permission/' + permissionID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                Swal.fire('Destroyed', 'The Record has been Destroyed.', 'success')
                                    .then(() => { window.location.href = '/admin/permission'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an issue deleting the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-permission', function () {
                const permissionID = $(this).data('permission-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore Record?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/permission/' + permissionID + '/restore',
                            method: 'POST',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record is Now in Active List.', 'success')
                                    .then(() => { window.location.href = '/admin/permission/trash'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Restoring the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-permission', function () {
                const permissionID = $(this).data('permission-id');
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
                            url: '/admin/permission/' + permissionID + '/force-delete',
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record has been Deleted.', 'success')
                                    .then(() => { window.location.href = '/admin/permission/trash'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Deleting the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

        });
    </script>
@endpush
