@extends('layout.master')

@section('title', __('User'))

@push('styles')
    <style>
        .user-profile-image {
            width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: 50%;
        }
        .user-profile-image.img-thumbnail {
            padding: 4px;
            border-width: 1px;
        }
        .avatar-custom .avatar-title {
            width: 100%;
            height: 100%;
            font-size: 18px;
        }
        .profile-user {
            position: relative;
        }

        .profile-actions {
            position: absolute;
            bottom: 10px;
            right: 10px;
        }
        .avatar-action-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .avatar-action-btn:hover {
            transform: scale(1.1);
            filter: brightness(0.95);
        }
        .avatar-action-btn:active {
            transform: scale(0.95);
        }
        .avatar-action-btn i {
            pointer-events: none;
        }
        /* Make Label Behave Like Button */
        label.avatar-action-btn {
            cursor: pointer;
        }

    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    {{-- Start Card --}}
                    <div class="card">
                        {{-- Start Card Header --}}
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('User Edit') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i>
                                    <span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('user.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i>
                                    <span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>
                        {{-- End Card Header --}}

                        {{-- Start Form --}}
                        <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            {{-- Start Card Body --}}
                            <div class="card-body">

                                {{-- Start Validation Errors --}}
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger alert-dismissible fade show">
                                            {{ $error }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    @endforeach
                                @endif
                                {{-- End Validation Errors --}}

                                <div class="row">
                                    {{-- Start Left Column --}}
                                    <div class="col-12 col-md-9">

                                        {{-- Start User Type Input --}}
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-2 col-form-label text-md-end form-mandatory">
                                                {{ __('User Type') }}
                                            </label>
                                            <div class="col-12 col-md-6">
                                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                                    <option value="admin" {{ $user->type == 'admin' ? 'selected' : '' }}>Admin</option>
                                                    <option value="employee" {{ $user->type == 'employee' ? 'selected' : '' }}>Employee</option>
                                                    <option value="user" {{ $user->type == 'user' ? 'selected' : '' }}>User</option>
                                                    <option value="member" {{ $user->type == 'member' ? 'selected' : '' }}>Member</option>
                                                </select>
                                                @error('type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        {{-- End User Type Input --}}

                                        {{-- Start Name Input --}}
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-2 col-form-label text-md-end form-mandatory">{{ __('Name') }}</label>
                                            <div class="col-12 col-md-6">
                                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        {{-- End Name Input --}}

                                        {{-- Start Mobile Input --}}
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-2 col-form-label text-md-end form-mandatory">{{ __('Mobile Number') }}</label>
                                            <div class="col-12 col-md-6">
                                                <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror" value="{{ old('mobile', $user->mobile) }}" required>
                                                @error('mobile')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        {{-- End Mobile Input --}}

                                        {{-- Start Email Input --}}
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-2 col-form-label text-md-end form-mandatory">{{ __('Email Address') }}</label>
                                            <div class="col-12 col-md-6">
                                                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                                @error('email')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                        {{-- End Email Input --}}
                                    </div>
                                    {{-- End Left Column --}}

                                    {{-- Start Right Column --}}
                                    <div class="col-12 col-md-3 text-center">
                                        @error('profile_picture')<small class="text-danger">{{ $message }}</small>@enderror
                                        <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                            <img id="profileImagePreview" src="{{ $user->profile_picture ? asset('storage/'.$user->profile_picture) : asset('assets/images/avatar_3.png') }}" class="rounded-circle avatar-xxl img-thumbnail user-profile-image" alt="Profile Photo">
                                            <!-- Actions -->
                                            <div class="profile-actions d-flex gap-2">
                                                <!-- Upload -->
                                                <label for="profile-img-file-input" class="avatar-action-btn bg-light text-body" title="Upload Photo"><i class="ri-camera-fill"></i></label>
                                                <!-- Remove -->
                                                @if($user->profile_picture)
                                                    <button type="button" id="removeProfilePictureBtn" class="avatar-action-btn bg-danger text-white" title="Remove photo"><i class="ri-delete-bin-5-line"></i></button>
                                                @endif
                                            </div>
                                            <input type="file" id="profile-img-file-input" name="profile_picture" class="d-none" accept="image/*">
                                            <input type="hidden" id="remove_profile_picture" name="remove_profile_picture" value="0">
                                        </div>
                                    </div>
                                    {{-- End Right Column --}}
                                </div>
                            </div>
                            {{-- End Card Body --}}

                            {{-- Start Card Footer --}}
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('user.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                            {{-- End Card Footer --}}
                        </form>
                        {{-- End Form --}}
                    </div>
                    {{-- End Card --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function () {
            const $fileInput    = $('#profile-img-file-input');
            const $previewImg   = $('#profileImagePreview');
            const $removeBtn    = $('#removeProfilePictureBtn');
            const $removeFlag   = $('#remove_profile_picture');
            const defaultAvatar = "{{ asset('assets/images/avatar_3.png') }}";

            // Preview New Image
            $fileInput.on('change', function () {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        $previewImg.attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                    // Upload Overrides Delete
                    $removeFlag.val(0);
                    // Show Remove Button Again (If Previously Hidden)
                    $removeBtn.show();
                }
            });

            // Remove Profile Picture (UI Only)
            $removeBtn.on('click', function () {
                $previewImg.attr('src', defaultAvatar);
                $fileInput.val('');      // Clear File Input
                $removeFlag.val(1);      // Mark for Deletion
                $(this).hide();          // Hide Remove Button
            });

        });
    </script>
@endpush