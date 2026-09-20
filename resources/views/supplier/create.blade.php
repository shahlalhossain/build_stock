@extends('layout.master')

@section('title', __('Supplier'))

@push('styles')
    <style>
        .primary-radio {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        /* Repeater rows read as one discrete "entry" on mobile instead of a
           wall of stacked full-width inputs with no visual grouping. */
        @media (max-width: 767.98px) {
            .repeater-row {
                margin-left: 0;
                margin-right: 0;
                padding: 12px 12px 8px;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                background-color: rgba(0, 0, 0, 0.015);
            }

            .repeater-row .primary-field {
                padding-top: 0.5rem;
            }
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
                                                <select id="supplier_type_id" name="supplier_type_id" class="form-select @error('supplier_type_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Supplier Type ==') }}</option>
                                                    @foreach($supplierTypes as $key => $supplierType)
                                                        <option value="{{ $supplierType->id }}">{{ $supplierType->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('supplier_type_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

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
                                            <label for="credit_limit" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Credit Limit') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('credit_limit') is-invalid @enderror" id="credit_limit" name="credit_limit" value="{{ old('credit_limit') }}">
                                                @error('credit_limit')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="minimum_order_quantity" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Min. Order Count') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('minimum_order_quantity') is-invalid @enderror" id="minimum_order_quantity" name="minimum_order_quantity" value="{{ old('minimum_order_quantity') }}">
                                                @error('minimum_order_quantity')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="minimum_order_amount" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Min. Order Amount') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('minimum_order_amount') is-invalid @enderror" id="minimum_order_amount" name="minimum_order_amount" value="{{ old('minimum_order_amount') }}">
                                                @error('minimum_order_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="payment_terms_days" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Payment Terms') }}</label>
                                            <div class="col-12 col-md-8">
                                                <div class="input-group">
                                                    <input type="text" class="form-control @error('payment_terms_days') is-invalid @enderror" id="payment_terms_days" name="payment_terms_days" value="{{ old('payment_terms_days') }}">
                                                    <span class="input-group-text" id="payment_terms_days"> {{ __('Days') }}</span>
                                                </div>
                                                @error('payment_terms_days')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="lead_time_days" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Lead Time') }}</label>
                                            <div class="col-12 col-md-8">
                                                <div class="input-group">
                                                    <input type="text" class="form-control @error('lead_time_days') is-invalid @enderror" id="lead_time_days" name="lead_time_days" value="{{ old('lead_time_days') }}">
                                                    <span class="input-group-text" id="lead_time_days"> {{ __('Days') }}</span>
                                                </div>
                                                @error('lead_time_days')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Remarks') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                                                @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr>

                                <!-- ===================== SUPPLIER CONTACTS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Supplier Contacts') }} <span class="form-mandatory"></span></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="contacts"><i class="ri-add-line"></i> {{ __('Add Contact') }}</button>
                                </div>
                                @error('contacts')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="contacts-rows"></div>

                                <template id="contacts-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="contacts">
                                        <div class="col-12 col-sm-6 col-md-3 pt-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][name]" placeholder="{{ __('Name') }}" required>
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-2 pt-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][designation]" placeholder="{{ __('Designation') }}">
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-2 pt-2">
                                            <input type="text" class="form-control" name="contacts[__INDEX__][mobile]" placeholder="{{ __('Mobile') }}">
                                        </div>
                                        <div class="col-12 col-sm-6 col-md-3 pt-2">
                                            <input type="email" class="form-control" name="contacts[__INDEX__][email]" placeholder="{{ __('Email') }}">
                                        </div>
                                        <div class="col-8 col-sm-6 col-md-1 primary-field" style="padding-top: 13px;">
                                            <input type="radio" class="form-check-input primary-radio" name="contacts[__INDEX__][is_primary]" value="1">
                                            <label class="form-check-label pt-1 ps-2"> {{ __('Primary') }}</label>
                                        </div>
                                        <div class="col-4 col-sm-6 col-md-1 text-end text-md-start" style="padding-top: 13px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== ADDRESSES ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Supplier Addresses') }} <span class="form-mandatory"></span></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="addresses"><i class="ri-add-line"></i> {{ __('Add Address') }}</button>
                                </div>
                                @error('addresses')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="addresses-rows"></div>

                                <template id="addresses-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="addresses">
                                        <div class="col-12 col-sm-12 col-md-3 pt-2">
                                            <input type="text" class="form-control" name="addresses[__INDEX__][address]" placeholder="{{ __('Address') }}" required>
                                        </div>
                                        <div class="col-12 col-sm-4 col-md-2 pt-2">
                                            <select class="form-select address-division" name="addresses[__INDEX__][division_id]">
                                                <option value="">{{ __('== Division ==') }}</option>
                                                @foreach($divisions as $division)
                                                    <option value="{{ $division->id }}">{{ $division->name_en }} ({{ $division->name_bn }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-4 col-md-2 pt-2">
                                            <select class="form-select address-district" name="addresses[__INDEX__][district_id]">
                                                <option value="">{{ __('== District ==') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-4 col-md-2 pt-2">
                                            <select class="form-select address-thana" name="addresses[__INDEX__][thana_id]">
                                                <option value="">{{ __('== Thana ==') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-2 pt-2">
                                            <select class="form-select" name="addresses[__INDEX__][address_type]" required>
                                                <option value="">{{ __('== Addr. Type ==') }}</option>
                                                @foreach($addressTypes as $addressType)
                                                    <option value="{{ $addressType->id }}">{{ $addressType->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-1 text-end text-md-start" style="padding-top: 13px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== PAYMENT ACCOUNTS (Optional) ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Payment Accounts') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="payment_accounts"><i class="ri-add-line"></i> {{ __('Bank A/C') }}</button>
                                </div>
                                @error('payment_accounts')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="payment_accounts-rows"></div>

                                <template id="payment_accounts-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="payment_accounts">
                                        <div class="col-12 col-sm-12 col-md-3 pt-2">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][account_name]" placeholder="{{ __('Account Name') }}">
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-2 pt-2">
                                            <input type="text" class="form-control" name="payment_accounts[__INDEX__][account_number]" placeholder="{{ __('Account Number') }}">
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-2 pt-2">
                                            <select class="form-select payment-bank" name="payment_accounts[__INDEX__][bank_name]">
                                                <option value="" data-bank-id="">{{ __('== Bank Name ==') }}</option>
                                                @foreach($banks as $bank)
                                                    <option value="{{ $bank->bank_name }}" data-bank-id="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-8 col-md-2 pt-2">
                                            <select class="form-select payment-branch" name="payment_accounts[__INDEX__][branch_name]">
                                                <option value="">{{ __('== Branch ==') }}</option>
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-2 pt-2">
                                            <input type="text" class="form-control branch-routing-number" name="branch_routing_number" disabled placeholder="{{ __('Routing Number') }}">
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-1 text-end text-md-start" style="padding-top: 13px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== MFS ACCOUNTS (Optional) ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('MFS Accounts') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="mfs_accounts"><i class="ri-add-line"></i> {{ __('MFS Account') }}</button>
                                </div>
                                @error('mfs_accounts')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="mfs_accounts-rows"></div>

                                <template id="mfs_accounts-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="mfs_accounts">
                                        <div class="col-12 col-sm-12 col-md-2 pt-2">
                                            <select class="form-select" name="mfs_accounts[__INDEX__][mfs_operator_name]">
                                                <option value="">{{ __('== MFS Operator ==') }}</option>
                                                @foreach($mfsCompanies as $mfsCompany)
                                                    <option value="{{ $mfsCompany->service_name }}">{{ $mfsCompany->service_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-12 col-md-2 pt-2">
                                            <input type="text" class="form-control" name="mfs_accounts[__INDEX__][mfs_account_number]" placeholder="{{ __('Mobile Banking Number') }}">
                                        </div>
                                        <div class="col-8 col-sm-6 col-md-1 primary-field" style="padding-top: 13px;">
                                            <input type="radio" class="form-check-input primary-radio" name="mfs_accounts[__INDEX__][is_primary]" value="1">
                                            <label class="form-check-label pt-1 ps-2"> {{ __('Primary') }}</label>
                                        </div>
                                        <div class="col-4 col-sm-6 col-md-2 text-end text-md-start" style="padding-top: 13px;">
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

            /*
            |--------------------------------------------------------------------------
            | PRIMARY RADIO: ONE CHECKED PER GROUP
            |--------------------------------------------------------------------------
            | Each row's Primary radio has an array-indexed name (e.g.
            | contacts[0][is_primary]) so it submits correctly, which means it
            | can't rely on native radio grouping (that requires an identical
            | name). Instead, checking one Primary radio in a group manually
            | unchecks every other Primary radio within that same group.
            */
            $(document).on('change', '.primary-radio', function () {
                const group = $(this).closest('.repeater-row').data('group');

                $('#' + group + '-rows .primary-radio').not(this).prop('checked', false);
            });

            /*
            |--------------------------------------------------------------------------
            | ADDRESS ROW: DIVISION -> DISTRICT -> THANA CASCADE
            |--------------------------------------------------------------------------
            | Each Address row has its own Division/District/Thana selects
            | (repeater rows aren't unique ids), so the cascade is scoped to
            | the row the change happened in via .closest('.repeater-row').
            */
            $(document).on('change', '.address-division', function () {
                const row = $(this).closest('.repeater-row');
                const divisionId = $(this).val();
                const $district = row.find('.address-district');
                const $thana = row.find('.address-thana');

                $district.html('<option value="">{{ __("== District ==") }}</option>');
                $thana.html('<option value="">{{ __("== Thana ==") }}</option>');

                if (!divisionId) {
                    return;
                }

                $.get('{{ route('getDistrictsByDivision') }}', { division_id: divisionId }, function (districts) {
                    districts.forEach(function (district) {
                        $district.append('<option value="' + district.id + '">' + district.name_en + ' (' + district.name_bn + ')</option>');
                    });
                });
            });

            $(document).on('change', '.address-district', function () {
                const row = $(this).closest('.repeater-row');
                const districtId = $(this).val();
                const $thana = row.find('.address-thana');

                $thana.html('<option value="">{{ __("== Thana ==") }}</option>');

                if (!districtId) {
                    return;
                }

                $.get('{{ route('getThanasByDistrict') }}', { district_id: districtId }, function (thanas) {
                    thanas.forEach(function (thana) {
                        $thana.append('<option value="' + thana.id + '">' + thana.name_en + ' (' + thana.name_bn + ')</option>');
                    });
                });
            });

            /*
            |--------------------------------------------------------------------------
            | PAYMENT ACCOUNT ROW: BANK -> BRANCH CASCADE
            |--------------------------------------------------------------------------
            | bank_name/branch_name are stored as plain strings (not foreign
            | keys), so each <option value="..."> is the Bank/Branch NAME for
            | submission, while data-bank-id carries the numeric id needed to
            | fetch that bank's branches.
            */
            $(document).on('change', '.payment-bank', function () {
                const row = $(this).closest('.repeater-row');
                const bankId = $(this).find(':selected').data('bank-id');
                const $branch = row.find('.payment-branch');
                const $routing = row.find('.branch-routing-number');

                $branch.html('<option value="">{{ __("== Branch ==") }}</option>');
                $routing.val('');

                if (!bankId) {
                    return;
                }

                $.get('{{ route('getBranchesByBank') }}', { bank_id: bankId }, function (branches) {
                    branches.forEach(function (branch) {
                        $branch.append('<option value="' + branch.branch_name + '" data-routing-no="' + (branch.routing_no ?? '') + '">' + branch.branch_name + '</option>');
                    });
                });
            });

            /*
            |--------------------------------------------------------------------------
            | PAYMENT ACCOUNT ROW: DISPLAY THE SELECTED BRANCH'S ROUTING NUMBER
            |--------------------------------------------------------------------------
            */
            $(document).on('change', '.payment-branch', function () {
                const row = $(this).closest('.repeater-row');
                const routingNo = $(this).find(':selected').data('routing-no');

                row.find('.branch-routing-number').val(routingNo || '');
            });

            // Seed each group with one starter row.
            addRow('contacts');
            addRow('addresses');
        });
    </script>
@endpush
