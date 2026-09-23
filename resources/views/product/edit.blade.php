@extends('layout.master')

@section('title', __('Product'))

@push('styles')
    <style>
        .spec-value-check .form-check-input {
            width: 1.3em;
            height: 1.3em;
        }

        .spec-value-check .form-check-label {
            padding-top: 2px;
            padding-left: 0.25em;
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Product Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('product.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('product.update', $product->id) }}" method="POST">
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
                                            <label for="code" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Product Code') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control" id="code" value="{{ $product->code }}" readonly disabled>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="category_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Category') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Category ==') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('category_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="sub_category_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Sub-Category') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="sub_category_id" name="sub_category_id" class="form-select @error('sub_category_id') is-invalid @enderror">
                                                    <option value="">{{ __('== Select Sub-Category ==') }}</option>
                                                    @foreach($subCategories as $subCategory)
                                                        <option value="{{ $subCategory->id }}" data-category-id="{{ $subCategory->category_id }}" @selected(old('sub_category_id', $product->sub_category_id) == $subCategory->id)>{{ $subCategory->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('sub_category_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="brand_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Brand') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="brand_id" name="brand_id" class="form-select @error('brand_id') is-invalid @enderror">
                                                    <option value="">{{ __('== Select Brand ==') }}</option>
                                                    @foreach($brands as $brand)
                                                        <option value="{{ $brand->id }}" @selected(old('brand_id', $product->brand_id) == $brand->id)>{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('brand_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="unit_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Unit') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="unit_id" name="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                                    <option value="">{{ __('== Select Unit ==') }}</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" @selected(old('unit_id', $product->unit_id) == $unit->id)>{{ $unit->name }} ({{ $unit->symbol }}) - {{ $unit->group }}</option>
                                                    @endforeach
                                                </select>
                                                @error('unit_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Product Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="sku" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('SKU') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}">
                                                @error('sku')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2">{{ old('description', $product->description) }}</textarea>
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== SPECIFICATIONS (ATTRIBUTES) ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Specifications') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="specs"><i class="ri-add-line"></i> {{ __('Add Specification') }}</button>
                                </div>
                                @error('attribute_value_ids')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="specs-rows"></div>

                                <template id="specs-row-template">
                                    <div class="row mb-2 align-items-start repeater-row" data-group="specs">
                                        <div class="col-12 col-sm-4 col-md-2 pt-2 d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row flex-shrink-0"><i class="ri-close-line"></i></button>
                                            <select class="form-select spec-attribute">
                                                <option value="">{{ __('== Select Attribute ==') }}</option>
                                                @foreach($attributes as $attribute)
                                                    <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-7 col-md-8 pt-2">
                                            <div class="spec-values d-flex flex-wrap gap-3 pt-2 text-muted">{{ __('Select an Attribute First') }}</div>
                                        </div>
                                    </div>
                                </template>

                                <hr>

                                <!-- ===================== VARIANTS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Variants') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="variants"><i class="ri-add-line"></i> {{ __('Add Variant') }}</button>
                                </div>
                                @error('variants')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="variants-rows"></div>

                                <template id="variants-row-template">
                                    <div class="card mb-2 repeater-row" data-group="variants">
                                        <div class="card-body">
                                            <div class="row mb-2 align-items-start">
                                                <div class="col-12 col-md-1 pt-2 text-md-center">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                                </div>
                                                <div class="col-12 col-md-4 mb-2 mb-md-0">
                                                    <input type="text" class="form-control variant-sku" placeholder="{{ __('SKU') }}">
                                                </div>
                                                <div class="col-12 col-md-3 mb-2 mb-md-0">
                                                    <input type="number" step="0.01" min="0" class="form-control variant-unit-price" placeholder="{{ __('Unit Price') }}">
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center mb-2">
                                                <h6 class="mb-0 flex-grow-1 fst-italic">{{ __('Attribute Values') }} <small class="text-muted">({{ __('Optional') }})</small></h6>
                                                <button type="button" class="btn btn-sm btn-outline-success add-variant-spec-row"><i class="ri-add-line"></i> {{ __('Add Attribute') }}</button>
                                            </div>
                                            <div class="variant-specs-rows"></div>
                                        </div>
                                    </div>
                                </template>

                                <template id="variant-specs-row-template">
                                    <div class="row mb-2 align-items-start repeater-row">
                                        <div class="col-12 col-sm-4 col-md-2 pt-2 d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row flex-shrink-0"><i class="ri-close-line"></i></button>
                                            <select class="form-select variant-spec-attribute">
                                                <option value="">{{ __('== Select Attribute ==') }}</option>
                                                @foreach($attributes as $attribute)
                                                    <option value="{{ $attribute->id }}">{{ $attribute->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-12 col-sm-7 col-md-8 pt-2">
                                            <div class="variant-spec-values d-flex flex-wrap gap-3 pt-2 text-muted">{{ __('Select an Attribute First') }}</div>
                                        </div>
                                    </div>
                                </template>

                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('product.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Update') }}</button>
                                    </div>
                                </div>
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
            /*
            |--------------------------------------------------------------------------
            | CATEGORY -> SUB-CATEGORY: CLIENT-SIDE FILTER
            |--------------------------------------------------------------------------
            */
            const $subCategory = $('#sub_category_id');
            const $subCategoryOptions = $subCategory.find('option[data-category-id]');

            function filterSubCategories() {
                const categoryId = $('#category_id').val();
                let selectedStillMatches = true;

                $subCategoryOptions.each(function () {
                    const matches = !categoryId || String($(this).data('category-id')) === String(categoryId);
                    $(this).toggle(matches);

                    if ($(this).prop('selected') && !matches) {
                        selectedStillMatches = false;
                    }
                });

                if (!selectedStillMatches) {
                    $subCategory.val('');
                }
            }

            $('#category_id').on('change', filterSubCategories);
            filterSubCategories();

            /*
            |--------------------------------------------------------------------------
            | SPECIFICATIONS REPEATER: ATTRIBUTE -> ATTRIBUTE VALUE
            |--------------------------------------------------------------------------
            | Seeded from the Product's existing attribute-value assignments.
            */
            const attributeValues = @json($attributes->mapWithKeys(function ($attribute) {
                return [$attribute->id => $attribute->values->map(fn ($value) => ['id' => $value->id, 'value' => $value->value])];
            }));

            const existingAttributeValueIds = @json($product->attributeValues->pluck('id'));

            const existingAttributeIds = @json($product->attributeValues->pluck('attribute_id')->unique()->values());

            function renderValues(row, attributeId, checkedIds) {
                const $values = row.find('.spec-values');

                $values.empty();

                const values = attributeValues[attributeId] || [];

                if (!values.length) {
                    $values.text('{{ __("No Values Available for This Attribute") }}');

                    return;
                }

                values.forEach(function (value) {
                    const checkboxId = 'spec-value-' + attributeId + '-' + value.id;
                    const isChecked = checkedIds.includes(value.id);

                    $values.append(
                        $('<div class="form-check form-check-success spec-value-check">').append(
                            $('<input type="checkbox" name="attribute_value_ids[]">')
                                .addClass('form-check-input')
                                .attr('id', checkboxId)
                                .prop('checked', isChecked)
                                .val(value.id),
                            $('<label class="form-check-label">')
                                .attr('for', checkboxId)
                                .text(value.value)
                        )
                    );
                });
            }

            function addRow(group, attributeId) {
                const template = document.getElementById(group + '-row-template').innerHTML;
                const $row = $(template);

                if (attributeId) {
                    $row.find('.spec-attribute').val(attributeId);
                    renderValues($row, attributeId, existingAttributeValueIds);
                }

                $('#' + group + '-rows').append($row);
                refreshAttributeOptions();
            }

            // NOTE: '.add-row'/'.remove-row' are shared with the Variants repeater
            // below (data-group="variants") — each handler ignores clicks that
            // belong to the other group so both repeaters coexist correctly.
            $(document).on('click', '.add-row', function () {
                if ($(this).data('group') === 'variants') {
                    return;
                }
                addRow($(this).data('group'));
            });

            $(document).on('click', '.remove-row', function () {
                const $row = $(this).closest('.repeater-row');
                if ($row.data('group') === 'variants') {
                    return;
                }
                $row.remove();
                refreshAttributeOptions();
            });

            $(document).on('change', '.spec-attribute', function () {
                const row = $(this).closest('.repeater-row');
                renderValues(row, $(this).val(), []);
                refreshAttributeOptions();
            });

            /*
            |--------------------------------------------------------------------------
            | PREVENT DUPLICATE ATTRIBUTE SELECTION ACROSS ROWS
            |--------------------------------------------------------------------------
            | Once an Attribute is picked in one row, it's disabled in every other
            | row's Attribute <select> so the same Attribute can't be added twice.
            */
            function refreshAttributeOptions() {
                const selectedIds = $('.spec-attribute').map(function () {
                    return $(this).val();
                }).get().filter(Boolean);

                $('.spec-attribute').each(function () {
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

            if (existingAttributeIds.length) {
                existingAttributeIds.forEach(attributeId => addRow('specs', attributeId));
            } else {
                addRow('specs');
            }

            /*
            |--------------------------------------------------------------------------
            | VARIANTS REPEATER (Top Level) + PER-VARIANT ATTRIBUTE MINI-REPEATER
            |--------------------------------------------------------------------------
            | Mirrors create.blade.php's Variants repeater exactly, but seeded from
            | the Product's existing Variants (each with its own Attribute Values),
            | matching the Specifications section's existing seeding convention.
            */
            @php
                $existingVariantsList = $product->variants->map(function ($variant) {
                    return [
                        'sku' => $variant->sku,
                        'unit_price' => $variant->unit_price,
                        'attribute_value_ids' => $variant->attributeValues->pluck('id'),
                        'attribute_ids' => $variant->attributeValues->pluck('attribute_id')->unique()->values(),
                    ];
                });
            @endphp
            const existingVariants = @json($existingVariantsList);

            let variantRowSeq = 0;

            function reindexVariantRows() {
                $('#variants-rows > .repeater-row').each(function (index) {
                    const $row = $(this);
                    $row.find('.variant-sku').attr('name', 'variants[' + index + '][sku]');
                    $row.find('.variant-unit-price').attr('name', 'variants[' + index + '][unit_price]');
                    $row.find('.variant-spec-value-check-input').each(function () {
                        $(this).attr('name', 'variants[' + index + '][attribute_value_ids][]');
                    });
                });
            }

            function renderVariantSpecValues($specRow, $variantRow, attributeId, checkedIds) {
                const $values = $specRow.find('.variant-spec-values');
                $values.empty();

                const values = attributeValues[attributeId] || [];

                if (!values.length) {
                    $values.text('{{ __("No Values Available for This Attribute") }}');
                    return;
                }

                values.forEach(function (value) {
                    const checkboxId = 'variant-spec-value-' + $variantRow.attr('data-variant-seq') + '-' + attributeId + '-' + value.id;
                    const isChecked = checkedIds.includes(value.id);

                    $values.append(
                        $('<div class="form-check form-check-success spec-value-check">').append(
                            $('<input type="checkbox">')
                                .addClass('form-check-input variant-spec-value-check-input')
                                .attr('id', checkboxId)
                                .prop('checked', isChecked)
                                .val(value.id),
                            $('<label class="form-check-label">').attr('for', checkboxId).text(value.value)
                        )
                    );
                });
            }

            function addVariantSpecRow($variantRow, attributeId, checkedIds) {
                const template = document.getElementById('variant-specs-row-template').innerHTML;
                const $specRow = $(template);

                if (attributeId) {
                    $specRow.find('.variant-spec-attribute').val(attributeId);
                    renderVariantSpecValues($specRow, $variantRow, attributeId, checkedIds || []);
                }

                $variantRow.find('.variant-specs-rows').append($specRow);
                refreshVariantAttributeOptions($variantRow);
            }

            function addVariantRow(data) {
                const template = document.getElementById('variants-row-template').innerHTML;
                const $row = $(template);
                const rowSeq = variantRowSeq++;
                $row.attr('data-variant-seq', rowSeq);

                $('#variants-rows').append($row);

                if (data) {
                    $row.find('.variant-sku').val(data.sku);
                    $row.find('.variant-unit-price').val(data.unit_price);

                    if (data.attribute_ids && data.attribute_ids.length) {
                        data.attribute_ids.forEach(attributeId => addVariantSpecRow($row, attributeId, data.attribute_value_ids));
                    } else {
                        addVariantSpecRow($row);
                    }
                } else {
                    addVariantSpecRow($row);
                }

                reindexVariantRows();
            }

            $(document).on('click', '.add-variant-spec-row', function () {
                addVariantSpecRow($(this).closest('.repeater-row[data-group="variants"]'));
            });

            $(document).on('click', '.add-row', function () {
                if ($(this).data('group') === 'variants') {
                    addVariantRow();
                }
            });

            // Removing a Variant's own mini attribute-spec row (nested one level deeper
            // than the top-level Variant row itself).
            $(document).on('click', '.variant-specs-rows .remove-row', function () {
                const $variantRow = $(this).closest('.repeater-row[data-group="variants"]');
                $(this).closest('.repeater-row').remove();
                refreshVariantAttributeOptions($variantRow);
            });

            // Removing a whole top-level Variant row.
            $(document).on('click', '.repeater-row[data-group="variants"] > .card-body > .row > div > .remove-row', function () {
                $(this).closest('.repeater-row[data-group="variants"]').remove();
                reindexVariantRows();
            });

            $(document).on('change', '.variant-spec-attribute', function () {
                const $specRow = $(this).closest('.repeater-row');
                const $variantRow = $(this).closest('.repeater-row[data-group="variants"]');
                renderVariantSpecValues($specRow, $variantRow, $(this).val(), []);
                refreshVariantAttributeOptions($variantRow);
                reindexVariantRows();
            });

            /*
            |--------------------------------------------------------------------------
            | PREVENT DUPLICATE ATTRIBUTE SELECTION WITHIN ONE VARIANT ROW ONLY
            |--------------------------------------------------------------------------
            | Scoped to the single Variant row passed in — duplicate prevention does
            | NOT apply across different Variants.
            */
            function refreshVariantAttributeOptions($variantRow) {
                const $attributeSelects = $variantRow.find('.variant-spec-attribute');

                const selectedIds = $attributeSelects.map(function () {
                    return $(this).val();
                }).get().filter(Boolean);

                $attributeSelects.each(function () {
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

            if (existingVariants.length) {
                existingVariants.forEach(variant => addVariantRow(variant));
            }
        });
    </script>
@endpush
