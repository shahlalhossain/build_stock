<div class="inline">

    @can('product-receive.show')
        <!-- View Button -->
        <a href="{{ route('product-receive.show', $productReceive->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('product-receive.restore')
        <!-- Restore Button -->
        <button class="btn btn-sm btn-soft-success restore-product-receive" data-product-receive-id="{{ $productReceive->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('product-receive.delete')
        <!-- Delete Button -->
        <button class="btn btn-sm btn-danger delete-product-receive" data-product-receive-id="{{ $productReceive->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
