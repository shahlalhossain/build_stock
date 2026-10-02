@extends('layout.master')

@section('title', __('Product Delivery'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Delivery') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-delivery.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('product-delivery.store') }}" method="POST">
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
                                            <label for="store_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Source Store') }}</label>
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
                                            <label for="delivered_to" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Delivered To') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('delivered_to') is-invalid @enderror" id="delivered_to" name="delivered_to" value="{{ old('delivered_to') }}" placeholder="{{ __('Customer / Project / Site Name') }}">
                                                @error('delivered_to')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="transaction_date" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Delivery Date') }}</label>
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

                                <!-- ===================== DELIVERY ITEMS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Delivery Items') }}</h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="items"><i class="ri-add-line"></i> {{ __('Add Item') }}</button>
                                </div>
                                @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" id="items-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 5%;"></th>
                                            <th style="width: 28%;">{{ __('Product') }}</th>
                                            <th style="width: 20%;">{{ __('Variant') }}</th>
                                            <th style="width: 15%;">{{ __('Quantity') }}</th>
                                            <th style="width: 12%;">{{ __('Unit') }}</th>
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
                                        <td data-label="{{ __('Remarks') }}">
                                            <input type="text" class="form-control item-remarks" data-field="remarks" placeholder="{{ __('Remarks') }}">
                                        </td>
                                    </tr>
                                </template>

                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('product-delivery.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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

            $(document).on('change', '.item-product', function () {
                syncVariantOptions($(this).closest('.repeater-row'));
                refreshProductOptions();
                refreshVariantOptions();
            });

            $(document).on('change', '.item-variant', function () {
                refreshProductOptions();
                refreshVariantOptions();
            });

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
                refreshProductOptions();
                refreshVariantOptions();
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
                refreshVariantOptions();
                reindexItemRows();
            });

            // Seed with one starter row.
            addRow('items');
            reindexItemRows();
        });
    </script>
@endpush
