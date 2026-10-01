@extends('layout.master')

@section('title', __('Product Receive'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Receive Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-receive.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('product-receive.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('product-receive.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-7 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $productReceive->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Transfer') }}</th><td class="text-start ps-2"><a href="{{ route('product-transfer.show', $productReceive->transfer->id) }}" class="badge bg-secondary text-decoration-none">{{ $productReceive->transfer?->code }}</a></td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Source Store') }}</th><td class="text-start ps-2">{{ $productReceive->transfer?->sourceStore?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Destination Store') }}</th><td class="text-start ps-2">{{ $productReceive->transfer?->destinationStore?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Receive Date') }}</th><td class="text-start ps-2">{{ optional($productReceive->transaction_date)->format('d F, Y') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $productReceive->remarks }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($productReceive->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($productReceive->is_active == 0)
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
                                                        $status = $productReceive->status;
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
                                                @if(!$productReceive->trashed())
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

                                    <h6 class="fw-bold fst-italic">{{ __('Received Items') }}</h6>

                                    {{-- Desktop / Tablet View --}}
                                    <div class="table-responsive d-none d-md-block">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Variant') }}</th>
                                                <th>{{ __('Sent Qty') }}</th>
                                                <th>{{ __('Received Qty') }}</th>
                                                <th>{{ __('Unit') }}</th>
                                                <th>{{ __('Variance Remarks') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($productReceive->items as $item)
                                                <tr>
                                                    <td>{{ $item->product?->name }} @if($item->product?->code) ({{ $item->product->code }}) @endif</td>
                                                    <td>{{ $item->productVariant?->variant_name ?? '' }}</td>
                                                    <td>{{ $item->transferItem?->quantity }}</td>
                                                    <td>{{ $item->received_quantity }}</td>
                                                    <td>{{ $item->unit?->name }} @if($item->unit?->symbol) ({{ $item->unit->symbol }}) @endif</td>
                                                    <td>{{ $item->variance_remarks }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center">{{ __('No Line Items Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    {{-- Mobile View --}}
                                    <div class="d-md-none">
                                        @forelse($productReceive->items as $item)
                                            <div class="border rounded mb-2 p-2">
                                                {{-- Product (+ Variant) --}}
                                                <div class="row mb-2">
                                                    <div class="fw-semibold">
                                                        {{ $item->product?->name }}
                                                        @if($item->product?->code)
                                                            <span class="text-muted">({{ $item->product->code }})</span>
                                                        @endif
                                                        @if($item->productVariant?->variant_name)
                                                            <span class="text-muted">({{ $item->productVariant->variant_name }})</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                {{-- Sent Qty + Received Qty --}}
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <div class="small text-muted">{{ __('Sent Qty') }}</div>
                                                        <div>
                                                            {{ $item->transferItem?->quantity }}
                                                            <span class="text-muted">({{ $item->unit?->symbol }})</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="small text-muted">{{ __('Received Qty') }}</div>
                                                        <div class="fw-semibold">
                                                            {{ $item->received_quantity }}
                                                            <span class="text-muted">({{ $item->unit?->symbol }})</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Variance Remarks --}}
                                                @if($item->variance_remarks)
                                                    <div class="row mb-2">
                                                        <div class="small text-muted">{{ __('Variance Remarks') }}</div>
                                                        <div>{{ $item->variance_remarks }}</div>
                                                    </div>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="text-center text-muted py-2">
                                                {{ __('No Line Items Found') }}
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 ps-5 order-2">
                                    @php
                                        $auditLogs = [
                                            [
                                                'action' => __('Created'),
                                                'user' => $productReceive->creator?->name,
                                                'at' => $productReceive->created_at,
                                            ],
                                            [
                                                'action' => __('Updated'),
                                                'user' => $productReceive->updater?->name,
                                                'at' => $productReceive->updated_at,
                                            ],
                                        ];

                                        if ($productReceive->trashed()) {
                                            $auditLogs[] = [
                                                'action' => __('Deleted'),
                                                'user' => $productReceive->deleter?->name,
                                                'at' => $productReceive->deleted_at,
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

                                    @if($productReceive->approvalLogs->isNotEmpty())
                                        @foreach($productReceive->approvalLogs as $log)
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

                            <div class="row">
                                <div class="col-12 text-start mt-2 pb-2">
                                    @if($productReceive->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-product-receive" id="restoreProductReceive" data-product-receive-id="{{ $productReceive->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                        <button class="btn btn-sm btn-danger delete-product-receive" id="deleteProductReceive" data-product-receive-id="{{ $productReceive->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                    @else
                                        @if($productReceive->status === 'pending')
                                            <a href="{{ route('product-receive.edit', $productReceive->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-product-receive" id="destroyProductReceive" data-product-receive-id="{{ $productReceive->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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
                        <form id="statusUpdateForm" action="{{ route('product-receive.update-status', $productReceive->id) }}" method="POST" data-current-status="{{ $productReceive->status }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Receive Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" @checked($productReceive->status === 'pending')>
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" @checked($productReceive->status === 'approved')>
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" @checked($productReceive->status === 'rejected')>
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
                                <input type="hidden" name="product_receive_id" value="{{ $productReceive->id }}">
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

            const toastMessage = sessionStorage.getItem('productReceiveToastMessage');
            const toastType = sessionStorage.getItem('productReceiveToastType');

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

                sessionStorage.removeItem('productReceiveToastMessage');
                sessionStorage.removeItem('productReceiveToastType');
            }

            $(document).on('click', '.destroy-product-receive', function () {
                const productReceiveID = $(this).data('product-receive-id');
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
                            url: '/product-receive/' + productReceiveID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productReceiveToastMessage', response.message || 'Receive Destroyed Successfully.');
                                sessionStorage.setItem('productReceiveToastType', 'success');
                                window.location.href = '/product-receive/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('productReceiveToastMessage', message);
                                sessionStorage.setItem('productReceiveToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-product-receive', function () {
                const productReceiveID = $(this).data('product-receive-id');
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
                            url: '/product-receive/' + productReceiveID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productReceiveToastMessage', response.message || 'Receive Restored Successfully.');
                                sessionStorage.setItem('productReceiveToastType', 'success');
                                window.location.href = '/product-receive/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('productReceiveToastMessage', message);
                                sessionStorage.setItem('productReceiveToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-product-receive', function () {
                const productReceiveID = $(this).data('product-receive-id');
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
                            url: '/product-receive/' + productReceiveID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productReceiveToastMessage', response.message || 'Receive Deleted Permanently.');
                                sessionStorage.setItem('productReceiveToastType', 'success');
                                window.location.href = '/product-receive/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('productReceiveToastMessage', message);
                                sessionStorage.setItem('productReceiveToastType', 'failed');
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
            | UPDATE RECEIVE STATUS
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
                            sessionStorage.setItem('productReceiveToastMessage', response.message || 'Receive Status Updated Successfully.');
                            sessionStorage.setItem('productReceiveToastType', 'success');
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
