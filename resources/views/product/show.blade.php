@extends('layout.master')

@section('title', __('Product'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Product Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('product.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                <a href="{{ route('product.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="row">

                                <div class="row">
                                    <div class="col-12 col-md-7 order-1">
                                        <table class="table table-hover table-responsive table-bordered table-sm">
                                            <tbody>
                                            <tr><th class="text-end pe-2">{{ __('Name') }}</th><td class="text-start ps-2">{{ $product->name }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Code') }}</th><td class="text-start ps-2">{{ $product->code }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('SKU') }}</th><td class="text-start ps-2">{{ $product->sku ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Category') }}</th><td class="text-start ps-2">{{ $product->category?->name }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Sub-Category') }}</th><td class="text-start ps-2">{{ $product->subCategory?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Brand') }}</th><td class="text-start ps-2">{{ $product->brand?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Unit') }}</th><td class="text-start ps-2">{{ $product->unit ? $product->unit->name.' ('.$product->unit->symbol.')' : '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Description') }}</th><td class="text-start ps-2">{{ $product->description ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Is Active') }}</th>
                                                <td class="text-start ps-2">
                                                    @if($product->is_active == 1)
                                                        <span class="badge bg-success">{{ __('Yes') }}</span>
                                                    @elseif($product->is_active == 0)
                                                        <span class="badge bg-warning">{{ __('No') }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>

                                        <div class="text-start mt-2 pb-2">
                                            @if($product->trashed())
                                                <button class="btn btn-sm btn-soft-success restore-product" id="restoreProduct" data-product-id="{{ $product->id }}"><i class="ri-recycle-line"></i><span class="d-none d-sm-inline"> {{ __('Restore') }}</span></button>
                                                <button class="btn btn-sm btn-danger delete-product" id="deleteProduct" data-product-id="{{ $product->id }}"><i class="ri-close-line"></i><span class="d-none d-sm-inline"> {{ __('Delete') }}</span></button>
                                            @else
                                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                                <button class="btn btn-sm btn-warning destroy-product" id="destroyProduct" data-product-id="{{ $product->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-5 ps-5 order-2">
                                        <table class="table table-hover table-responsive table-bordered table-sm">
                                            <tbody>
                                            <tr><th class="text-end pe-2">{{ __('Created By') }}</th><td class="text-start ps-2">{{ $product->creator?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Created At') }}</th><td class="text-start ps-2">{{ $product->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Updated By') }}</th><td class="text-start ps-2">{{ $product->updater?->name ?? '' }}</td></tr>
                                            <tr><th class="text-end pe-2">{{ __('Updated At') }}</th><td class="text-start ps-2">{{ $product->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                            @if($product->trashed())
                                                <tr><th class="text-end pe-2">{{ __('Deleted By') }}</th><td class="text-start ps-2">{{ $product->deleter?->name ?? '' }}</td></tr>
                                                <tr><th class="text-end pe-2">{{ __('Deleted At') }}</th><td class="text-start ps-2">{{ $product->deleted_at->format('Y-m-d H:i:s') }}</td></tr>
                                            @endif
                                            </tbody>
                                        </table>

{{--                                        @if($product->approvalLogs->isNotEmpty())--}}
{{--                                            @foreach($product->approvalLogs as $log)--}}
{{--                                                <hr style="padding: 0 !important; margin: 0 !important;">--}}
{{--                                                <div class="pb-2 pt-2">--}}
{{--                                                    <strong>{{ ucfirst($log->action_name) }}</strong>--}}

{{--                                                    @if($log->actionedBy)--}}
{{--                                                        by <em>{{ $log->actionedBy->name }}</em>--}}
{{--                                                    @endif--}}

{{--                                                    @if($log->actioned_at)--}}
{{--                                                        on {{ $log->actioned_at->format('d F, Y h:i A') }}--}}
{{--                                                    @endif--}}
{{--                                                    <br>--}}
{{--                                                    @if($log->remarks)--}}
{{--                                                        <strong><em>Remarks: </em></strong> {{ $log->remarks }}--}}
{{--                                                    @endif--}}
{{--                                                </div>--}}
{{--                                            @endforeach--}}
{{--                                            <hr style="padding: 0 !important; margin: 0 !important;">--}}
{{--                                        @else--}}
{{--                                            <p>No Approval History Found</p>--}}
{{--                                        @endif--}}
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <h6 class="fw-bold fst-italic">{{ __('Specifications') }}</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Attribute') }}</th>
                                        <th>{{ __('Values') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($product->attributeValues->groupBy('attribute.name') as $attributeName => $values)
                                        <tr>
                                            <td>{{ $attributeName }}</td>
                                            <td>{{ $values->pluck('value')->implode(', ') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="2" class="text-center">{{ __('No Specifications Added') }}</td></tr>
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
            const toastMessage = sessionStorage.getItem('productToastMessage');
            const toastType = sessionStorage.getItem('productToastType');

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
                sessionStorage.removeItem('productToastMessage');
                sessionStorage.removeItem('productToastType');
            }

            /*
            |--------------------------------------------------------------------------
            | DESTROY / SOFT DELETE PRODUCT
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.destroy-product', function () {
                const productID = $(this).data('product-id');
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
                            url: '/product/' + productID,
                            type: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productToastMessage', response.message || 'Product Destroyed Successfully.');
                                sessionStorage.setItem('productToastType', 'success');
                                window.location.href = '/product/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue destroying the Record.';
                                sessionStorage.setItem('productToastMessage', message);
                                sessionStorage.setItem('productToastType', 'failed');
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
            | RESTORE PRODUCT
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.restore-product', function () {
                const productID = $(this).data('product-id');
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
                            url: '/product/' + productID + '/restore',
                            method: 'POST',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productToastMessage', response.message || 'Product Restored Successfully.');
                                sessionStorage.setItem('productToastType', 'success');
                                window.location.href = '/product/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue restoring the Record.';
                                sessionStorage.setItem('productToastMessage', message);
                                sessionStorage.setItem('productToastType', 'failed');
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
            | PERMANENT DELETE PRODUCT
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.delete-product', function () {
                const productID = $(this).data('product-id');
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
                            url: '/product/' + productID + '/force-delete',
                            method: 'DELETE',
                            data: { _token: csrfToken },
                            success: function (response) {
                                sessionStorage.setItem('productToastMessage', response.message || 'Product Deleted Permanently.');
                                sessionStorage.setItem('productToastType', 'success');
                                window.location.href = '/product/trash';
                            },
                            error: function (xhr) {
                                const message = xhr.responseJSON?.message || 'There was an issue deleting the Record.';
                                sessionStorage.setItem('productToastMessage', message);
                                sessionStorage.setItem('productToastType', 'failed');
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
