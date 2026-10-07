@extends('layout.master')

@section('title', __('My Notifications'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                {{ __('My Notifications') }}
                                <span class="badge bg-danger-subtle text-danger ms-1 {{ $unreadCount === 0 ? 'd-none' : '' }}" id="board-unread-badge"><span id="board-unread-count">{{ $unreadCount }}</span> {{ __('Unread') }}</span>
                            </h4>
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-success {{ $unreadCount === 0 ? 'd-none' : '' }}" id="markAllRead"><i class="ri-check-double-line"></i><span class="d-none d-sm-inline"> {{ __('Mark All as Read') }}</span></button>
                            </div>
                        </div>

                        <div class="card-body">
                            <ul class="nav nav-pills nav-sm mb-3 gap-1">
                                <li class="nav-item"><a class="nav-link {{ $filter === 'all' ? 'active' : '' }}" href="{{ route('my-notification.index') }}">{{ __('All') }}</a></li>
                                <li class="nav-item"><a class="nav-link {{ $filter === 'unread' ? 'active' : '' }}" href="{{ route('my-notification.index', ['filter' => 'unread']) }}">{{ __('Unread') }}</a></li>
                            </ul>

                            <div class="list-group list-group-flush border border-secondary border-opacity-50 rounded" id="board-list">
                                @forelse ($items as $item)
                                    <div class="list-group-item border-secondary border-opacity-50 board-item {{ $item['is_read'] ? '' : 'bg-primary-subtle' }}" data-id="{{ $item['id'] }}">
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 board-title {{ $item['is_read'] ? '' : 'fw-semibold' }}">
                                                    @if ($item['url'])
                                                        <a href="{{ $item['url'] }}" class="text-reset">{{ $item['title'] }}</a>
                                                    @else
                                                        {{ $item['title'] }}
                                                    @endif
                                                    @if (in_array($item['priority'], ['high', 'urgent'], true))
                                                        <span class="badge bg-danger ms-1">{{ ucfirst($item['priority']) }}</span>
                                                    @endif
                                                    <span class="badge bg-info ms-1 board-new-badge {{ $item['is_read'] ? 'd-none' : '' }}">{{ __('New') }}</span>
                                                </h6>
                                                @if ($item['body'])
                                                    <p class="mb-1 text-muted" style="white-space: pre-wrap;">{{ $item['body'] }}</p>
                                                @endif
                                                <small class="text-muted">{{ $item['time_human'] }}</small>
                                            </div>
                                            <div class="d-flex align-items-center ms-2 gap-2">
                                                <button type="button" class="btn btn-sm btn-soft-success board-mark-read {{ $item['is_read'] ? 'd-none' : '' }}" data-id="{{ $item['id'] }}" title="{{ __('Mark as Read') }}"><i class="ri-check-line"></i><span class="d-none d-sm-inline"> {{ __('Mark as Read') }}</span></button>
                                                <button type="button" class="btn btn-sm btn-soft-danger board-delete" data-id="{{ $item['id'] }}" title="{{ __('Delete') }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="list-group-item text-center text-muted py-4" id="board-empty">
                                        {{ $filter === 'unread' ? __('No unread notifications.') : __("You're all caught up! No notifications yet.") }}
                                    </div>
                                @endforelse
                            </div>

                            <div class="mt-3">{{ $items->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const csrfToken = "{{ csrf_token() }}";
            const readUrl = "{{ url('my-notifications') }}/__ID__/read";
            const deleteUrl = "{{ url('my-notifications') }}/__ID__";

            function toast(text, ok) {
                Toastify({ text: text, duration: 3000, gravity: 'top', position: 'right', close: true, className: ok ? 'success-toast' : 'failed-toast', stopOnFocus: true }).showToast();
            }

            // Shows the new unread number here and in the header bell.
            function showUnread(count) {
                $('#board-unread-count').text(count);
                $('#board-unread-badge, #markAllRead').toggleClass('d-none', count === 0);
                if (window.notificationBell) {
                    window.notificationBell.setCount(count);
                    window.notificationBell.load();
                }
            }

            function markRowRead(row) {
                row.removeClass('bg-primary-subtle').find('.board-title').removeClass('fw-semibold');
                row.find('.board-new-badge, .board-mark-read').addClass('d-none');
            }

            $(document).on('click', '.board-mark-read', function () {
                const row = $(this).closest('.board-item');
                $.ajax({
                    url: readUrl.replace('__ID__', $(this).data('id')), method: 'POST', data: { _token: csrfToken },
                    success: function (response) { markRowRead(row); showUnread(response.unread_count); },
                    error: function (xhr) { toast(xhr.responseJSON?.message || 'Unable to mark as read.', false); }
                });
            });

            $(document).on('click', '.board-delete', function () {
                const row = $(this).closest('.board-item');
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are You Sure?', text: 'This notification will be removed from your board.', icon: 'warning',
                    showCancelButton: true, confirmButtonText: 'Yes, Delete', cancelButtonText: 'No, Cancel'
                }).then(function (result) {
                    if (!result.isConfirmed) { return; }
                    $.ajax({
                        url: deleteUrl.replace('__ID__', id), method: 'DELETE', data: { _token: csrfToken },
                        success: function (response) {
                            row.fadeOut(200, function () { $(this).remove(); });
                            showUnread(response.unread_count);
                            toast(response.message, true);
                        },
                        error: function (xhr) { toast(xhr.responseJSON?.message || 'Unable to delete.', false); }
                    });
                });
            });

            // A new notification arrived in real time: reload the list (keeps the current filter and page).
            $(document).on('notification:received', function (event, item) {
                $('#board-list').load(window.location.href + ' #board-list > *');
                showUnread(item.unread_count);
            });

            $('#markAllRead').on('click', function () {
                $.ajax({
                    url: "{{ route('my-notification.read-all') }}", method: 'POST', data: { _token: csrfToken },
                    success: function (response) { markRowRead($('.board-item')); showUnread(response.unread_count); toast(response.message, true); },
                    error: function (xhr) { toast(xhr.responseJSON?.message || 'Unable to mark all as read.', false); }
                });
            });
        });
    </script>
@endpush
