<div class="inline">

    @can('product-transfer.show')
        <!-- View Button -->
        <a href="{{ route('product-transfer.show', $productTransfer->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @if($productTransfer->status === 'pending')
        @can('product-transfer.edit')
            <!-- Edit Button -->
            <a href="{{ route('product-transfer.edit', $productTransfer->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
        @endcan

        @can('product-transfer.destroy')
            <!-- Destroy Button -->
            <button class="btn btn-sm btn-warning destroy-product-transfer" data-product-transfer-id="{{ $productTransfer->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
        @endcan
    @endif

</div>
