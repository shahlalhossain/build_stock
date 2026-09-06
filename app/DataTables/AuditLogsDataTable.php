<?php

namespace App\DataTables;

use App\Models\AuditLog;
use App\Models\LoginActivity;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AuditLogsDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->editColumn('event', function(AuditLog $auditLog) {
                return ucwords($auditLog->event);
            })
            ->editColumn('causer_id', function(AuditLog $auditLog) {
                return ucwords($auditLog->user?->name);
            })
            ->editColumn('created_at', function(AuditLog $auditLog) {
                return $auditLog->created_at->format('Y-m-d H:i A');
            })
            ->rawColumns([]);
    }

    /**
     * Query source
     */
    public function query(AuditLog $model): QueryBuilder
    {
        return $model->newQuery()->with('user');
    }

    /**
     * Html builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('audit-log-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->selectStyleSingle()
            ->parameters([
                'serverSide' => true,
                'processing' => true,
                'stateSave'  => false,
                'pageLength' => 15,
                'lengthMenu' => [[15, 20, 30, 40, 50, 100, -1], [15, 20, 30, 40, 50, 100, "All"]],
            ]);
    }

    /**
     * Columns
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('log_name')->title('Log Title')->orderable(true)->searchable(true),
            Column::make('event')->title('Event Name')->orderable(true)->searchable(true),
            Column::make('causer_id')->title('Occurred By')->orderable(true)->searchable(true),
            Column::make('created_at')->title('Event Occurred At')->orderable(true)->searchable(true),
            Column::make('description')->title('Description')->orderable(true)->searchable(true),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'AuditLog_' . date('YmdHis');
    }
}
