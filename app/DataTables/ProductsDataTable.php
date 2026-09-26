<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProductsDataTable extends DataTable
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
            ->editColumn('name', function (Product $product) {
                return ucwords($product->name);
            })
            ->addColumn('variant_count', function (Product $product) {
                return $product->variants_count;
            })
            ->editColumn('Category', function (Product $product) {
                return $product->category?->name;
            })
            ->editColumn('Brand', function (Product $product) {
                return $product->brand?->name ?? '--';
            })
            ->addColumn('status', function (Product $product) {
                $map = [
                    'pending' => 'bg-warning',
                    'approved' => 'bg-success',
                    'rejected' => 'bg-danger',
                ];

                $class = $map[$product->status] ?? 'bg-secondary';

                return '<span class="badge '.$class.'">'.ucfirst($product->status ?? '--').'</span>';
            })
            ->addColumn('actions', function (Product $product) {
                if ($this->showTrashed) {
                    return view('product.actions_trashed', ['product' => $product]);
                }

                return view('product.actions', ['product' => $product]);
            })
            ->rawColumns(['status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['category', 'brand'])->withCount('variants')->onlyTrashed();   // Show Trashed Records
        }

        return $model->newQuery()->with(['category', 'brand'])->withCount('variants')->withoutTrashed();    // Show Active Records
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('products-table')
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
            Column::make('name')->title('Name')->orderable(true)->searchable(true),
            Column::make('sku')->title('SKU')->orderable(true)->searchable(true),
            Column::computed('variant_count')->title('Variant')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('Category', 'category')->orderable(false)->searchable(false),
            Column::make('Brand', 'brand')->orderable(false)->searchable(false),
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
        return 'Products_'.date('YmdHis');
    }
}
