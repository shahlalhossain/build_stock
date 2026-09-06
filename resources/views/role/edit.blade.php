@extends('layout.master')

@section('title', __('Role'))

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            {{-- TODO: Have to change the Page Design & Layout --}}
            <!-- Start Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-lg">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Role Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('role.edit', $role->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('role.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>

                        <form action="{{ route('role.update', $role->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="card-body">

                                <div class="row g-3">
                                    <div class="col-sm-2">
                                        <label for="type" class="form-label">{{ __('Type') }}</label>
                                        <select class="form-select" id="type" name="type" required>
                                            <option value="admin" selected>{{ __('Admin') }}</option>
                                            <option value="member">{{ __('Member') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <label for="guard" class="form-label">{{ __('Guard') }}</label>
                                        <select class="form-select" id="guard" name="guard_name" required>
                                            <option value="web" selected>{{ __('Web') }}</option>
                                            <option value="api">{{ __('API') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-3">
                                        <label for="name" class="form-label">{{ __('Role Name') }}</label>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $role->name) }}" required placeholder="User Manager">
                                    </div>
                                    <div class="col-sm-5">
                                        <label for="description" class="form-label">{{ __('Description') }}</label>
                                        <input type="text" class="form-control" id="description" name="description" value="{{ old('description', $role->description) }}" required placeholder="Manage Users">
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
                                                                                <input type="checkbox" class="form-check-input" id="{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ collect($assignedPermissions)->contains($permission->id) ? 'checked' : '' }}>
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