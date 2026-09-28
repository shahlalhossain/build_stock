<div class="inline">

    <!-- View Requisition Button -->
    <a href="{{ route('product-requisition.show', $productRequisition->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>

    <!-- Purchase Button -->
    <a href="{{ route('product-purchase.create-from-requisition', $productRequisition->id) }}" class="btn btn-sm btn-success"><i class="ri-shopping-cart-line"></i><span class="d-none d-sm-inline"> {{ __('Purchase') }}</span></a>

</div>
