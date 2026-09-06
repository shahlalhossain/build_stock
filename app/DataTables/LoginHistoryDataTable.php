<?php

namespace App\DataTables;

use App\Models\LoginActivity;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LoginHistoryDataTable extends DataTable
{
    protected string $type = 'all';
    public function __construct(string $type = 'all')
    {
        $this->type = $type;
    }

    /**
     * Build DataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->addColumn('is_active', function (LoginActivity $loginActivity) {
                return $loginActivity->is_active
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('login_at', function (LoginActivity $loginActivity) {
                return $loginActivity->login_at
                    ? $loginActivity->login_at->format('Y-m-d H:i')
                    : '-';
            })
            ->editColumn('logout_at', function (LoginActivity $loginActivity) {
                return $loginActivity->logout_at
                    ? $loginActivity->logout_at->format('Y-m-d H:i')
                    : '-';
            })
            ->addColumn('actions', function (LoginActivity $loginActivity) {
                return view('activity.actions', ['loginActivity' => $loginActivity]);
            })
            ->rawColumns(['is_active', 'actions']);
    }

    /**
     * Query source
     */
    public function query(LoginActivity $model): QueryBuilder
    {
        $query = $model->newQuery()->with('user');

        if ($this->type === 'active') {
            $query->where('is_active', 1);
        } elseif ($this->type === 'expired') {
            $query->where('is_active', 0);
        } elseif ($this->type === 'my') {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    /**
     * Html builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('login-history-table')
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
     * Columns
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('user.name')->title('User')->orderable(true)->searchable(true),
            Column::make('ip_address')->title('IP Address'),
            Column::make('os')->title('OS')->orderable(true)->searchable(true),
            Column::make('browser')->title('Browser')->orderable(true)->searchable(true),
            Column::make('device')->title('Device')->orderable(true)->searchable(true),
            Column::computed('is_active')->title('Active')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('login_at')->title('Login At')->orderable(true)->searchable(true),
            Column::make('logout_at')->title('Logout At')->orderable(true)->searchable(true),
            Column::computed('actions')->orderable(false)->searchable(false)->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'LoginHistory_' . date('YmdHis');
    }
}
