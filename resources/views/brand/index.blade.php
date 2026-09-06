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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Brands') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('brand.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('brand.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
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


    @if (session('success'))
        <script>
            Toastify({
                text: @json(session('success')),
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                className: "success-toast",
                stopOnFocus: true
            }).showToast();
        </script>
    @endif
    @if (session('error'))
        <script>
            Toastify({
                text: @json(session('success')),
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                className: "failed-toast",
                stopOnFocus: true
            }).showToast();
        </script>
    @endif

@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function () {
            const toastMessage = sessionStorage.getItem('destroySuccess');
            if (toastMessage) {
                Toastify({
                    text: toastMessage,
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    className: "failed-toast",
                    stopOnFocus: true
                }).showToast();
                sessionStorage.removeItem('destroySuccess');
            }
        });

        $(document).on('click', '.destroy-brand', function() {
            const brandID = $(this).data('brand-id');
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
                        url: '/brand/' + brandID,
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            sessionStorage.setItem('destroySuccess', response.message || 'Brand Destroyed Successfully');
                            window.location.href = '/brand';
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
