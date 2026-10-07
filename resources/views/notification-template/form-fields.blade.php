{{-- Text fields shared by the create and edit forms. Expects optional $notificationTemplate. --}}
<div class="row mb-2">
    <label for="subject" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Subject') }}</label>
    <div class="col-12 col-md-8">
        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $notificationTemplate->subject ?? '') }}" placeholder="">
        @error('subject')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="row mb-2">
    <label for="title" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Title') }}</label>
    <div class="col-12 col-md-8">
        <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $notificationTemplate->title ?? '') }}" placeholder="">
        @error('title')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="row mb-2">
    <label for="body" class="col-12 col-md-4 col-form-label text-md-end text-start form-mandatory">{{ __('Body') }}</label>
    <div class="col-12 col-md-8">
        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="6" required>{{ old('body', $notificationTemplate->body ?? '') }}</textarea>
        @error('body')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>

<div class="row mb-2">
    <label for="variables" class="col-12 col-md-4 col-form-label text-md-end text-start">{{ __('Variables') }}</label>
    <div class="col-12 col-md-8">
        <textarea class="form-control @error('variables') is-invalid @enderror" id="variables" name="variables" rows="3">{{ old('variables', implode("\n", $notificationTemplate->variables ?? [])) }}</textarea>
        <small class="text-muted d-block">{{ __('One per line, e.g. brand_name. Use them in the text as') }} @{{brand_name}}</small>
        @error('variables')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
</div>
