@php($services = $salon['categories']->flatMap->services)
@php($selectedService = $isDemo ? null : old('extra.service_id', is_scalar(request('service')) ? request('service') : null))
<form class="ks-form" action="{{ route($booking ? 'salon.booking' : 'salon.contact', absolute:false) }}" method="post" data-salon-form data-sending="{{ $copy['form']['sending_label'] }}" data-rate-limit="{{ $copy['form']['rate_limit'] }}" data-send-error="{{ $copy['form']['send_error'] }}" @if($isDemo) data-static-preview="true" @endif>
    @if(!$isDemo)
        @csrf<input type="hidden" name="page_id" value="{{ $page->id }}"><input type="hidden" name="form_token" value="{{ Crypt::encryptString((string)time()) }}">
    @endif
    @if(!$isDemo && session('success'))<div class="ks-form-alert" role="status">{{ session('success') }}</div>@endif
    @if(!$isDemo && $errors->any())<div class="ks-form-alert is-error" role="alert"><strong>{{ $copy['form']['error_heading'] }}</strong>@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <fieldset>
    @if($booking)
        <input type="hidden" name="extra[form]" value="booking">
        <div class="ks-form-step"><span>01</span><h3>Your little luxury</h3></div>
        <label class="ks-field"><span>Choose a service <b aria-hidden="true">*</b></span><select name="extra[service_id]" required data-booking-service><option value="">Find your perfect treatment</option>@foreach($salon['categories'] as $category)<optgroup label="{{ $category->name }}">@foreach($category->services as $service)<option value="{{ $service->id }}" data-price="{{ $service->priceLabel() }}" data-gel="{{ $service->gel_price ? '$'.number_format((float)$service->gel_price,0) : '' }}" @selected((string)$selectedService===(string)$service->id)>{{ $service->name }} — {{ $service->priceLabel() }}</option>@endforeach</optgroup>@endforeach</select></label>
        <div class="ks-selected-service" data-selected-service hidden><span>YOUR SELECTED SERVICE</span><strong data-selected-name></strong><small data-selected-price></small></div>
        <div class="ks-form-step"><span>02</span><h3>Your preferred moment</h3></div>
        <div class="ks-form-grid"><label class="ks-field"><span>Preferred date <b aria-hidden="true">*</b></span><input type="date" name="extra[date]" min="{{ now('America/New_York')->toDateString() }}" max="{{ now('America/New_York')->addDays(90)->toDateString() }}" value="{{ $isDemo ? '' : old('extra.date') }}" required></label><label class="ks-field"><span>Preferred time of day <b aria-hidden="true">*</b></span><select name="extra[time]" required><option value="">Choose a preference</option>@foreach(['morning'=>'Morning','afternoon'=>'Afternoon','evening'=>'Evening','flexible'=>'I am flexible'] as $key=>$label)<option value="{{ $key }}" @selected(!$isDemo && old('extra.time')===$key)>{{ $label }}</option>@endforeach</select></label></div>
        <p class="ks-field-note">Salon local time · America/New_York. Availability will be confirmed by our team.</p>
        <div class="ks-form-step"><span>03</span><h3>A way to reach you</h3></div>
    @endif
    <div class="ks-form-grid"><x-forms.input name="full_name" :label="$copy['form']['name_label']" autocomplete="name" :placeholder="$copy['form']['name_placeholder']" maxlength="120" required :static-preview="$isDemo"/><x-forms.input name="phone" :label="$copy['form']['phone_label']" type="tel" autocomplete="tel" :placeholder="$copy['form']['phone_placeholder']" maxlength="24" :required="$booking" :static-preview="$isDemo"/></div>
    <x-forms.input name="email" :label="$copy['form']['email_label']" type="email" autocomplete="email" :placeholder="$copy['form']['email_placeholder']" maxlength="160" :static-preview="$isDemo"/>
    @if(!$booking)<p class="ks-field-note">{{ $copy['form']['contact_note'] }}</p>@endif
    <label class="ks-field"><span>{{ $booking ? 'Anything you would like us to know? (optional)' : $copy['form']['message_label'] }}</span><textarea name="{{ $booking ? 'notes' : 'message' }}" rows="3" maxlength="{{ $booking ? 2500 : 4000 }}" placeholder="{{ $booking ? 'A design idea, gel polish preference, or a little note for our team…' : $copy['form']['message_placeholder'] }}">{{ $isDemo ? '' : old($booking ? 'notes' : 'message') }}</textarea></label>
    <div class="ks-honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
    <label class="ks-consent"><input type="checkbox" name="consent" value="1" required><span>{{ $copy['form']['consent'] }}</span></label>
    @if(!$isDemo && config('starter.leads.captcha_site_key'))<div class="cf-turnstile" data-sitekey="{{ config('starter.leads.captcha_site_key') }}"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
    <button type="submit" class="ks-button ks-submit"><span data-submit-label>{{ $booking ? 'Request My Appointment' : $copy['form']['submit_label'] }}</span></button>
    </fieldset>
    <div class="ks-form-status" data-form-status role="status" tabindex="-1"></div>
    <p class="ks-form-fineprint">{{ $booking ? 'An appointment request is not a confirmed reservation.' : $copy['form']['fineprint'] }} <button type="button" data-open-dialog="appointment-policy">{{ $copy['global']['policy_label'] }}</button></p>
</form>
