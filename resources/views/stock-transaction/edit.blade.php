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
                                                <th style="width: 20%;">{{ __('Product') }}</th>
                                                <th style="width: 20%;">{{ __('Variant') }}</th>
                                                <th style="width: 12%;">{{ __('Quantity') }}</th>
                                                <th style="width: 12%;">{{ __('Unit Cost') }}</th>
                                                <th style="width: 12%;">{{ __('Total Cost') }}</th>
                                                <th style="width: 19%;">{{ __('Remarks') }}</th>
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
                                                <select class="form-select item-product">
                                                    <option value="">{{ __('== Select Product ==') }}</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}">{{ $product->name }} @if($product->code) ({{ $product->code }}) @endif</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select class="form-select item-variant" name="items[][product_variant_id]" disabled>
                                                    <option value="">{{ __('== Select Product First ==') }}</option>
                                                </select>
                                                <small class="text-danger d-none item-variant-empty">{{ __('No Variants Available for this Product — Add a Variant First') }}</small>

                                                <button type="button" class="btn btn-sm btn-outline-info d-none item-variant-wise-btn">
                                                    <i class="ri-list-settings-line"></i> {{ __('Set Variant-wise Quantities') }}
                                                </button>
                                                <div class="small text-muted d-none item-variant-wise-summary"></div>
                                                <div class="item-variant-wise-hidden-inputs"></div>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control item-quantity" name="items[][quantity]" placeholder="{{ __('Quantity') }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" min="0" class="form-control item-unit-cost" name="items[][unit_cost]" placeholder="{{ __('Unit Cost') }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control item-total-cost" readonly tabindex="-1" placeholder="{{ __('Total Cost') }}">
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

    @if($stockTransaction->status === 'pending')
        <!-- ===================== VARIANT-WISE QUANTITIES MODAL ===================== -->
        <div class="modal fade" id="variantWiseModal" tabindex="-1" aria-labelledby="variantWiseModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="variantWiseModalLabel">{{ __('Set Variant-wise Quantities') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <hr>
                    <div class="modal-body">
                        <div id="variantWiseError" class="alert alert-danger d-none"></div>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered align-middle">
                                <thead>
                                <tr>
                                    <th style="width: 4%;"></th>
                                    <th style="width: 32%;">{{ __('Variant') }}</th>
                                    <th style="width: 18%;">{{ __('Quantity') }}</th>
                                    <th style="width: 18%;">{{ __('Unit Price') }}</th>
                                    <th style="width: 18%;">{{ __('Total Price') }}</th>
                                    <th style="width: 10%;">{{ __('Remarks') }}</th>
                                </tr>
                                </thead>
                                <tbody id="variantWiseRows"></tbody>
                            </table>
                        </div>
                    </div>
                    <hr>
                    <div class="modal-footer justify-content-between">
                        <div id="variantWiseSummary" class="fw-semibold"></div>
                        <div>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            <button type="button" class="btn btn-sm btn-info" id="variantWiseSaveBtn" disabled>{{ __('Save') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <template id="variant-wise-row-template">
            <tr class="variant-wise-row">
                <td class="text-center">
                    <input type="checkbox" class="form-check-input variant-wise-check" checked>
                </td>
                <td class="variant-wise-label"></td>
                <td>
                    <input type="number" step="0.01" class="form-control variant-wise-quantity">
                </td>
                <td>
                    <input type="number" step="0.01" min="0" class="form-control variant-wise-unit-price">
                </td>
                <td>
                    <input type="text" class="form-control variant-wise-total-price" readonly tabindex="-1">
                </td>
                <td>
                    <input type="text" class="form-control variant-wise-remarks">
                </td>
            </tr>
        </template>
    @endif
    <!-- End Page Content -->
@endsection

@push('scripts')
    @if($stockTransaction->status === 'pending')
        <script>
            $(document).ready(function () {
                /*
                |--------------------------------------------------------------------------
                | GROUP EXISTING ITEMS BY PRODUCT
                |--------------------------------------------------------------------------
                | A Product with MORE THAN ONE existing Line Item row was clearly
                | submitted via Variant-wise mode (the plain 1-Variant path can only ever
                | produce a single Line Item per Product) — that Row reopens directly in
                | Variant-wise Summary mode, pre-filled from these grouped rows, rather
                | than the plain single-select dropdown.
                */
                @php
                    $existingGroupsList = $stockTransaction->items
                        ->groupBy(fn ($item) => $item->productVariant?->product_id)
                        ->map(function ($items) {
                            return [
                                'product_id' => $items->first()->productVariant?->product_id,
                                'is_variant_wise' => $items->count() > 1,
                                'items' => $items->map(fn ($item) => [
                                    'product_variant_id' => $item->product_variant_id,
                                    'quantity' => $item->quantity,
                                    'unit_cost' => $item->unit_cost,
                                    'remarks' => $item->remarks,
                                ])->values(),
                            ];
                        })
                        ->values();
                @endphp
                const existingGroups = @json($existingGroupsList);

                /*
                |--------------------------------------------------------------------------
                | PRODUCT -> VARIANT: CLIENT-SIDE CASCADE (NO AJAX)
                |--------------------------------------------------------------------------
                | Matches create.blade.php's convention exactly: all active Variants for
                | all Products are preloaded here, keyed by Product ID.
                */
                @php
                    $productVariantsMap = $products->mapWithKeys(function ($product) {
                        $variantList = $product->variants->map(function ($variant) {
                            return [
                                'id' => $variant->id,
                                'label' => $variant->display_label,
                                'sku' => $variant->sku,
                                'unit_price' => $variant->unit_price,
                                'attribute_label' => $variant->attribute_values_label,
                            ];
                        });

                        return [
                            $product->id => [
                                'name' => $product->name,
                                'variants' => $variantList,
                            ],
                        ];
                    });
                @endphp
                const productVariants = @json($productVariantsMap);

                let $activeVariantWiseRow = null;

                function variantWiseLabel(productName, variant) {
                    return variant.attribute_label
                        ? (productName + ' - ' + variant.attribute_label)
                        : (productName + ' - ' + variant.sku);
                }

                function populateVariantSelect($row, selectedVariantId, unitPriceCallback) {
                    const productId = $row.find('.item-product').val();
                    const $variantSelect = $row.find('.item-variant');
                    const $variantEmpty = $row.find('.item-variant-empty');
                    const $variantWiseBtn = $row.find('.item-variant-wise-btn');

                    $variantSelect.empty();
                    $variantWiseBtn.addClass('d-none');

                    if (!$row.hasClass('is-variant-wise')) {
                        exitVariantWiseMode($row);
                    }

                    if (!productId) {
                        $variantSelect.append('<option value="">{{ __("== Select Product First ==") }}</option>');
                        $variantSelect.prop('disabled', true).removeClass('is-invalid').removeClass('d-none');
                        $variantEmpty.addClass('d-none');
                        return;
                    }

                    const product = productVariants[productId] || {name: '', variants: []};
                    const variants = product.variants || [];

                    if (!variants.length) {
                        $variantSelect.append('<option value="">{{ __("== No Variants Available ==") }}</option>');
                        $variantSelect.prop('disabled', true).removeClass('d-none');
                        $variantEmpty.removeClass('d-none');
                        return;
                    }

                    $variantEmpty.addClass('d-none');

                    if (variants.length === 1) {
                        $variantSelect.removeClass('d-none').prop('disabled', false);
                        $variantSelect.append('<option value="">{{ __("== Select Variant ==") }}</option>');

                        variants.forEach(function (variant) {
                            const $option = $('<option>').val(variant.id).text(variant.label).attr('data-unit-price', variant.unit_price ?? '');
                            $variantSelect.append($option);
                        });

                        $variantSelect.val(String(variants[0].id));

                        if (selectedVariantId) {
                            $variantSelect.val(String(selectedVariantId));
                        }

                        if (typeof unitPriceCallback === 'function') {
                            unitPriceCallback();
                        }

                        return;
                    }

                    // 2+ Variants: hide the plain Select, show the "Set Variant-wise
                    // Quantities" button instead.
                    $variantSelect.addClass('d-none').prop('disabled', true);
                    $variantWiseBtn.removeClass('d-none').data('product-id', productId);
                }

                $(document).on('change', '.item-product', function () {
                    const $row = $(this).closest('.repeater-row');
                    populateVariantSelect($row);
                    $row.find('.item-unit-cost').val('');
                    syncItemTotalCost($row);
                });

                /*
                |--------------------------------------------------------------------------
                | VARIANT -> UNIT COST: AUTO-FILL FROM THE VARIANT'S CATALOG PRICE
                |--------------------------------------------------------------------------
                */
                $(document).on('change', '.item-variant', function () {
                    const $row = $(this).closest('.repeater-row');
                    const unitPrice = $(this).find('option:selected').data('unit-price');

                    if (unitPrice !== undefined && unitPrice !== '' && unitPrice !== null) {
                        $row.find('.item-unit-cost').val(parseFloat(unitPrice).toFixed(2));
                    }

                    syncItemTotalCost($row);
                });

                /*
                |--------------------------------------------------------------------------
                | LINE ITEM TOTAL COST: AUTO-CALCULATE FROM QUANTITY x UNIT COST
                |--------------------------------------------------------------------------
                */
                function syncItemTotalCost($row) {
                    const quantity = parseFloat($row.find('.item-quantity').val());
                    const unitCost = parseFloat($row.find('.item-unit-cost').val());
                    const $total = $row.find('.item-total-cost');

                    if (isNaN(quantity) || isNaN(unitCost)) {
                        $total.val('');
                        return;
                    }

                    $total.val((quantity * unitCost).toFixed(2));
                }

                $(document).on('input', '.item-quantity, .item-unit-cost', function () {
                    const $row = $(this).closest('.repeater-row');
                    if (!$row.hasClass('is-variant-wise')) {
                        syncItemTotalCost($row);
                    }
                });

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
                | LINE ITEMS REPEATER (Pre-Filled from Existing Items, Grouped by Product)
                |--------------------------------------------------------------------------
                */
                function addRow(group, itemGroup) {
                    const template = document.getElementById(group + '-row-template').innerHTML;
                    const $row = $(template);

                    if (itemGroup) {
                        const firstItem = itemGroup.items[0];
                        $row.find('.item-product').val(itemGroup.product_id);
                        populateVariantSelect($row, firstItem.product_variant_id);

                        if (itemGroup.is_variant_wise) {
                            // Reopens directly in Variant-wise Summary mode — a Product
                            // with more than one existing Line Item row can only have
                            // been submitted via Variant-wise mode.
                            const $hiddenContainer = $row.find('.item-variant-wise-hidden-inputs');
                            let checkedCount = 0;
                            let totalQuantity = 0;

                            itemGroup.items.forEach(function (item) {
                                checkedCount++;
                                totalQuantity += parseFloat(item.quantity) || 0;

                                const $hGroup = $('<div class="hidden-variant-group">');
                                $hGroup.append($('<input type="hidden" name="items[][product_variant_id]">').val(item.product_variant_id));
                                $hGroup.append($('<input type="hidden" name="items[][quantity]">').val(item.quantity));
                                $hGroup.append($('<input type="hidden" name="items[][unit_cost]">').val(item.unit_cost));
                                $hGroup.append($('<input type="hidden" name="items[][remarks]">').val(item.remarks));
                                $hiddenContainer.append($hGroup);
                            });

                            enterVariantWiseMode($row, checkedCount, totalQuantity);
                        } else {
                            $row.find('.item-quantity').val(firstItem.quantity);
                            $row.find('.item-unit-cost').val(firstItem.unit_cost);
                            $row.find('.item-remarks').val(firstItem.remarks);
                            syncItemTotalCost($row);
                        }
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
                    // Removing the Row also removes its injected variant-wise hidden inputs.
                    $(this).closest('.repeater-row').remove();
                });

                /*
                |--------------------------------------------------------------------------
                | VARIANT-WISE QUANTITIES MODAL (identical behavior to create.blade.php)
                |--------------------------------------------------------------------------
                */
                const $modal = $('#variantWiseModal');
                const $modalRows = $('#variantWiseRows');
                const $modalSummary = $('#variantWiseSummary');
                const $modalSaveBtn = $('#variantWiseSaveBtn');
                const $modalError = $('#variantWiseError');

                function buildVariantWiseRow(productName, variant, seedData) {
                    const rowTemplate = document.getElementById('variant-wise-row-template').innerHTML;
                    const $row = $(rowTemplate);

                    $row.attr('data-variant-id', variant.id);
                    $row.find('.variant-wise-label').text(variantWiseLabel(productName, variant));
                    $row.find('.variant-wise-unit-price').val(
                        seedData ? seedData.unit_cost : (variant.unit_price ?? '')
                    );
                    $row.find('.variant-wise-remarks').val(seedData ? (seedData.remarks || '') : '');

                    if (seedData) {
                        $row.find('.variant-wise-quantity').val(seedData.quantity);
                    } else {
                        $row.find('.variant-wise-check').prop('checked', true);
                    }

                    return $row;
                }

                function syncVariantWiseRowTotal($row) {
                    const quantity = parseFloat($row.find('.variant-wise-quantity').val());
                    const unitPrice = parseFloat($row.find('.variant-wise-unit-price').val());
                    const $total = $row.find('.variant-wise-total-price');

                    if (isNaN(quantity) || isNaN(unitPrice)) {
                        $total.val('');
                        return;
                    }

                    $total.val((quantity * unitPrice).toFixed(2));
                }

                function syncVariantWiseSummary() {
                    const targetQuantity = parseFloat($activeVariantWiseRow.find('.item-quantity').val());
                    let allocated = 0;

                    $modalRows.find('.variant-wise-row').each(function () {
                        if (!$(this).find('.variant-wise-check').prop('checked')) {
                            return;
                        }

                        const quantity = parseFloat($(this).find('.variant-wise-quantity').val());
                        allocated += isNaN(quantity) ? 0 : quantity;
                    });

                    const target = isNaN(targetQuantity) ? 0 : targetQuantity;
                    const remaining = target - allocated;
                    const matches = Math.abs(remaining) < 0.005 && target > 0;

                    $modalSummary
                        .removeClass('text-danger text-success')
                        .addClass(matches ? 'text-success' : 'text-danger')
                        .text(
                            '{{ __("Target Quantity") }}: ' + target.toFixed(2) +
                            ' | {{ __("Allocated") }}: ' + allocated.toFixed(2) +
                            ' | {{ __("Remaining") }}: ' + remaining.toFixed(2)
                        );

                    $modalSaveBtn.prop('disabled', !matches);
                    $modalError.addClass('d-none');

                    return matches;
                }

                $(document).on('click', '.item-variant-wise-btn', function () {
                    $activeVariantWiseRow = $(this).closest('.repeater-row');
                    const productId = $(this).data('product-id');
                    const product = productVariants[productId] || {name: '', variants: []};

                    const targetQuantity = $activeVariantWiseRow.find('.item-quantity').val();
                    if (!targetQuantity || parseFloat(targetQuantity) === 0) {
                        Swal.fire('Notice', '{{ __("Enter this Line Item\'s Quantity First.") }}', 'warning');
                        return;
                    }

                    $modalRows.empty();

                    const existingHidden = $activeVariantWiseRow.find('.item-variant-wise-hidden-inputs input[name$="[product_variant_id]"]');
                    const seedByVariantId = {};

                    if (existingHidden.length) {
                        existingHidden.each(function () {
                            const $group = $(this).closest('.hidden-variant-group');
                            seedByVariantId[$(this).val()] = {
                                quantity: $group.find('input[name$="[quantity]"]').val(),
                                unit_cost: $group.find('input[name$="[unit_cost]"]').val(),
                                remarks: $group.find('input[name$="[remarks]"]').val(),
                            };
                        });
                    }

                    (product.variants || []).forEach(function (variant) {
                        const seedData = seedByVariantId[variant.id] || null;
                        const $row = buildVariantWiseRow(product.name, variant, seedData);
                        $modalRows.append($row);
                        syncVariantWiseRowTotal($row);
                    });

                    syncVariantWiseSummary();

                    $modal.modal('show');
                });

                $(document).on('change', '.variant-wise-check', function () {
                    const $row = $(this).closest('.variant-wise-row');
                    $row.find('.variant-wise-quantity, .variant-wise-unit-price, .variant-wise-remarks').prop('disabled', !this.checked);
                    $row.toggleClass('opacity-50', !this.checked);
                    syncVariantWiseSummary();
                });

                $(document).on('input', '.variant-wise-quantity, .variant-wise-unit-price', function () {
                    const $row = $(this).closest('.variant-wise-row');
                    syncVariantWiseRowTotal($row);
                    syncVariantWiseSummary();
                });

                function enterVariantWiseMode($row, checkedCount, targetQuantity) {
                    $row.addClass('is-variant-wise');
                    $row.find('.item-quantity').val(targetQuantity).prop('readonly', true);
                    $row.find('.item-unit-cost').val('').prop('disabled', true).attr('placeholder', '{{ __("Multiple") }}');
                    $row.find('.item-total-cost').val('').attr('placeholder', '{{ __("Multiple") }}');
                    $row.find('.item-variant-wise-summary')
                        .removeClass('d-none')
                        .text('{{ __("Variant-wise") }}: ' + checkedCount + ' {{ __("Variants") }}');
                    $row.find('.item-variant-wise-btn').text('{{ __("Edit Variant-wise Quantities") }}');
                }

                function exitVariantWiseMode($row) {
                    $row.removeClass('is-variant-wise');
                    $row.find('.item-quantity').prop('readonly', false);
                    $row.find('.item-unit-cost').prop('disabled', false).attr('placeholder', '{{ __("Unit Cost") }}');
                    $row.find('.item-total-cost').attr('placeholder', '{{ __("Total Cost") }}');
                    $row.find('.item-variant-wise-summary').addClass('d-none').text('');
                    $row.find('.item-variant-wise-hidden-inputs').empty();
                    $row.find('.item-variant-wise-btn').text('{{ __("Set Variant-wise Quantities") }}');
                }

                $modalSaveBtn.on('click', function () {
                    if (!syncVariantWiseSummary()) {
                        $modalError.text('{{ __("Allocated Quantity must exactly match the Target Quantity before Saving.") }}').removeClass('d-none');
                        return;
                    }

                    const $row = $activeVariantWiseRow;
                    const $hiddenContainer = $row.find('.item-variant-wise-hidden-inputs');
                    $hiddenContainer.empty();

                    let checkedCount = 0;
                    const targetQuantity = $row.find('.item-quantity').val();

                    $modalRows.find('.variant-wise-row').each(function () {
                        if (!$(this).find('.variant-wise-check').prop('checked')) {
                            return;
                        }

                        checkedCount++;
                        const variantId = $(this).data('variant-id');
                        const quantity = $(this).find('.variant-wise-quantity').val();
                        const unitCost = $(this).find('.variant-wise-unit-price').val();
                        const remarks = $(this).find('.variant-wise-remarks').val();

                        const $group = $('<div class="hidden-variant-group">');
                        $group.append($('<input type="hidden" name="items[][product_variant_id]">').val(variantId));
                        $group.append($('<input type="hidden" name="items[][quantity]">').val(quantity));
                        $group.append($('<input type="hidden" name="items[][unit_cost]">').val(unitCost));
                        $group.append($('<input type="hidden" name="items[][remarks]">').val(remarks));
                        $hiddenContainer.append($group);
                    });

                    enterVariantWiseMode($row, checkedCount, targetQuantity);
                    $modal.modal('hide');
                });

                /*
                |--------------------------------------------------------------------------
                | PREVENT SUBMIT WHEN A ROW HAS NO VARIANTS AVAILABLE
                |--------------------------------------------------------------------------
                */
                $('form').on('submit', function (e) {
                    const $blockedRow = $('.repeater-row[data-group="items"]').filter(function () {
                        const $r = $(this);
                        if (!$r.find('.item-product').val() || $r.hasClass('is-variant-wise')) {
                            return false;
                        }
                        return $r.find('.item-variant option').length <= 1 && $r.find('.item-variant').prop('disabled') && $r.find('.item-variant-wise-btn').hasClass('d-none');
                    }).first();

                    if ($blockedRow.length) {
                        e.preventDefault();
                        Swal.fire('Notice', '{{ __("One or More Line Items have No Variants Available. Add a Variant First.") }}', 'warning');
                        return;
                    }

                    const $unsavedVariantWiseRow = $('.repeater-row[data-group="items"]').filter(function () {
                        const $r = $(this);
                        return $r.find('.item-product').val()
                            && !$r.find('.item-variant-wise-btn').hasClass('d-none')
                            && !$r.hasClass('is-variant-wise');
                    }).first();

                    if ($unsavedVariantWiseRow.length) {
                        e.preventDefault();
                        Swal.fire('Notice', '{{ __("Set Variant-wise Quantities for Every Line Item with Multiple Variants.") }}', 'warning');
                    }
                });

                if (existingGroups.length) {
                    existingGroups.forEach(function (itemGroup) {
                        addRow('items', itemGroup);
                    });
                } else {
                    addRow('items');
                }
            });
        </script>
    @endif
@endpush
