<?php

namespace App\DataTables;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RolesDataTable extends DataTable
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
            ->editColumn('name', function (Role $role) {
                return ucwords($role->name);
            })
            ->addColumn('permissions', function (Role $role) {
                return $role->permissions_count;
            })
            ->addColumn('users', function (Role $role) {
                return $role->users_count;
            })
            ->editColumn('type', function (Role $role) {
                return ucwords($role->type);
            })
            ->editColumn('guard_name', function (Role $role) {
                return ucwords($role->guard_name);
            })
            ->editColumn('created_at', function (Role $role) {
                return $role->created_at->format('Y-m-d H:i:s');
            })
            ->editColumn('updated_at', function (Role $role) {
                return $role->updated_at->format('Y-m-d H:i:s');
            })
            ->addColumn('actions', function (Role $role) {
                if ($this->showTrashed) {
                    return view('role.actions_trashed', ['role' => $role]);
                } else {
                    return view('role.actions', ['role' => $role]);
                }
            });
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Role $model): QueryBuilder
    {
//        if ($this->showTrashed) {
//            return $model->newQuery()->withCount(['permissions', 'users'])->orderBy('id')->where('id', '!=', 1)->onlyTrashed();   // Show Trashed Records
//        }
//        return $model->newQuery()->withCount(['permissions', 'users'])->orderBy('id')->where('id', '!=',1)->withoutTrashed();    // Show Active Records

        $query = $model->newQuery()->withCount(['permissions', 'users'])->orderBy('id')->where('id', '!=', 1);
        return $this->showTrashed ? $query->onlyTrashed() : $query->withoutTrashed();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('roles-table')
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
            Column::computed('users')->sortable(false)->searchable(false)->addClass('text-center'),
            Column::computed('permissions')->sortable(false)->searchable(false)->addClass('text-center'),
            Column::make('type')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('guard_name')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('created_at')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('updated_at')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::computed('actions')->orderable(false)->searchable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Roles_' . date('YmdHis');
    }
}
