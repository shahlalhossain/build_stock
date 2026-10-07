<div class="inline">

    @can('notification-setting.show')
        <a href="{{ route('notification-setting.show', $notificationSetting->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @can('notification-setting.edit')
        <a href="{{ route('notification-setting.edit', $notificationSetting->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
    @endcan

    @can('notification-setting.update-status')
        <button class="btn btn-sm {{ $notificationSetting->is_active ? 'btn-soft-danger' : 'btn-soft-success' }} toggle-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}">
            <i class="ri-toggle-line"></i><span class="d-none d-sm-inline"> {{ $notificationSetting->is_active ? __('Disable') : __('Enable') }}</span>
        </button>
    @endcan

    @can('notification-setting.destroy')
        <button class="btn btn-sm btn-warning destroy-notification-setting" data-notification-setting-id="{{ $notificationSetting->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
    @endcan

</div>
