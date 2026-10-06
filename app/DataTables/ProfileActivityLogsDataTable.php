<?php

namespace App\DataTables;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProfileActivityLogsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->editColumn('event', fn (AuditLog $auditLog) => ucwords((string) $auditLog->event))
            ->editColumn('created_at', fn (AuditLog $auditLog) => $auditLog->created_at?->format('Y-m-d h:i A') ?? '-');
    }

    /**
     * Query source — only activity caused by the logged-in user
     */
    public function query(AuditLog $model): QueryBuilder
    {
        return $model->newQuery()
            ->where('causer_type', User::class)
            ->where('causer_id', auth()->id());
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('profile-activity-logs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(4, 'desc')
            ->parameters([
                'serverSide' => true,
                'processing' => true,
                'stateSave' => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 20, 30, 40, 50, 100], [10, 20, 30, 40, 50, 100]],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('log_name')->title('Log Title'),
            Column::make('event')->title('Event Name'),
            Column::make('description')->title('Description'),
            Column::make('created_at')->title('Event Occurred At'),
        ];
    }

    protected function filename(): string
    {
        return 'ProfileActivityLogs_'.date('YmdHis');
    }
}
