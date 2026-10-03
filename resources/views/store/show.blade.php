@extends('layout.master')

@section('title', __('Store'))

@push('styles')
    <style>
        /* Activity Feed: Velzon's own .activity-feed/.feed-item draws its
           connector spine on the RIGHT (border-right + a dot at right:-6px) —
           built for a right-anchored layout. Flipped to the left here so the
           spine leads the eye in natural LTR reading order, left of the text. */
        .activity-feed {
            padding-left: 6px;
        }
        .activity-feed .feed-item {
            border-right: 0;
            border-left: 2px solid var(--vz-border-color);
            padding-right: 0;
            padding-left: 16px;
        }
        .activity-feed .feed-item:after {
            right: auto;
            left: -6px;
        }

        .store-detail-image {
            width: 100%;
            max-width: 220px;
            height: 220px;
            object-fit: cover;
            border-radius: var(--vz-border-radius);
        }

        .store-detail-list .list-group-item:nth-child(odd) {
            background-color: var(--vz-light);
        }
    </style>
@endpush

@section('content')
    <!-- Start Page Content -->
    <div class="page-content">
        <!-- Start Container-Fluid -->
        <div class="container-fluid">
            <!-- Start Row -->
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Store Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('store.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('store.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('store.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12 col-md-9 order-2 order-md-1">
                                    <ul class="list-group list-group-flush store-detail-list">
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ $store->isHeadOffice() ? __('Location') : __('Project Office') }}</span>
                                            <span class="text-end">{{ $store->isHeadOffice() ? __('Head Office') : $store->project?->name }}</span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ $store->isWarehouse() ? __('Warehouse Name') : __('Store Name') }}</span>
                                            <span class="text-end">
                                                {{ $store->name }}
                                                @if($store->isWarehouse())
                                                    <span class="badge bg-warning">{{ __('Warehouse') }}</span>
                                                @else
                                                    <span class="badge bg-info">{{ __('Store') }}</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Code') }}</span>
                                            <span class="text-end">{{ $store->code }}</span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Storekeeper') }}</span>
                                            <span class="text-end">{{ $store->storekeepers->pluck('name')->implode(', ') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Mobile') }}</span>
                                            <span class="text-end">{{ $store->mobile }}</span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Email') }}</span>
                                            <span class="text-end">{{ $store->email }}</span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Store Manager') }}</span>
                                            <span class="text-end">{{ $store->managers->pluck('name')->implode(', ') }}</span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Is Active') }}</span>
                                            <span class="text-end">
                                                @if($store->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($store->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2 px-0">
                                            <span class="fw-medium text-muted">{{ __('Status') }}</span>
                                            <span class="d-flex flex-wrap align-items-center gap-2">
                                                @php
                                                    $status = $store->status;
                                                    $map = [
                                                        'pending'  => ['class' => 'text-warning', 'icon' => 'ri-loader-4-line', 'label' => 'Pending'],
                                                        'approved' => ['class' => 'text-success', 'icon' => 'ri-checkbox-circle-line', 'label' => 'Approved'],
                                                        'rejected' => ['class' => 'text-danger', 'icon'  => 'ri-close-circle-line', 'label' => 'Rejected'],
                                                    ];
                                                @endphp

                                                @if($status && isset($map[$status]))
                                                    <span class="{{ $map[$status]['class'] }}"><i class="{{ $map[$status]['icon'] }}"></i> {{ __($map[$status]['label']) }}</span>
                                                @else
                                                    {{ '--' }}
                                                @endif

                                                @if(!$store->trashed())
                                                    <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#statusUpdateModal">
                                                        <i class="ri-fingerprint-line"></i>
                                                        <span class="btn-text d-none d-sm-inline">{{ __('Update Status') }}</span>
                                                    </button>
                                                @endif
                                            </span>
                                        </li>
                                    </ul>

                                    <h6 class="text-uppercase fs-13 text-muted mt-4 mb-2">{{ __('Activity') }}</h6>
                                    @php
                                        $auditLogs = [
                                            [
                                                'action' => __('Created'),
                                                'user' => $store->creator?->name,
                                                'at' => $store->created_at,
                                            ],
                                            [
                                                'action' => __('Updated'),
                                                'user' => $store->updater?->name,
                                                'at' => $store->updated_at,
                                            ],
                                        ];

                                        if ($store->trashed()) {
                                            $auditLogs[] = [
                                                'action' => __('Deleted'),
                                                'user' => $store->deleter?->name,
                                                'at' => $store->deleted_at,
                                            ];
                                        }
                                    @endphp

                                    <ul class="activity-feed mb-0 ps-2">
                                        @foreach($auditLogs as $log)
                                            <li class="feed-item">
                                                <strong>{{ $log['action'] }}</strong>
                                                @if($log['user'])
                                                    <span>{{ __('by') }} <em>{{ $log['user'] }}</em></span>
                                                @endif
                                                @if($log['at'])
                                                    <span class="small text-muted d-block">{{ $log['at']->format('d F, Y h:i A') }}</span>
                                                @endif
                                            </li>
                                        @endforeach

                                        @foreach($store->approvalLogs as $log)
                                            <li class="feed-item">
                                                <strong>{{ ucfirst($log->action_name) }}</strong>
                                                @if($log->actionedBy)
                                                    <span>{{ __('by') }} <em>{{ $log->actionedBy->name }}</em></span>
                                                @endif
                                                @if($log->actioned_at)
                                                    <span class="small text-muted d-block">{{ $log->actioned_at->format('d F, Y h:i A') }}</span>
                                                @endif
                                                @if($log->remarks)
                                                    <div class="mt-1">
                                                        <strong><em>{{ __('Remarks') }}:</em></strong>
                                                        <div class="text-break">{{ $log->remarks }}</div>
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-12 col-md-3 order-1 order-md-2 mb-3 mb-md-0">
                                    @if($store->image)
                                        <div class="text-center">
                                            <img src="{{ asset('storage/'.$store->image) }}" class="img-thumbnail store-detail-image" alt="{{ __('Store Image') }}">
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="row pt-md-2 pt-2">
                                <div class="col-12 text-start mt-2 pb-2">
                                    @if($store->trashed())
                                        <button class="btn btn-sm btn-soft-success restore-store" id="restoreStore" data-store-id="{{ $store->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                        <button class="btn btn-sm btn-danger delete-store" id="deleteStore" data-store-id="{{ $store->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                    @else
                                        <a href="{{ route('store.edit', $store->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                        <button class="btn btn-sm btn-warning destroy-store" id="destroyStore" data-store-id="{{ $store->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-labelledby="statusUpdateModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form id="statusUpdateForm" action="{{ route('store.update-status', $store->id) }}" method="POST" data-current-status="{{ $store->status }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title" id="statusUpdateModalLabel"> {{ __('Update Store Status') }} </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                            </div>
                            <hr>
                            <div class="modal-body">
                                <div id="statusUpdateError" class="alert alert-danger d-none"></div>
                                <div class="row mb-2">
                                    <label class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory"> {{ __('Select Status') }} </label>
                                    <div class="col-12 col-md-8">
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusPending" value="pending" @checked($store->status === 'pending')>
                                            <label class="form-check-label" for="statusPending"> {{ __('Pending') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusApproved" value="approved" @checked($store->status === 'approved')>
                                            <label class="form-check-label" for="statusApproved"> {{ __('Approve') }} </label>
                                        </div>
                                        <div class="form-check form-check-inline pt-2 mb-2">
                                            <input class="form-check-input" type="radio" name="status" id="statusRejected" value="rejected" @checked($store->status === 'rejected')>
                                            <label class="form-check-label" for="statusRejected"> {{ __('Reject') }} </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start" > {{ __('Add Remarks') }} </label>
                                    <div class="col-12 col-md-8 pt-2">
                                        <textarea id="remarks" name="remarks" class="form-control" rows="2" ></textarea>
                                    </div>
                                </div>
                                <input type="hidden" name="store_id" value="{{ $store->id }}">
                            </div>
                            <hr>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" > {{ __('Cancel') }} </button>
                                <button type="reset" class="btn btn-sm btn-warning" > {{ __('Reset') }} </button>
                                <button type="submit" class="btn btn-sm btn-info" id="updateStatusBtn" > {{ __('Update Status') }} </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Page Content -->
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            const csrfToken = "{{ csrf_token() }}";
            /*
            |--------------------------------------------------------------------------
            | PAGE LOAD TOAST
            |--------------------------------------------------------------------------
            | Shows Success/Failed Toast After Page Reload
            |--------------------------------------------------------------------------
            */
            const toastMessage = sessionStorage.getItem('storeToastMessage');
            const toastType = sessionStorage.getItem('storeToastType');

            if (toastMessage) {
                Toastify({
                    text: toastMessage,
                    duration: 4000,
                    gravity: "top",
                    position: "right",
                    close: true,
                    className: toastType === 'success' ? 'success-toast' : 'failed-toast',
                    stopOnFocus: true
                }).showToast();

                // Remove After Display the Toast Message
                sessionStorage.removeItem('storeToastMessage');
                sessionStorage.removeItem('storeToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE STORE
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-store', function () {
                const storeID = $(this).data('store-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Destroy this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Destroy',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/store/' + storeID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('storeToastMessage', response.message || 'Store Destroyed Successfully.');
                                sessionStorage.setItem('storeToastType', 'success');
                                window.location.href = '/store/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('storeToastMessage', message);
                                sessionStorage.setItem('storeToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | RESTORE STORE
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-store', function () {
                const storeID = $(this).data('store-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore this Data.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/store/' + storeID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('storeToastMessage', response.message || 'Store Restored Successfully.');
                                sessionStorage.setItem('storeToastType', 'success');
                                window.location.href = '/store/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('storeToastMessage', message);
                                sessionStorage.setItem('storeToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | PERMANENT DELETE STORE
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-store', function () {
                const storeID = $(this).data('store-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Permanently Delete this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/store/' + storeID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('storeToastMessage', response.message || 'Store Deleted Permanently.');
                                sessionStorage.setItem('storeToastType', 'success');
                                window.location.href = '/store/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('storeToastMessage', message);
                                sessionStorage.setItem('storeToastType', 'failed');
                                window.location.reload();
                            }
                        });

                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            /*
            |--------------------------------------------------------------------------
            | UPDATE STORE STATUS
            |--------------------------------------------------------------------------
            */
            const $storeStatusForm = $('#statusUpdateForm');
            const storeCurrentStatus = $storeStatusForm.data('current-status');
            const $storeStatusUpdateBtn = $('#updateStatusBtn');
            const $storeStatusUpdateError = $('#statusUpdateError');

            function syncStoreStatusSubmitState() {
                const selected = $storeStatusForm.find('input[name="status"]:checked').val();
                const unchanged = selected === storeCurrentStatus;

                $storeStatusUpdateBtn.prop('disabled', unchanged);

                if (unchanged) {
                    $storeStatusUpdateError.html('{{ __("Select a Different Status to Update.") }}').removeClass('d-none');
                } else {
                    $storeStatusUpdateError.addClass('d-none').html('');
                }
            }

            $(document).on('shown.bs.modal', '#statusUpdateModal', syncStoreStatusSubmitState);
            $storeStatusForm.on('change', 'input[name="status"]', syncStoreStatusSubmitState);
            syncStoreStatusSubmitState();

            $storeStatusForm.on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const button = $storeStatusUpdateBtn;
                const errorBox = $storeStatusUpdateError;
                // Clear Previous Errors
                errorBox.addClass('d-none').html('');

                const selectedStatus = form.find('input[name="status"]:checked').val();
                if (selectedStatus === storeCurrentStatus) {
                    errorBox.html('{{ __("Select a Different Status to Update.") }}').removeClass('d-none');
                    return;
                }

                // Disable Button
                button.prop('disabled', true);

                button.html('<i class="ri-loader-4-line ri-spin"></i> {{ __("Updating...") }}');

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: form.serialize(),

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS UPDATE SUCCESS
                    |--------------------------------------------------------------------------
                    */
                    success: function (response) {
                        if (response.success) {
                            sessionStorage.setItem('storeToastMessage', response.message || 'Store Status Updated Successfully.');
                            sessionStorage.setItem('storeToastType', 'success');
                            $('#statusUpdateModal').modal('hide');
                            window.location.reload();
                        }
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | STATUS UPDATE ERROR
                    |--------------------------------------------------------------------------
                    */
                    error: function (xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON?.errors || {};
                            const messages = [];
                            $.each(errors, function (field, errorMessages) {
                                if (errorMessages.length > 0) {
                                    messages.push(errorMessages[0]);
                                }
                            });

                            if (!messages.length && xhr.responseJSON?.message) {
                                messages.push(xhr.responseJSON.message);
                            }

                            errorBox.html(messages.join('<br>') || '{{ __("Validation Failed. Please Check Your Input.") }}').removeClass('d-none');
                            return;
                        }

                        const message = xhr.responseJSON?.message || 'Something went wrong. Please try again.';

                        sessionStorage.setItem('storeToastMessage', message);
                        sessionStorage.setItem('storeToastType', 'failed');
                        window.location.reload();
                    },

                    complete: function () {
                        button.prop('disabled', false);
                        button.html('{{ __("Update Status") }}');
                    }
                });
            });
        });
    </script>
@endpush
