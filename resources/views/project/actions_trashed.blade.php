<div class="inline">

    <!-- View Button -->
    <a href="{{ route('project.show', $project->id) }}" class="btn btn-sm btn-info">
        <i class="ri-eye-line"></i>
        <span class="d-none d-sm-inline">{{ __('View') }}</span>
    </a>

    <!-- Restore Button -->
    <button class="btn btn-sm btn-soft-success restore-project" data-project-id="{{ $project->id }}">
        <i class="ri-recycle-line"></i>
        <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
    </button>

    <!-- Delete Button -->
    <button class="btn btn-sm btn-danger delete-project" data-project-id="{{ $project->id }}">
        <i class="ri-close-line"></i>
        <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
    </button>
</div>
