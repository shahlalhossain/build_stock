@extends('layout.master')

@section('title', __('Upazila/Thana'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Upazila/Thana') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('thana.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
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

                            <table id="thanas-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        $(document).on('click', '.delete-thana', function() {
            const thanaID = $(this).data('thana-id');
            // Show SweetAlert Confirmation Dialog
            Swal.fire({
                title: 'Are You Sure?',
                text: 'You will not be able to Recover this Data.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'No, Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/thana/' + thanaID,
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            // If the Deletion was Successful, Show a Success Alert
                            Swal.fire('Deleted', 'The Record has been Deleted.', 'success')
                                .then(() => { window.location.href = '/admin/thana'; });  // Redirect to the Upazila/Thana List
                        },
                        error: function (xhr, status, error) {
                            // Handle the Error Case
                            Swal.fire('Error!', 'There was an issue on deleting the Record.', 'error');
                        }
                    });
                } else {
                    Swal.fire('Cancelled', 'Your Record is Safe :)', 'info');
                }
            });
        });
    </script>
@endpush
