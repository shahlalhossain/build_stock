<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\ProductPurchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProductPurchasesDataTable extends DataTable
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
            ->editColumn('code', function (ProductPurchase $productPurchase) {
                return $productPurchase->code;
            })
            ->editColumn('store.name', function (ProductPurchase $productPurchase) {
                return ucwords($productPurchase->store?->name ?? '');
            })
            ->editColumn('supplier.name', function (ProductPurchase $productPurchase) {
                return $productPurchase->supplier?->name ? ucwords($productPurchase->supplier->name) : '';
            })
            ->editColumn('transaction_date', function (ProductPurchase $productPurchase) {
                return $productPurchase->transaction_date?->format('d F, Y');
            })
            ->editColumn('net_amount', function (ProductPurchase $productPurchase) {
                return $productPurchase->net_amount !== null ? number_format((float) $productPurchase->net_amount, 2) : '';
            })
            ->addColumn('status', function (ProductPurchase $productPurchase) {
                if ($productPurchase->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($productPurchase->status).'</span>';
                } elseif ($productPurchase->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($productPurchase->status).'</span>';
                } elseif ($productPurchase->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($productPurchase->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->addColumn('actions', function (ProductPurchase $productPurchase) {
                if ($this->showTrashed) {
                    return view('product-purchase.actions_trashed', ['productPurchase' => $productPurchase]);
                }

                return view('product-purchase.actions', ['productPurchase' => $productPurchase]);
            })
            ->rawColumns(['status', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductPurchase $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['store', 'supplier'])->onlyTrashed();
        }

        return $model->newQuery()->with(['store', 'supplier'])->withoutTrashed();
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-purchases-table')
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
            Column::make('code')->orderable(true)->searchable(true),
            Column::make('store.name', 'store')->title('Store')->orderable(false)->searchable(false),
            Column::make('supplier.name', 'supplier')->title('Supplier')->orderable(false)->searchable(false),
            Column::make('transaction_date')->orderable(true)->searchable(false),
            Column::computed('net_amount')->title('Net Amount')->orderable(false)->searchable(false)->addClass('text-end'),
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
        return 'ProductPurchases_'.date('YmdHis');
    }
}
