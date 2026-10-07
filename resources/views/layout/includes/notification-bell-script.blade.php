{{--
| Header bell: shows the unread count and the latest notifications, and lets the user
| "Mark as Read" or "Delete" straight from the dropdown. All data comes from the
| "My Notifications" routes. The same helper (window.notificationBell) is used to add a
| new notification live when one arrives.
--}}
<script>
    window.notificationBell = (function () {
        const urls = {
            summary: "{{ route('my-notification.summary') }}",
            read: "{{ url('my-notifications') }}/__ID__/read",
            destroy: "{{ url('my-notifications') }}/__ID__"
        };
        const csrfToken = "{{ csrf_token() }}";

        // Builds one notification row. Text is added with .text() so nothing from the message can inject HTML.
        function buildItem(item) {
            const urgent = item.priority === 'urgent' || item.priority === 'high';
            const icon = urgent ? 'bx-error text-danger' : 'bx-bell text-info';
            const row = $('<div class="text-reset notification-item d-block dropdown-item position-relative"></div>')
                .attr('data-notification-id', item.id)
                .toggleClass('notification-unread', !item.is_read);

            const content = $('<div class="flex-grow-1"></div>');
            const title = $('<h6 class="mt-0 mb-1 fs-13"></h6>').addClass(item.is_read ? '' : 'fw-semibold');
            title.append(item.url ? $('<a class="text-reset"></a>').attr('href', item.url).text(item.title) : document.createTextNode(item.title));
            content.append(title);
            if (item.body) {
                content.append($('<p class="mb-1 fs-12 text-muted"></p>').text(item.body));
            }
            content.append($('<p class="mb-0 fs-11 fw-medium text-muted"></p>').text(item.time_human));

            const actions = $('<div class="d-flex align-items-center ms-2 gap-2"></div>');
            actions.append(
                $('<button type="button" class="btn btn-sm btn-icon btn-ghost-success mark-notification-read" title="Mark as read"><i class="bx bx-check fs-16"></i></button>')
                    .attr('data-id', item.id).toggleClass('d-none', item.is_read),
                $('<button type="button" class="btn btn-sm btn-icon btn-ghost-danger delete-notification" title="Delete"><i class="bx bx-trash fs-16"></i></button>')
                    .attr('data-id', item.id)
            );

            return row.append(
                $('<div class="d-flex align-items-start"></div>').append(
                    $('<div class="avatar-xs me-3 flex-shrink-0"></div>').append(
                        $('<span class="avatar-title bg-light rounded-circle fs-16"></span>').append($('<i class="bx"></i>').addClass(icon))
                    ),
                    content,
                    actions
                )
            );
        }

        // Updates the red badge on the bell and the "N New" badge in the dropdown header.
        function setCount(count) {
            $('#notification-count').text(count).toggleClass('d-none', count === 0);
            $('#notification-header-count').text(count).closest('.badge').toggleClass('d-none', count === 0);
        }

        // Shows the empty message when the list has no notifications.
        function checkEmpty() {
            const isEmpty = $('#notification-list .notification-item').length === 0;
            $('#notification-empty').toggleClass('d-none', !isEmpty);
            $('#notification-list').toggleClass('d-none', isEmpty);
        }

        // Fills the dropdown with the given list of notifications.
        function render(items, unreadCount) {
            $('#notification-list').empty().append(items.map(buildItem));
            setCount(unreadCount);
            checkEmpty();
        }

        // Adds one new notification at the top (used for real-time arrivals) and keeps the list at 10.
        function prepend(item, unreadCount) {
            $('#notification-list .notification-item[data-notification-id="' + item.id + '"]').remove();
            $('#notification-list').prepend(buildItem(item)).removeClass('d-none');
            $('#notification-list .notification-item').slice(10).remove();
            setCount(unreadCount);
            checkEmpty();
        }

        // Loads the latest notifications from the server.
        function load() {
            $.getJSON(urls.summary, function (response) {
                render(response.items, response.unread_count);
            });
        }

        return { urls: urls, csrfToken: csrfToken, load: load, render: render, prepend: prepend, setCount: setCount, checkEmpty: checkEmpty };
    })();

    $(document).ready(function () {
        const bell = window.notificationBell;
        bell.load();

        // "Mark as read" button in the dropdown.
        $(document).on('click', '#notification-list .mark-notification-read', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const button = $(this);
            const row = button.closest('.notification-item');

            $.ajax({
                url: bell.urls.read.replace('__ID__', button.data('id')),
                type: 'POST',
                data: { _token: bell.csrfToken },
                success: function (response) {
                    row.removeClass('notification-unread').find('h6').removeClass('fw-semibold');
                    button.addClass('d-none');
                    bell.setCount(response.unread_count);
                },
                error: function () {
                    Toastify({ text: 'Unable to mark the notification as read.', duration: 4000, gravity: 'top', position: 'right', close: true, className: 'failed-toast' }).showToast();
                }
            });
        });

        // "Delete" button in the dropdown (hides it from the user's board).
        $(document).on('click', '#notification-list .delete-notification', function (e) {
            e.preventDefault();
            e.stopPropagation();
            const button = $(this);
            const row = button.closest('.notification-item');

            $.ajax({
                url: bell.urls.destroy.replace('__ID__', button.data('id')),
                type: 'DELETE',
                data: { _token: bell.csrfToken },
                success: function (response) {
                    row.fadeOut(200, function () {
                        $(this).remove();
                        bell.checkEmpty();
                    });
                    bell.setCount(response.unread_count);
                },
                error: function () {
                    Toastify({ text: 'Unable to delete the notification.', duration: 4000, gravity: 'top', position: 'right', close: true, className: 'failed-toast' }).showToast();
                }
            });
        });
    });
</script>
