<div class="inline">

    @can('product-delivery.show')
        <!-- View Button -->
        <a href="{{ route('product-delivery.show', $productDelivery->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('product-delivery.restore')
        <!-- Restore Button -->
        <button class="btn btn-sm btn-soft-success restore-product-delivery" data-product-delivery-id="{{ $productDelivery->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('product-delivery.delete')
        <!-- Delete Button -->
        <button class="btn btn-sm btn-danger delete-product-delivery" data-product-delivery-id="{{ $productDelivery->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
