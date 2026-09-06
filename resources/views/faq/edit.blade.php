@extends('layout.master')

@section('title', __('FAQ'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('FAQ Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('faq.edit', $faq->id) }}" class="btn btn-sm btn-warning"><i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span></a>
                                <a href="{{ route('faq.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>

                        <form action="{{ route('faq.update', $faq->id) }}" method="POST">
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
                                    <div class="col-12 col-md-4">

                                        <div class="row mb-2">
                                            <label for="faq_category_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Select Category') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="faq_category_id" name="faq_category_id" class="form-select" required>
                                                    <option value="">{{ __('=== Select Category ===') }}</option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" {{ old('faq_category_id', $faq->faq_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('faq_category_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="language" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Select Language') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="language" name="language" class="form-select" required>
                                                    <option value="">{{ __('=== Select Language ===') }}</option>
                                                    <option value="English" {{ old('language', $faq->language) == 'English' ? 'selected' : '' }}>{{ __('English') }}</option>
                                                    <option value="Bangla" {{ old('language', $faq->language) == 'Bangla' ? 'selected' : '' }}>{{ __('Bangla') }}</option>
                                                </select>
                                                @error('language')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-8">

                                        <div class="row mb-2">
                                            <label for="question" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Question') }}</label>
                                            <div class="col-12 col-md-10">
                                                <input type="text" class="form-control @error('question') is-invalid @enderror" id="question" name="question" value="{{ old('question', $faq->question) }}" required placeholder="{{ __('Question') }}">
                                                @error('question')<small class="invalid-feedback text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="answer" class="col-12 col-md-2 col-form-label text-md-end text-start form-mandatory">{{ __('Answer') }}</label>
                                            <div class="col-12 col-md-10">
                                                <input type="text" class="form-control @error('answer') is-invalid @enderror" id="answer" name="answer" value="{{ old('answer', $faq->answer) }}" required placeholder="{{ __('Answer') }}">
                                                @error('answer')<small class="invalid-feedback text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                    </div>
                                    <!-- End Right Column -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('faq.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
