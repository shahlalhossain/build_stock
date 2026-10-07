{{-- "Notification Preferences" tab of the profile page: the user switches each notification channel on or off for themselves. --}}
<div class="tab-pane" id="notificationPreferences" role="tabpanel">
    <h5 class="card-title mb-1" style="font-size: 14px; font-weight: bold; font-style: italic;">
        {{ __('Notification Preferences') }}
    </h5>
    <p class="text-muted mb-3">{{ __('Choose how you want to receive notifications. If you switch a channel off, you will not receive notifications on it.') }}</p>

    <div class="list-group list-group-flush border border-secondary border-opacity-50 rounded" id="notification-preference-list">
        @forelse ($notificationChannels as $channel)
            <div class="list-group-item d-flex align-items-center justify-content-between border-secondary border-opacity-50">
                <div>
                    <div class="fw-semibold">{{ $channel['name'] }}</div>
                    @if ($channel['description'])
                        <small class="text-muted">{{ $channel['description'] }}</small>
                    @endif
                </div>
                <div class="form-check form-switch form-switch-success mb-0">
                    <input class="form-check-input notification-preference-switch" type="checkbox" role="switch"
                           id="notification-channel-{{ $channel['id'] }}"
                           data-channel-id="{{ $channel['id'] }}"
                           @checked($channel['is_enabled'])>
                </div>
            </div>
        @empty
            <div class="list-group-item text-muted">{{ __('No notification channels are available.') }}</div>
        @endforelse
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function () {
            // Saves the switch as soon as the user flips it. If saving fails, the switch goes back.
            $(document).on('change', '.notification-preference-switch', function () {
                const switchInput = $(this);
                const wantsOn = switchInput.is(':checked');

                $.ajax({
                    url: "{{ route('notification-preference.update') }}",
                    method: 'PUT',
                    data: {
                        _token: "{{ csrf_token() }}",
                        channel_id: switchInput.data('channel-id'),
                        is_enabled: wantsOn ? 1 : 0
                    },
                    success: function (response) {
                        Toastify({
                            text: response.message || 'Notification Preference Saved.',
                            duration: 3000, gravity: "top", position: "right", close: true,
                            className: 'success-toast', stopOnFocus: true
                        }).showToast();
                    },
                    error: function (xhr) {
                        switchInput.prop('checked', !wantsOn);
                        Toastify({
                            text: xhr.responseJSON?.message || 'There was an Issue on Saving the Preference.',
                            duration: 4000, gravity: "top", position: "right", close: true,
                            className: 'failed-toast', stopOnFocus: true
                        }).showToast();
                    }
                });
            });
        });
    </script>
@endpush
