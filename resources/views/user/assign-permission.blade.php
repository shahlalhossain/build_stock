@extends('layout.master')

@section('title', __('Assign Permissions'))

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
                        @php $selectedPermissions = old('permissions', $userPermissions); @endphp

                        <form action="{{ route('user.assign-permissions', $user->id) }}" method="POST">
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
                                        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-success gap-2">
                                            <!-- Left -->
                                            <p class="fs-16 text-dark mb-0">{{ __('Group & Non-Group Permissions') }}</p>
                                            <!-- Right -->
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('user.show', $user->id) }}" class="btn btn-sm btn-soft-info">
                                                    <i class="ri-arrow-left-up-fill"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Back User Details') }}</span>
                                                </a>
                                                <a href="{{ route('user.assign-roles', $user->id) }}" class="btn btn-sm btn-soft-primary">
                                                    <i class="ri-settings-3-line"></i>
                                                    <span class="d-none d-sm-inline"> {{ __('Assign Roles') }}</span>
                                                </a>
                                            </div>
                                        </div>

                                        <div class="row">
                                            {{-- Group Permissions --}}
                                            <div class="col-sm-9">
                                                <p class="fs-16 text-dark mb-2 fst-italic">{{ __('Group Permissions') }}</p>
                                                @foreach ($groups as $parentPermission)
                                                    <div class="permission-group mb-3 p-3 border rounded shadow-lg">
                                                        <!-- Parent Permission -->
                                                        <div class="form-check form-switch form-switch-md parent-permission mb-3">
                                                            <input type="checkbox" name="permissions[]" class="form-check-input parent-group" value="{{ $parentPermission->name }}" {{ in_array($parentPermission->name, $selectedPermissions) ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-semibold">{{ $parentPermission->description }}</label>
                                                        </div>
                                                        <!-- Child Permissions -->
                                                        <div class="row child-container ms-md-4 ms-3">
                                                            @foreach ($parentPermission->children as $childPermission)
                                                                <div class="col-12 col-md-4 mb-1">
                                                                    <div class="form-check form-switch form-switch-md child-permission">
                                                                        <input type="checkbox" class="form-check-input child-group" name="permissions[]" value="{{ $childPermission->name }}" {{ in_array($childPermission->name, $selectedPermissions) ? 'checked' : '' }}>
                                                                        <label class="form-check-label">{{ $childPermission->description }}</label>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            {{-- Non-Group Permissions --}}
                                            <div class="col-sm-3">
                                                <p class="fs-16 text-dark mb-2 fst-italic">{{ __('Non-Group Permissions') }}</p>
                                                @foreach ($nongroups as $permission)
                                                    <div class="form-check form-switch form-switch-info form-switch-md">
                                                        <input type="checkbox" class="form-check-input non-group-permission" name="permissions[]" value="{{ $permission->name }}" {{ in_array($permission->name, $selectedPermissions) ? 'checked' : '' }}>
                                                        <label class="form-check-label">{{ $permission->description }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('user.show', $user->id) }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Sync Permissions') }}</button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            document.querySelectorAll('.permission-group').forEach(group => {
                const parent = group.querySelector('.parent-group');
                const children = group.querySelectorAll('.child-group');

                // ---- INITIAL LOAD CHECK ----
                const anyChildChecked = Array.from(children).some(child => child.checked);
                if (anyChildChecked) {
                    parent.checked = true;
                }

                // ---- PARENT → CHILDREN ----
                parent.addEventListener('change', function () {
                    children.forEach(child => {
                        child.checked = parent.checked;
                    });
                });

                // ---- CHILDREN → PARENT ----
                children.forEach(child => {
                    child.addEventListener('change', function () {
                        const anyChecked = Array.from(children).some(c => c.checked);
                        parent.checked = anyChecked;
                    });
                });
            });

        });
    </script>
@endpush
