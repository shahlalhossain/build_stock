<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Attribute;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class AttributesDataTable extends DataTable
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
            ->editColumn('name', function (Attribute $attribute) {
                return ucwords($attribute->name);
            })
            ->addColumn('values_count', function (Attribute $attribute) {
                return $attribute->values()->count();
            })
            ->addColumn('is_active', function (Attribute $attribute) {
                return $attribute->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('Created By', function (Attribute $attribute) {
                return ucwords($attribute->creator?->name);
            })
            ->editColumn('Updated By', function (Attribute $attribute) {
                return ucwords($attribute->updater?->name);
            })
            ->editColumn('created_at', function (Attribute $attribute) {
                return $attribute->created_at->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function (Attribute $attribute) {
                return $attribute->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Attribute $attribute) {
                if ($this->showTrashed) {
                    return view('attribute.actions_trashed', ['attribute' => $attribute]);
                }

                return view('attribute.actions', ['attribute' => $attribute]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Attribute $model): QueryBuilder
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
            ->setTableId('attributes-table')
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
            Column::computed('values_count')->title('Values')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::computed('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('Created By', 'creator')->orderable(false)->searchable(false),
            Column::make('Updated By', 'updater')->orderable(false)->searchable(false),
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
        return 'Attributes_'.date('YmdHis');
    }
}
