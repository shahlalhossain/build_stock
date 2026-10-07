<?php

namespace App\DataTables;

use App\Models\NotificationTemplate;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class NotificationTemplatesDataTable extends DataTable
{
    /**
     * Builds the table rows: setting, channel, text, status badge and action buttons.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->addColumn('setting_name', function (NotificationTemplate $template) {
                return e($template->setting?->name).'<br><small class="text-muted">'.e($template->setting?->event_code).'</small>';
            })
            ->addColumn('channel_name', function (NotificationTemplate $template) {
                return e($template->channel?->name);
            })
            ->addColumn('text', function (NotificationTemplate $template) {
                return e($template->title ?: $template->subject);
            })
            ->addColumn('is_active', function (NotificationTemplate $template) {
                return $template->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('updated_at', function (NotificationTemplate $template) {
                return $template->updated_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (NotificationTemplate $template) {
                return view('notification-template.actions', ['notificationTemplate' => $template]);
            })
            ->rawColumns(['setting_name', 'is_active']);
    }

    /**
     * Gets the records for the table, with setting and channel loaded.
     */
    public function query(NotificationTemplate $model): QueryBuilder
    {
        return $model->newQuery()->with(['setting', 'channel']);
    }

    /**
     * Sets up the table (page size, server side loading).
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('notification-templates-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->selectStyleSingle()
            ->parameters([
                'serverSide' => true,
                'processing' => true,
                'stateSave' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 20, 30, 40, 50, 100, -1], [10, 20, 30, 40, 50, 100, 'All']],
            ]);
    }

    /**
     * Lists the table columns.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::computed('setting_name')->title('Setting')->orderable(false)->searchable(false),
            Column::computed('channel_name')->title('Channel')->orderable(false)->searchable(false),
            Column::computed('text')->title('Title / Subject')->orderable(false)->searchable(false),
            Column::computed('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('updated_at')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::computed('actions')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
        ];
    }

    /**
     * Gets the file name used when the table is exported.
     */
    protected function filename(): string
    {
        return 'NotificationTemplates_'.date('YmdHis');
    }
}
