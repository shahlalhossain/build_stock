@extends('layout.master')

@section('title', __('Notification Details'))

@php
    // Badge colour for a status word (used for the notification and for each delivery).
    $badge = fn (string $status) => match ($status) {
        'completed', 'sent', 'delivered' => 'bg-success',
        'partial', 'pending', 'queued', 'processing' => 'bg-warning',
        'failed' => 'bg-danger',
        default => 'bg-secondary',
    };
@endphp

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification') }} #{{ $notification->id }}</h4>
                            <div class="flex-shrink-0">
                                @can('notification.index')
                                    <a href="{{ route('notification.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                                @endcan
                                @can('notification.send')
                                    <a href="{{ route('notification.create') }}" class="btn btn-sm btn-success"><i class="ri-send-plane-line"></i><span class="d-none d-sm-inline"> {{ __('Send Another') }}</span></a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tr><th class="text-end w-25">{{ __('Type') }}</th><td>{{ $notification->type->label() }}</td></tr>
                                    <tr><th class="text-end">{{ __('Event') }}</th><td>{{ $notification->event_code ?? '—' }} @if($notification->setting)<small class="text-muted">({{ $notification->setting->name }})</small>@endif</td></tr>
                                    <tr><th class="text-end">{{ __('Title') }}</th><td>{{ $notification->title }}</td></tr>
                                    <tr><th class="text-end">{{ __('Message') }}</th><td style="white-space: pre-wrap;">{{ $notification->message_body }}</td></tr>
                                    <tr><th class="text-end">{{ __('Priority') }}</th><td>{{ $notification->priority->label() }}</td></tr>
                                    <tr><th class="text-end">{{ __('Status') }}</th><td><span class="badge {{ $badge($notification->status->value) }}">{{ $notification->status->label() }}</span></td></tr>
                                    <tr><th class="text-end">{{ __('Created By') }}</th><td>{{ $notification->creator?->name ?? __('System') }}</td></tr>
                                    <tr><th class="text-end">{{ __('Created At') }}</th><td>{{ $notification->created_at->format('Y-m-d H:i') }}</td></tr>
                                    @if ($notification->scheduled_at)
                                        <tr><th class="text-end">{{ __('Scheduled At') }}</th><td>{{ $notification->scheduled_at->format('Y-m-d H:i') }}</td></tr>
                                    @endif
                                    <tr>
                                        <th class="text-end">{{ __('Deliveries') }}</th>
                                        <td>
                                            @forelse ($statusCounts as $status => $total)
                                                <span class="badge {{ $badge($status) }}">{{ ucfirst($status) }}: {{ $total }}</span>
                                            @empty
                                                —
                                            @endforelse
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">{{ __('Deliveries') }} <small class="text-muted">({{ __('first 100 of :total', ['total' => $receiverTotal]) }})</small></h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle mb-0">
                                    <thead>
                                    <tr>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Channel') }}</th>
                                        <th class="text-center">{{ __('Status') }}</th>
                                        <th class="text-center">{{ __('Attempts') }}</th>
                                        <th>{{ __('Sent At') }}</th>
                                        <th>{{ __('Next Retry') }}</th>
                                        <th>{{ __('Provider Message ID') }}</th>
                                        <th>{{ __('Error') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($receivers as $receiver)
                                        <tr>
                                            <td>{{ $receiver->user?->name }}</td>
                                            <td>{{ $receiver->channel?->name }}</td>
                                            <td class="text-center"><span class="badge {{ $badge($receiver->status->value) }}">{{ $receiver->status->label() }}</span></td>
                                            <td class="text-center">{{ $receiver->attempts }}</td>
                                            <td>{{ $receiver->sent_at?->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $receiver->next_retry_at?->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $receiver->provider_message_id }}</td>
                                            <td class="text-danger">{{ $receiver->error_message }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="8" class="text-center text-muted">{{ __('No deliveries.') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><h5 class="card-title mb-0">{{ __('Timeline') }} <small class="text-muted">({{ __('latest 50 steps') }})</small></h5></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead>
                                    <tr><th>{{ __('Time') }}</th><th>{{ __('User') }}</th><th>{{ __('Channel') }}</th><th>{{ __('Step') }}</th><th>{{ __('Details') }}</th></tr>
                                    </thead>
                                    <tbody>
                                    @forelse ($logs as $log)
                                        <tr>
                                            <td>{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                            <td>{{ $log->receiver?->user?->name }}</td>
                                            <td>{{ $log->receiver?->channel?->name }}</td>
                                            <td>{{ $log->event }}</td>
                                            <td>{{ $log->message }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">{{ __('No history yet.') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
