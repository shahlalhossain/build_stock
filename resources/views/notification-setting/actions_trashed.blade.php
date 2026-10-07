<div class="inline">

    @can('notification-setting.show')
        <a href="{{ route('notification-setting.show', $notificationSetting->id) }}" class="btn btn-sm btn-info">
            <i class="ri-eye-line"></i>
            <span class="d-none d-sm-inline">{{ __('View') }}</span>
        </a>
    @endcan

    @can('notification-setting.restore')
        <button class="btn btn-sm btn-soft-success restore-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}">
            <i class="ri-recycle-line"></i>
            <span class="d-none d-sm-inline">{{ __('Restore') }}</span>
        </button>
    @endcan

    @can('notification-setting.delete')
        <button class="btn btn-sm btn-danger delete-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}">
            <i class="ri-close-line"></i>
            <span class="d-none d-sm-inline">{{ __('Delete') }}</span>
        </button>
    @endcan
</div>
