<div class="inline">

    @can('product-requisition.show')
        <!-- View Button -->
        <a href="{{ route('product-requisition.show', $productRequisition->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @if($productRequisition->status === 'pending')
        @can('product-requisition.edit')
            <!-- Edit Button -->
            <a href="{{ route('product-requisition.edit', $productRequisition->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
        @endcan

        @can('product-requisition.destroy')
            <!-- Destroy Button -->
            <button class="btn btn-sm btn-warning destroy-product-requisition" data-product-requisition-id="{{ $productRequisition->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
        @endcan
    @endif

</div>
