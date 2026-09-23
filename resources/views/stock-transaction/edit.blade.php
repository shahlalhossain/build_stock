@extends('layout.master')

@section('title', __('Stock Transaction'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Stock Transaction Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('stock-transaction.edit', $stockTransaction->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('stock-transaction.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        @if($stockTransaction->status !== 'pending')
                            <div class="card-body">
                                <div class="alert alert-warning mb-0">
                                    {{ __('This Stock Transaction is :status and can no longer be Edited.', ['status' => ucwords($stockTransaction->status)]) }}
                                </div>
                            </div>
                        @else
                            @php
                                $isTransfer = $stockTransaction->type === 'transfer_out' || $stockTransaction->type === 'transfer_in';
                                $sourceTransaction = $isTransfer && $stockTransaction->type === 'transfer_in' ? $stockTransaction->linkedTransaction : $stockTransaction;
                            @endphp
                            <form action="{{ route('stock-transaction.update', $stockTransaction->id) }}" method="POST">
                                @csrf
                                @method('PATCH')

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

                                    <div class="row">
                                        <!-- Start Left Column -->
                                        <div class="col-12 col-md-6">
                                            <div class="row mb-2">
                                                <label for="type" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Type') }}</label>
                                                <div class="col-12 col-md-8">
                                                    <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                        @php $currentType = $isTransfer ? 'transfer' : $stockTransaction->type; @endphp
                                                        <option value="opening_balance" @selected(old('type', $currentType) === 'opening_balance')>{{ __('Opening Balance') }}</option>
                                                        <option value="purchase" @selected(old('type', $currentType) === 'purchase')>{{ __('Purchase') }}</option>
                                                        <option value="issue" @selected(old('type', $currentType) === 'issue')>{{ __('Issue') }}</option>
                                                        <option value="adjustment" @selected(old('type', $currentType) === 'adjustment')>{{ __('Adjustment') }}</option>
                                                        <option value="transfer" @selected(old('type', $currentType) === 'transfer')>{{ __('Transfer') }}</option>
                                                    </select>
                                                    @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <label for="store_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Store') }}</label>
                                                <div class="col-12 col-md-8">
                                                    <select id="store_id" name="store_id" class="form-select @error('store_id') is-invalid @enderror" required>
                                                        <option value="">{{ __('== Select Store ==') }}</option>
                                                        @foreach($stores as $store)
                                                            <option value="{{ $store->id }}" @selected(old('store_id', $sourceTransaction->store_id) == $store->id)>{{ ucwords($store->name) }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('store_id')<small class="text-danger">{{ $message }}</small>@enderror
                                                </div>
                                            </div>

                                            <div class="row mb-2 d-none" id="destination_store_row">
                                                <label for="destination_store_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Destination Store') }}</label>
                                                <div class="col-12 col-md-8">
                                                    <select id="destination_store_id" name="destination_store_id" class="form-select @error('destination_store_id') is-invalid @enderror">
                                                        <option value="">{{ __('== Select Destination Store ==') }}</option>
                                                        @foreach($stores as $store)
                                                            <option value="{{ $store->id }}" @selected(old('destination_store_id', $isTransfer ? $stockTransaction->linkedTransaction->store_id : null) == $store->id)>{{ ucwords($store->name) }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('destination_store_id')<small class="text-danger">{{ $message }}</small>@enderror
                                                </div>
                                            </div>

                                            <div class="row mb-2 d-none" id="supplier_row">
                                                <label for="supplier_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier') }}</label>
                                                <div class="col-12 col-md-8">
                                                    <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror">
                                                        <option value="">{{ __('== Select Supplier ==') }}</option>
                                                        @foreach($suppliers as $supplier)
                                                            <option value="{{ $supplier->id }}" @selected(old('supplier_id', $stockTransaction->supplier_id) == $supplier->id)>{{ ucwords($supplier->name) }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('supplier_id')<small class="text-danger">{{ $message }}</small>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Left Column -->

                                        <!-- Start Right Column -->
                                        <div class="col-12 col-md-6">
                                            <div class="row mb-2">
                                                <label for="transaction_date" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Transaction Date') }}</label>
                                                <div class="col-12 col-md-8">
                                                    <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', optional($stockTransaction->transaction_date)->format('Y-m-d')) }}" required>
                                                    @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                                                </div>
                                            </div>

                                            <div class="row mb-2">
                                                <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Remarks') }}</label>
                                                <div class="col-12 col-md-8">
                                                    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks', $stockTransaction->remarks) }}</textarea>
                                                    @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Right Column -->
                                    </div>

                                    <hr>

                                    <!-- ===================== LINE ITEMS ===================== -->
                                    <div class="d-flex align-items-center mb-2">
                                        <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Line Items') }}</h5>
                                        <button type="button" class="btn btn-sm btn-success add-row" data-group="items"><i class="ri-add-line"></i> {{ __('Add Item') }}</button>
                                    </div>
                                    @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle">
                                            <thead>
                                            <tr>
                                                <th style="width: 5%;"></th>
                                                <th style="width: 30%;">{{ __('Product') }}</th>
                                                <th style="width: 20%;">{{ __('Quantity') }}</th>
                                                <th style="width: 20%;">{{ __('Unit Cost') }}</th>
                                                <th style="width: 25%;">{{ __('Remarks') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody id="items-rows"></tbody>
                                        </table>
                                    </div>

                                    <template id="items-row-template">
                                        <tr class="repeater-row" data-group="items">
                                            <td class="text-center pt-2">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                            </td>
                                            <td>
                                                <select class="form-select item-product" name="items[][product_id]">
                                                    <option value="">{{ __('== Select Product ==') }}</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }} @if($product->code) ({{ $product->code }}) @endif</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control item-quantity" name="items[][quantity]" placeholder="{{ __('Quantity') }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control item-unit-cost" name="items[][unit_cost]" placeholder="{{ __('Unit Cost') }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control item-remarks" name="items[][remarks]" placeholder="{{ __('Remarks') }}">
                                            </td>
                                        </tr>
                                    </template>

                                </div>

                                <div class="card-footer">
                                    <div class="row">
                                        <div class="col-6 text-start">
                                            <a href="{{ route('stock-transaction.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                        </div>
                                        <div class="col-6 text-end">
                                            <button type="submit" class="btn btn-sm btn-info">{{ __('Update') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Content -->
@endsection

@push('scripts')
    @if($stockTransaction->status === 'pending')
        <script>
            $(document).ready(function () {
                const existingItems = @json($stockTransaction->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost,
                        'remarks' => $item->remarks,
                    ];
                }));

                /*
                |--------------------------------------------------------------------------
                | TYPE -> SUPPLIER / DESTINATION STORE: CLIENT-SIDE SHOW/HIDE
                |--------------------------------------------------------------------------
                */
                const $type = $('#type');
                const $supplierRow = $('#supplier_row');
                const $supplierSelect = $('#supplier_id');
                const $destinationRow = $('#destination_store_row');
                const $destinationSelect = $('#destination_store_id');
                const $storeSelect = $('#store_id');

                function syncTypeDependentFields() {
                    const type = $type.val();

                    if (type === 'purchase') {
                        $supplierRow.removeClass('d-none');
                    } else {
                        $supplierRow.addClass('d-none');
                    }

                    if (type === 'transfer') {
                        $destinationRow.removeClass('d-none');
                        filterDestinationStoreOptions();
                    } else {
                        $destinationRow.addClass('d-none');
                    }
                }

                function filterDestinationStoreOptions() {
                    const sourceStoreId = $storeSelect.val();

                    $destinationSelect.find('option').each(function () {
                        if (!$(this).val()) {
                            return;
                        }

                        const isSameStore = String($(this).val()) === String(sourceStoreId);
                        $(this).toggle(!isSameStore);

                        if (isSameStore && $(this).prop('selected')) {
                            $destinationSelect.val('');
                        }
                    });
                }

                $type.on('change', syncTypeDependentFields);
                $storeSelect.on('change', function () {
                    if ($type.val() === 'transfer') {
                        filterDestinationStoreOptions();
                    }
                });
                syncTypeDependentFields();

                /*
                |--------------------------------------------------------------------------
                | LINE ITEMS REPEATER (Pre-Filled from Existing Items)
                |--------------------------------------------------------------------------
                */
                function addRow(group, data) {
                    const template = document.getElementById(group + '-row-template').innerHTML;
                    const $row = $(template);

                    if (data) {
                        $row.find('.item-product').val(data.product_id);
                        $row.find('.item-quantity').val(data.quantity);
                        $row.find('.item-unit-cost').val(data.unit_cost);
                        $row.find('.item-remarks').val(data.remarks);
                    }

                    $('#' + group + '-rows').append($row);
                }

                $(document).on('click', '.add-row', function () {
                    addRow($(this).data('group'));
                });

                $(document).on('click', '.remove-row', function () {
                    const $rows = $('.repeater-row[data-group="items"]');
                    if ($rows.length <= 1) {
                        Swal.fire('Notice', 'At Least One Line Item is Required.', 'warning');
                        return;
                    }
                    $(this).closest('.repeater-row').remove();
                });

                if (existingItems.length) {
                    existingItems.forEach(function (item) {
                        addRow('items', item);
                    });
                } else {
                    addRow('items');
                }
            });
        </script>
    @endif
@endpush
