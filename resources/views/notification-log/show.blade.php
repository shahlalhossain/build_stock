@extends('layout.master')

@section('title', __('Notification Log'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification Log') }} #{{ $log->id }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('notification-log.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tr><th class="text-end w-25">{{ __('Time') }}</th><td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td></tr>
                                    <tr>
                                        <th class="text-end">{{ __('Notification') }}</th>
                                        <td>
                                            @can('notification.show')
                                                <a href="{{ route('notification.show', $log->receiver?->notification_message_id) }}">#{{ $log->receiver?->notification_message_id }}</a>
                                            @else
                                                #{{ $log->receiver?->notification_message_id }}
                                            @endcan
                                        </td>
                                    </tr>
                                    <tr><th class="text-end">{{ __('User') }}</th><td>{{ $log->receiver?->user?->name }}</td></tr>
                                    <tr><th class="text-end">{{ __('Channel') }}</th><td>{{ $log->receiver?->channel?->name }}</td></tr>
                                    <tr><th class="text-end">{{ __('Event') }}</th><td>{{ $log->event }}</td></tr>
                                    <tr><th class="text-end">{{ __('Status') }}</th><td>{{ $log->status }}</td></tr>
                                    <tr><th class="text-end">{{ __('Message') }}</th><td style="white-space: pre-wrap;">{{ $log->message }}</td></tr>
                                    <tr><th class="text-end">{{ __('Request') }}</th><td><pre class="mb-0">{{ $log->request_payload ? json_encode($log->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre></td></tr>
                                    <tr><th class="text-end">{{ __('Response') }}</th><td><pre class="mb-0">{{ $log->response_payload ? json_encode($log->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '—' }}</pre></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
