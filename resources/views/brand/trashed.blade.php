@extends('layout.master')

@section('title', __('Brand'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Brand Trashbox') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('brand.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('brand.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
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

                            <table id="brands-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
            const toastMessage = sessionStorage.getItem('brandToast');
            if (toastMessage) {
                Toastify({
                    text: toastMessage,
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    className: "success-toast",
                    stopOnFocus: true
                }).showToast();
                sessionStorage.removeItem('brandToast');
            }
        });

        $(document).on('click', '.restore-brand', function() {
            const brandID = $(this).data('brand-id');
            console.log(brandID);
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
                        url: '/brand/' + brandID + '/restore',
                        method: 'POST',
                        data: { "_token": "{{ csrf_token() }}" },
                        success: function(response) {
                            sessionStorage.setItem('brandToast', response.message || 'Brand Restored Successfully');
                            window.location.href = '/brand/trash';
                        },
                        error: function(xhr, status, error) {
                            Swal.fire('Error!', 'There was an Issue on Restoring the Record.', 'error');
                        }
                    });
                } else {
                    Swal.fire('Cancelled', 'Your Record is in Trash', 'info');
                }
            });
        });

        $(document).on('click', '.delete-brand', function() {
            const brandID = $(this).data('brand-id');
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
                        url: '/brand/' + brandID + '/force-delete',
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            sessionStorage.setItem('brandToast', response.message || 'Brand Deleted Successfully');
                            window.location.href = '/brand/trash';
                        },
                        error: function (xhr, status, error) {
                            Swal.fire('Error!', 'There was an Issue on Deleting the Record.', 'error');
                        }
                    });
                } else {
                    Swal.fire('Cancelled', 'Your Record is Back to Trash', 'info');
                }
            });
        });

    </script>
@endpush
