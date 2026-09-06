@extends('layout.master')

@section('title', __('FAQ'))

@push('styles')
    <style>
        .success-toast {
            background: linear-gradient(135deg, #00c9a7, #1f3c88);
            color: #fff;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .failed-toast {
            background: linear-gradient(135deg, #ff922b, #e8590c);
            color: #fff;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }
    </style>
@endpush

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Manage FAQs') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('faq.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('faq.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Trash Box') }}</span></a>
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

                            <table id="faqs-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
        $(document).ready(function () {
            const message = sessionStorage.getItem('message');
            if (message) {
                Toastify({
                    text: message,
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    className: "success-toast",
                    stopOnFocus: true
                }).showToast();
                sessionStorage.removeItem('message');
            }
        });

        $(document).on('click', '.destroy-faq', function() {
            const faqID = $(this).data('faq-id');
            Swal.fire({
                title: 'Are You Sure?',
                ext: 'You want to Destroy this Data',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Destroy',
                cancelButtonText: 'No, Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/faq/' + faqID,
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            $('#faqs-table').DataTable().ajax.reload(null, false);
                            Toastify({
                                text: response.message || "The Record has been Destroyed Successfully.",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                close: true,
                                className: "success-toast",
                                stopOnFocus: true
                            }).showToast();
                        },
                        error: function (xhr, status, error) {
                            Toastify({
                                text: "There was an Issue on Destroying the Record.",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                close: true,
                                className: "failed-toast",
                                stopOnFocus: true
                            }).showToast();
                        }
                    });
                }
            });
        });
    </script>
@endpush
