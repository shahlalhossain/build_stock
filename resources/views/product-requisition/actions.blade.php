<div class="inline">

    <!-- View Button -->
    <a href="{{ route('product-requisition.show', $productRequisition->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>

    @if($productRequisition->status === 'pending')
        <!-- Edit Button -->
        <a href="{{ route('product-requisition.edit', $productRequisition->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>

        <!-- Destroy Button -->
        <button class="btn btn-sm btn-warning destroy-product-requisition" data-product-requisition-id="{{ $productRequisition->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
    @endif

</div>
