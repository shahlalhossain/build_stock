<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Permission;
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
class PermissionsDataTable extends DataTable
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
            ->editColumn('type', function (Permission $permission) {
                return ucwords($permission->type);
            })
            ->addColumn('user_count', function (Permission $permission) {
                return $permission->users_count;
            })
            ->editColumn('created_at', function(Permission $permission) {
                return $permission->created_at->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function(Permission $permission) {
                return $permission->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (Permission $permission) {
                if ($this->showTrashed) {
                    return view('permission.actions_trashed', ['permission' => $permission]);
                }
                return view('permission.actions', ['permission' => $permission]);
            });
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Permission $model): QueryBuilder
    {
//        if ($this->showTrashed) {
//            return $model->newQuery()->withCount('users')->orderBy('id')->where('id', '!=', 1)->onlyTrashed();   // Show Trashed Records
//        }
//        return $model->newQuery()->withCount('users')->orderBy('id')->where('id', '!=', 1)->withoutTrashed();    // Show Active Records

        $query = $model->newQuery()->withCount(['users'])->orderBy('id')->where('id', '!=', 1);
        return $this->showTrashed ? $query->onlyTrashed() : $query->withoutTrashed();
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('permissions-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2, 'asc')
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
            Column::make('description')->orderable(true)->searchable(true),
            Column::make('type')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('users_count')->title('User Count')->orderable(true)->searchable(false)->addClass('text-center'),
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
        return 'Permissions_' . date('YmdHis');
    }
}
