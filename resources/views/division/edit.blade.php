@extends('layout.master')

@section('title', __('Division'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Division Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('division.edit', $division->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('division.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('division.update', $division) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-sm-3">
                                        <label for="name_en" class="form-label">{{ __('Name') }}</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en', $division->name_en) }}" required placeholder="Name">
                                        @error('name_en')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="name_bn" class="form-label">{{ __('Name (in Bangla)') }}</label>
                                        <input type="text" class="form-control" id="name_bn" name="name_bn" value="{{ old('name_bn', $division->name_bn) }}" required placeholder="Name (in Bangla)">
                                        @error('name_bn')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="latitude" class="form-label">{{ __('Latitude') }}</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude', $division->latitude) }}" required placeholder="Latitude">
                                        @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="longitude" class="form-label">{{ __('Longitude') }}</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude', $division->longitude) }}" required placeholder="Longitude">
                                        @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="description_en" class="form-label">{{ __('Description') }}</label>
                                        <input type="text" class="form-control" id="description_en" name="description_en" value="{{ old('description_en', $division->description_en) }}" placeholder="Description">
                                        @error('description_en')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="description_bn" class="form-label">{{ __('Description (in Bangla)') }}</label>
                                        <input type="text" class="form-control" id="description_bn" name="description_bn" value="{{ old('description_bn', $division->description_bn) }}" placeholder="Description (in Bangla)">
                                        @error('description_bn')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('division.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
