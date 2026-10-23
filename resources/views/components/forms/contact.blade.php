@props(['page','isDemo'=>false])
<form class="contact-form" action="{{ $isDemo ? '#' : route('leads.store', absolute:false) }}" method="post" data-lead-form>
    @if($isDemo)<div class="alert">Chế độ demo: form không gửi hoặc lưu dữ liệu.</div>@else
        @csrf<input type="hidden" name="page_id" value="{{ $page->id }}"><input type="hidden" name="form_token" value="{{ Crypt::encryptString((string) time()) }}">
    @endif
    @if(!$isDemo && session('success'))<div class="alert" role="status">{{ session('success') }}</div>@endif
    @if(!$isDemo && $errors->any())<div class="alert alert-error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <div class="form-grid"><x-forms.input name="full_name" label="Họ và tên" autocomplete="name" required maxlength="120" placeholder="Tên của bạn" :disabled="$isDemo" />
        <x-forms.input name="phone" label="Số điện thoại" type="tel" autocomplete="tel" maxlength="24" placeholder="Số điện thoại liên hệ" :disabled="$isDemo" />
    </div>
    <x-forms.input name="email" label="Email" type="email" autocomplete="email" maxlength="160" placeholder="ban@congty.vn" :disabled="$isDemo" />
    <label class="field"><span>Bạn đang ấp ủ điều gì?</span><textarea name="message" rows="3" maxlength="4000" placeholder="Chia sẻ một chút về dự án của bạn…" @disabled($isDemo)>{{ $isDemo ? '' : old('message') }}</textarea></label>
    @foreach(config('starter.leads.extra_fields') as $name => $field)<x-forms.input :name="'extra['.$name.']'" :label="$field['label']" :disabled="$isDemo" />@endforeach
    <div class="honeypot" aria-hidden="true"><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div>
    @if(config('starter.leads.consent'))<label class="consent-field"><input type="checkbox" name="consent" value="1" required @disabled($isDemo)><span>Tôi đồng ý để thông tin được sử dụng nhằm phản hồi yêu cầu liên hệ này.</span></label>@endif
    @if(!$isDemo && config('starter.leads.captcha_site_key'))<div class="cf-turnstile" data-sitekey="{{ config('starter.leads.captcha_site_key') }}"></div><script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>@endif
    <button class="button button-dark" type="submit" @disabled($isDemo)><span data-submit-label>{{ $isDemo ? 'Form trình diễn' : 'Gửi lời nhắn' }}</span><span aria-hidden="true">↗</span></button>
    <p class="form-note">Cung cấp số điện thoại hoặc email để chúng tôi phản hồi.</p><div data-form-status role="status" tabindex="-1"></div>
</form>
