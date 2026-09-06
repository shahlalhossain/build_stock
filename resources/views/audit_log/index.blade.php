@extends('layout.master')

@section('title', __('Audit Logs'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Audit Logs') }}</h4>
                            <div class="flex-shrink-0">
{{--                                <a href="{{ route('login-history', 'all') }}" class="btn btn-sm btn-soft-primary"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> All Login History</span></a>--}}
{{--                                <a href="{{ route('login-history', 'active') }}" class="btn btn-sm btn-soft-info"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> Active Login History</span></a>--}}
{{--                                <a href="{{ route('login-history', 'expired') }}" class="btn btn-sm btn-soft-warning"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> Expire Login History</span></a>--}}
{{--                                <a href="{{ route('login-history', 'my') }}" class="btn btn-sm btn-soft-success"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> My Login History</span></a>--}}
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="audit-log-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

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
        $(document).on('click', '.delete-audit-log', function() {
            const auditLogID = $(this).data('audit-log-id');
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
                        url: '/audit-log/' + auditLogID + '/delete/',
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            $('#audit-log-table').DataTable().ajax.reload(null, false);
                            Toastify({
                                text: response.message || "Login Log Deleted Successfully.",
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
                                text: "There was an Issue on Deleting the Record.",
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
