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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Project Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('project.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('project.store') }}" method="POST" enctype="multipart/form-data">
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
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Project Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="1">{{ old('description') }}</textarea>
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="project_manager_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Project Manager') }}</label>
                                            <div class="col-12 col-md-8">
                                                {{-- TODO: Populate from a searchable user select once a dedicated user-picker component exists in this app. --}}
                                                <input type="number" class="form-control @error('project_manager_id') is-invalid @enderror" id="project_manager_id" name="project_manager_id" value="{{ old('project_manager_id') }}" placeholder="{{ __('User ID') }}">
                                                @error('project_manager_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="current_state" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Current State') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="current_state" name="current_state" class="form-select @error('current_state') is-invalid @enderror" required>
                                                    @foreach(\App\Models\Project::CURRENT_STATES as $state)
                                                        <option value="{{ $state }}" @selected(old('current_state', 'proposed') === $state)>{{ ucwords($state) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('current_state')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="start_date" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Start Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}">
                                                @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="expected_end_date" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Expected End Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('expected_end_date') is-invalid @enderror" id="expected_end_date" name="expected_end_date" value="{{ old('expected_end_date') }}">
                                                @error('expected_end_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="estimated_budget" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Estimated Budget') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('estimated_budget') is-invalid @enderror" id="estimated_budget" name="estimated_budget" value="{{ old('estimated_budget') }}">
                                                @error('estimated_budget')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <h6 class="mb-0 fw-bold fst-italic">{{ __('Site Address') }}</h6>
                                <div class="row">
                                    <div class="col-12 col-md-6">

                                        <div class="row mb-2">
                                            <label for="division_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Division') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="division_id" name="division_id" class="form-select @error('division_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Division ==') }}</option>
                                                    @foreach($divisions as $division)
                                                        <option value="{{ $division->id }}" @selected(old('division_id') == $division->id)>{{ $division->name_en }} ({{ $division->name_bn }})</option>
                                                    @endforeach
                                                </select>
                                                @error('division_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="district_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('District') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select District ==') }}</option>
                                                </select>
                                                @error('district_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="thana_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Thana/Upazila') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="thana_id" name="thana_id" class="form-select @error('thana_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Thana/Upazila ==') }}</option>
                                                </select>
                                                @error('thana_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="address" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Address') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="1">{{ old('address') }}</textarea>
                                                @error('address')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="landmark" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Landmark') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('landmark') is-invalid @enderror" id="landmark" name="landmark">{{ old('landmark') }}</input>
                                                @error('landmark')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="latitude" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Latitude') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}">
                                                @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="longitude" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Longitude') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}">
                                                @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="map_address" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Map Address') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('map_address') is-invalid @enderror" id="map_address" name="map_address" value="{{ old('map_address') }}">
                                                @error('map_address')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                    {{-- TODO: OpenStreet Map will be Shown Here --}}
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('project.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
        $(document).ready(function () {
            /*
            |--------------------------------------------------------------------------
            | CASCADING DIVISION -> DISTRICT -> THANA SELECTS
            |--------------------------------------------------------------------------
            */
            $('#division_id').on('change', function () {
                const divisionId = $(this).val();
                const $district = $('#district_id');
                const $thana = $('#thana_id');

                $district.html('<option value="">{{ __("===== Select District =====") }}</option>');
                $thana.html('<option value="">{{ __("===== Select Thana/Upazila =====") }}</option>');

                if (!divisionId) {
                    return;
                }

                $.get('{{ route('getDistrictsByDivision') }}', { division_id: divisionId }, function (districts) {
                    districts.forEach(function (district) {
                        $district.append('<option value="' + district.id + '">' + district.name_en + ' (' + district.name_bn + ')</option>');
                    });
                });
            });

            $('#district_id').on('change', function () {
                const districtId = $(this).val();
                const $thana = $('#thana_id');

                $thana.html('<option value="">{{ __("===== Select Thana/Upazila =====") }}</option>');

                if (!districtId) {
                    return;
                }

                $.get('{{ route('getThanasByDistrict') }}', { district_id: districtId }, function (thanas) {
                    thanas.forEach(function (thana) {
                        $thana.append('<option value="' + thana.id + '">' + thana.name_en + ' (' + thana.name_bn + ')</option>');
                    });
                });
            });
        });
    </script>
@endpush
