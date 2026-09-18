@extends('layout.master')

@section('title', __('Attribute'))

@push('styles')
    <style>
        @media (min-width: 768px) {
            .attribute-col-left {
                padding-right: 24px;
            }

            .border-start-md {
                border-left: 1px solid var(--vz-border-color, #dee2e6);
                padding-left: 24px;
            }
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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Attribute Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('attribute.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('attribute.store') }}" method="POST">
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
                                    <!-- Start Left Column: Name + Description -->
                                    <div class="col-12 col-md-7 order-1 attribute-col-left">
                                        <div class="row mb-2">
                                            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Attribute Name') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="">
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}" placeholder="">
                                                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <hr class="d-md-none order-2 mx-auto my-3" style="width: 90%">

                                    <!-- Start Right Column: Attribute Values -->
                                    <div class="col-12 col-md-3 order-3 border-start-md">
                                        <div class="row mb-2 align-items-center">
                                            <div class="col-10">
                                                <h6 class="mb-0 fw-bold fst-italic">{{ __('Attribute Values') }}</h6>
                                            </div>
                                            <div class="col-2 text-start">
                                                <button type="button" class="btn btn-sm btn-success add-row" data-group="values"><i class="ri-add-line"></i></button>
                                            </div>
                                        </div>
                                        @error('values')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                        <div id="values-rows"></div>

                                        <template id="values-row-template">
                                            <div class="row mb-2 align-items-start repeater-row" data-group="values">
                                                <div class="col-10">
                                                    <input type="text" class="form-control" name="values[]" placeholder="{{ __('Value') }}">
                                                </div>
                                                <div class="col-2 text-start pt-1">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="ri-close-line"></i></button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <!-- End Right Column -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('attribute.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
            function addRow(group) {
                const template = document.getElementById(group + '-row-template').innerHTML;
                $('#' + group + '-rows').append(template);
            }

            $(document).on('click', '.add-row', function () {
                addRow($(this).data('group'));
            });

            $(document).on('click', '.remove-row', function () {
                $(this).closest('.repeater-row').remove();
            });

            // Seed with one starter row.
            addRow('values');
        });
    </script>
@endpush
