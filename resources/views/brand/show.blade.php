@extends('layout.master')

@section('title', __('Brand'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Brand Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('brand.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('brand.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('brand.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-hover table-responsive table-bordered table-sm">
                                        <tbody>
                                        <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $brand->name }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Slug') }}</th><td class="text-start ps-2">{{ $brand->slug }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Priority Order') }}</th><td class="text-start ps-2">{{ $brand->priority_order }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Status') }}</th>
                                            <td class="text-start ps-2">
                                                @if($brand->is_active == 1)
                                                    <span class="badge bg-success">{{ __('Active') }}</span>
                                                @elseif($brand->is_active == 0)
                                                    <span class="badge bg-warning">{{ __('Not Active') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ ucwords($brand->description) }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $brand->creator?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $brand->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $brand->updater?->name ?? '' }}</td></tr>
                                        <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $brand->updated_at->format('Y-m-d H:i:s') }}</td></tr>
                                        </tbody>
                                    </table>

                                    <div class="text-start mt-2 pb-2">
                                        @if($brand->trashed())
                                            <button class="btn btn-sm btn-soft-success restore-brand" id="restoreBrand" data-brand-id="{{ $brand->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                            <button class="btn btn-sm btn-danger delete-brand" id="deleteBrand" data-brand-id="{{ $brand->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                        @else
                                            <a href="{{ route('brand.edit', $brand->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                            <button class="btn btn-sm btn-warning destroy-brand" id="destroyBrand" data-brand-id="{{ $brand->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
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

            $(document).on('click', '.destroy-brand', function () {
                var brandID = $(this).data('brand-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Destroy this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/brand/' + brandID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                Swal.fire('Destroyed', 'The Record has been Destroyed.', 'success')
                                    .then(() => { window.location.href = '/brand'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an issue deleting the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe.', 'info');
                    }
                });
            });

            $(document).on('click', '.restore-brand', function () {
                const brandID = $(this).data('brand-id');
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You want to Restore Record?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Restore',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/brand/' + brandID + '/restore',
                            method: 'POST',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record is Now in Active List.', 'success')
                                    .then(() => { window.location.href = '/brand/trash'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Restoring the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is in Trash Box.', 'info');
                    }
                });
            });

            $(document).on('click', '.delete-brand', function () {
                const brandID = $(this).data('brand-id');
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
                            url: '/brand/' + brandID + '/force-delete',
                            method: 'DELETE',
                            data: { "_token": csrfToken },
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record has been Deleted.', 'success')
                                    .then(() => { window.location.href = '/brand/trash'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an Issue on Deleting the Record.', 'error');
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
