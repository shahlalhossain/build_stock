@extends('layout.master')

@section('title', __('Unit Conversion'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Unit Conversion Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('unit-conversion.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('unit-conversion.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('unit-conversion.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Product') }}</th><td class="text-start ps-2">{{ $unitConversion->product?->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Base Unit') }}</th><td class="text-start ps-2">{{ $unitConversion->product?->unit?->name }} @if($unitConversion->product?->unit?->symbol) ({{ $unitConversion->product->unit->symbol }}) @endif</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Transaction Unit') }}</th><td class="text-start ps-2">{{ $unitConversion->unit?->name }} @if($unitConversion->unit?->symbol) ({{ $unitConversion->unit->symbol }}) @endif</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Conversion Factor') }}</th><td class="text-start ps-2">1 {{ $unitConversion->unit?->symbol }} = {{ rtrim(rtrim(number_format((float) $unitConversion->factor_to_base, 6), '0'), '.') }} {{ $unitConversion->product?->unit?->symbol }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                            <td class="text-start ps-2">
                                                @if($unitConversion->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Yes') }}</span>
                                                @elseif($unitConversion->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('No') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $unitConversion->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $unitConversion->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $unitConversion->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $unitConversion->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                        @if($unitConversion->trashed())
                                            <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $unitConversion->deleter?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $unitConversion->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                        @endif
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($unitConversion->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-unit-conversion" id="restoreUnitConversion" data-unit-conversion-id="{{ $unitConversion->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-unit-conversion" id="deleteUnitConversion" data-unit-conversion-id="{{ $unitConversion->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('unit-conversion.edit', $unitConversion->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-unit-conversion" id="destroyUnitConversion" data-unit-conversion-id="{{ $unitConversion->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                        </div>
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

            const toastMessage = sessionStorage.getItem('unitConversionToastMessage');
            const toastType = sessionStorage.getItem('unitConversionToastType');

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

                sessionStorage.removeItem('unitConversionToastMessage');
                sessionStorage.removeItem('unitConversionToastType');
            }

            $(document).on('click', '.destroy-unit-conversion', function () {
                const unitConversionID = $(this).data('unit-conversion-id');
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
                            url: '/unit-conversion/' + unitConversionID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('unitConversionToastMessage', response.message || 'Unit Conversion Destroyed Successfully.');
                                sessionStorage.setItem('unitConversionToastType', 'success');
                                window.location.href = '/unit-conversion/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('unitConversionToastMessage', message);
                                sessionStorage.setItem('unitConversionToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-unit-conversion', function () {
                const unitConversionID = $(this).data('unit-conversion-id');
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
                            url: '/unit-conversion/' + unitConversionID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('unitConversionToastMessage', response.message || 'Unit Conversion Restored Successfully.');
                                sessionStorage.setItem('unitConversionToastType', 'success');
                                window.location.href = '/unit-conversion/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('unitConversionToastMessage', message);
                                sessionStorage.setItem('unitConversionToastType', 'failed');
                                window.location.reload();
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-unit-conversion', function () {
                const unitConversionID = $(this).data('unit-conversion-id');
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
                            url: '/unit-conversion/' + unitConversionID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('unitConversionToastMessage', response.message || 'Unit Conversion Deleted Permanently.');
                                sessionStorage.setItem('unitConversionToastType', 'success');
                                window.location.href = '/unit-conversion/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('unitConversionToastMessage', message);
                                sessionStorage.setItem('unitConversionToastType', 'failed');
                                window.location.reload();
                            }
                        });

                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });
        });
    </script>
@endpush
