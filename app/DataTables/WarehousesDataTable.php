<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class WarehousesDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->editColumn('name', function (Warehouse $warehouse) {
                return ucwords($warehouse->name);
            })
            ->editColumn('Project', function (Warehouse $warehouse) {
                return $warehouse->isHeadOffice() ? 'Head Office' : ucwords($warehouse->project?->name);
            })
            ->addColumn('is_active', function (Warehouse $warehouse) {
                return $warehouse->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('created_at', function (Warehouse $warehouse) {
                return $warehouse->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Warehouse $warehouse) {
                if ($this->showTrashed) {
                    return view('warehouse.actions_trashed', ['warehouse' => $warehouse]);
                }

                return view('warehouse.actions', ['warehouse' => $warehouse]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Warehouse $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with('project')->onlyTrashed();   // Show Trashed Records
        }

        return $model->newQuery()->with('project')->withoutTrashed();    // Show Active Records
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('warehouses-table')
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
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('name')->orderable(true)->searchable(true),
            Column::make('code')->orderable(true)->searchable(true),
            Column::make('Project', 'project')->orderable(false)->searchable(false),
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
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Warehouses_'.date('YmdHis');
    }
}
