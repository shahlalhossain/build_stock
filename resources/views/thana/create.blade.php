@extends('layout.master')

@section('title', __('Upazila/Thana'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Upazila/Thana Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('thana.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('thana.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="division_id" class="form-label">{{ __('Select Division') }}</label>
                                        <select id="division_id" name="division_id" class="form-select" required>
                                            <option value="" selected>{{ __('===== Select Division =====') }}</option>
                                            @foreach($divisions as $key => $division)
                                                <option value="{{ $division->id }}">{{ $division->name_en }} ({{ $division->name_bn }})</option>
                                            @endforeach
                                        </select>
                                        @error('division_id')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="district_id" class="form-label">{{ __('Select District') }}</label>
                                        <select id="district_id" name="district_id" class="form-select" required>
                                            <option value="" selected>{{ __('===== Select District =====') }}</option>
                                            @foreach($districts as $key => $district)
                                                <option value="{{ $district->id }}">{{ $district->name_en }} ({{ $district->name_bn }})</option>
                                            @endforeach
                                        </select>
                                        @error('district_id')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <br>
                                <div class="row g-3">
                                    <div class="col-sm-3">
                                        <label for="name_en" class="form-label">{{ __('Name') }}</label>
                                        <input type="text" class="form-control" id="name_en" name="name_en" value="{{ old('name_en') }}" required placeholder="Name">
                                        @error('name_en')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="name_bn" class="form-label">{{ __('Name (in Bangla)') }}</label>
                                        <input type="text" class="form-control" id="name_bn" name="name_bn" value="{{ old('name_bn') }}" placeholder="Name (in Bangla)">
                                        @error('name_bn')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="latitude" class="form-label">{{ __('Latitude') }}</label>
                                        <input type="text" class="form-control" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="Latitude">
                                        @error('latitude')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="longitude" class="form-label">{{ __('Longitude') }}</label>
                                        <input type="text" class="form-control" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="Longitude">
                                        @error('longitude')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <br>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="description_en" class="form-label">{{ __('Description') }}</label>
                                        <input type="text" class="form-control" id="description_en" name="description_en" value="{{ old('description_en') }}" placeholder="Description">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="description_bn" class="form-label">{{ __('Description (in Bangla)') }}</label>
                                        <input type="text" class="form-control" id="description_bn" name="description_bn" value="{{ old('description_bn') }}" placeholder="Description (in Bangla)">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('thana.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
            $('#division_id').on('change', function () {
                var divisionID = $(this).val();

                // Clear and Show Loading
                $('#district_id').html('<option value="">Loading...</option>');

                if (divisionID) {
                    $.ajax({
                        url: '{{ route("getDistrictsByDivision") }}',
                        type: 'GET',
                        data: { division_id: divisionID },
                        success: function (response) {
                            $('#district_id').empty().append('<option value="">===== Select District =====</option>');
                            $.each(response, function (key, district) {
                                $('#district_id').append('<option value="' + district.id + '">' + district.name_en + ' (' + district.name_bn + ')</option>');
                            });
                        }, error: function () {
                            $('#district_id').html('<option value="">Failed to Load Districts</option>');
                        }
                    });
                } else {
                    $('#district_id').html('<option value="">===== Select District =====</option>');
                }
            });
        });
    </script>
@endpush

