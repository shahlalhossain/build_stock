<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    public bool $showTrashed = false;

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
            ->editColumn('created_at', function(User $user) {
                return $user->created_at->format('Y-m-d H:i');
            })
            ->editColumn('updated_at', function(User $user) {
                return $user->created_at->format('Y-m-d H:i');
            })
            ->addColumn('is_active', function (User $user) {
                return $user->is_active
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('actions', function (User $user) {
                if ($this->showTrashed) {
                    return view('user.actions_trashed', ['user' => $user]);
                }
                return view('user.actions', ['user' => $user]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        $query = $model->newQuery()->where('id', '!=', 1);

        if ($this->showTrashed) {
            return $query->onlyTrashed();
        }

        return $query->withoutTrashed();
    }

    /**
     * Optional method if you want to use the HTML Builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(2, 'asc') // Set the Default Order to the First Column (index 0)
            ->selectStyleSingle()
            ->parameters([
                'serverSide' => true,
                'processing' => true,
                'stateSave'  => false,
                'pageLength' => 10,
                'lengthMenu' => [[10, 20, 30, 40, 50, 100, -1], [10, 20, 30, 40, 50, 100, "All"]],
                'columnDefs' => [
                    [
                        'targets'       => 0,
                        'orderable'     => false,
                        'searchable'    => false,
                    ],
                ],
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
            Column::make('username')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('mobile')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('email')->orderable(true)->searchable(true),
            Column::make('created_at')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('updated_at')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::computed('is_active')->title('Active')->orderable(true)->searchable(true)->addClass('text-center'),
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
        return 'Users_' . date('YmdHis');
    }
}
