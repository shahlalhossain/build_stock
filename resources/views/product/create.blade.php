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

        #product-images-dropzone.dropzone {
            min-height: unset;
            padding: 8px 10px;
        }

        #product-images-dropzone.dropzone .dz-message {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        #product-images-dropzone.dropzone .dz-message .display-6 {
            font-size: 1.25rem;
            margin-bottom: 0 !important;
        }

        #product-images-dropzone.dropzone .dz-message h6 {
            margin-bottom: 0;
        }

        #product-images-dropzone.dropzone .dz-message .fs-13 {
            display: none;
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
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Product Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
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
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Product Images') }}</label>
                                            <div class="col-12 col-md-8">
                                                {{--
    View only for now — Dropzone stages files client-side (image
    previews, drag & drop, remove-before-submit) with autoProcessQueue
    disabled and no live url, so nothing is uploaded on Save yet. Wiring
    this to storage is a separate follow-up (FileService/ImageService
    already exist in this app and are the natural fit).

    Markup/init follow Velzon's own Dropzone pattern (see
    ecommerce-product-create.init.js): #dropzone-preview-list is a
    hidden ROW TEMPLATE, read once at init time and removed from the DOM,
    then re-rendered by Dropzone into the separate #dropzone-preview list
    below the drop area for every accepted File.
--}}
                                                <div action="#" class="dropzone" id="product-images-dropzone">
                                                    <div class="dz-message needsclick">
                                                        <div class="mb-2"><i class="display-6 text-muted ri-upload-cloud-2-line"></i></div>
                                                        <h6>{{ __('Drop Files Here or Click to Upload') }}</h6>
                                                        <span class="text-muted fs-13">{{ __('Upload Multiple Product Images') }}</span>
                                                    </div>
                                                </div>

                                                <ul class="list-unstyled mb-0" id="dropzone-preview">
                                                    {{--
                                                        This <li> is the ROW TEMPLATE only (see the note above) —
                                                        display:none from first paint so it never flashes on screen
                                                        before JS captures and removes it on window 'load'.
                                                    --}}
                                                    <li class="mt-2" id="dropzone-preview-list" style="display: none;">
                                                        <div class="border rounded">
                                                            <div class="d-flex p-2">
                                                                <div class="flex-shrink-0 me-3">
                                                                    <div class="avatar-sm bg-light rounded p-2">
                                                                        <img data-dz-thumbnail class="img-fluid rounded d-block" src="{{ asset('assets/images/small/img-9.jpg') }}" alt="">
                                                                    </div>
                                                                </div>
                                                                <div class="pt-1 flex-grow-1">
                                                                    <h5 class="fs-14 mb-1" data-dz-name></h5>
                                                                    <p class="fs-13 text-muted mb-0" data-dz-size></p>
                                                                </div>
                                                                <div class="flex-shrink-0 ms-3">
                                                                    <button type="button" class="btn btn-sm btn-danger product-image-remove">{{ __('Delete') }}</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== SPECIFICATIONS (ATTRIBUTES) ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Specifications') }} <small class="text-muted">({{ __('Optional') }})</small></h5>
                                    <button type="button" class="btn btn-sm btn-success add-row" data-group="specs"><i class="ri-add-line"></i> {{ __('Add Spec.') }}</button>
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
        // Disable Auto-Discovery Globally so Every Dropzone Instance is Explicit.
        Dropzone.autoDiscover = false;

        /*
        |------------------------------------------------------------------------------
        | PRODUCT IMAGES: DROPZONE (DRAG & DROP + PREVIEWS, VIEW ONLY FOR NOW)
        |------------------------------------------------------------------------------
        | Deliberately initialized on window 'load', NOT $(document).ready(): this
        | theme's plugins.js conditionally loads Toastify/Choices.js/Flatpickr via
        | document.write() (see assets/js/plugins.js), which — because those are
        | external <script src> tags — pauses HTML parsing until they've downloaded.
        | $(document).ready() can fire while that pause has left later elements
        | (including #dropzone-preview-list, further down this same page) not yet
        | parsed into the DOM, so querying for it there intermittently returns null.
        | 'load' waits for the ENTIRE page — every resource, including those injected
        | scripts — so the element is always reliably present by the time this runs.
        */
        window.addEventListener('load', function () {
            const dropzonePreviewNode = document.querySelector('#dropzone-preview-list');
            // The Template row is display:none in the Blade markup so it never flashes
            // on screen before this runs — strip that back off before capturing the
            // template string, or every REAL preview clone would render hidden too.
            dropzonePreviewNode.style.display = '';
            const previewTemplate = dropzonePreviewNode.parentNode.innerHTML;
            dropzonePreviewNode.parentNode.removeChild(dropzonePreviewNode);

            const productImagesDropzone = new Dropzone('#product-images-dropzone', {
                url: '#',
                autoProcessQueue: false,
                maxFiles: 10,
                maxFilesize: 5, // MB
                acceptedFiles: 'image/jpeg,image/png,image/webp',
                previewTemplate: previewTemplate,
                previewsContainer: '#dropzone-preview',
                dictDefaultMessage: '{{ __('Drop Files Here or Click to Upload') }}',
                dictMaxFilesExceeded: '{{ __('You cannot Upload any more Files.') }}',
                dictInvalidFileType: '{{ __('You cannot Upload Files of this Type.') }}',
                // The "at"-prefixed double-curly below is Blade's literal-brace escape,
                // needed so Dropzone's OWN filesize/maxFilesize placeholders reach the
                // browser as-is instead of being parsed as a Blade echo.
                dictFileTooBig: '{{ __('File is too Big') }} (@{{filesize}}MiB). {{ __('Max Filesize') }}: @{{maxFilesize}}MiB.',
                init: function () {
                    this.on('addedfile', function () {
                        if (this.files.length > this.options.maxFiles) {
                            this.removeFile(this.files[0]);

                            Toastify({
                                text: '{{ __('You can Upload a Maximum of 10 Images.') }}',
                                duration: 3000,
                                gravity: 'top',
                                position: 'right',
                                className: 'failed-toast',
                                stopOnFocus: true
                            }).showToast();
                        }
                    });

                    /*
                    |----------------------------------------------------------------
                    | REMOVE WITHOUT ANY CONFIRM DIALOG
                    |----------------------------------------------------------------
                    | The Delete button is deliberately NOT [data-dz-remove] — Dropzone
                    | auto-wires that attribute to its own removeFileEvent handler,
                    | which calls window.confirm() whenever a File's status is
                    | UPLOADING (which every File here transiently is, since
                    | autoProcessQueue never resolves it to a final state). Binding our
                    | OWN click handler and calling removeFile() directly skips that
                    | logic entirely — no dialog, ever.
                    */
                    this.on('addedfile', function (file) {
                        $(file.previewElement).find('.product-image-remove').on('click', function () {
                            productImagesDropzone.removeFile(file);
                        });
                    });
                }
            });

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
                let selectedStillMatches = true;

                $subCategoryOptions.each(function () {
                    const matches = !categoryId || String($(this).data('category-id')) === String(categoryId);
                    $(this).toggle(matches);

                    if ($(this).prop('selected') && !matches) {
                        selectedStillMatches = false;
                    }
                });

                // Clear the Sub-Category selection if it no longer belongs to the selected Category.
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
                refreshAttributeOptions();
            }

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('.repeater-row').remove();
                refreshAttributeOptions();
            });

            $(document).on('change', '.spec-attribute', function () {
                const row = $(this).closest('.repeater-row');
                const attributeId = $(this).val();
                const $values = row.find('.spec-values');

                $values.empty();

                const values = attributeValues[attributeId] || [];

                if (!values.length) {
                    $values.text('{{ __("No Values Available for This Attribute") }}');
                } else {
                    values.forEach(function (value) {
                        const checkboxId = 'spec-value-' + attributeId + '-' + value.id;

                        $values.append(
                            $('<div class="form-check form-check-success spec-value-check">').append(
                                $('<input type="checkbox" name="attribute_value_ids[]">').addClass('form-check-input').attr('id', checkboxId).val(value.id),
                                $('<label class="form-check-label">').attr('for', checkboxId).text(value.value)
                            )
                        );
                    });
                }

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

            // Seed with one starter row.
            addRow('specs');
        });
    </script>
@endpush
