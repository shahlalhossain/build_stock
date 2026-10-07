<div class="inline">
    @can('notification.show')
        <!-- View Button -->
        <a href="{{ route('notification.show', $notification->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan
</div>
