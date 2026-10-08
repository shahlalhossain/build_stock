<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\NotificationChannel;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class NotificationChannelsDataTable extends DataTable
{
    /**
     * Builds the table rows (badges, dates and action buttons).
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->addColumn('is_active', function (NotificationChannel $channel) {
                return $channel->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('created_at', function (NotificationChannel $channel) {
                return $channel->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (NotificationChannel $channel) {
                if ($this->showTrashed) {
                    return view('notification-channel.actions_trashed', ['channel' => $channel]);
                }

                return view('notification-channel.actions', ['channel' => $channel]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Picks which channels to show: trashed ones or normal ones.
     */
    public function query(NotificationChannel $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->onlyTrashed();
        }

        return $model->newQuery()->withoutTrashed();
    }

    /**
     * Sets up the table's HTML and options.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('notification-channels-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(5, 'asc')
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
            Column::make('name')->orderable(true)->searchable(true),
            Column::make('code')->orderable(true)->searchable(true),
            Column::make('driver')->orderable(true)->searchable(true),
            Column::computed('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('created_at')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::computed('actions')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
        ];
    }

    /**
     * Names the file used when the table is exported.
     */
    protected function filename(): string
    {
        return 'NotificationChannels_'.date('YmdHis');
    }
}
