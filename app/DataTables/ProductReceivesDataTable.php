<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\ProductReceive;
use App\Services\StoreAccessService;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProductReceivesDataTable extends DataTable
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
            ->editColumn('code', function (ProductReceive $productReceive) {
                return $productReceive->code;
            })
            ->editColumn('transfer.code', function (ProductReceive $productReceive) {
                return $productReceive->transfer?->code ?? '';
            })
            ->editColumn('transfer.destinationStore.name', function (ProductReceive $productReceive) {
                return ucwords($productReceive->transfer?->destinationStore?->name ?? '');
            })
            ->editColumn('transaction_date', function (ProductReceive $productReceive) {
                return $productReceive->transaction_date?->format('d F, Y');
            })
            ->addColumn('products', function (ProductReceive $productReceive) {
                $lines = $productReceive->items->map(function ($item) {
                    $productName = $item->product?->name ?? '';
                    $variantLabel = $item->productVariant ? ($item->productVariant->variant_name ?: $item->productVariant->attributeValues->pluck('value')->implode(' / ')) : '';
                    $label = $variantLabel !== '' ? "{$productName} ({$variantLabel})" : $productName;
                    $quantity = number_format((float) $item->received_quantity, 2);
                    $unit = $item->unit?->symbol ?? '';

                    return e(trim("{$label} {$quantity} {$unit}"));
                });

                return $lines->implode('<br>');
            })
            ->addColumn('total_quantity', function (ProductReceive $productReceive) {
                return $productReceive->items_sum_received_quantity ?? 0;
            })
            ->addColumn('status', function (ProductReceive $productReceive) {
                if ($productReceive->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($productReceive->status).'</span>';
                } elseif ($productReceive->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($productReceive->status).'</span>';
                } elseif ($productReceive->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($productReceive->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->editColumn('is_active', function (ProductReceive $productReceive) {
                return $productReceive->is_active
                    ? '<span class="badge bg-success">'.__('Yes').'</span>'
                    : '<span class="badge bg-warning">'.__('No').'</span>';
            })
            ->addColumn('actions', function (ProductReceive $productReceive) {
                if ($this->showTrashed) {
                    return view('product-receive.actions_trashed', ['productReceive' => $productReceive]);
                }

                return view('product-receive.actions', ['productReceive' => $productReceive]);
            })
            ->rawColumns(['products', 'status', 'is_active', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ProductReceive $model): QueryBuilder
    {
        $itemRelations = [
            'items.product',
            'items.productVariant.attributeValues',
            'items.unit',
        ];

        $query = $this->showTrashed
            ? $model->newQuery()->with(array_merge(['transfer.destinationStore'], $itemRelations))->withSum('items', 'received_quantity')->onlyTrashed()
            : $model->newQuery()->with(array_merge(['transfer.destinationStore'], $itemRelations))->withSum('items', 'received_quantity')->withoutTrashed();

        return app(StoreAccessService::class)->scopeQueryToVisibleStoresViaRelation($query, Auth::user(), 'transfer', ['source_store_id', 'destination_store_id']);
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('product-receives-table')
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
            Column::computed('products')->title('Products')->orderable(false)->searchable(false),
            Column::computed('total_quantity')->title('Total Quantity')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('transfer.code', 'transfer')->title('Transfer')->orderable(false)->searchable(false),
            Column::make('transfer.destinationStore.name', 'destinationStore')->title('Destination Store')->orderable(false)->searchable(false),
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
        return 'ProductReceives_'.date('YmdHis');
    }
}
