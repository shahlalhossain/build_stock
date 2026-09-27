<div class="inline">

    <!-- View Button -->
    <a href="{{ route('product-receive.show', $productReceive->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>

    @if($productReceive->status === 'pending')
        <!-- Edit Button -->
        <a href="{{ route('product-receive.edit', $productReceive->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>

        <!-- Destroy Button -->
        <button class="btn btn-sm btn-warning destroy-product-receive" data-product-receive-id="{{ $productReceive->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
    @endif

</div>
