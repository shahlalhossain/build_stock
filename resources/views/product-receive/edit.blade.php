@extends('layout.master')

@section('title', __('Product Receive'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Receive Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-receive.edit', $productReceive->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('product-receive.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('product-receive.update', $productReceive->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="card-body">

                                <!-- Start Page Error Section -->
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            {{ $error }}
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    @endforeach
                                @endif
                                <!-- End Page Error Section -->

                                <div class="row">
                                    <!-- Start Left Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Transfer') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control" readonly tabindex="-1" value="{{ $productReceive->transfer?->code }} — {{ __('to') }} {{ $productReceive->transfer?->destinationStore?->name }}">
                                                <div class="form-text">{{ __('The Transfer a Receive is Against cannot be Changed.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="transaction_date" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Receive Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', optional($productReceive->transaction_date)->format('Y-m-d')) }}" required>
                                                @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Remarks') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks', $productReceive->remarks) }}</textarea>
                                                @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== RECEIVE ITEMS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Receive Items') }}</h5>
                                </div>
                                @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" id="items-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 22%;">{{ __('Product') }}</th>
                                            <th style="width: 15%;">{{ __('Variant') }}</th>
                                            <th style="width: 10%;">{{ __('Sent Qty') }}</th>
                                            <th style="width: 10%;">{{ __('Remaining') }}</th>
                                            <th style="width: 13%;">{{ __('Received Qty') }}</th>
                                            <th style="width: 12%;">{{ __('Unit') }}</th>
                                            <th style="width: 18%;">{{ __('Variance Remarks') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody id="items-rows"></tbody>
                                    </table>
                                </div>

                                <template id="items-row-template">
                                    <tr class="repeater-row" data-group="items">
                                        <td class="item-product-label" data-label="{{ __('Product') }}"></td>
                                        <td class="item-variant-label" data-label="{{ __('Variant') }}"></td>
                                        <td class="item-sent-qty" data-label="{{ __('Sent Qty') }}"></td>
                                        <td class="item-remaining-qty" data-label="{{ __('Remaining') }}"></td>
                                        <td data-label="{{ __('Received Qty') }}">
                                            <input type="number" step="0.01" min="0" class="form-control item-received-quantity" data-field="received_quantity" placeholder="{{ __('Received Qty') }}">
                                            <input type="hidden" class="item-transfer-item-id" data-field="transfer_item_id">
                                        </td>
                                        <td data-label="{{ __('Unit') }}">
                                            <select class="form-select item-unit" data-field="unit_id">
                                                <option value="">{{ __('== Unit ==') }}</option>
                                                @foreach($units as $unit)
                                                    <option value="{{ $unit->id }}">{{ $unit->name }} @if($unit->symbol) ({{ $unit->symbol }}) @endif</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td data-label="{{ __('Variance Remarks') }}">
                                            <input type="text" class="form-control item-variance-remarks" data-field="variance_remarks" placeholder="{{ __('e.g. Breakage in Transit') }}">
                                        </td>
                                    </tr>
                                </template>

                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('product-receive.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                        <button type="reset" class="btn btn-sm btn-warning">{{ __('Reset') }}</button>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Update') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@php
    // Precomputed outside @json(...) — Blade's Directive-Argument Parser tracks
    // only Parenthesis Depth, so a multi-line Array Literal (with [...]) nested
    // inside @json(...) can close the Directive early at the first ')' it finds
    // inside the Expression, truncating the compiled Output (see the PHP Parse
    // Error this caused: "Unclosed '[' does not match ')'").
    $transferItemsByIdForJs = $productReceive->transfer->items->keyBy('id')->map(fn ($item) => [
        'product_label' => $item->product?->name.($item->product?->code ? ' ('.$item->product->code.')' : ''),
        'variant_label' => $item->productVariant?->variant_name ?? '',
        'sent_quantity' => (float) $item->quantity,
        'unit_id' => $item->unit_id,
    ]);

    $existingItemsForJs = $productReceive->items->map(fn ($item) => [
        'transfer_item_id' => $item->transfer_item_id,
        'unit_id' => $item->unit_id,
        'received_quantity' => $item->received_quantity,
        'variance_remarks' => $item->variance_remarks,
    ]);
@endphp

@push('scripts')
    <script>
        $(document).ready(function () {
            const transferItemsById = @json($transferItemsByIdForJs ?? []);

            const existingItems = @json($existingItemsForJs ?? []);

            function addRow() {
                const template = document.getElementById('items-row-template').innerHTML;
                $('#items-rows').append(template);
            }

            function reindexItemRows() {
                $('.repeater-row[data-group="items"]').each(function (rowIndex) {
                    $(this).find('[data-field]').each(function () {
                        $(this).attr('name', 'items[' + rowIndex + '][' + $(this).data('field') + ']');
                    });
                });
            }

            existingItems.forEach(function (item) {
                addRow();
                const $row = $('.repeater-row[data-group="items"]').last();
                const transferItem = transferItemsById[item.transfer_item_id] || {};

                $row.find('.item-product-label').text(transferItem.product_label || '');
                $row.find('.item-variant-label').text(transferItem.variant_label || '');
                $row.find('.item-sent-qty').text(transferItem.sent_quantity ?? '');
                // Remaining shown here already accounts for this Receive's OWN current
                // quantity (excluded from the "remaining" calc while still Pending).
                $row.find('.item-remaining-qty').text('--');
                $row.find('.item-transfer-item-id').val(item.transfer_item_id);
                $row.find('.item-received-quantity').val(item.received_quantity);
                $row.find('.item-unit').val(item.unit_id);
                $row.find('.item-variance-remarks').val(item.variance_remarks);
            });

            reindexItemRows();
        });
    </script>
@endpush
