<div class="inline">

    @can('product-purchase.show')
        <!-- View Button -->
        <a href="{{ route('product-purchase.show', $productPurchase->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @if($productPurchase->status === 'pending')
        @can('product-purchase.edit')
            <!-- Edit Button -->
            <a href="{{ route('product-purchase.edit', $productPurchase->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
        @endcan

        @can('product-purchase.destroy')
            <!-- Destroy Button -->
            <button class="btn btn-sm btn-warning destroy-product-purchase" data-product-purchase-id="{{ $productPurchase->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
        @endcan
    @endif

</div>
