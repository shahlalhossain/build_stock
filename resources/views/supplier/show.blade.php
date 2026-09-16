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
                                <div class="col-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Supplier Type') }}</th><td class="text-start ps-2">{{ $supplier->supplier_type_id }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $supplier->code }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $supplier->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('TIN Number') }}</th><td class="text-start ps-2">{{ $supplier->tin_number }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('BIN Number') }}</th><td class="text-start ps-2">{{ $supplier->bin_number }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($supplier->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($supplier->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Remarks') }}</th><td class="text-start ps-2">{{ $supplier->remarks }}</td></tr>

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
                            </div>

                            <hr>

                            <h5>{{ __('Contacts') }}</h5>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Designation') }}</th>
                                        <th>{{ __('Email') }}</th>
                                        <th>{{ __('Mobile') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Primary') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($supplier->contacts as $contact)
                                        <tr>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->designation }}</td>
                                            <td>{{ $contact->email }}</td>
                                            <td>{{ $contact->mobile }}</td>
                                            <td>{{ $contact->contact_type }}</td>
                                            <td>{{ $contact->is_primary ? __('Yes') : __('No') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">{{ __('No Contacts Found') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <h5>{{ __('Addresses') }}</h5>
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
                                            <td>{{ $address->address_type }}</td>
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

                            <h5>{{ __('Payment Accounts') }}</h5>
                            <div class="table-responsive mb-3">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Method') }}</th>
                                        <th>{{ __('Account Name') }}</th>
                                        <th>{{ __('Account Number') }}</th>
                                        <th>{{ __('Bank') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Primary') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($supplier->paymentAccounts as $paymentAccount)
                                        <tr>
                                            <td>{{ $paymentAccount->payment_method }}</td>
                                            <td>{{ $paymentAccount->account_name }}</td>
                                            <td>{{ $paymentAccount->account_number }}</td>
                                            <td>{{ $paymentAccount->bank_name }}</td>
                                            <td>{{ $paymentAccount->branch_name }}</td>
                                            <td>{{ $paymentAccount->is_primary ? __('Yes') : __('No') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">{{ __('No Payment Accounts Found') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <h5>{{ __('MFS Accounts') }}</h5>
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
        });
    </script>
@endpush
