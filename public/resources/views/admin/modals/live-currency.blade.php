<form method="post" action="{{route('admin.preference.setting.update') }}">
    @csrf
    @method('put')
    <div class="modal-body modal-padding-bottom">
        <div class="form-group align-items-center">
            <label for="api_access_key" class="form-control-label">{{ __('API Access Key') }}</label>
            <input name="live_api_currency" value="1" type="hidden">
            <input type="text" class="form-control" name="live_currency_access_key" placeholder="{{ __('API Access Key') }}" value="{{ settingHelper('live_currency_access_key') }}" required />
            <div class="invalid-feedback">
                <p><a href="https://manage.exchangeratesapi.io/dashboard" target="_blank">{{ __('Click Here to get Key') }}</a></p>
            </div>
        </div>
    </div>
    <div class="modal-footer modal-padding-bottom">
        <button type="submit" class="btn btn-outline-primary">{{ __('Save') }}</button>
    </div>
</form>

