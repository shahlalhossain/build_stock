@extends('layout.master')

@section('title', __('Store'))

@push('styles')
    <style>
        .store-image-wrapper {
            position: relative;
            width: 320px;
            height: 320px;
            margin: 0 auto;
        }
        .store-image-preview {
            width: 320px;
            height: 320px;
            object-fit: cover;
            border-radius: var(--vz-border-radius);
        }
        .store-image-placeholder {
            width: 320px;
            height: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: var(--vz-light);
            border-radius: var(--vz-border-radius);
            color: var(--vz-secondary-color);
        }
        .store-image-placeholder i {
            font-size: 3rem;
        }
        .store-image-action-btn,
        .store-image-remove-btn {
            box-sizing: border-box;
            position: absolute;
            bottom: 10px;
            width: 38px;
            height: 38px;
            margin: 0;
            padding: 0;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .store-image-action-btn {
            right: 10px;
            background-color: var(--vz-light);
            color: var(--vz-body-color);
        }
        .store-image-remove-btn {
            left: 10px;
            background-color: var(--vz-danger);
            color: #fff;
        }
        .store-image-action-btn:hover,
        .store-image-remove-btn:hover {
            transform: scale(1.1);
            filter: brightness(0.95);
        }
        .store-image-action-btn i,
        .store-image-remove-btn i {
            pointer-events: none;
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Store Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('store.edit', $store->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('store.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('store.update', $store->id) }}" method="POST" enctype="multipart/form-data">
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
                                    <div class="col-12 col-md-8">
                                        <div class="row mb-2">
                                            <label for="project_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Location') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                                                    <option value="" @selected(old('project_id', $store->project_id) === null)>{{ __('Head Office') }}</option>
                                                    @foreach($projects as $project)
                                                        <option value="{{ $project->id }}" @selected(old('project_id', $store->project_id) == $project->id)>{{ ucwords($project->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('project_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="type" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Type') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                    <option value="store" @selected(old('type', $store->type) === 'store')>{{ __('Store') }}</option>
                                                    <option value="warehouse" @selected(old('type', $store->type) === 'warehouse')>{{ __('Warehouse') }}</option>
                                                </select>
                                                @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Store Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $store->name) }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $store->description) }}">
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="storekeeper_ids" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Storekeeper') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="storekeeper_ids" name="storekeeper_ids[]" class="form-select @error('storekeeper_ids') is-invalid @enderror @error('storekeeper_ids.*') is-invalid @enderror" data-choices data-choices-removeItem multiple required>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('storekeeper_ids', $selectedStorekeeperIds)))>{{ ucwords($user->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('storekeeper_ids')<small class="text-danger">{{ $message }}</small>@enderror
                                                @error('storekeeper_ids.*')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="mobile" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Mobile') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile', $store->mobile) }}">
                                                @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="email" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Email') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $store->email) }}">
                                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="manager_ids" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Store Manager') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="manager_ids" name="manager_ids[]" class="form-select @error('manager_ids') is-invalid @enderror @error('manager_ids.*') is-invalid @enderror" data-choices data-choices-removeItem multiple>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('manager_ids', $selectedManagerIds)))>{{ ucwords($user->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('manager_ids')<small class="text-danger">{{ $message }}</small>@enderror
                                                @error('manager_ids.*')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-4 text-center">
                                        <div class="store-image-wrapper">
                                            <img id="storeImagePreview" src="{{ $store->image ? asset('storage/'.$store->image) : '' }}" class="store-image-preview img-thumbnail @if(! $store->image) d-none @endif" alt="{{ __('Store Image') }}">
                                            <div id="storeImagePlaceholder" class="store-image-placeholder @if($store->image) d-none @endif">
                                                <i class="ri-store-2-line"></i>
                                                <span>{{ __('No Store/Warehouse Image Selected') }}</span>
                                            </div>
                                            <button type="button" id="removeStoreImageBtn" class="store-image-remove-btn @if(! $store->image) d-none @endif" title="{{ __('Remove Image') }}"><i class="ri-delete-bin-5-line"></i></button>
                                            <label for="image" class="store-image-action-btn" title="{{ __('Upload Image') }}"><i class="ri-camera-fill"></i></label>
                                        </div>
                                        <label class="form-label d-block">{{ __('Store/Warehouese Photo') }}</label>
                                        <input type="file" id="image" name="image" class="d-none @error('image') is-invalid @enderror" accept="image/*">
                                        <input type="hidden" id="remove_image" name="remove_image" value="0">
                                        @error('image')<small class="text-danger d-block mt-1">{{ $message }}</small>@enderror
                                    </div>
                                    <!-- End Right Column -->
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('store.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
        document.querySelectorAll('[data-choices]').forEach(function (element) {
            new Choices(element, {
                removeItemButton: element.hasAttribute('data-choices-removeItem'),
                shouldSort: false,
            });
        });

        $(function () {
            const $fileInput = $('#image');
            const $preview = $('#storeImagePreview');
            const $placeholder = $('#storeImagePlaceholder');
            const $removeBtn = $('#removeStoreImageBtn');
            const $removeFlag = $('#remove_image');

            // Preview New Image
            $fileInput.on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $preview.attr('src', e.target.result).removeClass('d-none');
                        $placeholder.addClass('d-none');
                    };
                    reader.readAsDataURL(file);
                    // Upload Overrides Delete
                    $removeFlag.val(0);
                    $removeBtn.removeClass('d-none');
                }
            });

            // Remove Store Image (UI Only — the Service Deletes the File on Submit)
            $removeBtn.on('click', function () {
                $preview.addClass('d-none').attr('src', '');
                $placeholder.removeClass('d-none');
                $fileInput.val('');
                $removeFlag.val(1);
                $(this).addClass('d-none');
            });
        });
    </script>
@endpush
