<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\UnitConversion;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class UnitConversionsDataTable extends DataTable
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
            ->editColumn('product', function (UnitConversion $unitConversion) {
                return $unitConversion->product?->name;
            })
            ->editColumn('unit', function (UnitConversion $unitConversion) {
                return $unitConversion->unit?->name.($unitConversion->unit?->symbol ? ' ('.$unitConversion->unit->symbol.')' : '');
            })
            ->editColumn('base_unit', function (UnitConversion $unitConversion) {
                return $unitConversion->product?->unit?->name;
            })
            ->editColumn('factor_to_base', function (UnitConversion $unitConversion) {
                return '1 '.$unitConversion->unit?->symbol.' = '.rtrim(rtrim(number_format((float) $unitConversion->factor_to_base, 6), '0'), '.').' '.$unitConversion->product?->unit?->symbol;
            })
            ->addColumn('is_active', function (UnitConversion $unitConversion) {
                return $unitConversion->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('Created By', function (UnitConversion $unitConversion) {
                return ucwords($unitConversion->creator?->name);
            })
            ->editColumn('created_at', function (UnitConversion $unitConversion) {
                return $unitConversion->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (UnitConversion $unitConversion) {
                if ($this->showTrashed) {
                    return view('unit_conversion.actions_trashed', ['unitConversion' => $unitConversion]);
                }

                return view('unit_conversion.actions', ['unitConversion' => $unitConversion]);
            })
            ->rawColumns(['is_active']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(UnitConversion $model): QueryBuilder
    {
        $query = $this->showTrashed
            ? $model->newQuery()->onlyTrashed()
            : $model->newQuery()->withoutTrashed();

        return $query->with(['product.unit', 'unit', 'creator']);
    }

    /**
     * Optional method if you want to use the HTML builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('unit-conversions-table')
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
            Column::make('product', 'product.name')->title('Product')->orderable(false)->searchable(true),
            Column::make('unit', 'unit.name')->title('Transaction Unit')->orderable(false)->searchable(true),
            Column::computed('base_unit')->title('Base Unit')->orderable(false)->searchable(false),
            Column::computed('factor_to_base')->title('Conversion')->orderable(false)->searchable(false),
            Column::computed('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('Created By', 'creator')->orderable(false)->searchable(false),
            Column::make('created_at')->orderable(true)->searchable(true)->addClass('text-center'),
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
        return 'UnitConversions_'.date('YmdHis');
    }
}
