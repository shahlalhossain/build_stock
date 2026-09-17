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
            ->addColumn('is_active', function (Project $project) {
                return $project->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('status', function (Project $project) {
                if ($project->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($project->status).'</span>';
                } elseif ($project->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($project->status).'</span>';
                } elseif ($project->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($project->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })

            ->editColumn('Created By', function (Project $project) {
                return ucwords($project->creator?->name);
            })
            ->editColumn('Updated By', function (Project $project) {
                return ucwords($project->updater?->name);
            })

            ->editColumn('created_at', function (Project $project) {
                return $project->created_at->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function (Project $project) {
                return $project->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Project $project) {
                if ($this->showTrashed) {
                    return view('project.actions_trashed', ['project' => $project]);
                }

                return view('project.actions', ['project' => $project]);
            })
            ->rawColumns(['status', 'is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Project $model): QueryBuilder
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
        return 'Projects_'.date('YmdHis');
    }
}
