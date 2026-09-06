@extends('layout.master')

@section('title', __('Assign Roles'))

@push('styles')
    <style>
        .success-toast {
            background: linear-gradient(135deg, #00c9a7, #1f3c88);
            color: #fff;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .failed-toast {
            background: linear-gradient(135deg, #ff922b, #e8590c);
            color: #fff;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }
    </style>
@endpush

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Role Details Row -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Assign Roles') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('user.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('user.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('user.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        @php $selectedRoles = old('roles', $assignedRoles); @endphp

                        <form action="{{ route('user.assign-roles', $user->id) }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 table-responsive-sm">
                                        <table class="table table-sm table-bordered table-active">
                                            <tbody>
                                            <tr>
                                                <th class="text-end" style="width: 20%;">Name</th>
                                                <td class="text-center" style="width: 1%;">:</td>
                                                <td class="text-start" style="width: 79%;">{{ $user->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="text-end" style="width: 20%;">Mobile</th>
                                                <td class="text-center" style="width: 1%;">:</td>
                                                <td class="text-start" style="width: 79%;">
                                                    {{ $user->mobile }}
                                                    @if($user->is_mobile_verified == 1)
                                                        <span class="text-success ms-1"><i class="ri-checkbox-circle-fill"></i></span>
                                                    @elseif($user->is_mobile_verified == 0)
                                                        <span class="text-danger ms-1"><i class="ri-close-circle-fill"></i></span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="text-end" style="width: 20%;">Email</th>
                                                <td class="text-center" style="width: 1%;">:</td>
                                                <td class="text-start" style="width: 79%;">
                                                    {{ $user->email }}
                                                    @if($user->is_email_verified == 1)
                                                        <span class="text-success ms-1"><i class="ri-checkbox-circle-fill"></i></span>
                                                    @elseif($user->is_email_verified == 0)
                                                        <span class="text-danger ms-1"><i class="ri-close-circle-fill"></i></span>
                                                    @endif
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between align-items-center mb-1 gap-2">
                                            <!-- Left -->
                                            <p class="fs-16 text-dark mb-0">
                                                {{ __('Roles & Permissions') }}
                                            </p>

                                            <!-- Right -->
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('user.show', $user->id) }}" class="btn btn-sm btn-soft-info">
                                                    <i class="ri-arrow-left-up-fill"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Back User Details') }}</span>
                                                </a>

                                                <a href="{{ route('user.assign-permissions', $user->id) }}" class="btn btn-sm btn-soft-primary">
                                                    <i class="ri-settings-3-line"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Assign Permissions') }}</span>
                                                </a>
                                            </div>
                                        </div>
                                        @foreach ($roles as $role)
                                            <div class="card border mb-1">
                                                <div class="card-body p-3">
                                                    <div class="row align-items-start">
                                                        <!-- Role Column -->
                                                        <div class="col-12 col-lg-3 mb-3 mb-lg-0">
                                                            <div class="form-check form-switch form-switch-md form-switch-secondary">
                                                                <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" {{ in_array($role->name, $selectedRoles) ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-semibold text-dark">{{ $role->name }}</label>
                                                            </div>
                                                        </div>
                                                        <!-- Permissions Column -->
                                                        <div class="col-12 col-lg-9 ps-4 ps-lg-0">
                                                            <div class="row">
                                                                @foreach ($role->permissions as $permission)
                                                                    <div class="col-12 col-lg-3 mb-2">
                                                                        <span class="badge badge-label badge-gradient-primary text-start">
                                                                            <i class="mdi mdi-circle-medium"></i>{{ ucwords($permission->description) }}
                                                                        </span>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('user.show', $user->id) }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Update & Sync Roles') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- End Role Details Row -->

        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->
@endsection

@push('scripts')
    @if (session('error'))
        <script>
            Toastify({
                text: @json(session('error')),
                duration: 4000,
                gravity: "top",
                position: "right",
                close: true,
                className: "failed-toast",
                stopOnFocus: true
            }).showToast();
        </script>
    @endif
@endpush
