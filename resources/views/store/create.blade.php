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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Store Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('store.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('store.store') }}" method="POST">
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
                                    <div class="col-12 col-md-7">
                                        <div class="row mb-2">
                                            <label for="project_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Head Office / Project') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror">
                                                    <option value="" @selected(old('project_id') === null)>{{ __('Head Office') }}</option>
                                                    @foreach($projects as $proj)
                                                        <option value="{{ $proj->id }}" @selected(old('project_id') == $proj->id)>{{ ucwords($proj->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('project_id')<small class="text-danger">{{ $message }}</small>@enderror
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
                                            <label for="code" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Store Code') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" required>
                                                @error('code')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="type" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Type') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="type" name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                    <option value="" disabled @selected(old('type') === null)>{{ __('Select Type') }}</option>
                                                    <option value="store" @selected(old('type') === 'store')>{{ __('Store') }}</option>
                                                    <option value="warehouse" @selected(old('type') === 'warehouse')>{{ __('Warehouse') }}</option>
                                                </select>
                                                @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
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
