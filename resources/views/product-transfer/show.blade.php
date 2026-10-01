@extends('layout.master')

@section('title', __('Product Transfer'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Transfer Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-transfer.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('product-transfer.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('product-transfer.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-7 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $productTransfer->code }}</td></tr>
                                        @if($productTransfer->requisition)
                                            <tr><th class="text-end pe-2">{{ __('Requisition') }}</th><td class="text-start ps-2"><a href="{{ route('product-requisition.show', $productTransfer->requisition->id) }}" class="badge bg-secondary text-decoration-none">{{ $productTransfer->requisition->code }}</a></td></tr>
                                        @endif
                                        <tr><th class="text-end pe-2">{{ __('Source Store') }}</th><td class="text-start ps-2">{{ $productTransfer->sourceStore?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Destination Store') }}</th><td class="text-start ps-2">{{ $productTransfer->destinationStore?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Transfer Date') }}</th><td class="text-start ps-2">{{ optional($productTransfer->transaction_date)->format('d F, Y') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $productTransfer->remarks }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($productTransfer->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($productTransfer->is_active == 0)
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
                                                        $status = $productTransfer->status;
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
                                                @if(!$productTransfer->trashed())
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
                                    @if($productTransfer->status === 'approved')
                                        <h6 class="fw-bold fst-italic">{{ __('Receives Against this Transfer') }}</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead>
                                                <tr>
                                                    <th>{{ __('Code') }}</th>
                                                    <th>{{ __('Received Date') }}</th>
                                                    <th>{{ __('Status') }}</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @forelse($productTransfer->receives as $receive)
                                                    <tr>
                                                        <td><a href="{{ route('product-receive.show', $receive->id) }}">{{ $receive->code }}</a></td>
                                                        <td>{{ optional($receive->transaction_date)->format('d F, Y') }}</td>
                                                        <td>{{ ucfirst($receive->status) }}</td>
                                                    </tr>
                                                @empty
                                                    <tr><td colspan="3" class="text-center">{{ __('No Receives Recorded Yet') }}</td></tr>
                                                @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-12 col-md-5 order-2">

                                    @php
                                        $auditLogs = [
                                            [
                                                'action'    => __('Created'),
                                                'user'      => $productTransfer->creator?->name,
                                                'at'        => $productTransfer->created_at,
                                            ],
                                            [
                                                'action'    => __('Updated'),
                                                'user'      => $productTransfer->updater?->name,
                                                'at'        => $productTransfer->updated_at,
                                            ],
                                        ];

                                        if ($productTransfer->trashed()) {
                                            $auditLogs[] = [
                                                'action'    => __('Deleted'),
                                                'user'      => $productTransfer->deleter?->name,
                                                'at'        => $productTransfer->deleted_at,
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

                                    @if($productTransfer->approvalLogs->isNotEmpty())
                                        @foreach($productTransfer->approvalLogs as $log)
                                            <hr style="padding: 0 !important; margin: 0 !important;">
                                            <div class="pb-2 pt-2">
                                                <strong>{{ ucfirst($log->action_name) }}</strong>

                                                @if($log->actionedBy)
                                                    by <em>{{ $log->actionedBy->name }}</em>
                                                @endif

                                                @if($log->actioned_at)
                                                    on {{ $log->actioned_at->format('d F, Y h:i A') }}
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
                                    <h6 class="fw-bold fst-italic">{{ __('Transfer Items') }}</h6>

                                    {{-- Desktop / Tablet View --}}
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Remarks') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($productTransfer->items as $item)
                                                <tr>
                                                    <td>
                                                        {{ $item->product?->name }}
                                                        @if($item->productVariant?->variant_name) ({{ $item->productVariant?->variant_name }}) @endif
                                                    </td>
                                                    <td>
                                                        {{ $item->quantity }}
                                                        @if($item->unit?->symbol) ({{ $item->unit->symbol }}) @endif
                                                    </td>
                                                    <td>{{ $item->remarks }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center">{{ __('No Transfer Items Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Mobile View --}}
                                    <div class="d-md-none">
                                        @forelse($productTransfer->items as $item)
                                            <div class="border rounded mb-2 p-2">
                                                {{-- Product (+ Variant) --}}
                                                <div class="row mb-2">
                                                    <div class="fw-semibold">
                                                        {{ $item->product?->name }}
                                                        @if($item->productVariant?->variant_name)
                                                            <span class="text-muted">({{ $item->productVariant->variant_name }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                {{-- Quantity + Unit --}}
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <div class="fw-semibold">
                                                            {{ $item->quantity }}
                                                            <span class="text-muted">({{ $item->unit?->symbol }})</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Remarks --}}
                                                @if($item->remarks)
                                                    <div class="row mb-2">
                                                        <div class="small text-muted">{{ __('Remarks') }}</div>
                                                        <div>{{ $item->remarks }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-2">
                                                {{ __('No Transfer Items Found') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-start mt-2 pb-2">
                                    @if($productTransfer->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-product-transfer" id="restoreProductTransfer" data-product-transfer-id="{{ $productTransfer->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                        <button class="btn btn-sm btn-danger delete-product-transfer" id="deleteProductTransfer" data-product-transfer-id="{{ $productTransfer->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                    @else
                                        @if($productTransfer->status === 'pending')
                                            <a href="{{ route('product-transfer.edit', $productTransfer->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-product-transfer" id="destroyProductTransfer" data-product-transfer-id="{{ $productTransfer->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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
                        <form id="statusUpdateForm" action="{{ route('product-transfer.update-status', $productTransfer->id) }}" method="POST" data-current-status="{{ $productTransfer->status }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Transfer Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" @checked($productTransfer->status === 'pending')>
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" @checked($productTransfer->status === 'approved')>
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" @checked($productTransfer->status === 'rejected')>
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
                                <input type="hidden" name="product_transfer_id" value="{{ $productTransfer->id }}">
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

            const toastMessage = sessionStorage.getItem('productTransferToastMessage');
            const toastType = sessionStorage.getItem('productTransferToastType');

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

                sessionStorage.removeItem('productTransferToastMessage');
                sessionStorage.removeItem('productTransferToastType');
            }

            $(document).on('click', '.destroy-product-transfer', function () {
                const productTransferID = $(this).data('product-transfer-id');
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
                            url: '/product-transfer/' + productTransferID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productTransferToastMessage', response.message || 'Transfer Destroyed Successfully.');
                                sessionStorage.setItem('productTransferToastType', 'success');
                                window.location.href = '/product-transfer/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('productTransferToastMessage', message);
                                sessionStorage.setItem('productTransferToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-product-transfer', function () {
                const productTransferID = $(this).data('product-transfer-id');
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
                            url: '/product-transfer/' + productTransferID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productTransferToastMessage', response.message || 'Transfer Restored Successfully.');
                                sessionStorage.setItem('productTransferToastType', 'success');
                                window.location.href = '/product-transfer/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('productTransferToastMessage', message);
                                sessionStorage.setItem('productTransferToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-product-transfer', function () {
                const productTransferID = $(this).data('product-transfer-id');
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
                            url: '/product-transfer/' + productTransferID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productTransferToastMessage', response.message || 'Transfer Deleted Permanently.');
                                sessionStorage.setItem('productTransferToastType', 'success');
                                window.location.href = '/product-transfer/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('productTransferToastMessage', message);
                                sessionStorage.setItem('productTransferToastType', 'failed');
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
            | UPDATE TRANSFER STATUS
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
                            sessionStorage.setItem('productTransferToastMessage', response.message || 'Transfer Status Updated Successfully.');
                            sessionStorage.setItem('productTransferToastType', 'success');
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
