@extends('layout.master')

@section('title', __('Supplier'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Supplier Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('supplier.store') }}" method="POST" enctype="multipart/form-data" id="supplierForm">
                            @csrf
                            <div class="card-body">

                                <!-- Start Page Error Section -->
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ $error }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endforeach
                                @endif
                                <!-- End Page Error Section -->

                                <h5 class="mb-3">{{ __('Supplier Details') }}</h5>
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="supplier_type_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier Type') }}</label>
                                            <div class="col-12 col-md-8">
                                                {{-- TODO: Replace with a Select sourced from supplier_types once that table/migration is available. --}}
                                                <select id="supplier_type_id" name="supplier_type_id" class="form-select @error('supplier_type_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('===== Select Supplier Type =====') }}</option>
                                                </select>
                                                @error('supplier_type_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="code" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier Code') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
                                                @error('code')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="tin_number" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('TIN Number') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('tin_number') is-invalid @enderror" id="tin_number" name="tin_number" value="{{ old('tin_number') }}">
                                                @error('tin_number')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="bin_number" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('BIN Number') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('bin_number') is-invalid @enderror" id="bin_number" name="bin_number" value="{{ old('bin_number') }}">
                                                @error('bin_number')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Remarks') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="1">{{ old('remarks') }}</textarea>
                                                @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- ===================== CONTACTS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1">{{ __('Contacts') }} <span class="form-mandatory"></span></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="contacts"><i class="ri-add-line"></i> {{ __('Add Contact') }}</button>
                                </div>
                                @error('contacts')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="contacts-rows"></div>

                                <template id="contacts-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="contacts">
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][name]" placeholder="{{ __('Name') }}" required>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][designation]" placeholder="{{ __('Designation') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="email" class="form-control" name="contacts[__INDEX__][email]" placeholder="{{ __('Email') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][mobile]" placeholder="{{ __('Mobile') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][contact_type]" placeholder="{{ __('Contact Type') }}">
                                        </div>
                                        <div class="col-6 col-md-1 text-center pt-2">
                                            <input type="radio" class="form-check-input primary-radio" name="contacts_primary" value="__INDEX__">
                                            <label class="form-check-label d-block small">{{ __('Primary') }}</label>
                                        </div>
                                        <div class="col-6 col-md-1 text-center pt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== ADDRESSES ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1">{{ __('Addresses') }} <span class="form-mandatory"></span></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="addresses"><i class="ri-add-line"></i> {{ __('Add Address') }}</button>
                                </div>
                                @error('addresses')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="addresses-rows"></div>

                                <template id="addresses-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="addresses">
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][address_type]" placeholder="{{ __('Address Type (e.g. Billing, Warehouse)') }}" required>
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][address]" placeholder="{{ __('Address') }}" required>
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][address_bn]" placeholder="{{ __('Address (in Bangla)') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][division_name]" placeholder="{{ __('Division') }}">
                                        </div>
                                        <div class="col-6 col-md-1">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][district_name]" placeholder="{{ __('District') }}">
                                        </div>
                                        <div class="col-6 col-md-1">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][thana_name]" placeholder="{{ __('Thana') }}">
                                        </div>
                                        <div class="col-12 col-md-1 text-center pt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== PAYMENT ACCOUNTS (Optional) ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1">{{ __('Payment Accounts') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="payment_accounts"><i class="ri-add-line"></i> {{ __('Add Payment Account') }}</button>
                                </div>
                                @error('payment_accounts')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="payment_accounts-rows"></div>

                                <template id="payment_accounts-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="payment_accounts">
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][payment_method]" placeholder="{{ __('Payment Method') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][account_name]" placeholder="{{ __('Account Name') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][account_number]" placeholder="{{ __('Account Number') }}">
                                        </div>
                                        <div class="col-12 col-md-2">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][bank_name]" placeholder="{{ __('Bank Name') }}">
                                        </div>
                                        <div class="col-12 col-md-1">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][branch_name]" placeholder="{{ __('Branch') }}">
                                        </div>
                                        <div class="col-6 col-md-1">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][routing_number]" placeholder="{{ __('Routing No.') }}">
                                        </div>
                                        <div class="col-6 col-md-1 text-center pt-2">
                                            <input type="radio" class="form-check-input primary-radio" name="payment_accounts_primary" value="__INDEX__">
                                            <label class="form-check-label d-block small">{{ __('Primary') }}</label>
                                        </div>
                                        <div class="col-12 col-md-1 text-center pt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== MFS ACCOUNTS (Optional) ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1">{{ __('MFS Accounts') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="mfs_accounts"><i class="ri-add-line"></i> {{ __('Add MFS Account') }}</button>
                                </div>
                                @error('mfs_accounts')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="mfs_accounts-rows"></div>

                                <template id="mfs_accounts-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="mfs_accounts">
                                        <div class="col-12 col-md-4">
                                            <input type="text" class="form-control" name="mfs_accounts[__INDEX__][mfs_operator_name]" placeholder="{{ __('MFS Operator (e.g. bKash, Nagad)') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <input type="text" class="form-control" name="mfs_accounts[__INDEX__][mfs_account_number]" placeholder="{{ __('Account Number') }}">
                                        </div>
                                        <div class="col-6 col-md-2 text-center pt-2">
                                            <input type="radio" class="form-check-input primary-radio" name="mfs_accounts_primary" value="__INDEX__">
                                            <label class="form-check-label d-block small">{{ __('Primary') }}</label>
                                        </div>
                                        <div class="col-6 col-md-2 text-center pt-1">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('supplier.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Create') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            /*
            |--------------------------------------------------------------------------
            | GENERIC REPEATER: Contacts / Addresses / Payment Accounts / MFS Accounts
            |--------------------------------------------------------------------------
            | Each group has its own <template>#{group}-row-template and container
            | #{group}-rows. Row inputs use __INDEX__ placeholders that get replaced
            | with a running per-group counter so submitted fields become arrays
            | like contacts[0][name], contacts[1][name], etc. The "Primary" radio
            | group (one per repeater group) enforces at most one primary row
            | client-side; the server re-validates this independently.
            */
            const rowCounters = {
                contacts: 0,
                addresses: 0,
                payment_accounts: 0,
                mfs_accounts: 0,
            };

            function addRow(group) {
                const index = rowCounters[group]++;
                const template = document.getElementById(group + '-row-template').innerHTML;
                const html = template.split('__INDEX__').join(index);
                $('#' + group + '-rows').append(html);
            }

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('.repeater-row').remove();
            });

            // Seed each group with one starter row.
            addRow('contacts');
            addRow('addresses');
        });
    </script>
@endpush
