@extends('layout.master')

@section('title', __('Brand'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Brand Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('brand.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('brand.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('brand.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $brand->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Slug') }}</th><td class="text-start ps-2">{{ $brand->slug }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Priority Order') }}</th><td class="text-start ps-2">{{ $brand->priority_order }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($brand->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($brand->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ ucwords($brand->description) }}</td></tr>
                                        <tr>
                                            <th class="text-end pe-2 justify-content-between">{{ __('Status') }}</th>
                                            <td class="text-start d-flex justify-content-between align-items-center">
                                                <div class="text-start">
                                                    @php
                                                        $status = $brand->status;
                                                        $map = [
                                                            'pending'  => ['class' => 'text-warning', 'icon' => 'ri-loader-4-line', 'label' => 'Pending'],
                                                            'approved' => ['class' => 'text-success', 'icon' => 'ri-checkbox-circle-line', 'label' => 'Approved'],
                                                            'rejected' => ['class' => 'text-danger', 'icon'  => 'ri-close-circle-line', 'label' => 'Rejected'],
                                                            'archived' => ['class' => 'text-primary', 'icon' => 'ri-search-eye-line', 'label' => 'Archived'],
                                                        ];
                                                    @endphp

                                                    @if($status && isset($map[$status]))
                                                        <span class="{{ $map[$status]['class'] }}"><i class="{{ $map[$status]['icon'] }}"></i> {{ __($map[$status]['label']) }}</span>
                                                    @else
                                                        {{ '--' }}
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#statusUpdateModal">
                                                        <i class="ri-fingerprint-line"></i>
                                                        <span class="btn-text d-none d-sm-inline">{{ __('Update Status') }}</span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>


                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $brand->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $brand->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $brand->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $brand->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($brand->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $brand->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $brand->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($brand->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-brand" id="restoreBrand" data-brand-id="{{ $brand->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-brand" id="deleteBrand" data-brand-id="{{ $brand->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('brand.edit', $brand->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-brand" id="destroyBrand" data-brand-id="{{ $brand->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="statusUpdateForm" action="{{ route('brand.update-status', $brand->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Brand Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" >
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" >
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" >
                                            <label class="form-check-label" for="statusRejected"> {{ __('Reject') }} </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start" > {{ __('Add Remarks') }} </label>
                                    <div class="col-12 col-md-8 pt-2">
                                        <textarea id="remarks" name="remarks" class="form-control" rows="2" ></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="brand_id" value="{{ $brand->id }}">
                            </div>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" > {{ __('Cancel') }} </button>
                                <button type="reset" class="btn btn-sm btn-warning" > {{ __('Reset') }} </button>
                                <button type="submit" class="btn btn-sm btn-info" id="updateStatusBtn" > {{ __('Update Status') }} </button>
                            </div>
                        </form>
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
            const toastMessage = sessionStorage.getItem('brandToastMessage');
            const toastType = sessionStorage.getItem('brandToastType');

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
                sessionStorage.removeItem('brandToastMessage');
                sessionStorage.removeItem('brandToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE BRAND
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-brand', function () {
                const brandID = $(this).data('brand-id');
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
                            url: '/brand/' + brandID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('brandToastMessage', response.message || 'Brand Destroyed Successfully.');
                                sessionStorage.setItem('brandToastType', 'success');
                                // Reload Page
                                window.location.href = '/brand/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('brandToastMessage', message);
                                sessionStorage.setItem('brandToastType', 'failed');
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
            | RESTORE BRAND
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-brand', function () {
                const brandID = $(this).data('brand-id');
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
                            url: '/brand/' + brandID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('brandToastMessage', response.message || 'Brand Restored Successfully.');
                                sessionStorage.setItem('brandToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/brand/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('brandToastMessage', message);
                                sessionStorage.setItem('brandToastType', 'failed');
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
            | PERMANENT DELETE BRAND
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-brand', function () {
                const brandID = $(this).data('brand-id');
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
                            url: '/brand/' + brandID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('brandToastMessage', response.message || 'Brand Deleted Permanently.');
                                sessionStorage.setItem('brandToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/brand/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('brandToastMessage', message);
                                sessionStorage.setItem('brandToastType', 'failed');
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
            | UPDATE BRAND STATUS
            |--------------------------------------------------------------------------
            */
            $('#statusUpdateForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const button = $('#updateStatusBtn');
                const errorBox = $('#statusUpdateError');
                // Clear Previous Errors
                errorBox.addClass('d-none').html('');

                // Disable Button
                button.prop('disabled', true);

                button.html('<i class="ri-loader-4-line ri-spin"></i> {{ __("Updating...") }}');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS UPDATE SUCCESS
                    |--------------------------------------------------------------------------
                    */
                    success: function (response) {
                        if (response.success) {
                            // Store Success Toast Type and Message
                            sessionStorage.setItem('brandToastMessage', response.message || 'Brand Status Updated Successfully.');
                            sessionStorage.setItem('brandToastType', 'success');
                            // Hide Modal
                            $('#statusUpdateModal').modal('hide');
                            // Reload Page
                            window.location.reload();
                        }
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS UPDATE ERROR
                    |--------------------------------------------------------------------------
                    */
                    error: function (xhr) {
                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATION ERROR - KEEP INSIDE MODAL
                        |--------------------------------------------------------------------------
                        */
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            const messages = [];
                            $.each(errors, function (field, errorMessages) {
                                if (errorMessages.length > 0) {
                                    messages.push(errorMessages[0]);
                                }
                            });
                            errorBox.html(messages.join('<br>')).removeClass('d-none');
                            return;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | OTHER ERROR - SHOW FAILED TOAST AFTER RELOAD
                        |--------------------------------------------------------------------------
                        */
                        const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';

                        // Store Failed Toast Type and Message
                        sessionStorage.setItem('brandToastMessage', message);
                        sessionStorage.setItem('brandToastType', 'failed');
                        // Reload Page
                        window.location.reload();
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | AJAX COMPLETE
                    |--------------------------------------------------------------------------
                    */
                    complete: function () {
                        button.prop('disabled', false);
                        button.html('{{ __("Update Status") }}');
                    }
                });
            });
        });
    </script>
@endpush
