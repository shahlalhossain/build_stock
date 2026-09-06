@foreach ($children as $permission)
    <div class="col-md-4 mb-1">
        <div class="form-check form-switch form-switch-md form-switch-secondary">
            <input type="checkbox" class="form-check-input child-permission" id="perm_{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" data-parent="{{ $permission->parent_id }}" {{ collect($assignedPermissions)->contains($permission->id) ? 'checked' : '' }}>
            <label class="form-check-label" for="perm_{{ $permission->id }}">
                {{ $permission->description ?? $permission->name }}
            </label>
        </div>
    </div>

    @if ($permission->children->count())
        <div class="child-container ms-4" data-group="{{ $permission->id }}">
            @include('role.includes.child-permission', ['children' => $permission->children])
        </div>
    @endif
@endforeach