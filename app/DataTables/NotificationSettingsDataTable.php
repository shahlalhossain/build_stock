<?php

namespace App\DataTables;

use AllowDynamicProperties;
use App\Models\NotificationSetting;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

#[AllowDynamicProperties]
class NotificationSettingsDataTable extends DataTable
{
    /**
     * Builds the table rows: each column and the action buttons.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')
            ->addIndexColumn()
            ->addColumn('channels', function (NotificationSetting $setting) {
                return $this->channelBadges($setting);
            })
            ->addColumn('is_active', function (NotificationSetting $setting) {
                return $setting->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->editColumn('created_at', function (NotificationSetting $setting) {
                return $setting->created_at->format('Y-m-d H:i');
            })
            ->addColumn('actions', function (NotificationSetting $setting) {
                if ($this->showTrashed) {
                    return view('notification-setting.actions_trashed', ['notificationSetting' => $setting]);
                }

                return view('notification-setting.actions', ['notificationSetting' => $setting]);
            })
            ->rawColumns(['channels', 'is_active']);
    }

    /**
     * Makes one small badge for each channel that is switched on for the setting.
     */
    private function channelBadges(NotificationSetting $setting): string
    {
        $badges = [];

        foreach ($setting->channels as $channel) {
            if ($channel->pivot->is_active) {
                $badges[] = '<span class="badge bg-info">'.e($channel->name).'</span>';
            }
        }

        return $badges === [] ? '<span class="text-muted">-</span>' : implode(' ', $badges);
    }

    /**
     * Picks the rows to show: trashed ones or normal ones.
     */
    public function query(NotificationSetting $model): QueryBuilder
    {
        $query = $model->newQuery()->with('channels');

        if ($this->showTrashed) {
            return $query->onlyTrashed();
        }

        return $query->withoutTrashed();
    }

    /**
     * Sets up the table (id, ajax loading, paging).
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('notification-settings-table')
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
     * Lists the columns of the table.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('SN')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('name')->orderable(true)->searchable(true),
            Column::make('event_code')->orderable(true)->searchable(true),
            Column::computed('channels')->title('Channels')->orderable(false)->searchable(false),
            Column::computed('is_active')->title('Active')->orderable(false)->searchable(false)->addClass('text-center'),
            Column::make('created_at')->orderable(true)->searchable(false)->addClass('text-center'),
            Column::computed('actions')
                ->orderable(false)
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
        ];
    }

    /**
     * Gives the file name used when the table is exported.
     */
    protected function filename(): string
    {
        return 'NotificationSettings_'.date('YmdHis');
    }
}
