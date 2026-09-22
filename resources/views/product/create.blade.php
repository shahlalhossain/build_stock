@extends('layout.master')

@section('title', __('Product'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Product Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('product.store') }}" method="POST">
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
                                            <label for="category_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Category') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Category ==') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
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
                                                        <option value="{{ $subCategory->id }}" data-category-id="{{ $subCategory->category_id }}" @selected(old('sub_category_id') == $subCategory->id)>{{ $subCategory->name }}</option>
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
                                                        <option value="{{ $brand->id }}" @selected(old('brand_id') == $brand->id)>{{ $brand->name }}</option>
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
                                                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} ({{ $unit->symbol }}) - {{ $unit->group }}</option>
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
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="{{ __('e.g. MS Rod 12mm 60 Grade') }}">
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="sku" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('SKU') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku') }}" placeholder="{{ __('Optional Supplier/Barcode SKU') }}">
                                                @error('sku')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="2" placeholder="">{{ old('description') }}</textarea>
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
                                        <div class="col-12 col-sm-4 col-md-3 pt-2">
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
                                        <div class="col-12 col-sm-1 col-md-1 text-end text-md-start" style="padding-top: 13px;">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
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
            | CATEGORY -> SUB-CATEGORY: CLIENT-SIDE FILTER
            |--------------------------------------------------------------------------
            | All Sub-Categories are already rendered (each carries data-category-id).
            | Selecting a Category just shows/hides the matching <option>s instead of
            | an AJAX round-trip, matching this app's existing Category/Sub-Category
            | filtering convention.
            */
            const $subCategory = $('#sub_category_id');
            const $subCategoryOptions = $subCategory.find('option[data-category-id]');

            function filterSubCategories() {
                const categoryId = $('#category_id').val();

                $subCategoryOptions.each(function () {
                    const matches = !categoryId || String($(this).data('category-id')) === String(categoryId);
                    $(this).toggle(matches);
                });

                // Clear the Sub-Category selection if it no longer belongs to the selected Category.
                const $selected = $subCategory.find('option:selected');
                if ($selected.length && $selected.is(':hidden')) {
                    $subCategory.val('');
                }
            }

            $('#category_id').on('change', filterSubCategories);
            filterSubCategories();

            /*
            |--------------------------------------------------------------------------
            | SPECIFICATIONS REPEATER: ATTRIBUTE -> ATTRIBUTE VALUE
            |--------------------------------------------------------------------------
            | Attribute Values for every Attribute are preloaded (data-values on each
            | <option>), so choosing an Attribute repopulates the Value <select> from
            | that JSON instead of an AJAX call.
            */
            const attributeValues = @json($attributes->mapWithKeys(function ($attribute) {
                return [$attribute->id => $attribute->values->map(fn ($value) => ['id' => $value->id, 'value' => $value->value])];
            }));

            function addRow(group) {
                const template = document.getElementById(group + '-row-template').innerHTML;
                $('#' + group + '-rows').append(template);
            }

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('.repeater-row').remove();
            });

            $(document).on('change', '.spec-attribute', function () {
                const row = $(this).closest('.repeater-row');
                const attributeId = $(this).val();
                const $values = row.find('.spec-values');

                $values.empty();

                const values = attributeValues[attributeId] || [];

                if (!values.length) {
                    $values.text('{{ __("No Values Available for This Attribute") }}');

                    return;
                }

                values.forEach(function (value) {
                    const checkboxId = 'spec-value-' + attributeId + '-' + value.id;

                    $values.append(
                        $('<div class="form-check">').append(
                            $('<input type="checkbox" name="attribute_value_ids[]">')
                                .addClass('form-check-input')
                                .attr('id', checkboxId)
                                .val(value.id),
                            $('<label class="form-check-label">')
                                .attr('for', checkboxId)
                                .text(value.value)
                        )
                    );
                });
            });

            // Seed with one starter row.
            addRow('specs');
        });
    </script>
@endpush
