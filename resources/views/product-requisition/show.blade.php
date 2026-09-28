@extends('layout.master')

@section('title', __('Product Requisition'))

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Requisition Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-requisition.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('product-requisition.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('product-requisition.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-6 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $productRequisition->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Requesting Store') }}</th><td class="text-start ps-2">{{ $productRequisition->store?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Requisition Date') }}</th><td class="text-start ps-2">{{ optional($productRequisition->transaction_date)->format('d F, Y') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $productRequisition->remarks }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($productRequisition->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($productRequisition->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-end pe-2 justify-content-between">{{ __('Status') }}</th>
                                            <td class="text-start d-flex justify-content-between align-items-center">
                                                <div class="text-start">
                                                    @php
                                                        $status = $productRequisition->status;
                                                        $map = [
                                                            'pending'  => ['class' => 'text-warning', 'icon' => 'ri-loader-4-line', 'label' => 'Pending'],
                                                            'approved' => ['class' => 'text-success', 'icon' => 'ri-checkbox-circle-line', 'label' => 'Approved'],
                                                            'rejected' => ['class' => 'text-danger', 'icon'  => 'ri-close-circle-line', 'label' => 'Rejected'],
                                                        ];
                                                    @endphp

                                                    @if($status && isset($map[$status]))
                                                        <span class="{{ $map[$status]['class'] }}"><i class="{{ $map[$status]['icon'] }}"></i> {{ __($map[$status]['label']) }}</span>
                                                    @else
                                                        {{ '--' }}
                                                    @endif
                                                </div>
                                                @if(!$productRequisition->trashed())
                                                <div class="text-end">
                                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#statusUpdateModal">
                                                        <i class="ri-fingerprint-line"></i>
                                                        <span class="btn-text d-none d-sm-inline">{{ __('Update Status') }}</span>
                                                    </button>
                                                </div>
                                                @endif
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-12 col-md-6 order-2">
                                    @php
                                        $auditLogs = [
                                            [
                                                'action' => __('Created'),
                                                'user' => $productRequisition->creator?->name,
                                                'at' => $productRequisition->created_at,
                                            ],
                                            [
                                                'action' => __('Updated'),
                                                'user' => $productRequisition->updater?->name,
                                                'at' => $productRequisition->updated_at,
                                            ],
                                        ];

                                        if ($productRequisition->trashed()) {
                                            $auditLogs[] = [
                                                'action' => __('Deleted'),
                                                'user' => $productRequisition->deleter?->name,
                                                'at' => $productRequisition->deleted_at,
                                            ];
                                        }
                                    @endphp

                                    @foreach($auditLogs as $log)
                                        <hr style="padding: 0 !important; margin: 0 !important;">

                                        <div class="pb-2 pt-2">
                                            <strong>{{ $log['action'] }}</strong>

                                            @if($log['user'])
                                                by <em>{{ $log['user'] }}</em>
                                            @endif

                                            @if($log['at'])
                                                at {{ $log['at']->format('d F, Y h:i A') }}
                                            @endif
                                        </div>
                                    @endforeach

                                    @if($productRequisition->approvalLogs->isNotEmpty())
                                        @foreach($productRequisition->approvalLogs as $log)
                                            <hr style="padding: 0 !important; margin: 0 !important;">
                                            <div class="pb-2 pt-2">
                                                <strong>{{ ucfirst($log->action_name) }}</strong>

                                                @if($log->actionedBy)
                                                    by <em>{{ $log->actionedBy->name }}</em>
                                                @endif

                                                @if($log->actioned_at)
                                                    at {{ $log->actioned_at->format('d F, Y h:i A') }}
                                                @endif
                                                <br>
                                                @if($log->remarks)
                                                    <strong><em>Remarks: </em></strong> {{ $log->remarks }}
                                                @endif
                                            </div>
                                        @endforeach
                                        <hr style="padding: 0 !important; margin: 0 !important;">
                                    @endif
                                </div>
                            </div>

                            <div class="row pt-3">
                                <div class="col-12">
                                    <h6 class="fw-bold fst-italic">{{ __('Requested Items') }}</h6>

                                    {{-- Desktop / Tablet View --}}
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Variant') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Remarks') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($productRequisition->items as $item)
                                                <tr>
                                                    <td class="ps-2">
                                                        {{ $item->product?->name }}
                                                        @if($item->product?->code)
                                                            ({{ $item->product->code }})
                                                        @endif
                                                    </td>
                                                    <td class="ps-2">{{ $item->productVariant?->variant_name ?? '' }}</td>
                                                    <td class="ps-2">{{ $item->quantity }} ({{ $item->unit?->symbol }})</td>
                                                    <td class="ps-2">{{ $item->remarks }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        {{ __('No Items Found') }}
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Mobile View --}}
                                    <div class="d-md-none">
                                        @forelse($productRequisition->items as $item)
                                            <div class="border rounded mb-2 p-2">
                                                {{-- Product --}}
                                                <div class="mb-2">
                                                    <div class="small text-muted">{{ __('Product') }}</div>
                                                    <div class="fw-semibold">
                                                        {{ $item->product?->name }}
                                                        @if($item->product?->code)
                                                            <span class="text-muted">({{ $item->product->code }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                {{-- Variant --}}
                                                @if($item->productVariant?->variant_name)
                                                    <div class="mb-2">
                                                        <div class="small text-muted">{{ __('Variant') }}</div>
                                                        <div>{{ $item->productVariant->variant_name }}</div>
                                                    </div>
                                                @endif
                                                {{-- Quantity + Unit --}}
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <div class="small text-muted">{{ __('Quantity') }}</div>
                                                        <div class="fw-semibold">
                                                            {{ $item->quantity }}
                                                            <span class="text-muted">({{ $item->unit?->symbol }})</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Remarks --}}
                                                @if($item->remarks)
                                                    <div>
                                                        <div class="small text-muted">{{ __('Remarks') }}</div>
                                                        <div>{{ $item->remarks }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-2">
                                                {{ __('No Items Found') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-start mt-2 pb-2">
                                    @if($productRequisition->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-product-requisition" id="restoreProductRequisition" data-product-requisition-id="{{ $productRequisition->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                        <button class="btn btn-sm btn-danger delete-product-requisition" id="deleteProductRequisition" data-product-requisition-id="{{ $productRequisition->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                    @else
                                        @if($productRequisition->status === 'pending')
                                            <a href="{{ route('product-requisition.edit', $productRequisition->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-product-requisition" id="destroyProductRequisition" data-product-requisition-id="{{ $productRequisition->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                        @endif
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="statusUpdateForm" action="{{ route('product-requisition.update-status', $productRequisition->id) }}" method="POST" data-current-status="{{ $productRequisition->status }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Requisition Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" @checked($productRequisition->status === 'pending')>
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" @checked($productRequisition->status === 'approved')>
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" @checked($productRequisition->status === 'rejected')>
                                            <label class="form-check-label" for="statusRejected"> {{ __('Reject') }} </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start" > {{ __('Add Remarks') }} </label>
                                    <div class="col-12 col-md-8 pt-2">
                                        <textarea id="remarks" name="remarks" class="form-control" rows="2" ></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="product_requisition_id" value="{{ $productRequisition->id }}">
                            </div>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" > {{ __('Cancel') }} </button>
                                <button type="reset" class="btn btn-sm btn-warning" > {{ __('Reset') }} </button>
                                <button type="submit" class="btn btn-sm btn-info" id="updateStatusBtn" > {{ __('Update Status') }} </button>
                            </div>
                        </form>
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

            const toastMessage = sessionStorage.getItem('productRequisitionToastMessage');
            const toastType = sessionStorage.getItem('productRequisitionToastType');

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

                sessionStorage.removeItem('productRequisitionToastMessage');
                sessionStorage.removeItem('productRequisitionToastType');
            }

            $(document).on('click', '.destroy-product-requisition', function () {
                const productRequisitionID = $(this).data('product-requisition-id');
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
                            url: '/product-requisition/' + productRequisitionID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productRequisitionToastMessage', response.message || 'Requisition Destroyed Successfully.');
                                sessionStorage.setItem('productRequisitionToastType', 'success');
                                window.location.href = '/product-requisition/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('productRequisitionToastMessage', message);
                                sessionStorage.setItem('productRequisitionToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-product-requisition', function () {
                const productRequisitionID = $(this).data('product-requisition-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore this Data.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/product-requisition/' + productRequisitionID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productRequisitionToastMessage', response.message || 'Requisition Restored Successfully.');
                                sessionStorage.setItem('productRequisitionToastType', 'success');
                                window.location.href = '/product-requisition/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('productRequisitionToastMessage', message);
                                sessionStorage.setItem('productRequisitionToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-product-requisition', function () {
                const productRequisitionID = $(this).data('product-requisition-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Permanently Delete this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/product-requisition/' + productRequisitionID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productRequisitionToastMessage', response.message || 'Requisition Deleted Permanently.');
                                sessionStorage.setItem('productRequisitionToastType', 'success');
                                window.location.href = '/product-requisition/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('productRequisitionToastMessage', message);
                                sessionStorage.setItem('productRequisitionToastType', 'failed');
                                window.location.reload();
                            }
                        });

                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | UPDATE REQUISITION STATUS
            |--------------------------------------------------------------------------
            */
            const $statusForm = $('#statusUpdateForm');
            const currentStatus = $statusForm.data('current-status');
            const $statusUpdateBtn = $('#updateStatusBtn');
            const $statusUpdateError = $('#statusUpdateError');

            function syncStatusSubmitState() {
                const selected = $statusForm.find('input[name="status"]:checked').val();
                const unchanged = selected === currentStatus;

                $statusUpdateBtn.prop('disabled', unchanged);

                if (unchanged) {
                    $statusUpdateError.html('{{ __("Select a Different Status to Update.") }}').removeClass('d-none');
                } else {
                    $statusUpdateError.addClass('d-none').html('');
                }
            }

            $(document).on('shown.bs.modal', '#statusUpdateModal', syncStatusSubmitState);
            $statusForm.on('change', 'input[name="status"]', syncStatusSubmitState);
            syncStatusSubmitState();

            $statusForm.on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const button = $statusUpdateBtn;
                const errorBox = $statusUpdateError;
                errorBox.addClass('d-none').html('');

                const selectedStatus = form.find('input[name="status"]:checked').val();
                if (selectedStatus === currentStatus) {
                    errorBox.html('{{ __("Select a Different Status to Update.") }}').removeClass('d-none');
                    return;
                }

                button.prop('disabled', true);
                button.html('<i class="ri-loader-4-line ri-spin"></i> {{ __("Updating...") }}');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        if (response.success) {
                            sessionStorage.setItem('productRequisitionToastMessage', response.message || 'Requisition Status Updated Successfully.');
                            sessionStorage.setItem('productRequisitionToastType', 'success');
                            window.location.reload();
                        } else {
                            errorBox.html(response.message || 'There was an issue updating the Status.').removeClass('d-none');
                            button.prop('disabled', false);
                            button.html('{{ __("Update Status") }}');
                        }
                    },
                    error: function (xhr) {
                        const message = xhr.responseJSON?.message || 'There was an issue updating the Status.';
                        errorBox.html(message).removeClass('d-none');
                        button.prop('disabled', false);
                        button.html('{{ __("Update Status") }}');
                    }
                });
            });
        });
    </script>
@endpush
