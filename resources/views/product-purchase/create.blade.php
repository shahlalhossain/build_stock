@extends('layout.master')

@section('title', __('Product Purchase'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Purchase') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-purchase.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('product-purchase.store') }}" method="POST" enctype="multipart/form-data">
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
                                            <label for="store_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Receiving Store') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="store_id" name="store_id" class="form-select @error('store_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Store ==') }}</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}" @selected(old('store_id', $defaultStoreId) == $store->id)>{{ ucwords($store->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('store_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="supplier_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Supplier ==') }}</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ ucwords($supplier->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('supplier_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="transaction_date" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Purchase Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                                @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="invoice_number" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Invoice Number') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="{{ __('Supplier Invoice Number') }}" required>
                                                @error('invoice_number')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="supplier_invoice_date" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Invoice Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('supplier_invoice_date') is-invalid @enderror" id="supplier_invoice_date" name="supplier_invoice_date" value="{{ old('supplier_invoice_date') }}">
                                                @error('supplier_invoice_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Remarks') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks') }}</textarea>
                                                @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="invoice_attachment" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Invoice Attachment') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="file" class="form-control @error('invoice_attachment') is-invalid @enderror" id="invoice_attachment" name="invoice_attachment" accept=".pdf,.jpg,.jpeg,.png">
                                                <div class="form-text">{{ __('Optional. PDF, JPG or PNG — Max 10 MB.') }}</div>
                                                @error('invoice_attachment')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            {{-- TODO: Uploaded Invoice File Preview will be here (File Format could be Image or PDF) --}}
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== PURCHASE ITEMS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Purchase Items') }}</h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="items"><i class="ri-add-line"></i> {{ __('Add Item') }}</button>
                                </div>
                                @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" id="items-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 4%;"></th>
                                            <th style="width: 20%;">{{ __('Product') }}</th>
                                            <th style="width: 16%;">{{ __('Variant') }}</th>
                                            <th style="width: 11%;">{{ __('Quantity') }}</th>
                                            <th style="width: 10%;">{{ __('Unit') }}</th>
                                            <th style="width: 12%;">{{ __('Unit Cost') }}</th>
                                            <th style="width: 12%;">{{ __('Total Cost') }}</th>
                                            <th style="width: 15%;">{{ __('Remarks') }}</th>
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
                                                    <option value="{{ $product->id }}" data-unit-id="{{ $product->unit_id }}">{{ $product->name }} @if($product->code) ({{ $product->code }}) @endif</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td data-label="{{ __('Variant') }}">
                                            <select class="form-select item-variant" data-field="product_variant_id" disabled>
                                                <option value="">{{ __('== No Variant ==') }}</option>
                                            </select>
                                        </td>
                                        <td data-label="{{ __('Quantity') }}">
                                            <input type="number" step="0.01" min="0.01" class="form-control item-quantity" data-field="quantity" placeholder="{{ __('Quantity') }}">
                                        </td>
                                        <td data-label="{{ __('Unit') }}">
                                            <select class="form-select item-unit" data-field="unit_id">
                                                <option value="">{{ __('== Unit ==') }}</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }} @if($unit->symbol) ({{ $unit->symbol }}) @endif</option>
                                                @endforeach
                                            </select>
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

                                <hr>

                                <!-- ===================== TOTALS ===================== -->
                                <div class="row justify-content-end">
                                    <div class="col-12 col-md-5">
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Total Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="text" class="form-control" id="purchase_total_amount_display" readonly tabindex="-1" value="0.00">
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="discount_type" class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Discount') }}</label>
                                            <div class="col-6 col-md-4">
                                                <select id="discount_type" name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                                    <option value="fixed" @selected(old('discount_type') === 'fixed')>{{ __('Fixed') }}</option>
                                                    <option value="percentage" @selected(old('discount_type') === 'percentage')>{{ __('Percentage') }}</option>
                                                </select>
                                                @error('discount_type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <input type="number" step="0.01" min="0" class="form-control @error('discount_amount') is-invalid @enderror" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', 0) }}">
                                                @error('discount_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="tax_amount" class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Tax Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="number" step="0.01" min="0" class="form-control @error('tax_amount') is-invalid @enderror" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', 0) }}">
                                                @error('tax_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Net Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="text" class="form-control fw-bold" id="purchase_net_amount_display" readonly tabindex="-1" value="0.00">
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="payment_status" class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Payment Status') }}</label>
                                            <div class="col-12 col-md-7">
                                                <select id="payment_status" name="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                                    <option value="unpaid" @selected(old('payment_status', 'unpaid') === 'unpaid')>{{ __('Unpaid') }}</option>
                                                    <option value="partial" @selected(old('payment_status') === 'partial')>{{ __('Partial') }}</option>
                                                    <option value="paid" @selected(old('payment_status') === 'paid')>{{ __('Paid') }}</option>
                                                </select>
                                                @error('payment_status')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="paid_amount" class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Paid Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="number" step="0.01" min="0" class="form-control @error('paid_amount') is-invalid @enderror" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}">
                                                @error('paid_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('product-purchase.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
            const productVariants = @json($productVariants ?? []);

            function addRow(group) {
                const template = document.getElementById(group + '-row-template').innerHTML;
                $('#' + group + '-rows').append(template);
            }

            function reindexItemRows() {
                $('.repeater-row[data-group="items"]').each(function (rowIndex) {
                    $(this).find('[data-field]').each(function () {
                        $(this).attr('name', 'items[' + rowIndex + '][' + $(this).data('field') + ']');
                    });
                });
            }

            /*
            |--------------------------------------------------------------------------
            | VARIANT DROPDOWN: POPULATE FROM SELECTED PRODUCT
            |--------------------------------------------------------------------------
            */
            function syncVariantOptions($row) {
                const productId = $row.find('.item-product').val();
                const $variantSelect = $row.find('.item-variant');
                const currentValue = $variantSelect.val();
                const variants = productVariants[productId];

                $variantSelect.empty().append('<option value="">{{ __("== No Variant ==") }}</option>');

                if (Array.isArray(variants) && variants.length > 0) {
                    variants.forEach(function (variant) {
                        $variantSelect.append('<option value="' + variant.id + '">' + variant.label + '</option>');
                    });
                    $variantSelect.prop('disabled', false);
                    $variantSelect.val(currentValue);
                } else {
                    $variantSelect.prop('disabled', true);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PREVENT DUPLICATE PRODUCT / VARIANT SELECTION ACROSS LINE ITEMS
            |--------------------------------------------------------------------------
            | A Variant-less Product can only be picked once. A Product WITH
            | Variants may be picked in multiple Rows (one per Variant) and is only
            | disabled elsewhere once every one of its Variants has been used.
            */
            function refreshProductOptions() {
                const $rows = $('.repeater-row[data-group="items"]');

                const usedVariantIdsByProduct = {};
                $rows.each(function () {
                    const productId = $(this).find('.item-product').val();
                    const variantId = $(this).find('.item-variant').val();

                    if (productId && variantId) {
                        (usedVariantIdsByProduct[productId] = usedVariantIdsByProduct[productId] || []).push(String(variantId));
                    }
                });

                const usedPlainProductIds = $rows.map(function () {
                    const productId = $(this).find('.item-product').val();
                    const variants = productVariants[productId];
                    const hasVariants = Array.isArray(variants) && variants.length > 0;

                    return (productId && !hasVariants) ? productId : null;
                }).get().filter(Boolean);

                $('.item-product').each(function () {
                    const currentValue = $(this).val();

                    $(this).find('option').each(function () {
                        const optionProductId = $(this).val();
                        if (!optionProductId) {
                            return;
                        }

                        if (optionProductId === currentValue) {
                            $(this).prop('disabled', false);
                            return;
                        }

                        const variants = productVariants[optionProductId];
                        const hasVariants = Array.isArray(variants) && variants.length > 0;

                        let isExhausted;
                        if (hasVariants) {
                            const usedVariantIds = usedVariantIdsByProduct[optionProductId] || [];
                            isExhausted = variants.every(function (variant) {
                                return usedVariantIds.includes(String(variant.id));
                            });
                        } else {
                            isExhausted = usedPlainProductIds.includes(optionProductId);
                        }

                        $(this).prop('disabled', isExhausted);
                    });
                });
            }

            function refreshVariantOptions() {
                const $rows = $('.repeater-row[data-group="items"]');

                const usedVariantIdsByProduct = {};
                $rows.each(function () {
                    const productId = $(this).find('.item-product').val();
                    const variantId = $(this).find('.item-variant').val();

                    if (productId && variantId) {
                        (usedVariantIdsByProduct[productId] = usedVariantIdsByProduct[productId] || []).push(String(variantId));
                    }
                });

                $rows.each(function () {
                    const productId = $(this).find('.item-product').val();
                    const $variantSelect = $(this).find('.item-variant');
                    const currentValue = $variantSelect.val();
                    const usedVariantIds = usedVariantIdsByProduct[productId] || [];

                    $variantSelect.find('option').each(function () {
                        const optionVariantId = $(this).val();
                        if (!optionVariantId) {
                            return;
                        }

                        $(this).prop('disabled', optionVariantId !== currentValue && usedVariantIds.includes(optionVariantId));
                    });
                });
            }

            /*
            |--------------------------------------------------------------------------
            | UNIT DROPDOWN: DEFAULT TO THE SELECTED PRODUCT'S OWN BASE UNIT
            |--------------------------------------------------------------------------
            | A Convenience Default only — the User can still Override it. Fires on
            | every Product change, including Switching to a Product with no Unit Set
            | (clears the field rather than leaving a stale Unit Selected).
            */
            function syncUnitFromProduct($row) {
                const $productSelect = $row.find('.item-product');
                const unitId = $productSelect.find('option:selected').data('unit-id');

                $row.find('.item-unit').val(unitId ? String(unitId) : '');
            }

            $(document).on('change', '.item-product', function () {
                syncVariantOptions($(this).closest('.repeater-row'));
                syncUnitFromProduct($(this).closest('.repeater-row'));
                refreshProductOptions();
                refreshVariantOptions();
            });

            $(document).on('change', '.item-variant', function () {
                refreshProductOptions();
                refreshVariantOptions();
            });

            /*
            |--------------------------------------------------------------------------
            | LINE ITEM TOTAL COST + PURCHASE TOTALS
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

            function syncPurchaseTotals() {
                let totalAmount = 0;
                $('.repeater-row[data-group="items"]').each(function () {
                    const lineTotal = parseFloat($(this).find('.item-total-cost').val());
                    if (!isNaN(lineTotal)) {
                        totalAmount += lineTotal;
                    }
                });

                const discountType = $('#discount_type').val();
                const discountAmount = parseFloat($('#discount_amount').val()) || 0;
                const taxAmount = parseFloat($('#tax_amount').val()) || 0;

                const discountValue = discountType === 'percentage'
                    ? totalAmount * (discountAmount / 100)
                    : discountAmount;

                const netAmount = totalAmount - discountValue + taxAmount;

                $('#purchase_total_amount_display').val(totalAmount.toFixed(2));
                $('#purchase_net_amount_display').val(netAmount.toFixed(2));
            }

            $(document).on('input', '.item-quantity, .item-unit-cost', function () {
                syncItemTotalCost($(this).closest('.repeater-row'));
                syncPurchaseTotals();
            });

            $(document).on('input change', '#discount_type, #discount_amount, #tax_amount', syncPurchaseTotals);

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
                refreshProductOptions();
                refreshVariantOptions();
                reindexItemRows();
                syncPurchaseTotals();
            });

            $(document).on('click', '.remove-row', function () {
                const $rows = $('.repeater-row[data-group="items"]');
                if ($rows.length <= 1) {
                    Swal.fire('Notice', 'At Least One Line Item is Required.', 'warning');
                    return;
                }
                $(this).closest('.repeater-row').remove();
                refreshProductOptions();
                refreshVariantOptions();
                reindexItemRows();
                syncPurchaseTotals();
            });

            // Seed with one starter row.
            addRow('items');
            reindexItemRows();
        });
    </script>
@endpush
