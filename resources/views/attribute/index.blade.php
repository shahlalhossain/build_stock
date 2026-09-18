@extends('layout.master')

@section('title', __('Attribute'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage Attributes') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('attribute.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('attribute.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
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

                            <table id="attributes-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
            const toastMessage = sessionStorage.getItem('attributeToastMessage');
            const toastType = sessionStorage.getItem('attributeToastType');

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
                sessionStorage.removeItem('attributeToastMessage');
                sessionStorage.removeItem('attributeToastType');
            }

            $(document).on('click', '.destroy-attribute', function() {
                const attributeID = $(this).data('attribute-id');
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
                            url: '/attribute/' + attributeID,
                            method: 'DELETE',
                            data: { _token: csrfToken},
                            success: function (response) {
                                // Store Success Toast Message & Type
                                sessionStorage.setItem( 'attributeToastMessage', response.message || 'Attribute Destroyed Successfully' );
                                sessionStorage.setItem( 'attributeToastType', 'success' );
                                // Reload/Redirect to Attribute List Page
                                window.location.href = '/attribute';
                            },
                            error: function (xhr, status, error) {
                                // Get Laravel Error Message if Available
                                const message = xhr.responseJSON?.message || 'There was an Issue on Destroying the Record.';
                                // Store Failed Toast Message and Type
                                sessionStorage.setItem( 'attributeToastMessage', message ); sessionStorage.setItem( 'attributeToastType', 'failed' );
                                // Reload/Redirect to Attribute List Page
                                window.location.href = '/attribute';
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
