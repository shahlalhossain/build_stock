<div class="inline">

    <!-- View Button -->
    <a href="{{ route('product-requisition.show', $productRequisition->id) }}" class="btn btn-sm btn-info">
        <i class="ri-eye-line"></i>
        <span class="d-none d-sm-inline">{{ __('View') }}</span>
    </a>

    <!-- Restore Button -->
    <button class="btn btn-sm btn-soft-success restore-product-requisition" data-product-requisition-id="{{ $productRequisition->id }}">
        <i class="ri-recycle-line"></i>
        <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
    </button>

    <!-- Delete Button -->
    <button class="btn btn-sm btn-danger delete-product-requisition" data-product-requisition-id="{{ $productRequisition->id }}">
        <i class="ri-close-line"></i>
        <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
    </button>
</div>
