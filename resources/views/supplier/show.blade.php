@extends('layout.master')

@section('title', __('Supplier'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Supplier Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('supplier.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('supplier.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-7 order-1">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr>
                                            <th class="text-end pe-2">{{ __('Name') }}</th>
                                            <td class="text-start ps-2">{{ $supplier->name }} <span class="badge rounded-pill border border-secondary text-secondary">{{ $supplier->supplierType?->name }}</span></td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $supplier->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('TIN Number') }}</th><td class="text-start ps-2">{{ $supplier->tin_number }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('BIN Number') }}</th><td class="text-start ps-2">{{ $supplier->bin_number }}</td></tr>

                                        <tr><th class="text-end pe-2">{{ __('Payment Terms') }}</th><td class="text-start ps-2">{{ $supplier->payment_terms_days . " Days" }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Lead Delivery Time') }}</th><td class="text-start ps-2">{{ $supplier->lead_time_days . " Days" }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Credit Limit') }}</th><td class="text-start ps-2">{{ $supplier->credit_limit . " BDT" }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Minimum Order Quantity') }}</th><td class="text-start ps-2">{{ $supplier->minimum_order_quantity }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Minimum Order Amount') }}</th><td class="text-start ps-2">{{ $supplier->minimum_order_amount . " BDT" }}</td></tr>


                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($supplier->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($supplier->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge  bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $supplier->remarks }}</td></tr>
                                        <tr>
                                            <th class="text-end pe-2 justify-content-between">{{ __('Status') }}</th>
                                            <td class="text-start d-flex justify-content-between align-items-center">
                                                <div class="text-start">
                                                    @php
                                                        $status = $supplier->status;
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

                                                @if(!$supplier->trashed())
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

                                    <div class="text-start mt-2 pb-2">
                                        @if($supplier->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-supplier" id="restoreSupplier" data-supplier-id="{{ $supplier->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-supplier" id="deleteSupplier" data-supplier-id="{{ $supplier->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-supplier" id="destroySupplier" data-supplier-id="{{ $supplier->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 col-md-5 ps-5 order-2">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $supplier->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $supplier->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $supplier->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $supplier->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($supplier->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $supplier->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $supplier->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    @if($supplier->approvalLogs->isNotEmpty())
                                        @foreach($supplier->approvalLogs as $log)
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

                            <hr>

                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-bold fst-italic">{{ __('Contacts') }}</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Name') }}</th>
                                                <th>{{ __('Designation') }}</th>
                                                <th>{{ __('Mobile') }}</th>
                                                <th>{{ __('Primary') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($supplier->contacts as $contact)
                                                <tr>
                                                    <td>{{ $contact->name }}</td>
                                                    <td>{{ $contact->designation }}</td>
                                                    <td>{{ $contact->mobile }}</td>
                                                    <td>{{ $contact->is_primary ? __('Yes') : __('No') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center">{{ __('No Contacts Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-bold fst-italic">{{ __('Addresses') }}</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Type') }}</th>
                                                <th>{{ __('Address') }}</th>
                                                <th>{{ __('Division') }}</th>
                                                <th>{{ __('District') }}</th>
                                                <th>{{ __('Thana') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($supplier->addresses as $address)
                                                <tr>
                                                    <td>{{ $address->addressType?->name }}</td>
                                                    <td>{{ $address->address }}</td>
                                                    <td>{{ $address->division_name }}</td>
                                                    <td>{{ $address->district_name }}</td>
                                                    <td>{{ $address->thana_name }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="text-center">{{ __('No Addresses Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-bold fst-italic">{{ __('Payment Accounts') }}</h6>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Account Name') }}</th>
                                                <th>{{ __('Account Number') }}</th>
                                                <th>{{ __('Bank') }}</th>
                                                <th>{{ __('Branch') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($supplier->paymentAccounts as $paymentAccount)
                                                <tr>
                                                    <td>{{ $paymentAccount->account_name }}</td>
                                                    <td>{{ $paymentAccount->account_number }}</td>
                                                    <td>{{ $paymentAccount->bank_name }}</td>
                                                    <td>{{ $paymentAccount->branch_name }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="text-center">{{ __('No Payment Accounts Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-bold fst-italic">{{ __('MFS Accounts') }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                            <tr>
                                                <th>{{ __('Operator') }}</th>
                                                <th>{{ __('Account Number') }}</th>
                                                <th>{{ __('Primary') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($supplier->mfsAccounts as $mfsAccount)
                                                <tr>
                                                    <td>{{ $mfsAccount->mfs_operator_name }}</td>
                                                    <td>{{ $mfsAccount->mfs_account_number }}</td>
                                                    <td>{{ $mfsAccount->is_primary ? __('Yes') : __('No') }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="3" class="text-center">{{ __('No MFS Accounts Found') }}</td></tr>
                                            @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="statusUpdateForm" action="{{ route('supplier.update-status', $supplier->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Supplier Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" >
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" >
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" >
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
                                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}">
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
            const toastMessage = sessionStorage.getItem('supplierToastMessage');
            const toastType = sessionStorage.getItem('supplierToastType');

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
                sessionStorage.removeItem('supplierToastMessage');
                sessionStorage.removeItem('supplierToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE SUPPLIER
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-supplier', function () {
                const supplierID = $(this).data('supplier-id');
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
                            url: '/supplier/' + supplierID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('supplierToastMessage', response.message || 'Supplier Destroyed Successfully.');
                                sessionStorage.setItem('supplierToastType', 'success');
                                // Reload Page
                                window.location.href = '/supplier/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('supplierToastMessage', message);
                                sessionStorage.setItem('supplierToastType', 'failed');
                                // Reload Page
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
            | RESTORE SUPPLIER
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-supplier', function () {
                const supplierID = $(this).data('supplier-id');
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
                            url: '/supplier/' + supplierID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('supplierToastMessage', response.message || 'Supplier Restored Successfully.');
                                sessionStorage.setItem('supplierToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/supplier/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('supplierToastMessage', message);
                                sessionStorage.setItem('supplierToastType', 'failed');
                                // Reload Trash Page
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
            | PERMANENT DELETE SUPPLIER
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-supplier', function () {
                const supplierID = $(this).data('supplier-id');
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
                            url: '/supplier/' + supplierID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                // Store Success Toast Type and Message
                                sessionStorage.setItem('supplierToastMessage', response.message || 'Supplier Deleted Permanently.');
                                sessionStorage.setItem('supplierToastType', 'success');
                                // Reload Trash Page
                                window.location.href = '/supplier/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                // Store Failed Toast Type and Message
                                sessionStorage.setItem('supplierToastMessage', message);
                                sessionStorage.setItem('supplierToastType', 'failed');
                                // Reload Trash Page
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
            | UPDATE SUPPLIER STATUS
            |--------------------------------------------------------------------------
            */
            $('#statusUpdateForm').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const button = $('#updateStatusBtn');
                const errorBox = $('#statusUpdateError');
                // Clear Previous Errors
                errorBox.addClass('d-none').html('');

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
                            sessionStorage.setItem('supplierToastMessage', response.message || 'Supplier Status Updated Successfully.');
                            sessionStorage.setItem('supplierToastType', 'success');
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
                            errorBox.html(messages.join('<br>')).removeClass('d-none');
                            return;
                        }

                        const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';

                        sessionStorage.setItem('supplierToastMessage', message);
                        sessionStorage.setItem('supplierToastType', 'failed');
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
