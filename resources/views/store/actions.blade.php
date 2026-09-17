<div class="inline">

    <!-- View Button -->
    <a href="{{ route('store.show', $store->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>

    <!-- Edit Button -->
    <a href="{{ route('store.edit', $store->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>

    <!-- Destroy Button -->
    <button class="btn btn-sm btn-warning destroy-store" data-store-id="{{ $store->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>

</div>
