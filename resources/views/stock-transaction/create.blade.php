@extends('layout.master')

@section('title', __('Stock Transaction'))

@push('styles')
    <style>
        /* Larger tap target for the per-Variant "Enabled" checkbox — web and mobile alike. */
        #variantsModalRows .variant-row-enabled {
            width: 1.25em;
            height: 1.25em;
        }

        /*
        |------------------------------------------------------------------------------
        | PRODUCT ITEMS + SETUP VARIANTS TABLES: MOBILE-RESPONSIVE REFLOW
        |------------------------------------------------------------------------------
        | Web view is untouched above the md breakpoint. Below it, the SAME table/row
        | markup (no separate mobile template, no JS changes) reflows: thead is hidden,
        | each row becomes a bordered card, and each cell stacks with a label pulled
        | from its own data-label attribute via ::before. Scoped to #items-table and
        | #variants-table only so other pages' .table-responsive are unaffected.
        */
        @media (max-width: 767.98px) {
            #items-table thead, #variants-table thead {
                display: none;
            }

            #items-table, #items-table tbody, #items-table tr, #items-table td,
            #variants-table, #variants-table tbody, #variants-table tr, #variants-table td {
                display: block;
                width: 100% !important;
            }

            #items-table tr.repeater-row, #variants-table tr.variant-row {
                margin-bottom: 0.75rem;
                border: 1px solid var(--vz-border-color);
                border-radius: 0.25rem;
            }

            #items-table td, #variants-table td {
                border: none;
                border-bottom: 1px solid var(--vz-border-color);
                padding: 0.5rem 0.75rem;
            }

            #items-table td:last-child, #variants-table td:last-child {
                border-bottom: none;
            }

            #items-table td[data-label]::before, #variants-table td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--vz-secondary-color);
                margin-bottom: 0.25rem;
            }

            #items-table td:first-child {
                text-align: end;
                padding: 0.5rem 0.75rem;
            }

            /*
            | Remove (left) + Enabled (right) share ONE row on mobile: both cells sit
            | side by side at 50% width instead of each taking the full row, label and
            | control laid out inline within each half.
            */
            #variants-table tr.variant-row td.variant-row-action-cell {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                width: 50% !important;
                box-sizing: border-box;
                vertical-align: top;
            }

            #variants-table tr.variant-row td.variant-row-action-cell:first-child {
                justify-content: flex-start;
                border-right: 1px solid var(--vz-border-color);
            }

            #variants-table tr.variant-row td.variant-row-action-cell:nth-child(2) {
                justify-content: flex-end;
            }

            #variants-table tr.variant-row td.variant-row-action-cell[data-label]::before {
                display: inline;
                margin-bottom: 0;
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Stock Transaction') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('stock-transaction.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('stock-transaction.store') }}" method="POST">
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

                                <div class="row">
                                    <!-- Start Left Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="type" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Type') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                    <option value="" @selected(old('type') === null)>{{ __('Select Type') }}</option>
                                                    <option value="opening_balance" @selected(old('type') === 'opening_balance')>{{ __('Opening Balance') }}</option>
                                                    <option value="purchase" @selected(old('type') === 'purchase')>{{ __('Purchase') }}</option>
                                                    <option value="issue" @selected(old('type') === 'issue')>{{ __('Issue') }}</option>
                                                    <option value="adjustment" @selected(old('type') === 'adjustment')>{{ __('Adjustment') }}</option>
                                                    <option value="transfer" @selected(old('type') === 'transfer')>{{ __('Transfer') }}</option>
                                                </select>
                                                @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="store_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory" id="store_id_label">{{ __('Store') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="store_id" name="store_id" class="form-select @error('store_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Store ==') }}</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>{{ ucwords($store->name) }}</option>
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
                                                        <option value="{{ $store->id }}" @selected(old('destination_store_id') == $store->id)>{{ ucwords($store->name) }}</option>
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
                                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ ucwords($supplier->name) }}</option>
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
                                                <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                                @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
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
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== PRODUCT ITEMS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Product Items') }}</h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="items"><i class="ri-add-line"></i> {{ __('Add Item') }}</button>
                                </div>
                                @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" id="items-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 5%;"></th>
                                            <th style="width: 25%;">{{ __('Product') }}</th>
                                            <th style="width: 17%;">{{ __('Quantity') }}</th>
                                            <th style="width: 14%;">{{ __('Unit Cost') }}</th>
                                            <th style="width: 14%;">{{ __('Total Cost') }}</th>
                                            <th style="width: 20%;">{{ __('Remarks') }}</th>
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
                                        <td data-label="{{ __('Product') }}">
                                            <select class="form-select item-product" data-field="product_id">
                                                <option value="">{{ __('== Select Product ==') }}</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }} @if($product->code) ({{ $product->code }}) @endif</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td data-label="{{ __('Quantity') }}">
                                            <div class="d-flex gap-1">
                                                <input type="number" step="0.01" class="form-control item-quantity" data-field="quantity" placeholder="{{ __('Quantity') }}">
                                                <button type="button" class="btn btn-sm btn-outline-secondary item-setup-variants flex-shrink-0" disabled title="{{ __('Setup Product Variants') }}">
                                                    <i class="ri-stack-line"></i>
                                                </button>
                                            </div>
                                            <input type="hidden" class="item-variants-payload" value="">
                                        </td>
                                        <td data-label="{{ __('Unit Cost') }}">
                                            <input type="number" step="0.01" min="0" class="form-control item-unit-cost" data-field="unit_cost" placeholder="{{ __('Unit Cost') }}">
                                        </td>
                                        <td data-label="{{ __('Total Cost') }}">
                                            <input type="text" class="form-control item-total-cost" readonly tabindex="-1" placeholder="{{ __('Total Cost') }}">
                                        </td>
                                        <td data-label="{{ __('Remarks') }}">
                                            <input type="text" class="form-control item-remarks" data-field="remarks" placeholder="{{ __('Remarks') }}">
                                        </td>
                                    </tr>
                                </template>

                            </div>

                            <!-- ===================== SETUP PRODUCT VARIANTS MODAL ===================== -->
                            <div class="modal fade" id="variantsModal" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ __('Setup Product Variants') }} — <span id="variantsModalProductName"></span></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="variantsModalWarning" class="alert alert-warning d-none mb-3"></div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle" id="variants-table">
                                                    <thead>
                                                    <tr>
                                                        <th style="width: 4%;"></th>
                                                        <th style="width: 4%;"></th>
                                                        <th style="width: 26%;">{{ __('Variant') }}</th>
                                                        <th style="width: 14%;">{{ __('Quantity') }}</th>
                                                        <th style="width: 14%;">{{ __('Unit Price') }}</th>
                                                        <th style="width: 14%;">{{ __('Total Price') }}</th>
                                                        <th style="width: 24%;">{{ __('Remarks') }}</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody id="variantsModalRows"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer justify-content-between">
                                            <div>
                                                <strong>{{ __('Variants Total') }}:</strong> <span id="variantsModalTotal">0.00</span>
                                                / <strong>{{ __('Line Quantity') }}:</strong> <span id="variantsModalTargetQuantity">0.00</span>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                <button type="button" class="btn btn-sm btn-info" id="variantsModalSave">{{ __('Save Variants') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <template id="variants-modal-row-template">
                                <tr class="variant-row">
                                    <td class="text-center pt-2 variant-row-action-cell" data-label="{{ __('Remove') }}">
                                        <button type="button" class="btn btn-sm btn-outline-danger variant-row-remove"><i class="ri-close-line"></i></button>
                                    </td>
                                    <td class="text-center pt-2 variant-row-action-cell" data-label="{{ __('Enabled') }}">
                                        <input type="checkbox" class="form-check-input variant-row-enabled" checked>
                                    </td>
                                    <td class="pt-2 variant-row-label" data-label="{{ __('Variant') }}"></td>
                                    <td data-label="{{ __('Quantity') }}">
                                        <input type="number" step="0.01" class="form-control variant-row-quantity" placeholder="{{ __('Quantity') }}">
                                    </td>
                                    <td data-label="{{ __('Unit Price') }}">
                                        <input type="number" step="0.01" min="0" class="form-control variant-row-unit-price" placeholder="{{ __('Unit Price') }}">
                                    </td>
                                    <td data-label="{{ __('Total Price') }}">
                                        <input type="text" class="form-control variant-row-total-price" readonly tabindex="-1" placeholder="{{ __('Total Price') }}">
                                    </td>
                                    <td data-label="{{ __('Remarks') }}">
                                        <input type="text" class="form-control variant-row-remarks" placeholder="{{ __('Remarks') }}">
                                    </td>
                                </tr>
                            </template>
                            <!--
                            NOTE: The Modal above lists the Product's existing Variants directly (one Row per
                            product_variants row) — see the script block for how variant-row-label/data-variant-id
                            are populated. It no longer builds Attribute x Value Combinations client-side.
                            -->
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('stock-transaction.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
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
            | TYPE -> SUPPLIER / DESTINATION STORE: CLIENT-SIDE SHOW/HIDE
            |--------------------------------------------------------------------------
            | Plain jQuery show/hide, matching this app's existing Category->SubCategory
            | client-side filter convention (no AJAX).
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
                    $supplierSelect.val('');
                }

                if (type === 'transfer') {
                    $destinationRow.removeClass('d-none');
                    filterDestinationStoreOptions();
                } else {
                    $destinationRow.addClass('d-none');
                    $destinationSelect.val('');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DESTINATION STORE: EXCLUDE THE SELECTED SOURCE STORE
            |--------------------------------------------------------------------------
            */
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
            | LINE ITEMS REPEATER
            |--------------------------------------------------------------------------
            | Template cloning + Add/Remove row handlers, matching the Specifications
            | repeater pattern used in product/create.blade.php.
            */
            function addRow(group) {
                const template = document.getElementById(group + '-row-template').innerHTML;
                $('#' + group + '-rows').append(template);
            }

            /*
            |--------------------------------------------------------------------------
            | RE-INDEX LINE ITEM FIELD NAMES: items[N][field]
            |--------------------------------------------------------------------------
            | Explicit numeric indices (not items[][field]) are required so that a
            | Row's several fields (product_id, quantity, unit_cost, remarks, variants)
            | are parsed back into ONE Item per Row — PHP's auto-increment for repeated
            | items[][field] assigns a NEW top-level index to every field occurrence
            | regardless of which Row it came from, which silently corrupts any
            | Transaction with more than one Line Item. Called after every add/remove.
            */
            function reindexItemRows() {
                $('.repeater-row[data-group="items"]').each(function (rowIndex) {
                    $(this).find('[data-field]').each(function () {
                        $(this).attr('name', 'items[' + rowIndex + '][' + $(this).data('field') + ']');
                    });
                });
            }

            /*
            |--------------------------------------------------------------------------
            | SETUP PRODUCT VARIANTS
            |--------------------------------------------------------------------------
            | Each Product's existing Variants (product_variants rows) are preloaded as
            | JSON (no AJAX round-trip), matching this app's existing Specifications/
            | Category filter convention. The Modal lists these Variants directly and
            | lets the User optionally break the Line's Quantity down per Variant.
            */
            const productVariants = @json($productVariants ?? []);
            const productNames = @json($products->mapWithKeys(fn ($product) => [$product->id => $product->name]));

            let $activeVariantsRow = null;

            function productHasVariants(productId) {
                const variants = productVariants[productId];
                return Array.isArray(variants) && variants.length > 0;
            }

            function refreshVariantsButtonState($row) {
                const productId = $row.find('.item-product').val();
                const quantity = parseFloat($row.find('.item-quantity').val());
                const $button = $row.find('.item-setup-variants');

                const canSetup = !!productId && productHasVariants(productId) && !isNaN(quantity) && quantity !== 0;
                $button.prop('disabled', !canSetup);
            }

            function clearRowVariants($row) {
                $row.find('.item-variants-payload').val('');
                $row.find('.item-setup-variants').removeClass('btn-info').addClass('btn-outline-secondary');
            }

            $(document).on('change', '.item-product', function () {
                // Product changed: any previously configured Variant breakdown no
                // longer applies to the newly selected Product.
                const $row = $(this).closest('.repeater-row');
                clearRowVariants($row);
                refreshVariantsButtonState($row);
            });

            $(document).on('input change', '.item-quantity', function () {
                refreshVariantsButtonState($(this).closest('.repeater-row'));
            });

            function syncVariantRowTotal($row) {
                const quantity = parseFloat($row.find('.variant-row-quantity').val());
                const unitPrice = parseFloat($row.find('.variant-row-unit-price').val());
                const $total = $row.find('.variant-row-total-price');

                if (isNaN(quantity) || isNaN(unitPrice)) {
                    $total.val('');
                    return;
                }

                $total.val((quantity * unitPrice).toFixed(2));
            }

            function syncVariantsModalSummary() {
                const targetQuantity = parseFloat($('#variantsModalTargetQuantity').data('value')) || 0;
                let variantsTotal = 0;

                $('#variantsModalRows .variant-row').each(function () {
                    if (!$(this).find('.variant-row-enabled').prop('checked')) {
                        return;
                    }

                    const quantity = parseFloat($(this).find('.variant-row-quantity').val());
                    variantsTotal += isNaN(quantity) ? 0 : quantity;
                });

                $('#variantsModalTotal').text(variantsTotal.toFixed(2));

                const $warning = $('#variantsModalWarning');
                if (Math.abs(variantsTotal - targetQuantity) > 0.01) {
                    $warning.removeClass('d-none').text(
                        '{{ __("Variants Total does not match the Line Quantity.") }} ' +
                        '({{ __("Variants") }}: ' + variantsTotal.toFixed(2) + ' / {{ __("Line Quantity") }}: ' + targetQuantity.toFixed(2) + ')'
                    );
                } else {
                    $warning.addClass('d-none').text('');
                }
            }

            $(document).on('input change', '#variantsModalRows .variant-row-quantity, #variantsModalRows .variant-row-unit-price', function () {
                syncVariantRowTotal($(this).closest('.variant-row'));
                syncVariantsModalSummary();
            });

            $(document).on('change', '#variantsModalRows .variant-row-enabled', syncVariantsModalSummary);

            $(document).on('click', '#variantsModalRows .variant-row-remove', function () {
                $(this).closest('.variant-row').remove();
                syncVariantsModalSummary();
            });

            $(document).on('click', '.item-setup-variants', function () {
                const $row = $(this).closest('.repeater-row');
                const productId = $row.find('.item-product').val();
                const variants = productVariants[productId];

                if (!variants || !variants.length) {
                    return;
                }

                $activeVariantsRow = $row;

                const lineQuantity = parseFloat($row.find('.item-quantity').val()) || 0;
                const lineUnitCost = $row.find('.item-unit-cost').val();

                $('#variantsModalProductName').text(productNames[productId] || '');
                $('#variantsModalTargetQuantity').text(lineQuantity.toFixed(2)).data('value', lineQuantity);

                const $rows = $('#variantsModalRows').empty();
                const rowTemplate = document.getElementById('variants-modal-row-template').innerHTML;

                const existingPayload = $row.find('.item-variants-payload').val();
                const existingVariants = existingPayload ? JSON.parse(existingPayload) : [];
                const existingById = {};
                existingVariants.forEach(function (variant) {
                    existingById[variant.product_variant_id] = variant;
                });

                variants.forEach(function (variant) {
                    const $variantRow = $(rowTemplate);
                    const existing = existingById[variant.id];

                    $variantRow.attr('data-variant-id', variant.id);
                    $variantRow.find('.variant-row-label').text(variant.label + (variant.sku ? ' (' + variant.sku + ')' : ''));

                    if (existing) {
                        $variantRow.find('.variant-row-enabled').prop('checked', true);
                        $variantRow.find('.variant-row-quantity').val(existing.quantity);
                        $variantRow.find('.variant-row-unit-price').val(existing.unit_cost ?? lineUnitCost);
                        $variantRow.find('.variant-row-remarks').val(existing.remarks ?? '');
                    } else {
                        $variantRow.find('.variant-row-unit-price').val(lineUnitCost);
                    }

                    syncVariantRowTotal($variantRow);
                    $rows.append($variantRow);
                });

                syncVariantsModalSummary();

                const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('variantsModal'));
                modal.show();
            });

            $('#variantsModalSave').on('click', function () {
                if (!$activeVariantsRow) {
                    return;
                }

                const variants = [];

                $('#variantsModalRows .variant-row').each(function () {
                    if (!$(this).find('.variant-row-enabled').prop('checked')) {
                        return;
                    }

                    const quantity = parseFloat($(this).find('.variant-row-quantity').val());
                    if (isNaN(quantity) || quantity === 0) {
                        return;
                    }

                    variants.push({
                        product_variant_id: $(this).attr('data-variant-id'),
                        quantity: quantity,
                        unit_cost: $(this).find('.variant-row-unit-price').val() || null,
                        remarks: $(this).find('.variant-row-remarks').val() || null,
                    });
                });

                $activeVariantsRow.find('.item-variants-payload').val(variants.length ? JSON.stringify(variants) : '');
                $activeVariantsRow.find('.item-setup-variants')
                    .toggleClass('btn-info', variants.length > 0)
                    .toggleClass('btn-outline-secondary', variants.length === 0);

                bootstrap.Modal.getOrCreateInstance(document.getElementById('variantsModal')).hide();
                $activeVariantsRow = null;
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
                syncItemTotalCost($(this).closest('.repeater-row'));
            });

            /*
            |--------------------------------------------------------------------------
            | PREVENT DUPLICATE PRODUCT SELECTION ACROSS LINE ITEMS
            |--------------------------------------------------------------------------
            | Once a Product is picked in one row, it's disabled in every other row's
            | Product <select> so the same Product can't be added twice.
            */
            function refreshProductOptions() {
                const selectedIds = $('.item-product').map(function () {
                    return $(this).val();
                }).get().filter(Boolean);

                $('.item-product').each(function () {
                    const currentValue = $(this).val();

                    $(this).find('option').each(function () {
                        if (!$(this).val()) {
                            return;
                        }

                        const isSelectedElsewhere = selectedIds.includes($(this).val()) && $(this).val() !== currentValue;
                        $(this).prop('disabled', isSelectedElsewhere);
                    });
                });
            }

            $(document).on('change', '.item-product', refreshProductOptions);

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
                refreshProductOptions();
                reindexItemRows();
            });

            $(document).on('click', '.remove-row', function () {
                const $rows = $('.repeater-row[data-group="items"]');
                if ($rows.length <= 1) {
                    Swal.fire('Notice', 'At Least One Line Item is Required.', 'warning');
                    return;
                }
                $(this).closest('.repeater-row').remove();
                refreshProductOptions();
                reindexItemRows();
            });

            /*
            |--------------------------------------------------------------------------
            | EXPAND VARIANTS PAYLOAD INTO SUBMITTABLE NESTED INPUTS
            |--------------------------------------------------------------------------
            | The .item-variants-payload hidden input holds a JSON string (built by the
            | Modal). Laravel needs real nested field names to parse it as an array, so
            | just before submit each row's JSON is expanded into items[N][variants][n]
            | hidden inputs (N = this Row's reindexed position) and the JSON placeholder
            | is removed from submission.
            */
            $('form').on('submit', function () {
                $('.repeater-row[data-group="items"]').each(function (rowIndex) {
                    const $row = $(this);
                    const $payload = $row.find('.item-variants-payload');
                    const raw = $payload.val();

                    $row.find('.item-variants-generated').remove();

                    if (!raw) {
                        return;
                    }

                    const variants = JSON.parse(raw);

                    variants.forEach(function (variant, variantIndex) {
                        const prefix = 'items[' + rowIndex + '][variants][' + variantIndex + ']';

                        $row.append(
                            $('<input type="hidden" class="item-variants-generated">')
                                .attr('name', prefix + '[product_variant_id]')
                                .val(variant.product_variant_id)
                        );

                        $row.append(
                            $('<input type="hidden" class="item-variants-generated">')
                                .attr('name', prefix + '[quantity]')
                                .val(variant.quantity)
                        );
                        $row.append(
                            $('<input type="hidden" class="item-variants-generated">')
                                .attr('name', prefix + '[unit_cost]')
                                .val(variant.unit_cost ?? '')
                        );
                        $row.append(
                            $('<input type="hidden" class="item-variants-generated">')
                                .attr('name', prefix + '[remarks]')
                                .val(variant.remarks ?? '')
                        );
                    });

                    $payload.prop('disabled', true);
                });
            });

            // Seed with one starter row.
            addRow('items');
            reindexItemRows();
        });
    </script>
@endpush
