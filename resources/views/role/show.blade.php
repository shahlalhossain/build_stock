@extends('layout.master')

@section('title', __('Role'))

@push('styles')
    <style>
        .collapse:not(.show) {
            display: none !important;
        }

        .collapsing {
            transition: none !important;
            height: auto !important;
        }
    </style>
@endpush

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Role Details Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Role Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('role.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('role.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('role.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
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

                            <div class="row">
                                <div class="col-sm-6 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Type') }}</th><td class="text-start">{{ ucwords($role->type) }}</td></tr>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Guard') }}</th><td class="text-start">{{ ucwords($role->guard_name) }}</td></tr>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Name') }}</th><td class="text-start">{{ $role->name }}</td></tr>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Description') }}</th><td class="text-start">{{ ucwords($role->description) }}</td></tr>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Status') }}</th>
                                            <td class="text-start">
                                                @if($role->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @elseif($role->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('Not Active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Created By') }}</th><td class="text-start">{{ $role->creator?->name ?? '' }}</td></tr>
                                        <tr>
                                            <th class="text-end">{{ __('Created At') }}</th>
                                            <td class="text-start">
                                                {{--{{ $role->created_at->format('Y-m-d H:i:s') }}--}}
                                                {{ $role->created_at->format('j F, Y \a\t g:i A') }}
                                                ({{ $role->created_at->diffForHumans() }})
                                            </td>
                                        </tr>
                                        <tr><th class="text-end" style="width: 30%;">{{ __('Updated By') }}</th><td class="text-start">{{ $role->updater?->name ?? '' }}</td></tr>
                                        <tr>
                                            <th class="text-end">{{ __('Updated At') }}</th>
                                            <td class="text-start">
                                                {{--{{ $role->updated_at->format('Y-m-d H:i:s') }}--}}
                                                {{ $role->updated_at->format('j F, Y \a\t g:i A') }}
                                                ({{ $role->updated_at->diffForHumans() }})
                                            </td>
                                        </tr>
                                        @if($role->trashed())
                                            <tr><th class="text-end" style="width: 30%;">{{ __('Deleted By') }}</th><td class="text-start">{{ $role->deleter?->name ?? '' }}</td></tr>
                                            <tr>
                                                <th class="text-end" style="width: 30%;">{{ __('Deleted At') }}</th>
                                                <td class="text-start">
                                                    {{ $role->deleted_at?->format('j F, Y \a\t g:i A') }}
                                                    ({{ $role->deleted_at?->diffForHumans() }})
                                                </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <th class="text-end" style="width: 30%;">{{ __('Has Permission') }}</th>
                                            <td class="text-start">
                                                @if(count($permissions) > 0)
                                                    <div class="d-flex justify-content-between align-items-center w-100">
                                                        {{ __('Yes') }}
                                                        <div class="form-check form-switch form-switch-md d-flex align-items-center gap-3">
                                                            <label for="hide-show-permissions" class="mb-0">{{ __('Hide') }}</label>
                                                            <input class="form-check-input code-switcher ms-3" type="checkbox" id="hide-show-permissions">
                                                            <label for="hide-show-permissions" class="mb-0" style="padding-right: 10px;">{{ __('Show') }}</label>
                                                        </div>
                                                    </div>
                                                @else
                                                    {{ __('No') }}
                                                @endif
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>

                                    <div class="row pb-3">
                                        <div class="col-12 text-center">
                                            @if($role->trashed())
                                                <button class="btn btn-sm btn-soft-success restore-role" id="restoreRole" data-role-id="{{ $role->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                                <button class="btn btn-sm btn-danger delete-role" id="deleteRole" data-role-id="{{ $role->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                            @else
                                                <a href="{{ route('role.edit-permissions', $role->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="ri-settings-2-line"></i>
                                                    <span class="d-none d-sm-inline">{{ __('Assign Permissions') }}</span>
                                                </a>
                                                <a href="{{ route('role.edit', $role->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                                <button class="btn btn-sm btn-warning destroy-role" id="destroyRole" data-role-id="{{ $role->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                                <div class="col-sm-6 order-2">
                                    <div id="assigned-permissions-table" style="display: none;">
                                        <table class="table table-hover table-responsive table-bordered table-sm">
                                            <tbody>
                                            <tr>
                                                <td class="px-3 text-start">
                                                    @if(count($permissions) > 0)
                                                        <strong class="fw-bold border-bottom border-primary border-1 d-inline-block mb-1">
                                                            {{ __('Assigned Permissions') }}
                                                        </strong>

                                                        <div class="row mt-2">
                                                            @foreach($permissions as $permission)
                                                                <div class="col-md-4 col-sm-6 col-12 mb-1">
                                                                    <span class="badge badge-label bg-success text-start">
                                                                        <i class="mdi mdi-circle-medium"></i>
                                                                        {{ ucwords($permission->description) }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        {{ __('No Assigned Permission Found') }}
                                                    @endif
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
            </div>
            <!-- End Role Details Row -->

        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {

            const hideShowPermissionSwitch = $('#hide-show-permissions');
            const assignedPermissionTable  = $('#assigned-permissions-table');

            // Initial State
            if (hideShowPermissionSwitch.is(':checked')) {
                assignedPermissionTable.show();
            } else {
                assignedPermissionTable.hide();
            }

            // On Change
            hideShowPermissionSwitch.on('change', function () {
                if ($(this).is(':checked')) {
                    assignedPermissionTable.slideDown(500);   // Show
                } else {
                    assignedPermissionTable.slideUp(500);     // Hide
                }
            });


            const csrfToken = "{{ csrf_token() }}";

            $(document).on('click', '.destroy-role', function () {
                const roleID = $(this).data('role-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Destroy this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Destroy',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/role/' + roleID,
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Destroyed', 'The Record has been Destroyed.', 'success')
                                    .then(() => { window.location.href = '/admin/role'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Destroying the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-role', function () {
                const roleID = $(this).data('role-id');
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
                            url: '/admin/role/' + roleID + '/restore',
                            method: 'POST',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record is Now in Active List.', 'success')
                                    .then(() => { window.location.href = '/admin/role/trash'; });
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

            $(document).on('click', '.delete-role', function () {
                const roleID = $(this).data('role-id');
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
                            url: '/admin/role/' + roleID + '/force-delete',
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record has been Deleted.', 'success')
                                    .then(() => { window.location.href = '/admin/role/trash'; });
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