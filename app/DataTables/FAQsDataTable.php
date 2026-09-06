<?php

namespace App\DataTables;

use App\Models\FAQ;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FAQsDataTable extends DataTable
{
    public bool $showTrashed = false;

    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<FAQ> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->editColumn('created_by', function (FAQ $faq) {
                return $faq->creator?->name;
            })
            ->editColumn('updated_by', function (FAQ $faq) {
                return $faq->updater?->name;
            })
            ->addColumn('is_active', function (FAQ $faq) {
                return $faq->is_active
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('actions', function (FAQ $faq) {
                if ($this->showTrashed) {
                    return view('faq.actions_trashed', ['faq' => $faq]);
                }
                return view('faq.actions', ['faq' => $faq]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<FAQ>
     */
    public function query(FAQ $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['creator', 'updater']);
        return $this->showTrashed ? $query->onlyTrashed() : $query->withoutTrashed();
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('faqs-table')
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
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('question')->title(__('Question'))->orderable(true)->searchable(true),
            Column::make('language')->title(__('Language'))->orderable(true)->searchable(true),
            Column::computed('is_active')->title('Active')->orderable(true)->searchable(true)->addClass('text-center'),
            Column::make('created_by')->title(__('Creator'))->orderable(false)->searchable(false),
            Column::make('updated_by')->title(__('Updater'))->orderable(false)->searchable(false),
            Column::computed('actions')->orderable(false)->searchable(false)->exportable(false)->printable(false)->addClass('text-center'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'FAQs_' . date('YmdHis');
    }
}
