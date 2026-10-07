<div class="inline">
    @can('notification-log.show')
        <!-- View Button -->
        <a href="{{ route('notification-log.show', $log->id) }}" class="btn btn-sm btn-secondary"><i class="ri-eye-line"></i><span class="d-none d-sm-inline"> {{ __('View') }}</span></a>
    @endcan
</div>
