<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\ProductRequisition;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProductRequisitionsDataTable extends DataTable
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
            ->editColumn('store.name', function (ProductRequisition $productRequisition) {
                return ucwords($productRequisition->store?->name ?? '');
            })
            ->addColumn('store_location', function (ProductRequisition $productRequisition) {
                $store = $productRequisition->store;

                if (!$store) {
                    return '';
                }

                return $store->project_id ? ucwords($store->project?->name ?? '') : 'Head Office';
            })
            ->editColumn('transaction_date', function (ProductRequisition $productRequisition) {
                return $productRequisition->transaction_date?->format('d F, Y');
            })
            ->addColumn('status', function (ProductRequisition $productRequisition) {
                if ($productRequisition->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($productRequisition->status).'</span>';
                } elseif ($productRequisition->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($productRequisition->status).'</span>';
                } elseif ($productRequisition->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($productRequisition->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->addColumn('actions', function (ProductRequisition $productRequisition) {
                if ($this->showTrashed) {
                    return view('product-requisition.actions_trashed', ['productRequisition' => $productRequisition]);
                }

                return view('product-requisition.actions', ['productRequisition' => $productRequisition]);
            })
            ->rawColumns(['status', 'is_active', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductRequisition $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['store.project'])->withCount('items')->onlyTrashed();
        }

        return $model->newQuery()->with(['store.project'])->withCount('items')->withoutTrashed();
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-requisitions-table')
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
            Column::make('store.name', 'store')->title('Store/Warehouse')->orderable(false)->searchable(false),
            Column::computed('store_location')->title('Location')->orderable(false)->searchable(false),
            Column::make('transaction_date')->orderable(true)->searchable(false),
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
        return 'ProductRequisitions_'.date('YmdHis');
    }
}
