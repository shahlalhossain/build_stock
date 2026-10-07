{{-- Shared by create and edit. Needs: $channels, $users, $roles, $permissions, $selected* lists and optional $notificationSetting. --}}
@php
    $chosenChannelIds = array_map('intval', old('channels', $selectedChannelIds));
    $chosenUserIds = array_map('strval', old('receiver_users', $selectedUserIds));
    $chosenRoleNames = old('receiver_roles', $selectedRoleNames);
    $chosenPermissionNames = old('receiver_permissions', $selectedPermissionNames);
@endphp

<div class="row">
    <!-- Start Left Column -->
    <div class="col-12 col-md-7">
        <div class="row mb-2">
            <label for="name" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Setting Name') }}</label>
            <div class="col-12 col-md-8">
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $notificationSetting->name ?? '') }}" required maxlength="150">
                @error('name')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="row mb-2">
            <label for="event_code" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Event Code') }}</label>
            <div class="col-12 col-md-8">
                <input type="text" class="form-control @error('event_code') is-invalid @enderror" id="event_code" name="event_code" value="{{ old('event_code', $notificationSetting->event_code ?? '') }}" required maxlength="100" placeholder="brand.created">
                @error('event_code')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="row mb-2">
            <label for="permission_name" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Permission Name') }}</label>
            <div class="col-12 col-md-8">
                <input type="text" class="form-control @error('permission_name') is-invalid @enderror" id="permission_name" name="permission_name" value="{{ old('permission_name', $notificationSetting->permission_name ?? '') }}" maxlength="150">
                <small class="text-muted">{{ __('For information only. It does not control access.') }}</small>
                @error('permission_name')<br><small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="row mb-2">
            <label for="permission_code" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Permission Code') }}</label>
            <div class="col-12 col-md-8">
                <input type="text" class="form-control @error('permission_code') is-invalid @enderror" id="permission_code" name="permission_code" value="{{ old('permission_code', $notificationSetting->permission_code ?? '') }}" maxlength="150">
                @error('permission_code')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="row mb-2">
            <label for="description" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Description') }}</label>
            <div class="col-12 col-md-8">
                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" maxlength="500">{{ old('description', $notificationSetting->description ?? '') }}</textarea>
                @error('description')<small class="text-danger">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>
    <!-- End Left Column -->

    <!-- Start Right Column -->
    <div class="col-12 col-md-5">
        <div class="border rounded p-3 mb-3">
            <h6 class="mb-2">{{ __('Channels') }}</h6>
            @forelse ($channels as $channel)
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" name="channels[]" id="channel_{{ $channel->id }}" value="{{ $channel->id }}" @checked(in_array($channel->id, $chosenChannelIds, true))>
                    <label class="form-check-label" for="channel_{{ $channel->id }}">{{ $channel->name }}</label>
                </div>
            @empty
                <small class="text-muted">{{ __('No active channels found.') }}</small>
            @endforelse
            @error('channels')<br><small class="text-danger">{{ $message }}</small>@enderror
            @error('channels.*')<br><small class="text-danger">{{ $message }}</small>@enderror
        </div>

        <div class="border rounded p-3">
            <h6 class="mb-2">{{ __('Who receives it') }}</h6>

            <label for="receiver_users" class="form-label mb-1">{{ __('Specific Users') }}</label>
            <select class="form-select select2-receivers mb-2" id="receiver_users" name="receiver_users[]" multiple>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(in_array((string) $user->id, $chosenUserIds, true))>{{ $user->name }}</option>
                @endforeach
            </select>
            @error('receiver_users.*')<small class="text-danger">{{ $message }}</small>@enderror

            <label for="receiver_roles" class="form-label mb-1">{{ __('Roles') }}</label>
            <select class="form-select select2-receivers mb-2" id="receiver_roles" name="receiver_roles[]" multiple>
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}" @selected(in_array($role->name, $chosenRoleNames, true))>{{ $role->name }}</option>
                @endforeach
            </select>
            @error('receiver_roles.*')<small class="text-danger">{{ $message }}</small>@enderror

            <label for="receiver_permissions" class="form-label mb-1">{{ __('Permissions') }}</label>
            <select class="form-select select2-receivers" id="receiver_permissions" name="receiver_permissions[]" multiple>
                @foreach ($permissions as $permission)
                    <option value="{{ $permission->name }}" @selected(in_array($permission->name, $chosenPermissionNames, true))>{{ $permission->name }}</option>
                @endforeach
            </select>
            @error('receiver_permissions.*')<small class="text-danger">{{ $message }}</small>@enderror
        </div>
    </div>
    <!-- End Right Column -->
</div>
