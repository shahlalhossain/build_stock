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
                            <h4 class="card-title mb-0 flex-grow-1">{{ __('New Notification Template Create') }}</h4>
                            <div class="flex-shrink-0">
                                <a href="{{ route('notification-template.index') }}" class="btn btn-sm btn-primary"><i class="ri-list-check-2"></i><span class="d-none d-sm-inline"> {{ __('Back to List') }}</span></a>
                            </div>
                        </div>
                        <form action="{{ route('notification-template.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
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
                                            <label for="notification_setting_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Setting') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select class="form-select @error('notification_setting_id') is-invalid @enderror" id="notification_setting_id" name="notification_setting_id" required>
                                                    <option value="">{{ __('Select Setting') }}</option>
                                                    @foreach ($settings as $setting)
                                                        <option value="{{ $setting->id }}" @selected(old('notification_setting_id', request('notification_setting_id')) == $setting->id)>{{ $setting->name }} ({{ $setting->event_code }})</option>
                                                    @endforeach
                                                </select>
                                                @error('notification_setting_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="channel_id" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Channel') }}</label>
                                            <div class="col-12 col-md-8">
                                                <select class="form-select @error('channel_id') is-invalid @enderror" id="channel_id" name="channel_id" required>
                                                    <option value="">{{ __('Select Channel') }}</option>
                                                    @foreach ($channels as $channel)
                                                        <option value="{{ $channel->id }}" @selected(old('channel_id', request('channel_id')) == $channel->id)>{{ $channel->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('channel_id')<small class="text-danger">{{ $message }}</small>@enderror
                                            </div>
                                        </div>

                                        @include('notification-template.form-fields')
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
