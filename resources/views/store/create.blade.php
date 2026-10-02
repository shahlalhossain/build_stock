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
        .store-image-action-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            background-color: var(--vz-light);
            color: var(--vz-body-color);
            transition: all 0.2s ease;
        }
        .store-image-action-btn:hover {
            transform: scale(1.1);
            filter: brightness(0.95);
        }
        .store-image-action-btn i {
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Store Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('store.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('store.store') }}" method="POST" enctype="multipart/form-data">
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
                                            <label for="project_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Location') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                                                    <option value="" @selected(old('project_id') === null)>{{ __('Head Office') }}</option>
                                                    @foreach($projects as $project)
                                                        <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>{{ ucwords($project->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('project_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="type" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Type') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                    <option value="" @selected(old('type') === null)>{{ __('Select Type') }}</option>
                                                    <option value="store" @selected(old('type') === 'store')>{{ __('Store') }}</option>
                                                    <option value="warehouse" @selected(old('type') === 'warehouse')>{{ __('Warehouse') }}</option>
                                                </select>
                                                @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Store Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}">
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="storekeeper_ids" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Storekeeper') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="storekeeper_ids" name="storekeeper_ids[]" class="form-select @error('storekeeper_ids') is-invalid @enderror @error('storekeeper_ids.*') is-invalid @enderror" data-choices data-choices-removeItem multiple required>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('storekeeper_ids', [])))>{{ ucwords($user->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('storekeeper_ids')<small class="text-danger">{{ $message }}</small>@enderror
                                                @error('storekeeper_ids.*')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="mobile" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Mobile') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}">
                                                @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="email" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Email') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="manager_ids" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Store Manager') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="manager_ids" name="manager_ids[]" class="form-select @error('manager_ids') is-invalid @enderror @error('manager_ids.*') is-invalid @enderror" data-choices data-choices-removeItem multiple>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" @selected(in_array($user->id, old('manager_ids', [])))>{{ ucwords($user->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('manager_ids')<small class="text-danger">{{ $message }}</small>@enderror
                                                @error('manager_ids.*')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6 text-center">
                                        <label class="form-label d-block">{{ __('Store Image') }} <small class="text-muted">({{ __('Optional') }})</small></label>
                                        <div class="store-image-wrapper">
                                            <img id="storeImagePreview" class="store-image-preview img-thumbnail d-none" alt="{{ __('Store Image') }}">
                                            <div id="storeImagePlaceholder" class="store-image-placeholder">
                                                <i class="ri-store-2-line"></i>
                                                <span>{{ __('No Image Selected') }}</span>
                                            </div>
                                            <label for="image" class="store-image-action-btn" title="{{ __('Upload Image') }}"><i class="ri-camera-fill"></i></label>
                                        </div>
                                        <input type="file" id="image" name="image" class="d-none @error('image') is-invalid @enderror" accept="image/*">
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

            $fileInput.on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $preview.attr('src', e.target.result).removeClass('d-none');
                        $placeholder.addClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush
