{{--
| Real-time notifications (Pusher). Loaded ONLY when Pusher is the active broadcaster and
| a key is set, so nothing is requested from the outside world otherwise.
| Only the public key and cluster go to the browser, never the secret.
| When a notification arrives for the logged-in user: show a pop-up, update the bell,
| and tell the page (event "notification:received") so the My Notifications page can refresh.
--}}
@php
    $pusherConfig = config('broadcasting.connections.pusher');
    $realtimeOn = config('broadcasting.default') === 'pusher' && ! empty($pusherConfig['key']) && auth()->check();
@endphp

@if ($realtimeOn)
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
    <script>
        $(document).ready(function () {
            window.Pusher = Pusher;

            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: @json($pusherConfig['key']),
                cluster: @json($pusherConfig['options']['cluster'] ?? null),
                forceTLS: true,
                authEndpoint: "{{ url('broadcasting/auth') }}",
                auth: { headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } }
            });

            // Only this user's own private channel (the server refuses anyone else).
            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                .listen('.notification.received', function (item) {
                    if (window.notificationBell) {
                        window.notificationBell.prepend(item, item.unread_count);
                    }

                    Toastify({
                        text: item.title + (item.body ? ' — ' + item.body : ''),
                        duration: 6000,
                        gravity: 'top',
                        position: 'right',
                        close: true,
                        className: 'success-toast',
                        stopOnFocus: true,
                        onClick: function () { window.location.href = item.url || "{{ route('my-notification.index') }}"; }
                    }).showToast();

                    $(document).trigger('notification:received', [item]);
                });
        });
    </script>
@endif
