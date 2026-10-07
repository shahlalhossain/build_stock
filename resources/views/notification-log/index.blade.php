@extends('layout.master')

@section('title', __('Notification Logs'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification Logs') }}</h4>
                            <div class="flex-shrink-0">
                                <button type="button" class="btn btn-sm btn-danger" id="showFailures"><i class="ri-error-warning-line"></i><span class="d-none d-sm-inline"> {{ __('Failure History') }}</span></button>
                                @can('notification.index')
                                    <a href="{{ route('notification.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Notification List') }}</span></a>
                                @endcan
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Filters -->
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-2">
                                    <input type="date" id="filter_date_from" class="form-control form-control-sm" title="{{ __('From') }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="date" id="filter_date_to" class="form-control form-control-sm" title="{{ __('To') }}">
                                </div>
                                <div class="col-6 col-md-2">
                                    <select id="filter_event" class="form-select form-select-sm">
                                        <option value="">{{ __('All Events') }}</option>
                                        @foreach ($events as $event)
                                            <option value="{{ $event }}" @selected(request('event') === $event)>{{ $event }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <select id="filter_channel" class="form-select form-select-sm">
                                        <option value="">{{ __('All Channels') }}</option>
                                        @foreach ($channels as $channel)
                                            <option value="{{ $channel->id }}">{{ $channel->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <select id="filter_status" class="form-select form-select-sm">
                                        <option value="">{{ __('All Statuses') }}</option>
                                        @foreach (\App\Enums\NotificationReceiverStatus::cases() as $status)
                                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <input type="number" min="1" id="filter_notification_id" class="form-control form-control-sm" placeholder="{{ __('Notification ID') }}">
                                </div>
                                <div class="col-8 col-md-4">
                                    <input type="text" id="filter_user" class="form-control form-control-sm" placeholder="{{ __('User name or email') }}">
                                </div>
                                <div class="col-4 col-md-2 d-grid">
                                    <button type="button" id="resetFilters" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                </div>
                            </div>

                            <table id="notification-logs-table" class="table table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%"></table>
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
            let typingTimer;
            $('#filter_date_from, #filter_date_to, #filter_event, #filter_channel, #filter_status').on('change', reloadTable);
            $('#filter_notification_id, #filter_user').on('keyup', function () {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(reloadTable, 400);
            });
            $('#showFailures').on('click', function () {
                $('#filter_event').val('failed');
                reloadTable();
            });
            $('#resetFilters').on('click', function () {
                $('#filter_date_from, #filter_date_to, #filter_event, #filter_channel, #filter_status, #filter_notification_id, #filter_user').val('');
                reloadTable();
            });

            function reloadTable() {
                $('#notification-logs-table').DataTable().ajax.reload();
            }
        });
    </script>
@endpush
