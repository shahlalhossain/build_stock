@extends('layout.master')

@section('title', __('Product Purchase'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Purchase Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-purchase.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('product-purchase.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('product-purchase.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-7 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        @if($productPurchase->requisition)
                                            <tr><th class="text-end pe-2">{{ __('Requisition No.') }}</th><td class="text-start ps-2"><a href="{{ route('product-requisition.show', $productPurchase->requisition->id) }}" class="badge bg-secondary text-decoration-none">{{ $productPurchase->requisition->code }}</a></td></tr>
                                        @endif
                                        <tr><th class="text-end pe-2">{{ __('Receiving Store') }}</th><td class="text-start ps-2">{{ $productPurchase->store?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Supplier') }}</th><td class="text-start ps-2">{{ $productPurchase->supplier?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Purchase Date') }}</th><td class="text-start ps-2">{{ optional($productPurchase->transaction_date)->format('d F, Y') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Invoice Number') }}</th><td class="text-start ps-2">{{ $productPurchase->invoice_number }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Invoice Date') }}</th><td class="text-start ps-2">{{ optional($productPurchase->supplier_invoice_date)->format('d F, Y') }}</td></tr>
                                        @if($productPurchase->invoice_attachment_path)
                                            <tr><th class="text-end pe-2">{{ __('Invoice Attachment') }}</th><td class="text-start ps-2"><a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($productPurchase->invoice_attachment_path) }}" target="_blank">{{ __('Download') }}</a></td></tr>
                                        @endif
                                        <tr><th class="text-end pe-2">{{ __('Total Amount') }}</th><td class="text-start ps-2">{{ number_format((float) $productPurchase->total_amount, 2) }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Discount') }}</th><td class="text-start ps-2">{{ $productPurchase->discount_type ? ucfirst($productPurchase->discount_type) . ' - ' . number_format((float) $productPurchase->discount_amount, 2) : '--' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Tax Amount') }}</th><td class="text-start ps-2">{{ number_format((float) $productPurchase->tax_amount, 2) }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Net Amount') }}</th><td class="text-start ps-2 fw-bold">{{ number_format((float) $productPurchase->net_amount, 2) }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Payment Status') }}</th><td class="text-start ps-2">{{ ucfirst($productPurchase->payment_status) }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Paid Amount') }}</th><td class="text-start ps-2">{{ number_format((float) $productPurchase->paid_amount, 2) }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $productPurchase->remarks }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($productPurchase->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($productPurchase->is_active == 0)
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
                                                        $status = $productPurchase->status;
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
                                                @if(!$productPurchase->trashed())
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

                                <div class="col-12 col-md-5 ps-3 order-2">
                                    @if($productPurchase->invoice_attachment_path)
                                        @php
                                            $invoiceUrl         = \Illuminate\Support\Facades\Storage::disk('public')->url($productPurchase->invoice_attachment_path);
                                            $invoiceExtension   = strtolower(pathinfo($productPurchase->invoice_attachment_path, PATHINFO_EXTENSION));
                                            $isImageAttachment  = in_array($invoiceExtension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                                        @endphp

                                        <div class="text-center mb-3">
                                            <a href="{{ $invoiceUrl }}" target="_blank" rel="noopener noreferrer">
                                                @if($isImageAttachment)
                                                    <img src="{{ $invoiceUrl }}" alt="{{ __('Attached Invoice') }}" class="img-fluid img-thumbnail" style=" max-width: 100%; max-height: 350px; width: auto; height: auto; object-fit: contain;">
                                                    <div class="mt-2 fw-semibold">{{ __('Attached Invoice') }}</div>
                                                @else
                                                    <span class="d-inline-flex flex-column align-items-center text-decoration-none">
                                                        <i class="ri-file-pdf-2-line" style="font-size: 4rem; line-height: 1;"></i>
                                                        <span class="mt-1">{{ __('View PDF Invoice') }}</span>
                                                    </span>
                                                @endif
                                            </a>
                                        </div>
                                    @endif

                                    @php
                                        $auditLogs = [
                                            [
                                                'action'    => __('Created'),
                                                'user'      => $productPurchase->creator?->name,
                                                'at'        => $productPurchase->created_at,
                                            ],
                                            [
                                                'action'    => __('Updated'),
                                                'user'      => $productPurchase->updater?->name,
                                                'at'        => $productPurchase->updated_at,
                                            ],
                                        ];

                                        if ($productPurchase->trashed()) {
                                            $auditLogs[] = [
                                                'action'    => __('Deleted'),
                                                'user'      => $productPurchase->deleter?->name,
                                                'at'        => $productPurchase->deleted_at,
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

                                    @if($productPurchase->approvalLogs->isNotEmpty())
                                        @foreach($productPurchase->approvalLogs as $log)
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
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
                                    <h6 class="fw-bold fst-italic">{{ __('Purchase Items') }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Product') }}</th>
                                                <th>{{ __('Variant') }}</th>
                                                <th>{{ __('Quantity') }}</th>
                                                <th>{{ __('Unit') }}</th>
                                                <th>{{ __('Unit Cost') }}</th>
                                                <th>{{ __('Line Total') }}</th>
                                                <th>{{ __('Remarks') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($productPurchase->items as $item)
                                                <tr>
                                                    <td>{{ $item->product?->name }} @if($item->product?->code) ({{ $item->product->code }}) @endif</td>
                                                    <td>{{ $item->productVariant?->variant_name ?? '' }}</td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $item->unit?->name }} @if($item->unit?->symbol) ({{ $item->unit->symbol }}) @endif</td>
                                                    <td>{{ $item->unit_cost !== null ? number_format((float) $item->unit_cost, 2) : '' }}</td>
                                                    <td>{{ $item->line_total !== null ? number_format((float) $item->line_total, 2) : '' }}</td>
                                                    <td>{{ $item->remarks }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="text-center">{{ __('No Line Items Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-start mt-2 pb-2">
                                    @if($productPurchase->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-product-purchase" id="restoreProductPurchase" data-product-purchase-id="{{ $productPurchase->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                        <button class="btn btn-sm btn-danger delete-product-purchase" id="deleteProductPurchase" data-product-purchase-id="{{ $productPurchase->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                    @else
                                        @if($productPurchase->status === 'pending')
                                            <a href="{{ route('product-purchase.edit', $productPurchase->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-product-purchase" id="destroyProductPurchase" data-product-purchase-id="{{ $productPurchase->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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
                        <form id="statusUpdateForm" action="{{ route('product-purchase.update-status', $productPurchase->id) }}" method="POST" data-current-status="{{ $productPurchase->status }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Purchase Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" @checked($productPurchase->status === 'pending')>
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" @checked($productPurchase->status === 'approved')>
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" @checked($productPurchase->status === 'rejected')>
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
                                <input type="hidden" name="product_purchase_id" value="{{ $productPurchase->id }}">
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

            const toastMessage = sessionStorage.getItem('productPurchaseToastMessage');
            const toastType = sessionStorage.getItem('productPurchaseToastType');

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

                sessionStorage.removeItem('productPurchaseToastMessage');
                sessionStorage.removeItem('productPurchaseToastType');
            }

            $(document).on('click', '.destroy-product-purchase', function () {
                const productPurchaseID = $(this).data('product-purchase-id');
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
                            url: '/product-purchase/' + productPurchaseID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productPurchaseToastMessage', response.message || 'Purchase Destroyed Successfully.');
                                sessionStorage.setItem('productPurchaseToastType', 'success');
                                window.location.href = '/product-purchase/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('productPurchaseToastMessage', message);
                                sessionStorage.setItem('productPurchaseToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-product-purchase', function () {
                const productPurchaseID = $(this).data('product-purchase-id');
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
                            url: '/product-purchase/' + productPurchaseID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productPurchaseToastMessage', response.message || 'Purchase Restored Successfully.');
                                sessionStorage.setItem('productPurchaseToastType', 'success');
                                window.location.href = '/product-purchase/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('productPurchaseToastMessage', message);
                                sessionStorage.setItem('productPurchaseToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-product-purchase', function () {
                const productPurchaseID = $(this).data('product-purchase-id');
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
                            url: '/product-purchase/' + productPurchaseID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productPurchaseToastMessage', response.message || 'Purchase Deleted Permanently.');
                                sessionStorage.setItem('productPurchaseToastType', 'success');
                                window.location.href = '/product-purchase/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('productPurchaseToastMessage', message);
                                sessionStorage.setItem('productPurchaseToastType', 'failed');
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
            | UPDATE PURCHASE STATUS
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
                            sessionStorage.setItem('productPurchaseToastMessage', response.message || 'Purchase Status Updated Successfully.');
                            sessionStorage.setItem('productPurchaseToastType', 'success');
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
