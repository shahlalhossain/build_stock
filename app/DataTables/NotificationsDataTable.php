<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\NotificationMessage;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class NotificationsDataTable extends DataTable
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
            ->addColumn('type_label', fn (NotificationMessage $notification) => $notification->type->label())
            ->addColumn('status_badge', function (NotificationMessage $notification) {
                $class = match ($notification->status->value) {
                    'completed' => 'bg-success',
                    'partial', 'pending', 'processing' => 'bg-warning',
                    'failed' => 'bg-danger',
                    default => 'bg-secondary',
                };

                return '<span class="badge '.$class.'">'.e($notification->status->label()).'</span>';
            })
            ->addColumn('priority_label', fn (NotificationMessage $notification) => $notification->priority->label())
            ->addColumn('created_by_name', fn (NotificationMessage $notification) => $notification->creator?->name ?? __('System'))
            ->editColumn('created_at', fn (NotificationMessage $notification) => $notification->created_at->format('Y-m-d H:i'))
            ->addColumn('actions', fn (NotificationMessage $notification) => view('notification.actions', ['notification' => $notification]))
            ->rawColumns(['status_badge']);
    }

    /**
     * Builds the database query, including the filters chosen on the page
     * (type, status, event, date from / to).
     */
    public function query(NotificationMessage $model): QueryBuilder
    {
        $request = request();

        return $model->newQuery()
            ->with('creator')
            ->withCount('receivers')
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('event'), fn ($query) => $query->where('event_code', 'like', '%'.$request->input('event').'%'))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->input('date_to')));
    }

    /**
     * Sets up the HTML table. The filter values are sent along with every reload.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('notifications-table')
            ->columns($this->getColumns())
            ->minifiedAjax(script: "data.type = $('#filter_type').val(); data.status = $('#filter_status').val(); data.event = $('#filter_event').val(); data.date_from = $('#filter_date_from').val(); data.date_to = $('#filter_date_to').val();")
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
            Column::computed('type_label')->title('Type')->addClass('text-center'),
            Column::make('event_code')->title('Event')->orderable(true)->searchable(true),
            Column::make('title')->orderable(true)->searchable(true),
            Column::computed('priority_label')->title('Priority')->addClass('text-center'),
            Column::computed('status_badge')->title('Status')->addClass('text-center'),
            Column::make('receivers_count')->title('Deliveries')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::computed('created_by_name')->title('Created By'),
            Column::make('created_at')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::computed('actions')->orderable(false)->searchable(false)->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    /**
     * The file name used when the table is exported.
     */
    protected function filename(): string
    {
        return 'Notifications_'.date('YmdHis');
    }
}
