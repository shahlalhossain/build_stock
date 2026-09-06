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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('FAQ Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('faq.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('faq.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('faq.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-xxl-6">
                                    <div class="card border card-border-secondary shadow-lg">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0" style="font-size: 14px;"><i>Basic Information</i></h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tbody>
                                                <tr><th class="text-end border-end">{{ __('Question') }}</th><td class="text-start">{{ $faq->question ?? '' }}</td></tr>
                                                <tr><th class="text-end border-end">{{ __('Answer') }}</th><td class="text-start">{{ $faq->answer ?? '' }}</td></tr>
                                                <tr><th class="text-end border-end">{{ __('Language') }}</th><td class="text-start">{{ $faq->language ?? '' }}</td></tr>
                                                <tr><th class="text-end border-end">{{ __('Category') }}</th><td class="text-start">{{ $faq->faqCategory?->name ?? '' }}</td></tr>
                                                <tr><th class="text-end border-end">{{ __('Status') }}</th>
                                                    <td class="text-start">
                                                        @if($faq->is_active == 1)
                                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                                        @elseif($faq->is_active == 0)
                                                            <span class="badge bg-danger">{{ __('Not Active') }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-xxl-6">
                                    <div class="card border card-border-success shadow-lg">
                                        <div class="card-header">
                                            <h6 class="card-title mb-0" style="font-size: 14px;"><i>Audit Information</i></h6>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-sm">
                                                <tbody>
                                                <tr><th class="text-end border-end" style="width: 40%;">{{ __('Created By') }}</th><td class="text-start" style="width: 60%;">{{ $faq->creator->name ?? '' }}</td></tr>
                                                <tr><th class="text-end border-end" style="width: 40%;">{{ __('Created At') }}</th><td class="text-start" style="width: 60%;">{{ $faq->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                                <tr><th class="text-end border-end" style="width: 40%;">{{ __('Updated By') }}</th><td class="text-start" style="width: 60%;">{{ $faq->updater->name ?? '' }}</td></tr>
                                                <tr><th class="text-end border-end" style="width: 40%;">{{ __('Updated At') }}</th><td class="text-start" style="width: 60%;">{{ $faq->updated_at->format('Y-m-d H:i:s') }}</td></tr>
                                                @if($faq->trashed())
                                                    <tr><th class="text-end border-end" style="width: 40%;">{{ __('Deleted By') }}</th><td class="text-start" style="width: 60%;">{{ $faq->deleter?->name }}</td></tr>
                                                    <tr><th class="text-end border-end" style="width: 40%;">{{ __('Deleted At') }}</th><td class="text-start" style="width: 60%;">{{ $faq->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-start">
                                    @if($faq->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-faq" id="restoreFAQ" data-faq-id="{{ $faq->id }}">
                                            <i class="ri-recycle-line"></i>
                                            <span class="d-none d-sm-inline"> {{ __('Restore') }}</span>
                                        </button>
                                        <button class="btn btn-sm btn-danger delete-faq" id="deleteFAQ" data-faq-id="{{ $faq->id }}">
                                            <i class="ri-close-line"></i>
                                            <span class="d-none d-sm-inline"> {{ __('Delete') }}</span>
                                        </button>
                                    @else
                                        <a href="{{ route('faq.edit', $faq->id) }}" class="btn btn-sm btn-soft-info">
                                            <i class="ri-edit-line"></i>
                                            <span class="d-none d-sm-inline"> {{ __('Edit') }}</span>
                                        </a>
                                        <button class="btn btn-sm btn-warning destroy-faq" id="destroyFAQ" data-faq-id="{{ $faq->id }}">
                                            <i class="ri-delete-bin-line"></i>
                                            <span class="d-none d-sm-inline"> {{ __('Destroy') }}</span>
                                        </button>
                                    @endif
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

            $(document).on('click', '.destroy-faq', function () {
                const faqID = $(this).data('faq-id');
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
                            url: '/faq/' + faqID,
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                window.location.href = '/faq';
                                sessionStorage.setItem('message', response.message || "The Record has been Destroyed Successfully.");
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

            $(document).on('click', '.restore-faq', function () {
                const faqID = $(this).data('faq-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore Record?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/faq/' + faqID + '/restore',
                            method: 'POST',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                window.location.href = '/faq/trash';
                                sessionStorage.setItem('message', response.message || "The Record has been Restored Successfully.");
                            },
                            error: function (xhr, status, error) {
                                Toastify({
                                    text: "There was an Issue on Restoring the Record.",
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

            $(document).on('click', '.delete-faq', function () {
                const faqID = $(this).data('faq-id');
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
                            url: '/faq/' + faqID + '/force-delete',
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                window.location.href = '/faq/trash';
                                sessionStorage.setItem('message', response.message || "The Record has been Deleted Successfully.");
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
        });
    </script>
@endpush
