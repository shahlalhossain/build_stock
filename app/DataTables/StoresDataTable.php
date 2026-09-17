<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class StoresDataTable extends DataTable
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
            ->editColumn('name', function (Store $store) {
                return ucwords($store->name);
            })
            ->editColumn('Project', function (Store $store) {
                return $store->isHeadOffice() ? 'Head Office' : ucwords($store->project?->name);
            })
            ->addColumn('is_active', function (Store $store) {
                return $store->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('created_at', function (Store $store) {
                return $store->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Store $store) {
                if ($this->showTrashed) {
                    return view('store.actions_trashed', ['store' => $store]);
                }

                return view('store.actions', ['store' => $store]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Store $model): QueryBuilder
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
            ->setTableId('stores-table')
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
        return 'Stores_'.date('YmdHis');
    }
}
