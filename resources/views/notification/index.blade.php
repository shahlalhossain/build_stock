@extends('layout.master')

@section('title', __('Notifications'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            <!-- Dashboard numbers for the last 30 days -->
            <div class="row">
                <div class="col-6 col-md-3">
                    <div class="card"><div class="card-body">
                        <p class="text-muted mb-1">{{ __('Notifications') }}</p>
                        <h3 class="mb-0">{{ $summary['notifications']['total'] }}</h3>
                        <small class="text-muted">
                            {{ __('Completed') }} {{ $summary['notifications']['completed'] }} ·
                            {{ __('Partial') }} {{ $summary['notifications']['partial'] }} ·
                            {{ __('Failed') }} {{ $summary['notifications']['failed'] }} ·
                            {{ __('In progress') }} {{ $summary['notifications']['in_progress'] }}
                        </small>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card"><div class="card-body">
                        <p class="text-muted mb-1">{{ __('Deliveries') }}</p>
                        <h3 class="mb-0">{{ $summary['deliveries']['total'] }}</h3>
                        <small class="text-muted">{{ __('Waiting') }} {{ $summary['deliveries']['waiting'] }} · {{ __('Cancelled') }} {{ $summary['deliveries']['cancelled'] }}</small>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card"><div class="card-body">
                        <p class="text-muted mb-1">{{ __('Sent / Delivered') }}</p>
                        <h3 class="mb-0 text-success">{{ $summary['deliveries']['succeeded'] }}</h3>
                        <small class="text-muted">
                            {{ __('Success rate') }}:
                            {{ $summary['deliveries']['success_rate'] === null ? __('No data') : $summary['deliveries']['success_rate'].'%' }}
                        </small>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card"><div class="card-body">
                        <p class="text-muted mb-1">{{ __('Failed Deliveries') }}</p>
                        <h3 class="mb-0 text-danger">{{ $summary['deliveries']['failed'] }}</h3>
                        @can('notification-log.index')
                            <small><a href="{{ route('notification-log.index', ['event' => 'failed']) }}">{{ __('See failure history') }}</a></small>
                        @endcan
                    </div></div>
                </div>
            </div>
            <p class="text-muted small">
                {{ __('Numbers cover the last :days days (since :date). Success rate = (sent + delivered) ÷ (sent + delivered + failed). "Sent" means handed to the provider, not necessarily seen by the user.', ['days' => $summary['days'], 'date' => $summary['since']->format('Y-m-d')]) }}
            </p>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notifications') }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification.send')
                                    <a href="{{ route('notification.create') }}" class="btn btn-sm btn-success"><i class="ri-send-plane-line"></i><span class="d-none d-sm-inline"> {{ __('Send Notification') }}</span></a>
                                @endcan
                                @can('notification-log.index')
                                    <a href="{{ route('notification-log.index') }}" class="btn btn-sm btn-dark"><i class="ri-file-list-3-line"></i><span class="d-none d-sm-inline"> {{ __('Logs') }}</span></a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-2">
                                    <select id="filter_type" class="form-select form-select-sm">
                                        <option value="">{{ __('All Types') }}</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <select id="filter_status" class="form-select form-select-sm">
                                        <option value="">{{ __('All Statuses') }}</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-3">
                                    <input type="text" id="filter_event" class="form-control form-control-sm" placeholder="{{ __('Event, e.g. brand.created') }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="date" id="filter_date_from" class="form-control form-control-sm" title="{{ __('From') }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="date" id="filter_date_to" class="form-control form-control-sm" title="{{ __('To') }}">
                                </div>
                                <div class="col-12 col-md-1 d-grid">
                                    <button type="button" id="resetFilters" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                </div>
                            </div>

                            <table id="notifications-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        $(document).ready(function () {
            // Reload the table whenever a filter changes (the text box waits until typing stops).
            let typingTimer;
            $('#filter_type, #filter_status, #filter_date_from, #filter_date_to').on('change', reloadTable);
            $('#filter_event').on('keyup', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(reloadTable, 400);
            });
            $('#resetFilters').on('click', function () {
                $('#filter_type, #filter_status, #filter_event, #filter_date_from, #filter_date_to').val('');
                reloadTable();
            });

            function reloadTable() {
                $('#notifications-table').DataTable().ajax.reload();
            }
        });
    </script>
@endpush
