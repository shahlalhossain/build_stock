@extends('layout.master')

@section('title', __('Brand'))

@push('styles')
    <style>
        .vertical-divider {
            width: 1px;
            background-color: #dee2e6;
            height: 90%;
            margin: auto;
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Brand Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('permission.edit', $permission->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('permission.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('permission.update', $permission) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')

                            <div class="card-body">

                                <div class="row">
                                    <div class="col-12">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label for="type" class="form-label form-mandatory">{{ __('Type') }}</label>
                                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                                    <option value="admin" {{ old('type', $permission->type) == 'admin' ? 'selected' : '' }} >{{ __('Admin') }}</option>
                                                    <option value="user" {{ old('type', $permission->type) == 'users' ? 'selected' : '' }}>{{ __('User') }}</option>
                                                    <option value="employee" {{ old('type', $permission->type) == 'employee' ? 'selected' : '' }}>{{ __('Employee') }}</option>
                                                    <option value="member" {{ old('type', $permission->type) == 'member' ? 'selected' : '' }}>{{ __('Member') }}</option>
                                                </select>
                                                @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="guard" class="form-label form-mandatory">Guard</label>
                                                <select class="form-select @error('guard_name') is-invalid @enderror" id="guard" name="guard_name" required>
                                                    <option value="web" {{ old('guard_name', $permission->type) == 'web' ? 'selected' : '' }}>{{ __('Web') }}</option>
                                                    <option value="api" {{ old('guard_name', $permission->type) == 'api' ? 'selected' : '' }}>{{ __('API') }}</option>
                                                </select>
                                                @error('guard_name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="parent" class="form-label">{{ __('Parent Brand') }}</label>
                                                <select class="form-select @error('parent_id') is-invalid @enderror" id="parent" name="parent_id">
                                                    <option value="">{{ __('Choose...') }}</option>
                                                    @foreach($parentBrands as $parentBrand)
                                                        <option value="{{ $parentBrand->id }}"{{ old('parent_id', $permission->parent_id) == $parentBrand->id ? 'selected' : '' }}> {{ $parentBrand->description }}</option>
                                                    @endforeach
                                                </select>
                                                @error('parent_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label for="name" class="form-label form-mandatory">{{ __('Brand Name') }}</label>
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $permission->name) }}" required placeholder="user.index">
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-md-8">
                                                <label for="description" class="form-label form-mandatory">{{ __('Description') }}</label>
                                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $permission->description) }}" required placeholder="User List">
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('permission.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
