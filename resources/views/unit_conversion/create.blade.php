@extends('layout.master')

@section('title', __('Unit Conversion'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Unit Conversion Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('unit-conversion.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('unit-conversion.store') }}" method="POST">
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
                                            <label for="product_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Product') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="product_id" name="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Product ==') }}</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-unit-id="{{ $product->unit_id }}" @selected(old('product_id') == $product->id)>{{ $product->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('product_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Base Unit') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control" id="base_unit_display" readonly tabindex="-1" placeholder="{{ __('Select a Product First') }}">
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="unit_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Transaction Unit') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="unit_id" name="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Unit ==') }}</option>
                                                    @foreach($units as $unit)
                                                        <option value="{{ $unit->id }}" @selected(old('unit_id') == $unit->id)>{{ $unit->name }} @if($unit->symbol) ({{ $unit->symbol }}) @endif</option>
                                                    @endforeach
                                                </select>
                                                @error('unit_id')<small class="text-danger">{{ $message }}</small>@enderror
                                                <div class="form-text">{{ __('The Unit this Conversion applies to — must be different from the Base Unit above.') }}</div>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="factor_to_base" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Conversion Factor') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="number" step="0.000001" min="0.000001" class="form-control @error('factor_to_base') is-invalid @enderror" id="factor_to_base" name="factor_to_base" value="{{ old('factor_to_base') }}" required placeholder="{{ __('e.g. 50 (1 Transaction Unit = 50 Base Units)') }}">
                                                @error('factor_to_base')<small class="text-danger">{{ $message }}</small>@enderror
                                                <div class="form-text">{{ __('1 Transaction Unit = this many Base Units. Example: 1 Bag = 50 Kg -> enter 50.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('unit-conversion.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
            const units = @json($units->keyBy('id'));

            function syncBaseUnitDisplay() {
                const unitId = $('#product_id').find(':selected').data('unit-id');
                const unit = units[unitId];

                $('#base_unit_display').val(unit ? (unit.name + (unit.symbol ? ' (' + unit.symbol + ')' : '')) : '');
            }

            $('#product_id').on('change', syncBaseUnitDisplay);
            syncBaseUnitDisplay();
        });
    </script>
@endpush
