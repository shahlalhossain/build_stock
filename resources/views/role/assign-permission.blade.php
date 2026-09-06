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
            <!-- Start Role Details Row -->
            {{-- TODO: Have to change the Page Design & Layout --}}
            <div class="row">
                <div class="col-lg-12">
                    <form action="{{ route('role.update-permissions', $role) }}" method="POST">
                        @csrf
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">{{ __('Assign Permission to Role') }}</h4>
                                <div class="flex-shrink-0">
                                    <a href="{{ route('role.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                    <a href="{{ route('role.show', $role->id) }}" class="btn btn-sm btn-info"><i class="ri-arrow-left-line"></i><span class="d-none d-sm-inline"> {{ __('Go To Details') }}</span></a>
                                    <a href="{{ route('role.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                                </div>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm border-success-subtle table-hover table-responsive table-bordered">
                                    <tbody>
                                    <tr>
                                        <th class="text-end" style="width: 20%;">{{ __('Name') }}</th>
                                        <td class="text-center" style="width: 1%">:</td>
                                        <td class="text-start">
                                            {{ $role->name }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-end" style="width: 20%;">{{ __('Status') }}</th>
                                        <td class="text-center" style="width: 1%">:</td>
                                        <td class="text-start">
                                            @if($role->is_active == 1)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @elseif($role->is_active == 0)
                                                <span class="badge bg-warning">{{ __('Not Active') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-end" style="width: 20%;">{{ __('Description') }}</th>
                                        <td class="text-center" style="width: 1%">:</td>
                                        <td class="text-start">{{ ucwords($role->description) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-end" style="width: 20%;">{{ __('Created By') }}</th>
                                        <td class="text-center" style="width: 1%">:</td>
                                        <td class="text-start">{{ ucwords($role->creator?->name) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-end" style="width: 20%;">{{ __('Updated By') }}</th>
                                        <td class="text-center" style="width: 1%">:</td>
                                        <td class="text-start">{{ ucwords($role->updater?->name) }}</td>
                                    </tr>
                                    </tbody>
                                </table>

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

                                <div class="row g-3">
                                    <div class="col-sm-10">
                                        @if ($categorizedPermissions->count())
                                            <h5 class="mb-3">{{ __('Group Permissions') }}</h5>
                                            <table class="table table-responsive border">
                                                @foreach ($categorizedPermissions as $permission)
                                                    <tr>
                                                        <td style="width: 25%">
                                                            <div class="form-check form-switch form-switch-md form-switch-secondary form-check-inline" style="padding-left: 10px;">
                                                                <input type="checkbox" class="form-check-input parent-permission" id="group_{{ $permission->id }}" data-group="{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ collect($assignedPermissions)->contains($permission->id) ? 'checked' : '' }}>
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

                                    <div class="col-sm-2">
                                        @if ($unCategorizedPermissions->count())
                                            <h5 class="mb-3">{{ __('Nongroup Permissions') }}</h5>
                                            @foreach ($unCategorizedPermissions as $permission)
                                                <div class="form-check form-switch form-switch-info form-switch-md">
                                                    <input type="checkbox" class="form-check-input" id="{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ collect($assignedPermissions)->contains($permission->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="{{ $permission->id }}">{{ $permission->description ?? $permission->name }}</label>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('role.show', $role->id) }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Assign Permissions') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- End Role Details Row -->

        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->
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