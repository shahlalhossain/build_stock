<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class SuppliersDataTable extends DataTable
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
            ->editColumn('name', function (Supplier $supplier) {
                return ucwords($supplier->name);
            })
            ->editColumn('tin_number', function (Supplier $supplier) {
                return $supplier->tin_number;
            })
            ->editColumn('bin_number', function (Supplier $supplier) {
                return $supplier->bin_number;
            })
            ->editColumn('payment_terms_days', function (Supplier $supplier) {
                return $supplier->payment_terms_days !== null ? $supplier->payment_terms_days.' Days' : null;
            })
            ->editColumn('lead_time_days', function (Supplier $supplier) {
                return $supplier->lead_time_days !== null ? $supplier->lead_time_days.' Days' : null;
            })
            ->editColumn('credit_limit', function (Supplier $supplier) {
                return $supplier->credit_limit !== null ? $supplier->credit_limit.' BDT' : null;
            })
            ->editColumn('minimum_order_amount', function (Supplier $supplier) {
                return $supplier->minimum_order_amount !== null ? $supplier->minimum_order_amount.' BDT' : null;
            })

            ->editColumn('status', function (Supplier $supplier) {
                if ($supplier->status === 'pending') {
                    return '<span class="badge bg-warning">'.ucwords($supplier->status).'</span>';
                } elseif ($supplier->status === 'approved') {
                    return '<span class="badge bg-success">'.ucwords($supplier->status).'</span>';
                } elseif ($supplier->status === 'rejected') {
                    return '<span class="badge bg-danger">'.ucwords($supplier->status).'</span>';
                } else {
                    return '<span class="badge bg-secondary">'.ucwords('Unknown').'</span>';
                }
            })
            ->addColumn('actions', function (Supplier $supplier) {
                if ($this->showTrashed) {
                    return view('supplier.actions_trashed', ['supplier' => $supplier]);
                }

                return view('supplier.actions', ['supplier' => $supplier]);
            })
            ->rawColumns(['status']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Supplier $model): QueryBuilder
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
            ->setTableId('suppliers-table')
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
            Column::make('tin_number')->title('TIN Number')->orderable(true)->searchable(true),
            Column::make('bin_number')->title('BIN Number')->orderable(true)->searchable(true),
            Column::make('payment_terms_days')->title('Payment Terms')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::make('lead_time_days')->title('Lead Time')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::make('credit_limit')->title('Credit Limit')->orderable(true)->searchable(false)->addClass('text-end'),
            Column::make('minimum_order_amount')->title('Min. Order Amount')->orderable(true)->searchable(false)->addClass('text-end'),
            Column::computed('status')->title('Status')->orderable(false)->searchable(false)->addClass('text-center'),
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
        return 'Suppliers_'.date('YmdHis');
    }
}
