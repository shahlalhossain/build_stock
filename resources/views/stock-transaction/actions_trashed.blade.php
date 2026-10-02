<div class="inline">

    @can('stock-transaction.show')
        <!-- View Button -->
        <a href="{{ route('stock-transaction.show', $stockTransaction->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('stock-transaction.restore')
        <!-- Restore Button -->
        <button class="btn btn-sm btn-soft-success restore-stock-transaction" data-stock-transaction-id="{{ $stockTransaction->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('stock-transaction.delete')
        <!-- Delete Button -->
        <button class="btn btn-sm btn-danger delete-stock-transaction" data-stock-transaction-id="{{ $stockTransaction->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
