<div class="inline">

    @can('product-transfer.show')
        <!-- View Button -->
        <a href="{{ route('product-transfer.show', $productTransfer->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('product-transfer.restore')
        <!-- Restore Button -->
        <button class="btn btn-sm btn-soft-success restore-product-transfer" data-product-transfer-id="{{ $productTransfer->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('product-transfer.delete')
        <!-- Delete Button -->
        <button class="btn btn-sm btn-danger delete-product-transfer" data-product-transfer-id="{{ $productTransfer->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
