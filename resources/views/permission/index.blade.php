@extends('layout.master')

@section('title', __('Permission'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Permissions') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('permission.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('permission.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
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

                            <table id="permissions-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
        $(document).on('click', '.destroy-permission', function() {
            const permissionID = $(this).data('permission-id');
            Swal.fire({
                title: 'Are You Sure?',
                text: 'You want to Destroy this Data',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Destroy',
                cancelButtonText: 'No, Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/permission/' + permissionID,
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            Swal.fire('Destroyed', 'The Record has been Destroyed.', 'success')
                                .then(() => { window.location.href = '/permission'; });
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
    </script>
@endpush
