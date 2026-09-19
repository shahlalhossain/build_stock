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
            ->editColumn('Location', function (Store $store) {
                return $store->isHeadOffice() ? 'Head Office' : ucwords($store->project?->name);
            })
            ->addColumn('type', function (Store $store) {
                return $store->isWarehouse()
                    ? '<span class="badge bg-warning">'.__('Warehouse').'</span>'
                    : '<span class="badge bg-info">'.__('Store').'</span>';
            })
            ->editColumn('Storekeeper', function (Store $store) {
                return ucwords($store->storekeeper?->name);
            })
            ->editColumn('mobile', function (Store $store) {
                return $store->mobile;
            })
            ->editColumn('status', function (Store $store) {
                if ($store->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($store->status).'</span>';
                } elseif ($store->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($store->status).'</span>';
                } elseif ($store->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($store->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->addColumn('actions', function (Store $store) {
                if ($this->showTrashed) {
                    return view('store.actions_trashed', ['store' => $store]);
                }

                return view('store.actions', ['store' => $store]);
            })
            ->rawColumns(['type', 'status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Store $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['project', 'storekeeper'])->onlyTrashed();   // Show Trashed Records
        }

        return $model->newQuery()->with(['project', 'storekeeper'])->withoutTrashed();    // Show Active Records
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
            Column::make('Location', 'project')->orderable(false)->searchable(false),
            Column::computed('type')->title('Type')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('Storekeeper', 'storekeeper')->orderable(false)->searchable(false),
            Column::make('mobile')->orderable(true)->searchable(true),
            Column::computed('status')->title('Status')->orderable(false)->searchable(false)->addClass('text-center'),
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
