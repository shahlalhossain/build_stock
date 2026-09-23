<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\StockTransaction;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class StockTransactionsDataTable extends DataTable
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
            ->editColumn('code', function (StockTransaction $stockTransaction) {
                return $stockTransaction->code;
            })
            ->addColumn('type', function (StockTransaction $stockTransaction) {
                return '<span class="badge bg-info">'.ucwords(str_replace('_', ' ', $stockTransaction->type)).'</span>';
            })
            ->editColumn('store.name', function (StockTransaction $stockTransaction) {
                return ucwords($stockTransaction->store?->name ?? '');
            })
            ->editColumn('supplier.name', function (StockTransaction $stockTransaction) {
                return $stockTransaction->supplier?->name ? ucwords($stockTransaction->supplier->name) : '';
            })
            ->editColumn('transaction_date', function (StockTransaction $stockTransaction) {
                return $stockTransaction->transaction_date?->format('d F, Y');
            })
            ->addColumn('status', function (StockTransaction $stockTransaction) {
                if ($stockTransaction->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($stockTransaction->status).'</span>';
                } elseif ($stockTransaction->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($stockTransaction->status).'</span>';
                } elseif ($stockTransaction->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($stockTransaction->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->editColumn('is_active', function (StockTransaction $stockTransaction) {
                return $stockTransaction->is_active
                    ? '<span class="badge bg-success">'.__('Yes').'</span>'
                    : '<span class="badge bg-warning">'.__('No').'</span>';
            })
            ->addColumn('actions', function (StockTransaction $stockTransaction) {
                if ($this->showTrashed) {
                    return view('stock-transaction.actions_trashed', ['stockTransaction' => $stockTransaction]);
                }

                return view('stock-transaction.actions', ['stockTransaction' => $stockTransaction]);
            })
            ->rawColumns(['type', 'status', 'is_active', 'actions']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(StockTransaction $model): QueryBuilder
    {
        if ($this->showTrashed) {
            return $model->newQuery()->with(['store', 'supplier'])->onlyTrashed();   // Show Trashed Records
        }

        return $model->newQuery()->with(['store', 'supplier'])->withoutTrashed();    // Show Active Records
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-transactions-table')
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
            Column::make('code')->orderable(true)->searchable(true),
            Column::computed('type')->title('Type')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('store.name', 'store')->title('Store')->orderable(false)->searchable(false),
            Column::make('supplier.name', 'supplier')->title('Supplier')->orderable(false)->searchable(false),
            Column::make('transaction_date')->orderable(true)->searchable(false),
            Column::computed('status')->title('Status')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::computed('is_active')->title('Is Active')->orderable(false)->searchable(false)->addClass('text-center'),
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
        return 'StockTransactions_'.date('YmdHis');
    }
}
