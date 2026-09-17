@extends('layout.master')

@section('title', __('Store'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Stores') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('store.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('store.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
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

                            <table id="stores-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
            const csrfToken = "{{ csrf_token() }}";

            /*
            |--------------------------------------------------------------------------
            | PAGE LOAD TOAST
            |--------------------------------------------------------------------------
            | Shows Success/Failed Toast After Page Reload
            |--------------------------------------------------------------------------
            */
            const toastMessage = sessionStorage.getItem('storeToastMessage');
            const toastType = sessionStorage.getItem('storeToastType');

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
                sessionStorage.removeItem('storeToastMessage');
                sessionStorage.removeItem('storeToastType');
            }

            $(document).on('click', '.destroy-store', function() {
                const storeID = $(this).data('store-id');
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
                            url: '/store/' + storeID,
                            method: 'DELETE',
                            data: { _token: csrfToken},
                            success: function (response) {
                                // Store Success Toast Message & Type
                                sessionStorage.setItem( 'storeToastMessage', response.message || 'Store Destroyed Successfully' );
                                sessionStorage.setItem( 'storeToastType', 'success' );
                                // Reload/Redirect to Store List Page
                                window.location.href = '/store';
                            },
                            error: function (xhr, status, error) {
                                // Get Laravel Error Message if Available
                                const message = xhr.responseJSON?.message || 'There was an Issue on Destroying the Record.';
                                // Store Failed Toast Message and Type
                                sessionStorage.setItem( 'storeToastMessage', message ); sessionStorage.setItem( 'storeToastType', 'failed' );
                                // Reload/Redirect to Store List Page
                                window.location.href = '/store';
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });
        });

    </script>
@endpush
