@extends('layout.master')

@section('title', __('District'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('District Details') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('district.create') }}" class="btn btn-sm btn-success"><i class="ri-add-line"></i><span class="d-none d-sm-inline"> {{ __('Add New') }}</span></a>
                                <a href="{{ route('district.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Go to List') }}</span></a>
                                {{--<a href="{{ route('district.trash') }}" class="btn btn-sm btn-dark"><i class="ri-delete-bin-2-line"></i><span class="d-none d-sm-inline"> {{ __('Go to Trash') }}</span></a>--}}
                            </div>
                        </div>

                        <div class="card-body">
                            <table class="table table-sm table-hover table-responsive table-bordered">
                                <tbody>
                                <tr><th class="text-end" style="width: 40%">{{ __('Name') }}</th><td class="text-start" style="width: 60%">{{ $district->name_en ?? '' }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Name (in Bangla)') }}</th><td class="text-start" style="width: 60%">{{ $district->name_bn ?? '' }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Description') }}</th><td class="text-start" style="width: 60%">{{ ucwords($district->description_en) ?? '' }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Description (in Bangla)') }}</th><td class="text-start" style="width: 60%">{{ ucwords($district->description_bn) ?? '' }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Latitude') }}</th><td class="text-start" style="width: 60%">{{ $district->latitude ?? '' }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Longitude') }}</th><td class="text-start" style="width: 60%">{{ $district->longitude ?? '' }}</td></tr>

                                <tr><th class="text-end" style="width: 40%">{{ __('Status') }}</th>
                                    <td class="text-start" style="width: 60%">
                                        @if($district->is_active == 1)
                                            <span class="badge bg-success">{{ __('Active') }}</span>
                                        @elseif($district->is_active == 0)
                                            <span class="badge bg-danger">{{ __('Not Active') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                        @endif
                                    </td>
                                </tr>

                                <tr><th class="text-end" style="width: 40%">{{ __('Created By') }}</th><td class="text-start" style="width: 60%">{{ $district->created_by ?? '' }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Updated By') }}</th><td class="text-start" style="width: 60%">{{ $district->updated_by ?? '' }}</td></tr>

                                <tr><th class="text-end" style="width: 40%">{{ __('Created At') }}</th><td class="text-start" style="width: 60%">{{ $district->created_at->format('Y-m-d H:i:s') }}</td></tr>
                                <tr><th class="text-end" style="width: 40%">{{ __('Updated At') }}</th><td class="text-start" style="width: 60%">{{ $district->updated_at->format('Y-m-d H:i:s') }}</td></tr>

                                <tr>
                                    <th class="text-end" style="width: 40%">{{ __('Actions') }}</th>
                                    <td class="text-start" style="width: 60%">
                                        <a href="{{ route('district.edit', $district->id) }}" class="btn btn-sm btn-info"><i class="ri-edit-line"></i><span class="d-none d-sm-inline"> {{ __('Edit') }}</span></a>
                                        <button class="btn btn-sm btn-warning destroy-district" id="destroyDistrict" data-district-id="{{ $district->id }}"><i class="ri-delete-bin-line"></i><span class="d-none d-sm-inline"> {{ __('Destroy') }}</span></button>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
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
            // Click Event for Deleting the District
            $('#deleteDistrict').on('click', function () {
                var districtID = $(this).data('district-id');
                // Show SweetAlert Confirmation Dialog
                Swal.fire({
                    title: 'Are You Sure?',
                    text: 'You will not be able to Recover this Data.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'No, Cancel',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/district/' + districtID,
                            method: 'DELETE',
                            data: { "_token": "{{ csrf_token() }}"},
                            success: function (response) {
                                Swal.fire('Deleted', 'The Record has been Deleted.', 'success').then(() => { window.location.href = '/admin/district'; });
                            },
                            error: function (xhr, status, error) {
                                Swal.fire('Error!', 'There was an issue on deleting the Record.', 'error');
                            }
                        });
                    } else {
                        Swal.fire('Cancelled', 'Your Record is Safe :)', 'info');
                    }
                });
            });
        });
    </script>
@endpush
