@extends('layout.master')

@section('title', __('District'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('District Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('district.edit', $district->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('district.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('district.update', $district) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12 col-md">
                                        <label for="division_id" class="form-label">{{ __('Select Division') }}</label>
                                        <select id="division_id" name="division_id" class="form-select" required>
                                            <option value="">{{ __('===== Select Division =====') }}</option>
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->id }}" {{ old('division_id', $district->division_id) == $division->id ? 'selected' : '' }}>
                                                    {{ $division->name_en }} ({{ $division->name_bn }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('division_id')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-12 col-md">
                                        <label for="name_en" class="form-label">{{ __('Name') }}</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en', $district->name_en) }}" required placeholder="Name">
                                        @error('name_en')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-12 col-md">
                                        <label for="name_bn" class="form-label">{{ __('Name (in Bangla)') }}</label>
                                        <input type="text" class="form-control" id="name_bn" name="name_bn" value="{{ old('name_bn', $district->name_bn) }}" required placeholder="Name (in Bangla)">
                                        @error('name_bn')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-12 col-md">
                                        <label for="latitude" class="form-label">{{ __('Latitude') }}</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $district->latitude) }}" required placeholder="Latitude">
                                        @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-12 col-md">
                                        <label for="longitude" class="form-label">{{ __('Longitude') }}</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $district->longitude) }}" required placeholder="Longitude">
                                        @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <br>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="description_en" class="form-label">{{ __('Description') }}</label>
                                        <input type="text" class="form-control" id="description_en" name="description_en" value="{{ old('description_en', $district->description_en) }}" placeholder="Description">
                                        @error('description_en')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="description_bn" class="form-label">{{ __('Description (in Bangla)') }}</label>
                                        <input type="text" class="form-control" id="description_bn" name="description_bn" value="{{ old('description_bn', $district->description_bn) }}" placeholder="Description (in Bangla)">
                                        @error('description_bn')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('district.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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

@endpush
