<div class="inline">

    @can('notification-template.show')
        <a href="{{ route('notification-template.show', $notificationTemplate->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan

    @can('notification-template.edit')
        <a href="{{ route('notification-template.edit', $notificationTemplate->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
    @endcan

    @can('notification-template.update-status')
        <button class="btn btn-sm {{ $notificationTemplate->is_active ? 'btn-warning' : 'btn-success' }} toggle-notification-template" data-notification-template-id="{{ $notificationTemplate->id }}">
            <i class="ri-toggle-line"></i><span class="d-none d-sm-inline"> {{ $notificationTemplate->is_active ? __('Disable') : __('Enable') }}</span>
        </button>
    @endcan

    @can('notification-template.delete')
        <button class="btn btn-sm btn-danger delete-notification-template" data-notification-template-id="{{ $notificationTemplate->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
    @endcan

</div>
