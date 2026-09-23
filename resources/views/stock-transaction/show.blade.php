@extends('layout.master')

@section('title', __('Stock Transaction'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Stock Transaction Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('stock-transaction.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('stock-transaction.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('stock-transaction.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-7 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $stockTransaction->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Type') }}</th><td class="text-start ps-2"><span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $stockTransaction->type)) }}</span></td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Store') }}</th><td class="text-start ps-2">{{ $stockTransaction->store?->name }}</td></tr>
                                        @if($stockTransaction->type === 'purchase')
                                            <tr><th class="text-end pe-2">{{ __('Supplier') }}</th><td class="text-start ps-2">{{ $stockTransaction->supplier?->name ?? '' }}</td></tr>
                                        @endif
                                        @if($stockTransaction->isTransfer() && $stockTransaction->linkedTransaction)
                                            <tr>
                                                <th class="text-end pe-2">{{ $stockTransaction->type === 'transfer_out' ? __('Destination Store') : __('Source Store') }}</th>
                                                <td class="text-start ps-2">
                                                    {{ $stockTransaction->linkedTransaction->store?->name }}
                                                    <a href="{{ route('stock-transaction.show', $stockTransaction->linkedTransaction->id) }}" class="badge bg-secondary text-decoration-none">{{ $stockTransaction->linkedTransaction->code }}</a>
                                                </td>
                                            </tr>
                                        @endif
                                        <tr><th class="text-end pe-2">{{ __('Transaction Date') }}</th><td class="text-start ps-2">{{ optional($stockTransaction->transaction_date)->format('d F, Y') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $stockTransaction->remarks }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($stockTransaction->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($stockTransaction->is_active == 0)
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
                                                        $status = $stockTransaction->status;
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
                                                @if(!$stockTransaction->trashed())
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

                                    <h6 class="fw-bold fst-italic">{{ __('Line Items') }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Unit Cost') }}</th>
                                                <th>{{ __('Remarks') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($stockTransaction->items as $item)
                                                <tr>
                                                    <td>{{ $item->product?->name }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $item->unit_cost ?? '' }}</td>
                                                    <td>{{ $item->remarks }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="text-center">{{ __('No Line Items Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 ps-5 order-2">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $stockTransaction->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $stockTransaction->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $stockTransaction->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $stockTransaction->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($stockTransaction->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $stockTransaction->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $stockTransaction->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    @if($stockTransaction->approvalLogs->isNotEmpty())
                                        @foreach($stockTransaction->approvalLogs as $log)
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
                                    @else
                                        <p>No Approval History Found</p>
                                    @endif
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-start mt-2 pb-2">
                                    @if($stockTransaction->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-stock-transaction" id="restoreStockTransaction" data-stock-transaction-id="{{ $stockTransaction->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                        <button class="btn btn-sm btn-danger delete-stock-transaction" id="deleteStockTransaction" data-stock-transaction-id="{{ $stockTransaction->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                    @else
                                        @if($stockTransaction->status === 'pending')
                                            <a href="{{ route('stock-transaction.edit', $stockTransaction->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-stock-transaction" id="destroyStockTransaction" data-stock-transaction-id="{{ $stockTransaction->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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
                        <form id="statusUpdateForm" action="{{ route('stock-transaction.update-status', $stockTransaction->id) }}" method="POST" data-current-status="{{ $stockTransaction->status }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Stock Transaction Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" @checked($stockTransaction->status === 'pending')>
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" @checked($stockTransaction->status === 'approved')>
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" @checked($stockTransaction->status === 'rejected')>
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
                                <input type="hidden" name="stock_transaction_id" value="{{ $stockTransaction->id }}">
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
            /*
            |--------------------------------------------------------------------------
            | PAGE LOAD TOAST
            |--------------------------------------------------------------------------
            | Shows Success/Failed Toast After Page Reload
            |--------------------------------------------------------------------------
            */
            const toastMessage = sessionStorage.getItem('stockTransactionToastMessage');
            const toastType = sessionStorage.getItem('stockTransactionToastType');

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
                sessionStorage.removeItem('stockTransactionToastMessage');
                sessionStorage.removeItem('stockTransactionToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE STOCK TRANSACTION
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-stock-transaction', function () {
                const stockTransactionID = $(this).data('stock-transaction-id');
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
                            url: '/stock-transaction/' + stockTransactionID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('stockTransactionToastMessage', response.message || 'Stock Transaction Destroyed Successfully.');
                                sessionStorage.setItem('stockTransactionToastType', 'success');
                                window.location.href = '/stock-transaction/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('stockTransactionToastMessage', message);
                                sessionStorage.setItem('stockTransactionToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | RESTORE STOCK TRANSACTION
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-stock-transaction', function () {
                const stockTransactionID = $(this).data('stock-transaction-id');
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
                            url: '/stock-transaction/' + stockTransactionID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('stockTransactionToastMessage', response.message || 'Stock Transaction Restored Successfully.');
                                sessionStorage.setItem('stockTransactionToastType', 'success');
                                window.location.href = '/stock-transaction/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('stockTransactionToastMessage', message);
                                sessionStorage.setItem('stockTransactionToastType', 'failed');
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
            | PERMANENT DELETE STOCK TRANSACTION
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-stock-transaction', function () {
                const stockTransactionID = $(this).data('stock-transaction-id');
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
                            url: '/stock-transaction/' + stockTransactionID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('stockTransactionToastMessage', response.message || 'Stock Transaction Deleted Permanently.');
                                sessionStorage.setItem('stockTransactionToastType', 'success');
                                window.location.href = '/stock-transaction/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('stockTransactionToastMessage', message);
                                sessionStorage.setItem('stockTransactionToastType', 'failed');
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
            | UPDATE STOCK TRANSACTION STATUS
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
                // Clear Previous Errors
                errorBox.addClass('d-none').html('');

                const selectedStatus = form.find('input[name="status"]:checked').val();
                if (selectedStatus === currentStatus) {
                    errorBox.html('{{ __("Select a Different Status to Update.") }}').removeClass('d-none');
                    return;
                }

                // Disable Button
                button.prop('disabled', true);

                button.html('<i class="ri-loader-4-line ri-spin"></i> {{ __("Updating...") }}');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS UPDATE SUCCESS
                    |--------------------------------------------------------------------------
                    */
                    success: function (response) {
                        if (response.success) {
                            sessionStorage.setItem('stockTransactionToastMessage', response.message || 'Stock Transaction Status Updated Successfully.');
                            sessionStorage.setItem('stockTransactionToastType', 'success');
                            $('#statusUpdateModal').modal('hide');
                            window.location.reload();
                        }
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS UPDATE ERROR
                    |--------------------------------------------------------------------------
                    */
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            const messages = [];
                            $.each(errors, function (field, errorMessages) {
                                if (errorMessages.length > 0) {
                                    messages.push(errorMessages[0]);
                                }
                            });

                            if (!messages.length && xhr.responseJSON?.message) {
                                messages.push(xhr.responseJSON.message);
                            }

                            errorBox.html(messages.join('<br>') || '{{ __("Validation Failed. Please Check Your Input.") }}').removeClass('d-none');
                            return;
                        }

                        const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';

                        sessionStorage.setItem('stockTransactionToastMessage', message);
                        sessionStorage.setItem('stockTransactionToastType', 'failed');
                        window.location.reload();
                    },

                    complete: function () {
                        button.prop('disabled', false);
                        button.html('{{ __("Update Status") }}');
                    }
                });
            });
        });
    </script>
@endpush
