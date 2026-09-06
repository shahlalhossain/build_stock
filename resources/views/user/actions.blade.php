<div class="inline">

    <!-- View Button -->
    <a href="{{ route('user.show', $user->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>

    <!-- Edit Button -->
    <a href="{{ route('user.edit', $user->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>

    <!-- Delete Button -->
    <button class="btn btn-sm btn-warning destroy-user" data-user-id="{{ $user->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>

</div>
