<div class="inline">

    @can('product-delivery.show')
        <!-- View Button -->
        <a href="{{ route('product-delivery.show', $productDelivery->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @if($productDelivery->status === 'pending')
        @can('product-delivery.edit')
            <!-- Edit Button -->
            <a href="{{ route('product-delivery.edit', $productDelivery->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
        @endcan

        @can('product-delivery.destroy')
            <!-- Destroy Button -->
            <button class="btn btn-sm btn-warning destroy-product-delivery" data-product-delivery-id="{{ $productDelivery->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
        @endcan
    @endif

</div>
