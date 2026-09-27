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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Receive') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('product-receive.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('product-receive.store') }}" method="POST">
                            @csrf
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

                                @if($transfersData->isEmpty())
                                    <div class="alert alert-warning">{{ __('No Approved Transfers with Remaining Quantity to Receive are Available.') }}</div>
                                @endif

                                <div class="row">
                                    <!-- Start Left Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="transfer_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Transfer') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select id="transfer_id" name="transfer_id" class="form-select @error('transfer_id') is-invalid @enderror" required @if($transfersData->isEmpty()) disabled @endif>
                                                    <option value="">{{ __('== Select Transfer ==') }}</option>
                                                    @foreach($transfersData as $transfer)
                                                        <option value="{{ $transfer['id'] }}" @selected(old('transfer_id') == $transfer['id'])>{{ $transfer['code'] }} — {{ __('to') }} {{ $transfer['destination_store'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error('transfer_id')<small class="text-danger">{{ $message }}</small>@enderror
                                                <div class="form-text">{{ __('Only Approved Transfers with a Remaining Quantity are Listed.') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->

                                    <!-- Start Right Column -->
                                    <div class="col-12 col-md-6">
                                        <div class="row mb-2">
                                            <label for="transaction_date" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Receive Date') }}</label>
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
                                    <!-- End Right Column -->
                                </div>

                                <hr>

                                <!-- ===================== RECEIVE ITEMS ===================== -->
                                <div class="d-flex align-items-center mb-2">
                                    <h5 class="mb-0 flex-grow-1 fst-italic">{{ __('Receive Items') }}</h5>
                                </div>
                                @error('items')<small class="text-danger d-block mb-2">{{ $message }}</small>@enderror
                                <div id="no-transfer-selected" class="text-muted">{{ __('Select a Transfer Above to List its Remaining Items.') }}</div>
                                <div class="table-responsive d-none" id="items-table-wrapper">
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
                                        <button type="submit" class="btn btn-sm btn-info">{{ __('Create') }}</button>
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
            const transfersData = @json($transfersData);

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

            /*
            |--------------------------------------------------------------------------
            | AUTO-POPULATE ITEMS FROM SELECTED TRANSFER
            |--------------------------------------------------------------------------
            */
            function populateFromTransfer(transferId) {
                $('#items-rows').empty();

                const transfer = transfersData.find(t => String(t.id) === String(transferId));

                if (!transfer || !transfer.lines.length) {
                    $('#items-table-wrapper').addClass('d-none');
                    $('#no-transfer-selected').removeClass('d-none');
                    return;
                }

                $('#no-transfer-selected').addClass('d-none');
                $('#items-table-wrapper').removeClass('d-none');

                transfer.lines.forEach(function (line) {
                    addRow();
                    const $row = $('.repeater-row[data-group="items"]').last();

                    $row.find('.item-product-label').text(line.product_label);
                    $row.find('.item-variant-label').text(line.variant_label);
                    $row.find('.item-sent-qty').text(line.sent_quantity);
                    $row.find('.item-remaining-qty').text(line.remaining_quantity);
                    $row.find('.item-transfer-item-id').val(line.transfer_item_id);
                    $row.find('.item-received-quantity').attr('max', line.remaining_quantity).val(line.remaining_quantity);
                    $row.find('.item-unit').val(line.unit_id);
                });

                reindexItemRows();
            }

            $('#transfer_id').on('change', function () {
                populateFromTransfer($(this).val());
            });

            // Re-seed on validation-error redisplay if a Transfer was already chosen.
            const initialTransferId = $('#transfer_id').val();
            if (initialTransferId) {
                populateFromTransfer(initialTransferId);
            }
        });
    </script>
@endpush
