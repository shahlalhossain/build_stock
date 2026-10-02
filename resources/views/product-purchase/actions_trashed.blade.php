<div class="inline">

    @can('product-purchase.show')
        <!-- View Button -->
        <a href="{{ route('product-purchase.show', $productPurchase->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('product-purchase.restore')
        <!-- Restore Button -->
        <button class="btn btn-sm btn-soft-success restore-product-purchase" data-product-purchase-id="{{ $productPurchase->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('product-purchase.delete')
        <!-- Delete Button -->
        <button class="btn btn-sm btn-danger delete-product-purchase" data-product-purchase-id="{{ $productPurchase->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
