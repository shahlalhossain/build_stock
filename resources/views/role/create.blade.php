@extends('layout.master')

@section('title', __('Role'))

@push('styles')
    <style>

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
                    <div class="card shadow-lg">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Role Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('role.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>

                        <form action="{{ route('role.store') }}" method="POST" enctype="multipart/form-data">
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

                                {{-- TODO: Have to change the Page Design & Layout --}}
                                <div class="row g-3">
                                    <div class="col-sm-2">
                                        <label for="type" class="form-label form-mandatory">{{ __('Type') }}</label>
                                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                            <option value="admin" {{ old('type') === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
                                            <option value="member" {{ old('type') === 'member' ? 'selected' : '' }}>{{ __('Member') }}</option>
                                        </select>
                                        @error('type')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="guard" class="form-label form-mandatory">{{ __('Guard') }}</label>
                                        <select class="form-select @error('guard_name') is-invalid @enderror" id="guard" name="guard_name" required>
                                            <option value="web" {{ old('guard_name') === 'web' ? 'selected' : '' }}>{{ __('Web') }}</option>
                                            <option value="api" {{ old('guard_name') === 'api' ? 'selected' : '' }}>{{ __('API') }}</option>
                                        </select>
                                        @error('guard_name')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="name" class="form-label form-mandatory">{{ __('Role Name') }}</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="User Manager">
                                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                    <div class="col-sm-5">
                                        <label for="description" class="form-label">{{ __('Description') }}</label>
                                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}" required placeholder="Manage Users">
                                        @error('description')<small class="text-danger">{{ $message }}</small>@enderror
                                    </div>
                                </div>
                                <br>

                                <div class="accordion custom-accordionwithicon-plus accordion-border-box accordion-success" id="accordionBordered">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="accordionForPermissions">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#borderedAccordionForPermissions" aria-expanded="false" aria-controls="borderedAccordionForPermissions">
                                                <span class="fw-bold">Want to Assign Permissions?</span>
                                            </button>
                                        </h2>
                                        <div id="borderedAccordionForPermissions" class="accordion-collapse collapse" aria-labelledby="accordionForPermissions" data-bs-parent="#accordionBordered">
                                            <div class="accordion-body">
                                                <div class="row g-3">
                                                    <div class="col-sm-10 order-1">
                                                        <h6 class="mb-2"><i><strong>{{ __('Group Permissions') }}</strong></i></h6>
                                                        @if ($categorizedPermissions->count())
                                                            <table class="table table-responsive">
                                                                @foreach ($categorizedPermissions as $permission)
                                                                    <tr>
                                                                        <td style="width: 25%">
                                                                            <div class="form-check form-switch form-switch-md form-switch-secondary form-check-inline">
                                                                                <input type="checkbox" class="form-check-input parent-permission" id="group_{{ $permission->id }}" data-group="{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, $assignedPermissions ?? []) ? 'checked' : '' }}>
                                                                                <label class="form-check-label" for="group_{{ $permission->id }}">{{ $permission->description ?? $permission->name }}</label>
                                                                            </div>
                                                                        </td>
                                                                        <td style="width: 75%;">
                                                                            @if ($permission->children->count())
                                                                                <div class="row child-container" data-group="{{ $permission->id }}">
                                                                                    @include('role.includes.child-permission', ['children' => $permission->children])
                                                                                </div>
                                                                            @endif
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        @endif
                                                    </div>

                                                    <div class="col-sm-2 order-2">
                                                        <h6 class="mb-2"><i><strong>{{ __('Nongroup Permissions') }}</strong></i></h6>
                                                        @if ($unCategorizedPermissions->count())
                                                            <table class="table table-responsive">
                                                                <tr>
                                                                    <td>
                                                                        @foreach ($unCategorizedPermissions as $permission)
                                                                            <div class="form-check form-switch form-switch-md form-switch-info pb-1">
                                                                                <input type="checkbox" class="form-check-input" id="{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}">
                                                                                <label class="form-check-label" for="{{ $permission->id }}">{{ $permission->description ?? $permission->name }}</label>
                                                                            </div>
                                                                        @endforeach
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('role.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
            $(document).on('change', '.parent-permission', function() {
                const groupId = $(this).data('group');
                const isChecked = $(this).is(':checked');
                $(`[data-group="${groupId}"] input.child-permission`).prop('checked', isChecked);
            });
        });
    </script>
@endpush