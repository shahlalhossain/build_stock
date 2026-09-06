@extends('layout.master')

@section('title', __('Audit Logs'))

@push('styles')
    <style>
        .success-toast {
            background: linear-gradient(135deg, #00c9a7, #1f3c88);
            color: #fff;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .failed-toast {
            background: linear-gradient(135deg, #ff922b, #e8590c);
            color: #fff;
            border-radius: 4px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
        }

        .calendar-icon {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            font-size: 18px;
            color: #6c757d;
            pointer-events: none;
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
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Audit Logs') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('audit-log', 'all') }}" class="btn btn-sm btn-soft-primary"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> All </span></a>
                                <a href="{{ route('audit-log', 'created') }}" class="btn btn-sm btn-soft-success"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> Created</span></a>
                                <a href="{{ route('audit-log', 'updated') }}" class="btn btn-sm btn-soft-info"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> Updated</span></a>
                                <a href="{{ route('audit-log', 'deleted') }}" class="btn btn-sm btn-soft-warning"><i class="ri-list-ordered"></i><span class="d-none d-sm-inline"> Deleted</span></a>
                                <button type="button" class="btn btn-sm btn-soft-secondary" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="ri-filter-3-line"></i><span class="d-none d-sm-inline"> Filter</span>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div id="audit-log-wrapper">
                                <div class="accordion custom-accordionwithicon-plus custom-accordionwithicon custom-accordion-border accordion-border-box accordion-success" id="auditAccordion">
                                    @foreach($auditLogs as $auditLog)
                                        <div class="accordion-item mb-2">
                                            <h2 class="accordion-header" id="heading-{{ $auditLog->id }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    {{-- Accordion Toggle --}}
                                                    <button class="accordion-button collapsed flex-grow-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $auditLog->id }}" aria-expanded="false">
                                                        <strong>{{ $auditLog->log_name }}</strong> &nbsp;has been {{ ucwords($auditLog->event) }} by &nbsp; <strong>{{ $auditLog->user?->name ?? 'System' }}</strong> &nbsp;at {{ $auditLog->created_at->format('d M Y, h:i A') }}
                                                    </button>
                                                    {{-- Delete Button (NO TOGGLE) --}}
                                                    <button class="btn btn-sm btn-danger ms-2 me-2 delete-audit-log" data-audit-log-id="{{ $auditLog->id }}" title="Delete"><i class="ri-delete-bin-line"></i></button>
                                                </div>
                                            </h2>

                                            {{-- Collapsible Details --}}
                                            <div id="collapse-{{ $auditLog->id }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ $auditLog->id }}" data-bs-parent="#auditAccordion">
                                                <div class="accordion-body p-2">
                                                    <strong class="p-2">Event:</strong>
                                                    {{ $auditLog->log_name }} {{ ucwords($auditLog->event) }}

                                                    <div class="divider"></div>

                                                    @if(in_array($auditLog->event, ['created', 'updated']) && $auditLog->properties)

                                                        @php
                                                            $properties = is_array($auditLog->properties) ? $auditLog->properties : json_decode($auditLog->properties, true);
                                                            $old = $properties['old'] ?? [];
                                                            $new = $properties['attributes'] ?? [];
                                                        @endphp

                                                        @if(count($old))
                                                            <div class="table-responsive p-2">
                                                                <table class="table table-sm table-bordered mb-0 w-auto">
                                                                    <thead class="table-light">
                                                                    <tr>
                                                                        <th class="p-2">Field</th>
                                                                        <th class="p-2">Before</th>
                                                                        <th class="p-2">After</th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @foreach($old as $key => $value)
                                                                        <tr>
                                                                            <td class="p-2"><strong>{{ $key }}</strong></td>
                                                                            <td class="p-2 text-danger">
                                                                                @if(in_array($key, ['created_at','updated_at','deleted_at']) && $value)
                                                                                    {{ $newValue !== '-' ? \Carbon\Carbon::parse($newValue)->format('Y-m-d h:i:s A') : '-' }}
                                                                                @else
                                                                                    {{ is_bool($value) ? (int)$value : $value }}
                                                                                @endif
                                                                            </td>

                                                                            <td class="p-2 text-success">
                                                                                @php $newValue = $new[$key] ?? '-' @endphp
                                                                                @if(in_array($key, ['created_at','updated_at','deleted_at']) && $newValue)
                                                                                    {{ $newValue !== '-' ? \Carbon\Carbon::parse($newValue)->format('Y-m-d h:i:s A') : '-' }}
                                                                                @else
                                                                                    {{ is_bool($newValue) ? (int)$newValue : $newValue }}
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <p class="mb-0 p-2 text-muted">No Field Changes Found</p>
                                                        @endif
                                                    @else
                                                        <p class="mb-0 p-2 text-muted">No Details Available</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                {{ $auditLogs->links() }}
                            </div>
                        </div>
                    </div>

                    <div class="modal zoomIn" id="filterModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('Filter Audit Logs') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <hr style="margin-bottom: 0 !important;">
                                <form method="GET" action="{{ route('audit-log', $type ?? 'all') }}">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-xxl-6 col-12">
                                                <label for="from_date" class="form-label">{{ __('From Date') }}</label>
                                                <div class="position-relative">
                                                    <input type="text" id="from_date" name="from_date" value="{{ request('from_date') }}" class="form-control pe-5" readonly>
                                                    <i class="ri-calendar-todo-fill calendar-icon"></i>
                                                </div>
                                            </div>
                                            <div class="col-xxl-6 col-12">
                                                <label for="to_date" class="form-label">{{ __('To Date') }}</label>
                                                <div class="position-relative">
                                                    <input type="text" id="to_date" name="to_date" value="{{ request('to_date') }}" class="form-control pe-5" readonly>
                                                    <i class="ri-calendar-todo-fill calendar-icon"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr style="margin-top: 0 !important;">
                                    <div class="modal-footer">
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Apply Filter') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
            <!-- End Row -->
        </div>
        <!-- End Container-Fluid -->
    </div>
    <!-- End Page Content -->
@endsection

@push('scripts')
    <script>
        const fromDatePicker = flatpickr("#from_date", {
            dateFormat: "Y-m-d",
            defaultDate: "{{ request('from_date') }}",
            onChange: function (selectedDates) {
                toDatePicker.set('minDate', selectedDates[0]);
            }
        });

        const toDatePicker = flatpickr("#to_date", {
            dateFormat: "Y-m-d",
            defaultDate: "{{ request('to_date') }}"
        });

        $(document).on('click', '.delete-audit-log', function() {
            const auditLogID = $(this).data('audit-log-id');
            Swal.fire({
                title: 'Are You Sure?',
                text: 'You want to Delete this Data',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                cancelButtonText: 'No, Cancel',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/admin/audit-log/' + auditLogID + '/delete/',
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}"},
                        success: function (response) {
                            $('#audit-log-wrapper').load(location.href + ' #audit-log-wrapper > *');
                            Toastify({
                                text: response.message || "Login Log Deleted Successfully.",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                close: true,
                                className: "success-toast",
                                stopOnFocus: true
                            }).showToast();
                        },
                        error: function (xhr, status, error) {
                            Toastify({
                                text: "There was an Issue on Deleting the Record.",
                                duration: 4000,
                                gravity: "top",
                                position: "right",
                                close: true,
                                className: "failed-toast",
                                stopOnFocus: true
                            }).showToast();
                        }
                    });
                }
            });
        });
    </script>
@endpush
