<div class="inline">

    <!-- View Button -->
    <a href="{{ route('faq.show', $faq->id) }}" class="btn btn-sm btn-info">
        <i class="ri-eye-line"></i>
        <span class="d-none d-sm-inline"> {{ __('View') }}</span>
    </a>

    <!-- Restore Button -->
    <button class="btn btn-sm btn-soft-success restore-faq" data-faq-id="{{ $faq->id }}">
        <i class="ri-recycle-line"></i>
        <span class="d-none d-sm-inline"> {{ __('Restore') }}</span>
    </button>

    <!-- Delete Button -->
    <button class="btn btn-sm btn-danger delete-faq" data-faq-id="{{ $faq->id }}">
        <i class="ri-close-line"></i>
        <span class="d-none d-sm-inline"> {{ __('Delete') }}</span>
    </button>
</div>
