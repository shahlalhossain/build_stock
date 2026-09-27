<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\ProductDelivery;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProductDeliveriesDataTable extends DataTable
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
            ->editColumn('code', function (ProductDelivery $productDelivery) {
                return $productDelivery->code;
            })
            ->editColumn('store.name', function (ProductDelivery $productDelivery) {
                return ucwords($productDelivery->store?->name ?? '');
            })
            ->editColumn('delivered_to', function (ProductDelivery $productDelivery) {
                return $productDelivery->delivered_to ?? '';
            })
            ->editColumn('transaction_date', function (ProductDelivery $productDelivery) {
                return $productDelivery->transaction_date?->format('d F, Y');
            })
            ->addColumn('status', function (ProductDelivery $productDelivery) {
                if ($productDelivery->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($productDelivery->status).'</span>';
                } elseif ($productDelivery->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($productDelivery->status).'</span>';
                } elseif ($productDelivery->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($productDelivery->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->editColumn('is_active', function (ProductDelivery $productDelivery) {
                return $productDelivery->is_active
                    ? '<span class="badge bg-success">'.__('Yes').'</span>'
                    : '<span class="badge bg-warning">'.__('No').'</span>';
            })
            ->addColumn('actions', function (ProductDelivery $productDelivery) {
                if ($this->showTrashed) {
                    return view('product-delivery.actions_trashed', ['productDelivery' => $productDelivery]);
                }

                return view('product-delivery.actions', ['productDelivery' => $productDelivery]);
            })
            ->rawColumns(['status', 'is_active', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductDelivery $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['store'])->onlyTrashed();
        }

        return $model->newQuery()->with(['store'])->withoutTrashed();
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-deliveries-table')
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
            Column::computed('delivered_to')->title('Delivered To')->orderable(false)->searchable(true),
            Column::make('transaction_date')->orderable(true)->searchable(false),
            Column::computed('status')->title('Status')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::computed('is_active')->title('Is Active')->orderable(false)->searchable(false)->addClass('text-center'),
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
        return 'ProductDeliveries_'.date('YmdHis');
    }
}
