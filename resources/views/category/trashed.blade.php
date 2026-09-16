@extends('layout.master')

@section('title', __('Category'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Category Trashbox') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('category.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('category.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
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

                            <table id="categories-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
    {{ $dataTable->scripts() }}

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

                // Remove After Display Toast Message
                sessionStorage.removeItem('categoryToastMessage');
                sessionStorage.removeItem('categoryToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | RESTORE CATEGORY
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-category', function () {
                const categoryID = $(this).data('category-id');
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
                            url: '/category/' + categoryID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken},
                            success: function (response) {
                                // Save Success Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', response.message || 'Category Restored Successfully');
                                sessionStorage.setItem('categoryToastType', 'success');
                                window.location.href = '/category/trash';
                            },
                            error: function (xhr) {
                                // Save Failed Toast
                                sessionStorage.setItem('categoryToastMessage', xhr.responseJSON?.message || 'There was an Issue on Restoring the Record.');
                                sessionStorage.setItem('categoryToastType', 'failed');
                                // Reload Current Page
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash', 'info');
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
                    text: 'You want to Delete this Data',
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
                                // Save Success Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', response.message || 'Category Deleted Successfully');
                                sessionStorage.setItem('categoryToastType', 'success');
                                window.location.href = '/category/trash';
                            },
                            error: function (xhr) {
                                // Save Failed Toast Type and Message
                                sessionStorage.setItem('categoryToastMessage', xhr.responseJSON?.message || 'There was an Issue on Deleting the Record.');
                                sessionStorage.setItem('categoryToastType', 'failed');
                                // Reload Current Page
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
