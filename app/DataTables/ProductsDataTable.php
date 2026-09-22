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
            ->editColumn('Category', function (Product $product) {
                return $product->category?->name;
            })
            ->editColumn('Brand', function (Product $product) {
                return $product->brand?->name ?? '--';
            })
            ->editColumn('Unit', function (Product $product) {
                return $product->unit?->symbol ?? '--';
            })
            ->addColumn('is_active', function (Product $product) {
                return $product->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('created_at', function (Product $product) {
                return $product->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Product $product) {
                if ($this->showTrashed) {
                    return view('product.actions_trashed', ['product' => $product]);
                }

                return view('product.actions', ['product' => $product]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Product $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['category', 'brand', 'unit'])->onlyTrashed();   // Show Trashed Records
        }

        return $model->newQuery()->with(['category', 'brand', 'unit'])->withoutTrashed();    // Show Active Records
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
            Column::make('name')->orderable(true)->searchable(true),
            Column::make('code')->orderable(true)->searchable(true),
            Column::make('sku')->orderable(true)->searchable(true),
            Column::make('Category', 'category')->orderable(false)->searchable(false),
            Column::make('Brand', 'brand')->orderable(false)->searchable(false),
            Column::make('Unit', 'unit')->orderable(false)->searchable(false)->addClass('text-center'),
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
        return 'Products_'.date('YmdHis');
    }
}
