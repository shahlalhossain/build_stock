@extends('layout.master')

@section('title', __('Product-Unit'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Product-Unit Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-unit.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('product-unit.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('product-unit.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $productUnit->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Slug') }}</th><td class="text-start ps-2">{{ $productUnit->slug }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Priority Order') }}</th><td class="text-start ps-2">{{ $productUnit->priority_order }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($productUnit->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($productUnit->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ ucwords($productUnit->description) }}</td></tr>

                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $productUnit->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $productUnit->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $productUnit->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $productUnit->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($productUnit->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $productUnit->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $productUnit->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($productUnit->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-product-unit" id="restoreProductUnit" data-product-unit-id="{{ $productUnit->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-product-unit" id="deleteProductUnit" data-product-unit-id="{{ $productUnit->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('product-unit.edit', $productUnit->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-product-unit" id="destroyProductUnit" data-product-unit-id="{{ $productUnit->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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
            const toastMessage = sessionStorage.getItem('productUnitToastMessage');
            const toastType = sessionStorage.getItem('productUnitToastType');

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
                sessionStorage.removeItem('productUnitToastMessage');
                sessionStorage.removeItem('productUnitToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE PRODUCT-UNIT
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-product-unit', function () {
                const productUnitID = $(this).data('product-unit-id');
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
                            url: '/product-unit/' + productUnitID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('productUnitToastMessage', response.message || 'Product-Unit Destroyed Successfully.');
                                sessionStorage.setItem('productUnitToastType', 'success');
                                // Reload Page
                                window.location.href = '/product-unit/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('productUnitToastMessage', message);
                                sessionStorage.setItem('productUnitToastType', 'failed');
                                // Reload Page
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
            | RESTORE PRODUCT-UNIT
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-product-unit', function () {
                const productUnitID = $(this).data('product-unit-id');
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
                            url: '/product-unit/' + productUnitID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('productUnitToastMessage', response.message || 'Product-Unit Restored Successfully.');
                                sessionStorage.setItem('productUnitToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/product-unit/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('productUnitToastMessage', message);
                                sessionStorage.setItem('productUnitToastType', 'failed');
                                // Reload Trash Page
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
            | PERMANENT DELETE PRODUCT-UNIT
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-product-unit', function () {
                const productUnitID = $(this).data('product-unit-id');
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
                            url: '/product-unit/' + productUnitID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('productUnitToastMessage', response.message || 'Product-Unit Deleted Permanently.');
                                sessionStorage.setItem('productUnitToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/product-unit/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('productUnitToastMessage', message);
                                sessionStorage.setItem('productUnitToastType', 'failed');
                                // Reload Trash Page
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
