@extends('layout.master')

@section('title', __('Category'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Category Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('category.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('category.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('category.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $category->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Slug') }}</th><td class="text-start ps-2">{{ $category->slug }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Priority Order') }}</th><td class="text-start ps-2">{{ $category->priority_order }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($category->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($category->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ ucwords($category->description) }}</td></tr>

                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $category->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $category->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $category->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $category->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($category->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $category->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $category->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($category->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-category" id="restoreCategory" data-category-id="{{ $category->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-category" id="deleteCategory" data-category-id="{{ $category->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('category.edit', $category->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-category" id="destroyCategory" data-category-id="{{ $category->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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
            const toastMessage = sessionStorage.getItem('categoryToastMessage');
            const toastType = sessionStorage.getItem('categoryToastType');

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
                sessionStorage.removeItem('categoryToastMessage');
                sessionStorage.removeItem('categoryToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE CATEGORY
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-category', function () {
                const categoryID = $(this).data('category-id');
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
                            url: '/category/' + categoryID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', response.message || 'Category Destroyed Successfully.');
                                sessionStorage.setItem('categoryToastType', 'success');
                                // Reload Page
                                window.location.href = '/category/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', message);
                                sessionStorage.setItem('categoryToastType', 'failed');
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
            | RESTORE CATEGORY
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-category', function () {
                const categoryID = $(this).data('category-id');
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
                            url: '/category/' + categoryID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', response.message || 'Category Restored Successfully.');
                                sessionStorage.setItem('categoryToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/category/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', message);
                                sessionStorage.setItem('categoryToastType', 'failed');
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
            | PERMANENT DELETE CATEGORY
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-category', function () {
                const categoryID = $(this).data('category-id');
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
                            url: '/category/' + categoryID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', response.message || 'Category Deleted Permanently.');
                                sessionStorage.setItem('categoryToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/category/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', message);
                                sessionStorage.setItem('categoryToastType', 'failed');
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
