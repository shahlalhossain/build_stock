<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\ProductTransfer;
use App\Services\StoreAccessService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProductTransfersDataTable extends DataTable
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
            ->addColumn('products', function (ProductTransfer $productTransfer) {
                $lines = $productTransfer->items->map(function ($item) {
                    $productName = $item->product?->name ?? '';
                    $variantLabel = $item->productVariant ? ($item->productVariant->variant_name ?: $item->productVariant->attributeValues->pluck('value')->implode(' / ')) : '';
                    $label = $variantLabel !== '' ? "{$productName} ({$variantLabel})" : $productName;
                    $quantity = number_format((float) $item->quantity, 2);
                    $unit = $item->unit?->symbol ?? '';

                    return e(trim("{$label} {$quantity} {$unit}"));
                });

                return $lines->implode('<br>');
            })
            ->editColumn('sourceStore.name', function (ProductTransfer $productTransfer) {
                return ucwords($productTransfer->sourceStore?->name ?? '');
            })
            ->editColumn('destinationStore.name', function (ProductTransfer $productTransfer) {
                return ucwords($productTransfer->destinationStore?->name ?? '');
            })
            ->editColumn('transaction_date', function (ProductTransfer $productTransfer) {
                return $productTransfer->transaction_date?->format('d F, Y');
            })
            ->addColumn('status', function (ProductTransfer $productTransfer) {
                if ($productTransfer->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($productTransfer->status).'</span>';
                } elseif ($productTransfer->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($productTransfer->status).'</span>';
                } elseif ($productTransfer->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($productTransfer->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->addColumn('actions', function (ProductTransfer $productTransfer) {
                if ($this->showTrashed) {
                    return view('product-transfer.actions_trashed', ['productTransfer' => $productTransfer]);
                }

                return view('product-transfer.actions', ['productTransfer' => $productTransfer]);
            })
            ->rawColumns(['products', 'status', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductTransfer $model): QueryBuilder
    {
        $itemRelations = [
            'items.product',
            'items.productVariant.attributeValues',
            'items.unit',
        ];

        $query = $this->showTrashed
            ? $model->newQuery()->with(array_merge(['sourceStore', 'destinationStore'], $itemRelations))->withSum('items', 'quantity')->onlyTrashed()
            : $model->newQuery()->with(array_merge(['sourceStore', 'destinationStore'], $itemRelations))->withSum('items', 'quantity')->withoutTrashed();

        return app(StoreAccessService::class)->scopeQueryToVisibleStores($query, Auth::user(), ['source_store_id', 'destination_store_id']);
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-transfers-table')
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
            Column::make('sourceStore.name', 'sourceStore')->title('Source Store')->orderable(false)->searchable(false),
            Column::make('destinationStore.name', 'destinationStore')->title('Destination Store')->orderable(false)->searchable(false),
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
        return 'ProductTransfers_'.date('YmdHis');
    }
}
