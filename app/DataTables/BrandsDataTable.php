<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Html\SearchPane;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class BrandsDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->editColumn('Name', function(Brand $brand) {
                return ucwords($brand->name);
            })
            ->addColumn('is_active', function (Brand $brand) {
                return $brand->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('status', function (Brand $brand) {
                if ($brand->status === 'pending') {
                    return '<span class="badge bg-warning">' . ucwords($brand->status) . '</span>';
                } elseif ($brand->status === 'approved') {
                    return '<span class="badge bg-success">' . ucwords($brand->status) . '</span>';
                } elseif ($brand->status === 'rejected') {
                    return '<span class="badge bg-danger">' . ucwords($brand->status) . '</span>';
                } else {
                    return '<span class="badge bg-secondary">' . ucwords('Unknown') . '</span>';
                }
            })

            ->editColumn('Created By', function(Brand $brand) {
                return ucwords($brand->creator?->name);
            })
            ->editColumn('Updated By', function(Brand $brand) {
                return ucwords($brand->updater?->name);
            })

            ->editColumn('created_at', function(Brand $brand) {
                return $brand->created_at->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function(Brand $brand) {
                return $brand->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Brand $brand) {
                if ($this->showTrashed) {
                    return view('brand.actions_trashed', ['brand' => $brand]);
                }
                return view('brand.actions', ['brand' => $brand]);
            })
            ->rawColumns(['status', 'is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Brand $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->onlyTrashed();   // Show Trashed Records
        }
        return $model->newQuery()->withoutTrashed();    // Show Active Records
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('brands-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->selectStyleSingle()
            ->parameters([
                'serverSide' => true,
                'processing' => true,
                'stateSave'  => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 20, 30, 40, 50, 100, -1], [10, 20, 30, 40, 50, 100, "All"]],
        ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            //Column::make('id')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('name')->orderable(true)->searchable(true),
            Column::computed('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('Created By', 'creator')->orderable(false)->searchable(false),
            Column::make('Updated By', 'updater')->orderable(false)->searchable(false),
            Column::computed('status')->title('Status')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('created_at')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('updated_at')->orderable(true)->searchable(true)->addClass('text-center'),
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
        return 'Brands_' . date('YmdHis');
    }
}
