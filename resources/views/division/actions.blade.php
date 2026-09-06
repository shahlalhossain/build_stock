<div class="inline">

    <!-- View Button -->
    <a href="{{ route('division.show', $division->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>

    <!-- Edit Button -->
    <a href="{{ route('division.edit', $division->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>

    <!-- Destroy Button -->
    <button class="btn btn-sm btn-warning destroy-division" data-division-id="{{ $division->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>

</div>
