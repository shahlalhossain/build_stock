@extends('layout.master')

@section('title', __('Warehouse'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Warehouse Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('warehouse.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('warehouse.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('warehouse.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Head Office / Project') }}</th><td class="text-start ps-2">{{ $warehouse->isHeadOffice() ? __('Head Office') : $warehouse->project?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $warehouse->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $warehouse->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($warehouse->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($warehouse->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $warehouse->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $warehouse->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $warehouse->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $warehouse->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($warehouse->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $warehouse->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $warehouse->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($warehouse->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-warehouse" id="restoreWarehouse" data-warehouse-id="{{ $warehouse->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-warehouse" id="deleteWarehouse" data-warehouse-id="{{ $warehouse->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('warehouse.edit', $warehouse->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-warehouse" id="destroyWarehouse" data-warehouse-id="{{ $warehouse->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                        @endif
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
            const toastMessage = sessionStorage.getItem('warehouseToastMessage');
            const toastType = sessionStorage.getItem('warehouseToastType');

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
                sessionStorage.removeItem('warehouseToastMessage');
                sessionStorage.removeItem('warehouseToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE WAREHOUSE
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-warehouse', function () {
                const warehouseID = $(this).data('warehouse-id');
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
                            url: '/warehouse/' + warehouseID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('warehouseToastMessage', response.message || 'Warehouse Destroyed Successfully.');
                                sessionStorage.setItem('warehouseToastType', 'success');
                                window.location.href = '/warehouse/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('warehouseToastMessage', message);
                                sessionStorage.setItem('warehouseToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | RESTORE WAREHOUSE
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-warehouse', function () {
                const warehouseID = $(this).data('warehouse-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore this Data.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/warehouse/' + warehouseID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('warehouseToastMessage', response.message || 'Warehouse Restored Successfully.');
                                sessionStorage.setItem('warehouseToastType', 'success');
                                window.location.href = '/warehouse/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('warehouseToastMessage', message);
                                sessionStorage.setItem('warehouseToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | PERMANENT DELETE WAREHOUSE
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-warehouse', function () {
                const warehouseID = $(this).data('warehouse-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Permanently Delete this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/warehouse/' + warehouseID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('warehouseToastMessage', response.message || 'Warehouse Deleted Permanently.');
                                sessionStorage.setItem('warehouseToastType', 'success');
                                window.location.href = '/warehouse/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('warehouseToastMessage', message);
                                sessionStorage.setItem('warehouseToastType', 'failed');
                                window.location.reload();
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
