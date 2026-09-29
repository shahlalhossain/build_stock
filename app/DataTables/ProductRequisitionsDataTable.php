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
            ->addColumn('store_location', function (ProductRequisition $productRequisition) {
                $store = $productRequisition->store;
                if (! $store) {
                    return '';
                }
                $badgeClass = $store->type === 'warehouse' ? 'bg-info' : 'bg-primary';
                $location = $store->project_id ? 'Project-Site' : 'Head-Office';

                return $location.' <span class="badge '.$badgeClass.'">'.ucwords($store->type).'</span>';
            })
            ->addColumn('store_name', function (ProductRequisition $productRequisition) {
                return ucwords($productRequisition->store?->name ?? '');
            })
            ->editColumn('transaction_date', function (ProductRequisition $productRequisition) {
                return $productRequisition->transaction_date?->format('d F, Y');
            })
            ->addColumn('products', function (ProductRequisition $productRequisition) {
                $lines = $productRequisition->items->map(function ($item) {
                    $productName = $item->product?->name ?? '';
                    $variantLabel = $item->productVariant ? ($item->productVariant->variant_name ?: $item->productVariant->attributeValues->pluck('value')->implode(' / ')) : '';
                    $label = $variantLabel !== '' ? "{$productName} ({$variantLabel})" : $productName;
                    $quantity = number_format((float) $item->quantity, 2);
                    $unit = $item->unit?->symbol ?? '';

                    return e(trim("{$label} {$quantity} {$unit}"));
                });

                return $lines->implode('<br>');
            })
            ->addColumn('total_quantity', function (ProductRequisition $productRequisition) {
                return $productRequisition->items_sum_quantity ?? 0;
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
            ->rawColumns(['store_location', 'products', 'status', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductRequisition $model): QueryBuilder
    {
        $itemRelations = [
            'items.product',
            'items.productVariant.attributeValues',
            'items.unit',
        ];

        if ($this->showTrashed) {
            return $model->newQuery()->with(array_merge(['store'], $itemRelations))->withSum('items', 'quantity')->onlyTrashed();
        }

        return $model->newQuery()->with(array_merge(['store'], $itemRelations))->withSum('items', 'quantity')->withoutTrashed();
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
            Column::computed('products')->title('Products')->orderable(false)->searchable(false),
            Column::computed('total_quantity')->title('Total Quantity')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('transaction_date')->title('Requisition Date')->orderable(true)->searchable(false),
            Column::computed('store_location')->title('Store/Warehouse Location')->orderable(false)->searchable(false),
            Column::computed('store_name')->title('Store/Warehouse Name')->orderable(false)->searchable(false),
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
