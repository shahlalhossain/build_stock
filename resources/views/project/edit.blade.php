@extends('layout.master')

@section('title', __('Project'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Project Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('project.edit', $project->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('project.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('project.update', $project->id) }}" method="POST" enctype="multipart/form-data">
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
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Project Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $project->name) }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="slug" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Slug') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug', $project->slug) }}" required>
                                                @error('slug')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="priority_order" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Priority Order') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('priority_order') is-invalid @enderror" id="priority_order" name="priority_order" value="{{ old('priority_order', $project->priority_order) }}">
                                                @error('priority_order')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description', $project->description) }}">
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="site_address" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Site Address') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('site_address') is-invalid @enderror" id="site_address" name="site_address" rows="2">{{ old('site_address', $project->site_address) }}</textarea>
                                                @error('site_address')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="start_date" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Start Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
                                                @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="expected_end_date" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Expected End Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('expected_end_date') is-invalid @enderror" id="expected_end_date" name="expected_end_date" value="{{ old('expected_end_date', $project->expected_end_date?->format('Y-m-d')) }}">
                                                @error('expected_end_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="estimated_budget" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Estimated Budget') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('estimated_budget') is-invalid @enderror" id="estimated_budget" name="estimated_budget" value="{{ old('estimated_budget', $project->estimated_budget) }}">
                                                @error('estimated_budget')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="project_manager_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Project Manager') }}</label>
                                            <div class="col-12 col-md-8">
                                                {{-- TODO: Populate from a searchable user select once a dedicated user-picker component exists in this app. --}}
                                                <input type="number" class="form-control @error('project_manager_id') is-invalid @enderror" id="project_manager_id" name="project_manager_id" value="{{ old('project_manager_id', $project->project_manager_id) }}" placeholder="{{ __('User ID') }}">
                                                @error('project_manager_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('project.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
