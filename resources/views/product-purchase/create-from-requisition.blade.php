@extends('layout.master')

@section('title', __('Purchase against Requisition'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Purchase against Requisition') }} — {{ $requisition->code }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-purchase.requisition-list') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('product-purchase.store-from-requisition') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="requisition_id" value="{{ $requisition->id }}">
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
                                            <label class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Requisition') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control" value="{{ $requisition->code }}" readonly tabindex="-1">
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="store_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Receiving Store') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="store_id" name="store_id" class="form-select @error('store_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Store ==') }}</option>
                                                    @foreach($stores as $store)
                                                        <option value="{{ $store->id }}" @selected(old('store_id', $requisition->store_id) == $store->id)>{{ ucwords($store->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('store_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="supplier_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Supplier') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="supplier_id" name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                                    <option value="">{{ __('== Select Supplier ==') }}</option>
                                                    @foreach($suppliers as $supplier)
                                                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ ucwords($supplier->name) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('supplier_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="transaction_date" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Purchase Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" id="transaction_date" name="transaction_date" value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                                @error('transaction_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="remarks" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Remarks') }}</label>
                                            <div class="col-12 col-md-8">
                                                <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                                                @error('remarks')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="invoice_number" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Invoice Number') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="{{ __('Supplier Invoice Number') }}" required>
                                                @error('invoice_number')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="supplier_invoice_date" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Invoice Date') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="date" class="form-control @error('supplier_invoice_date') is-invalid @enderror" id="supplier_invoice_date" name="supplier_invoice_date" value="{{ old('supplier_invoice_date') }}">
                                                @error('supplier_invoice_date')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="invoice_attachment" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Invoice Attachment') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="file" class="form-control @error('invoice_attachment') is-invalid @enderror" id="invoice_attachment" name="invoice_attachment" accept=".pdf,.jpg,.jpeg,.png">
                                                <div class="form-text">{{ __('Optional. PDF, JPG or PNG — Max 10 MB.') }}</div>
                                                @error('invoice_attachment')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="payment_status" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Payment Status') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="payment_status" name="payment_status" class="form-select @error('payment_status') is-invalid @enderror">
                                                    <option value="unpaid" @selected(old('payment_status', 'unpaid') === 'unpaid')>{{ __('Unpaid') }}</option>
                                                    <option value="partial" @selected(old('payment_status') === 'partial')>{{ __('Partial') }}</option>
                                                    <option value="paid" @selected(old('payment_status') === 'paid')>{{ __('Paid') }}</option>
                                                </select>
                                                @error('payment_status')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="paid_amount" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Paid Amount') }}</label>
                                            <div class="col-12 col-md-8">
                                                <input type="number" step="0.01" min="0" class="form-control @error('paid_amount') is-invalid @enderror" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', 0) }}">
                                                @error('paid_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== REQUISITION ITEMS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Requisition Items') }}</h5>
                                </div>
                                <div class="form-text mb-2">{{ __('All Products from this Requisition are Listed. Fully Purchased Products are Disabled. Uncheck a Row to Skip it, or Adjust Quantity down to Purchase Partially. Quantity cannot Exceed the Remaining Amount.') }}</div>
                                @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" id="items-table">
                                        <thead>
                                        <tr>
                                            <th style="width: 4%;" class="text-center">{{ __('Include') }}</th>
                                            <th style="width: 19%;">{{ __('Product') }}</th>
                                            <th style="width: 13%;">{{ __('Variant') }}</th>
                                            <th style="width: 11%;">{{ __('Requisitioned') }}</th>
                                            <th style="width: 11%;">{{ __('Remaining') }}</th>
                                            <th style="width: 12%;">{{ __('Quantity to Purchase') }}</th>
                                            <th style="width: 10%;">{{ __('Unit Cost') }}</th>
                                            <th style="width: 11%;">{{ __('Total Cost') }}</th>
                                            <th style="width: 13%;">{{ __('Status') }}</th>
                                        </tr>
                                        </thead>
                                        <tbody id="items-rows">
                                        @foreach($requisition->items as $index => $item)
                                            @php
                                                $remaining = $item->remaining_quantity;
                                                $isFullyPurchased = $remaining <= 0;
                                                $variantLabel = $item->productVariant ? ($item->productVariant->variant_name ?: $item->productVariant->attributeValues->pluck('value')->implode(' / ')) : '';
                                            @endphp
                                            <tr class="requisition-item-row @if($isFullyPurchased) table-light text-muted @endif">
                                                <td class="text-center">
                                                    <input type="checkbox" class="form-check-input item-include" @checked(! $isFullyPurchased) @disabled($isFullyPurchased)>
                                                </td>
                                                <td data-label="{{ __('Product') }}">
                                                    {{ $item->product?->name }}
                                                    @unless($isFullyPurchased)
                                                        <input type="hidden" name="items[{{ $index }}][requisition_item_id]" value="{{ $item->id }}">
                                                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                                                    @endunless
                                                </td>
                                                <td data-label="{{ __('Variant') }}">
                                                    {{ $variantLabel ?: '—' }}
                                                    @unless($isFullyPurchased)
                                                        <input type="hidden" name="items[{{ $index }}][product_variant_id]" value="{{ $item->product_variant_id }}">
                                                    @endunless
                                                </td>
                                                <td data-label="{{ __('Requisitioned') }}" class="text-end">
                                                    {{ number_format((float) $item->quantity, 2) }} {{ $item->unit?->symbol }}
                                                    @unless($isFullyPurchased)
                                                        <input type="hidden" name="items[{{ $index }}][unit_id]" value="{{ $item->unit_id }}">
                                                    @endunless
                                                </td>
                                                <td data-label="{{ __('Remaining') }}" class="text-end fw-bold">
                                                    {{ number_format($remaining, 2) }} {{ $item->unit?->symbol }}
                                                </td>
                                                <td data-label="{{ __('Quantity to Purchase') }}">
                                                    <input type="number" step="0.01" min="0.01" max="{{ $remaining }}"
                                                           class="form-control item-quantity"
                                                           name="items[{{ $index }}][quantity]"
                                                           value="{{ old("items.$index.quantity", $isFullyPurchased ? 0 : $remaining) }}"
                                                           @disabled($isFullyPurchased)>
                                                </td>
                                                <td data-label="{{ __('Unit Cost') }}">
                                                    <input type="number" step="0.01" min="0" class="form-control item-unit-cost" name="items[{{ $index }}][unit_cost]" value="{{ old("items.$index.unit_cost") }}" @disabled($isFullyPurchased)>
                                                </td>
                                                <td data-label="{{ __('Total Cost') }}">
                                                    <input type="text" class="form-control item-total-cost" readonly tabindex="-1">
                                                </td>
                                                <td data-label="{{ __('Status') }}" class="text-center">
                                                    @if($isFullyPurchased)
                                                        <span class="badge bg-secondary">{{ __('Fully Purchased') }}</span>
                                                    @else
                                                        <span class="badge bg-success">{{ __('Available') }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>

                                <!-- ===================== TOTALS ===================== -->
                                <div class="row justify-content-end">
                                    <div class="col-12 col-md-5">
                                        <div class="row mb-2">
                                            <label class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Total Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="text" class="form-control" id="purchase_total_amount_display" readonly tabindex="-1" value="0.00">
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="discount_type" class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Discount') }}</label>
                                            <div class="col-6 col-md-4">
                                                <select id="discount_type" name="discount_type" class="form-select @error('discount_type') is-invalid @enderror">
                                                    <option value="" @selected(old('discount_type') === null)>{{ __('None') }}</option>
                                                    <option value="fixed" @selected(old('discount_type') === 'fixed')>{{ __('Fixed') }}</option>
                                                    <option value="percentage" @selected(old('discount_type') === 'percentage')>{{ __('Percentage') }}</option>
                                                </select>
                                                @error('discount_type')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <input type="number" step="0.01" min="0" class="form-control @error('discount_amount') is-invalid @enderror" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', 0) }}">
                                                @error('discount_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="tax_amount" class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Tax Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="number" step="0.01" min="0" class="form-control @error('tax_amount') is-invalid @enderror" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', 0) }}">
                                                @error('tax_amount')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label class="col-12 col-md-5 col-form-label text-md-end text-start">{{ __('Net Amount') }}</label>
                                            <div class="col-12 col-md-7">
                                                <input type="text" class="form-control fw-bold" id="purchase_net_amount_display" readonly tabindex="-1" value="0.00">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('product-purchase.requisition-list') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
                                    </div>
                                    <div class="col-6 text-end">
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Create Purchase') }}</button>
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

@push('scripts')
    <script>
        $(document).ready(function () {
            /*
            |--------------------------------------------------------------------------
            | INCLUDE/EXCLUDE ROW — Unchecking a Row Disables its Inputs so it is NOT
            | Submitted (Partial Product Selection out of the Requisition's Full List).
            |--------------------------------------------------------------------------
            */
            function toggleRow($row) {
                const included = $row.find('.item-include').is(':checked');
                $row.find('input:not(.item-include)').prop('disabled', !included);
                $row.toggleClass('table-light text-muted', !included);
            }

            $(document).on('change', '.item-include', function () {
                toggleRow($(this).closest('.requisition-item-row'));
                syncPurchaseTotals();
            });

            /*
            |--------------------------------------------------------------------------
            | LINE ITEM TOTAL COST + PURCHASE TOTALS
            |--------------------------------------------------------------------------
            */
            function syncItemTotalCost($row) {
                const quantity = parseFloat($row.find('.item-quantity').val());
                const unitCost = parseFloat($row.find('.item-unit-cost').val());
                const $total = $row.find('.item-total-cost');

                if (isNaN(quantity) || isNaN(unitCost)) {
                    $total.val('');
                    return;
                }

                $total.val((quantity * unitCost).toFixed(2));
            }

            function syncPurchaseTotals() {
                let totalAmount = 0;
                $('.requisition-item-row').each(function () {
                    if (!$(this).find('.item-include').is(':checked')) {
                        return;
                    }
                    const lineTotal = parseFloat($(this).find('.item-total-cost').val());
                    if (!isNaN(lineTotal)) {
                        totalAmount += lineTotal;
                    }
                });

                const discountType = $('#discount_type').val();
                const discountAmount = parseFloat($('#discount_amount').val()) || 0;
                const taxAmount = parseFloat($('#tax_amount').val()) || 0;

                const discountValue = discountType === 'percentage'
                    ? totalAmount * (discountAmount / 100)
                    : discountAmount;

                const netAmount = totalAmount - discountValue + taxAmount;

                $('#purchase_total_amount_display').val(totalAmount.toFixed(2));
                $('#purchase_net_amount_display').val(netAmount.toFixed(2));
            }

            $(document).on('input', '.item-quantity, .item-unit-cost', function () {
                syncItemTotalCost($(this).closest('.requisition-item-row'));
                syncPurchaseTotals();
            });

            $(document).on('input change', '#discount_type, #discount_amount, #tax_amount', syncPurchaseTotals);

            $('.requisition-item-row').each(function () {
                syncItemTotalCost($(this));
            });
            syncPurchaseTotals();
        });
    </script>
@endpush
