<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\NotificationLog;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Str;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class NotificationLogsDataTable extends DataTable
{
    /**
     * Builds the table rows (how each column looks).
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addColumn('notification_id', fn (NotificationLog $log) => $log->receiver?->notification_message_id)
            ->addColumn('user_name', fn (NotificationLog $log) => $log->receiver?->user?->name)
            ->addColumn('channel_name', fn (NotificationLog $log) => $log->receiver?->channel?->name)
            ->editColumn('message', fn (NotificationLog $log) => e(Str::limit((string) $log->message, 80)))
            ->editColumn('created_at', fn (NotificationLog $log) => $log->created_at?->format('Y-m-d H:i:s'))
            ->addColumn('actions', fn (NotificationLog $log) => view('notification-log.actions', ['log' => $log]));
    }

    /**
     * Builds the database query, including the filters chosen on the page:
     * date from / to, event, channel, status, user (name or email) and notification id.
     */
    public function query(NotificationLog $model): QueryBuilder
    {
        $request = request();

        return $model->newQuery()
            ->with(['receiver.user', 'receiver.channel'])
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')))
            ->when($request->filled('event'), fn ($query) => $query->where('event', $request->input('event')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('channel_id'), fn ($query) => $query->whereHas('receiver', fn ($receiver) => $receiver->where('channel_id', $request->input('channel_id'))))
            ->when($request->filled('notification_id'), fn ($query) => $query->whereHas('receiver', fn ($receiver) => $receiver->where('notification_message_id', $request->input('notification_id'))))
            ->when($request->filled('user'), fn ($query) => $query->whereHas('receiver.user', fn ($user) => $user
                ->where('name', 'like', '%'.$request->input('user').'%')
                ->orWhere('email', 'like', '%'.$request->input('user').'%')));
    }

    /**
     * Sets up the HTML table. The filter values are sent along with every reload.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('notification-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax(script: "data.date_from = $('#filter_date_from').val(); data.date_to = $('#filter_date_to').val(); data.event = $('#filter_event').val(); data.channel_id = $('#filter_channel').val(); data.status = $('#filter_status').val(); data.user = $('#filter_user').val(); data.notification_id = $('#filter_notification_id').val();")
            ->orderBy(0, 'desc')
            ->selectStyleSingle()
            ->parameters([
                'serverSide' => true,
                'processing' => true,
                'stateSave' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 20, 30, 40, 50, 100], [10, 20, 30, 40, 50, 100]],
            ]);
    }

    /**
     * Lists the table columns.
     *
     * @return array<int, Column>
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->title('ID')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::make('created_at')->title('Time')->orderable(true)->searchable(false),
            Column::computed('notification_id')->title('Notification')->addClass('text-center'),
            Column::computed('user_name')->title('User'),
            Column::computed('channel_name')->title('Channel'),
            Column::make('event')->orderable(true)->searchable(true),
            Column::make('status')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('message')->orderable(false)->searchable(true),
            Column::computed('actions')->orderable(false)->searchable(false)->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    /**
     * The file name used when the table is exported.
     */
    protected function filename(): string
    {
        return 'NotificationLogs_'.date('YmdHis');
    }
}
