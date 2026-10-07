@extends('layout.master')

@section('title', __('Notification Template'))

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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('Notification Template Edit & Update') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('notification-template.edit', $notificationTemplate->id) }}" class="btn btn-sm btn-warning">
                                    <i class="ri-refresh-line"></i><span class="d-none d-sm-inline"> {{ __('Reload') }}</span>
                                </a>
                                <a href="{{ route('notification-template.index') }}" class="btn btn-sm btn-primary">
                                    <i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span>
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('notification-template.update', $notificationTemplate->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PATCH')
                            <div class="card-body">

                                <!-- Start Page Error Section -->
                                @if (session('error'))
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        {{ session('error') }}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                @endif
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
                                    <div class="col-12 col-md-7">
                                        <div class="row mb-2">
                                            <label for="notification_setting_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Setting') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select class="form-select" id="notification_setting_id" disabled>
                                                    <option>{{ $notificationTemplate->setting?->name }} ({{ $notificationTemplate->setting?->event_code }})</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="channel_id" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Channel') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select class="form-select" id="channel_id" disabled>
                                                    <option>{{ $notificationTemplate->channel?->name }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        @include('notification-template.form-fields')

                                        <div class="row mb-2">
                                            <label for="is_active" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Active') }}</label>
                                            <div class="col-12 col-md-8 pt-2">
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" @checked(old('is_active', $notificationTemplate->is_active))>
                                                @error('is_active')<small class="text-danger d-block">{{ $message }}</small>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End Left Column -->
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-6 text-start">
                                        <a href="{{ route('notification-template.index') }}" class="btn btn-sm btn-danger">{{ __('Cancel') }}</a>
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
