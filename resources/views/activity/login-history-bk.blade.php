@extends('layout.master')

@section('title', __('Login History'))

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
                            <h4 class="card-title mb-0 flex-grow-1">Login History</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('login-history') }}" class="btn btn-sm btn-soft-primary"><i class="ri-list-ordered"></i> All Login History</a>
                                <a href="{{ route('login-history') }}" class="btn btn-sm btn-soft-info"><i class="ri-list-ordered"></i> Active Login History</a>
                                <a href="{{ route('login-history') }}" class="btn btn-sm btn-soft-warning"><i class="ri-list-ordered"></i> Expire Login History</a>
                                <a href="{{ route('login-history') }}" class="btn btn-sm btn-soft-success"><i class="ri-list-ordered"></i> My Login History</a>
                            </div>
                        </div>

                        <div class="card-body">
                            <table id="login-history-table" class="table table-sm table-hover table-bordered dt-responsive nowrap table-striped align-middle" style="width:100%">

                                <thead>
                                <tr>
                                    <th class="text-center">{{ __('SN') }}</th>
                                    <th>User</th>
                                    <th>IP</th>
                                    <th>OS</th>
                                    <th>Browser</th>
                                    <th>Device</th>
                                    <th>Login At</th>
                                    <th class="text-center">Active</th>
                                    <th>Logout At</th>
{{--                                    <th class="text-center">Action</th>--}}
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($loginHistories as $key => $loginHistory)
                                    <tr>
                                        <td class="text-center">{{ $key + 1 }}</td>
                                        <td>{{ $loginHistory->user_id }}</td>
                                        <td>{{ $loginHistory->ip_address }}</td>
                                        <td>{{ $loginHistory->os }}</td>
                                        <td>{{ $loginHistory->browser }}</td>
                                        <td>{{ $loginHistory->device }}</td>
                                        <td>{{ $loginHistory->login_at }}</td>
                                        <td class="text-center">
                                            @if($loginHistory->is_active == 1)
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @elseif($loginHistory->is_active == 0)
                                                <span class="badge bg-danger">{{ __('No') }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ __('Unknown') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $loginHistory->logout_at }}</td>
{{--                                        <td class="text-center">--}}
{{--                                            <a href="{{ route('login-history-details', $loginHistory->id) }}" class="btn btn-sm btn-soft-dark">{{ __('Details') }}</a>--}}
{{--                                            @if($loginHistory->is_active == 1 && is_null($loginHistory->logout_at))--}}
{{--                                                <a href="{{ route('logout', $loginHistory->id) }}" class="btn btn-sm btn-soft-danger">{{ __('Logout') }}</a>--}}
{{--                                            @endif--}}
{{--                                        </td>--}}
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>

                            {{ $loginHistories->onEachSide(3)->links() }}

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
{{--    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}--}}
@endpush
