<div class="inline">

    @can('notification-channel.show')
        <!-- View Button -->
        <a href="{{ route('notification-channel.show', $channel->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('notification-channel.restore')
        <!-- Restore Button -->
        <button class="btn btn-sm btn-soft-success restore-notification-channel" data-notification-channel-id="{{ $channel->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('notification-channel.delete')
        <!-- Delete Button -->
        <button class="btn btn-sm btn-danger delete-notification-channel" data-notification-channel-id="{{ $channel->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
