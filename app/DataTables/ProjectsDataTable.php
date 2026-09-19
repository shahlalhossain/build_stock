<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class ProjectsDataTable extends DataTable
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
            ->editColumn('name', function (Project $project) {
                return ucwords($project->name);
            })
            ->editColumn('start_date', function (Project $project) {
                return $project->start_date?->format('d F, Y');
            })
            ->editColumn('expected_end_date', function (Project $project) {
                return $project->expected_end_date?->format('d F, Y');
            })
            ->editColumn('estimated_budget', function (Project $project) {
                return $project->estimated_budget;
            })
            ->editColumn('Project Manager', function (Project $project) {
                return ucwords($project->manager?->name);
            })
            ->editColumn('current_state', function (Project $project) {
                return ucwords($project->current_state);
            })
            ->addColumn('actions', function (Project $project) {
                if ($this->showTrashed) {
                    return view('project.actions_trashed', ['project' => $project]);
                }

                return view('project.actions', ['project' => $project]);
            });
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Project $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with('manager')->onlyTrashed();   // Show Trashed Records
        }

        return $model->newQuery()->with('manager')->withoutTrashed();    // Show Active Records
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('projects-table')
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
            Column::make('start_date')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::make('expected_end_date')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::make('estimated_budget')->orderable(true)->searchable(false)->addClass('text-end'),
            Column::make('Project Manager', 'manager')->orderable(false)->searchable(false),
            Column::make('current_state')->title('Current State')->orderable(true)->searchable(false)->addClass('text-center'),
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
        return 'Projects_'.date('YmdHis');
    }
}
