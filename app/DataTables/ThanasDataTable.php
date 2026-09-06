<?php

namespace App\DataTables;

use App\Models\GeoThana;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ThanasDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<GeoThana> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->editColumn('Name', function(GeoThana $thana) {
                return ucwords($thana->name_en);
            })
            ->editColumn('Name (in Bangla)', function(GeoThana $thana) {
                return ucwords($thana->name_bn);
            })
            ->editColumn('Created By', function(GeoThana $thana) {
                return ucwords($thana->creator?->name);
            })
            ->editColumn('Updated By', function(GeoThana $thana) {
                return ucwords($thana->updater?->name);
            })
            ->editColumn('created_at', function(GeoThana $thana) {
                return $thana->created_at->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function(GeoThana $thana) {
                return $thana->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function(GeoThana $thana) {
                return view('thana.actions', ['thana' => $thana]);
            });
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<GeoThana>
     */
    public function query(GeoThana $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('thanas-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc') // Set the Default Order to the First Column (index 0)
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
            Column::make('id')->orderable(true)->searchable(true),
            Column::make('Name', 'name_en')->orderable(true)->searchable(true),
            Column::make('Name (in Bangla)', 'name_bn')->orderable(true)->searchable(true),
            Column::make('Created By', 'creator')->orderable(true)->searchable(true),
            Column::make('Updated By', 'updater')->orderable(true)->searchable(true),
            Column::make('created_at')->orderable(true)->searchable(true),
            Column::make('updated_at')->orderable(true)->searchable(true),
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
        return 'Thanas_' . date('YmdHis');
    }
}
