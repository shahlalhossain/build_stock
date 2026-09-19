@extends('layout.master')

@section('title', __('Store'))

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

                        <form action="{{ route('store.update', $store->id) }}" method="POST">
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
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="storekeeper_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Storekeeper') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="storekeeper_id" name="storekeeper_id" class="form-select @error('storekeeper_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Storekeeper ==') }}</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" @selected(old('storekeeper_id', $store->storekeeper_id) == $user->id)>{{ ucwords($user->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('storekeeper_id')<small class="text-danger">{{ $message }}</small>@enderror
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
                                            <label for="manager_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Store Manager') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="manager_id" name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
                                                    <option value="">{{ __('== Select Manager ==') }}</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}" @selected(old('manager_id', $store->manager_id) == $user->id)>{{ ucwords($user->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('manager_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
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

@endpush
