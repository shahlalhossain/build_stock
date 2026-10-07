@extends('layout.master')

@section('title', __('Send Notification'))

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Send Notification') }}</h4>
                        </div>
                        <form id="manualNotificationForm" action="{{ route('notification.store') }}" method="POST">
                            @csrf
                            <div class="card-body">

                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ $error }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endforeach
                                @endif

                                <div class="row">
                                    <div class="col-12 col-md-9">
                                        <div class="row mb-2">
                                            <label for="title" class="col-12 col-md-3 col-form-label text-md-end text-start form-mandatory">{{ __('Title') }}</label>
                                            <div class="col-12 col-md-9">
                                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
                                                @error('title')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="message" class="col-12 col-md-3 col-form-label text-md-end text-start form-mandatory">{{ __('Message') }}</label>
                                            <div class="col-12 col-md-9">
                                                <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="4" maxlength="2000" required>{{ old('message') }}</textarea>
                                                @error('message')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="priority" class="col-12 col-md-3 col-form-label text-md-end text-start form-mandatory">{{ __('Priority') }}</label>
                                            <div class="col-12 col-md-9">
                                                <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                                                    @foreach ($priorities as $priority)
                                                        <option value="{{ $priority->value }}" @selected(old('priority', 'normal') === $priority->value)>{{ $priority->label() }}</option>
                                                    @endforeach
                                                </select>
                                                @error('priority')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label class="col-12 col-md-3 col-form-label text-md-end text-start form-mandatory">{{ __('Channels') }}</label>
                                            <div class="col-12 col-md-9 pt-2">
                                                @forelse ($channels as $channel)
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox" name="channels[]" id="channel_{{ $channel->id }}" value="{{ $channel->id }}"
                                                               @checked(in_array($channel->id, old('channels', [])))>
                                                        <label class="form-check-label" for="channel_{{ $channel->id }}">{{ $channel->name }}</label>
                                                    </div>
                                                @empty
                                                    <span class="text-muted">{{ __('No active channels are available.') }}</span>
                                                @endforelse
                                                @error('channels')<br><small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="users" class="col-12 col-md-3 col-form-label text-md-end text-start">{{ __('Specific Users') }}</label>
                                            <div class="col-12 col-md-9">
                                                <select class="form-select" id="users" name="users[]" multiple>
                                                    @foreach ($selectedUsers as $user)
                                                        <option value="{{ $user->id }}" selected>{{ $user->name }} ({{ $user->email }})</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-muted">{{ __('Type at least 2 letters of a name or email to search.') }}</small>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="roles" class="col-12 col-md-3 col-form-label text-md-end text-start">{{ __('Everyone with Role') }}</label>
                                            <div class="col-12 col-md-9">
                                                <select class="form-select" id="roles" name="roles[]" multiple>
                                                    @foreach ($roles as $role)
                                                        <option value="{{ $role->name }}" @selected(in_array($role->name, old('roles', [])))>{{ $role->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="scheduled_at" class="col-12 col-md-3 col-form-label text-md-end text-start">{{ __('Send Later At') }}</label>
                                            <div class="col-12 col-md-9">
                                                <input type="datetime-local" class="form-control @error('scheduled_at') is-invalid @enderror" id="scheduled_at" name="scheduled_at" value="{{ old('scheduled_at') }}">
                                                <small class="text-muted">{{ __('Leave empty to send now.') }}</small>
                                                @error('scheduled_at')<br><small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="button" class="btn btn-sm btn-info" id="previewButton"><i class="ri-eye-line"></i> {{ __('Preview') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal: shows what will be sent, then asks for the final "Send" -->
    <div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Preview') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="previewError" class="alert alert-danger d-none"></div>
                    <div id="previewContent" class="d-none">
                        <h6 id="previewTitle" class="fw-bold"></h6>
                        <p id="previewMessage" class="mb-3" style="white-space: pre-wrap;"></p>
                        <table class="table table-sm table-borderless mb-0">
                            <tr><th class="w-50">{{ __('Priority') }}</th><td id="previewPriority"></td></tr>
                            <tr><th>{{ __('Channels') }}</th><td id="previewChannels"></td></tr>
                            <tr><th>{{ __('People') }}</th><td id="previewReceivers"></td></tr>
                            <tr><th>{{ __('Deliveries') }}</th><td id="previewDeliveries"></td></tr>
                            <tr id="previewSkippedRow" class="d-none"><th>{{ __('Skipped (channel switched off by the user)') }}</th><td id="previewSkipped"></td></tr>
                            <tr><th>{{ __('Sample Receivers') }}</th><td id="previewSample"></td></tr>
                            <tr><th>{{ __('Send At') }}</th><td id="previewWhen"></td></tr>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal">{{ __('Back to Edit') }}</button>
                    <button type="button" class="btn btn-sm btn-success d-none" id="confirmSendButton"><i class="ri-send-plane-line"></i> {{ __('Send') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const form = $('#manualNotificationForm');
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));

            // Specific users: search on the server while typing, so we never load every user.
            $('#users').select2({
                width: '100%',
                placeholder: '{{ __('Search users...') }}',
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('notification.receiver-search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) { return { q: params.term }; },
                    processResults: function (data) { return data; }
                }
            });
            $('#roles').select2({ width: '100%', placeholder: '{{ __('Select roles...') }}' });

            // Asks the server for a preview of what would be sent.
            $('#previewButton').on('click', function () {
                $('#previewError').addClass('d-none').empty();
                $('#previewContent').addClass('d-none');
                $('#confirmSendButton').addClass('d-none');

                $.ajax({
                    url: "{{ route('notification.preview') }}",
                    method: 'POST',
                    data: form.serialize(),
                    success: function (response) {
                        const p = response.preview;
                        $('#previewTitle').text(p.title);
                        $('#previewMessage').text(p.message);
                        $('#previewPriority').text(p.priority);
                        $('#previewChannels').text(p.channels.join(', '));
                        $('#previewReceivers').text(p.receiver_count);
                        $('#previewDeliveries').text(p.delivery_count);
                        $('#previewSkipped').text(p.skipped_by_preference);
                        $('#previewSkippedRow').toggleClass('d-none', p.skipped_by_preference === 0);
                        $('#previewSample').text(p.sample_receivers.join(', ') + (p.receiver_count > p.sample_receivers.length ? ', ...' : ''));
                        $('#previewWhen').text(p.scheduled_at || '{{ __('Now') }}');
                        $('#previewContent').removeClass('d-none');
                        $('#confirmSendButton').toggleClass('d-none', p.delivery_count === 0);
                        modal.show();
                    },
                    error: function (xhr) {
                        let text = xhr.responseJSON?.message || 'There was an Issue on Building the Preview.';
                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            text = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        $('#previewError').html(text).removeClass('d-none');
                        modal.show();
                    }
                });
            });

            // Final step: really send.
            $('#confirmSendButton').on('click', function () {
                $(this).prop('disabled', true);
                form.trigger('submit');
            });
        });
    </script>
@endpush
