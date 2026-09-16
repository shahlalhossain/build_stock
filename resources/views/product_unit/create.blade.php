@extends('layout.master')

@section('title', __('Product-Unit'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Product-Unit Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-unit.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('product-unit.store') }}" method="POST" enctype="multipart/form-data">
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
                                    <div class="col-12 col-md-7">
                                        <div class="row mb-2">
                                            <label for="group" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Group') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="group" name="group" class="form-select @error('group') is-invalid @enderror" required>
                                                    <option value="" selected>{{ __('===== Select Group =====') }}</option>
                                                    @foreach(\App\Models\ProductUnit::GROUPS as $group)
                                                        <option value="{{ $group }}" @selected(old('group') == $group)>{{ __($group) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('group')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Product-Unit Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="">
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="symbol" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Symbol') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('symbol') is-invalid @enderror" id="symbol" name="symbol" value="{{ old('symbol') }}" required placeholder="">
                                                @error('symbol')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}" placeholder="">
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="usage" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Usage') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('usage') is-invalid @enderror" id="usage" name="usage" value="{{ old('usage') }}" placeholder="">
                                                @error('usage')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->


                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('product-unit.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
