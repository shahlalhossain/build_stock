<div class="inline">

    @can('stock-transaction.show')
        <!-- View Button -->
        <a href="{{ route('stock-transaction.show', $stockTransaction->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @if($stockTransaction->status === 'pending')
        @can('stock-transaction.edit')
            <!-- Edit Button -->
            <a href="{{ route('stock-transaction.edit', $stockTransaction->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
        @endcan

        @can('stock-transaction.destroy')
            <!-- Destroy Button -->
            <button class="btn btn-sm btn-warning destroy-stock-transaction" data-stock-transaction-id="{{ $stockTransaction->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
        @endcan
    @endif

</div>
