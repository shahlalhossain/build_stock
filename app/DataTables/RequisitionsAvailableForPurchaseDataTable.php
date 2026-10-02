<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\ProductRequisition;
use App\Services\StoreAccessService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class RequisitionsAvailableForPurchaseDataTable extends DataTable
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
                    $remaining = number_format($item->remaining_quantity, 2);
                    $requisitioned = number_format((float) $item->quantity, 2);
                    $unit = $item->unit?->symbol ?? '';

                    return e(trim("{$label}: {$remaining}/{$requisitioned} {$unit} Remaining"));
                });

                return $lines->implode('<br>');
            })
            ->addColumn('fulfilment', function (ProductRequisition $productRequisition) {
                $hasAnyPurchased = $productRequisition->items->contains(fn ($item) => $item->remaining_quantity < (float) $item->quantity);

                return $hasAnyPurchased
                    ? '<span class="badge bg-info">'.__('Partially Purchased').'</span>'
                    : '<span class="badge bg-secondary">'.__('Not Purchased Yet').'</span>';
            })
            ->addColumn('actions', function (ProductRequisition $productRequisition) {
                return view('product-purchase.requisition-actions', ['productRequisition' => $productRequisition]);
            })
            ->rawColumns(['products', 'fulfilment', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductRequisition $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->availableForPurchase()
            ->with(['store', 'items.product', 'items.productVariant.attributeValues', 'items.unit', 'items.purchaseItems.productPurchase']);

        return app(StoreAccessService::class)->scopeQueryToVisibleStores($query, Auth::user());
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('requisitions-available-for-purchase-table')
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
            Column::computed('products')->title('Products (Remaining/Requisitioned)')->orderable(false)->searchable(false),
            Column::make('transaction_date')->title('Requisition Date')->orderable(true)->searchable(false),
            Column::computed('store_name')->title('Store/Warehouse Name')->orderable(false)->searchable(false),
            Column::computed('fulfilment')->title('Fulfilment')->orderable(false)->searchable(false)->addClass('text-center'),
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
        return 'RequisitionsAvailableForPurchase_'.date('YmdHis');
    }
}
