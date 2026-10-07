<div class="inline">

    @can('notification-channel.show')
        <!-- View Button -->
        <a href="{{ route('notification-channel.show', $channel->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @can('notification-channel.edit')
        <!-- Edit Button -->
        <a href="{{ route('notification-channel.edit', $channel->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
    @endcan

    @can('notification-channel.update-status')
        <!-- Enable/Disable Button -->
        <button class="btn btn-sm {{ $channel->is_active ? 'btn-soft-secondary' : 'btn-soft-success' }} toggle-notification-channel" data-notification-channel-id="{{ $channel->id }}" data-is-active="{{ $channel->is_active ? 1 : 0 }}">
            <i class="{{ $channel->is_active ? 'ri-toggle-line' : 'ri-toggle-fill' }}"></i><span class="d-none d-sm-inline"> {{ $channel->is_active ? __('Disable') : __('Enable') }}</span>
        </button>
    @endcan

    @can('notification-channel.destroy')
        <!-- Destroy Button -->
        <button class="btn btn-sm btn-warning destroy-notification-channel" data-notification-channel-id="{{ $channel->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
    @endcan

</div>
